(function($) {
    'use strict';

    // Estado del widget
    let isOpen = false;
    let messages = [];
    let messageCount = 0;
    let leadSubmitted = false;
    let isLoading = false;

    // Inicializar cuando el DOM esté listo
    $(function() {
        init();
    });

    function init() {
        // Botón principal del chat
        $('#ap-btn').on('click', toggle);

        // Notificación flotante
        $('#ap-notification').on('click', open);

        // Enviar mensaje
        $('#ap-send').on('click', sendMessage);
        $('#ap-input input').on('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Acciones rápidas
        $('#ap-quick button').on('click', function() {
            const question = $(this).data('q');
            $('#ap-input input').val(question);
            sendMessage();
        });

        // Modal de lead
        $('#ap-modal form').on('submit', submitLead);
        $('#ap-ask-lead').on('click', function() {
            $('#ap-modal').addClass('open');
        });
        $('#ap-modal .cancel').on('click', function() {
            $('#ap-modal').removeClass('open');
        });

        // Cerrar modal con Escape
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                $('#ap-modal').removeClass('open');
            }
        });
    }

    function toggle() {
        isOpen ? close() : open();
    }

    function open() {
        isOpen = true;
        $('#ap-btn').addClass('open');
        $('#ap-chat').addClass('open');
        $('#ap-notification').addClass('hide');

        // Mostrar saludo inicial si no hay mensajes
        if (messages.length === 0) {
            showGreeting();
        }

        setTimeout(function() {
            $('#ap-input input').focus();
        }, 100);
    }

    function close() {
        isOpen = false;
        $('#ap-btn').removeClass('open');
        $('#ap-chat').removeClass('open');
    }

    function showGreeting() {
        const greeting = '¡Hola! 👋 Soy el Asesor Previsional Virtual de ' + 
            (asesorPrevisional.advisorName || 'Luis Barría Chodil') + 
            ' (' + (asesorPrevisional.advisorCode || 'Cod. 1360 SPensiones') + '). ' +
            'Estoy aquí para ayudarte con tus consultas sobre pensiones en Chile. ¿En qué puedo ayudarte hoy?';
        
        addMessage('assistant', greeting);
    }

    function sendMessage() {
        const input = $('#ap-input input');
        const message = input.val().trim();

        if (!message || isLoading) return;

        // Limpiar input
        input.val('');
        input[0].style.height = 'auto';

        // Agregar mensaje del usuario
        addMessage('user', message);
        messageCount++;

        // Mostrar CTA de lead después de 2 mensajes
        if (messageCount >= 2 && !leadSubmitted) {
            $('#ap-lead').slideDown();
        }

        // Ocultar acciones rápidas después del primer mensaje
        if (messageCount >= 1) {
            $('#ap-quick').slideUp();
        }

        // Mostrar indicador de escritura
        showTyping();
        isLoading = true;

        // Preparar mensajes para enviar
        const messagesToSend = messages.map(m => ({
            role: m.role,
            content: m.content
        }));

        // Enviar al servidor
        $.ajax({
            url: asesorPrevisional.ajaxUrl,
            method: 'POST',
            data: {
                action: 'ap_chat',
                nonce: asesorPrevisional.nonce,
                messages: JSON.stringify(messagesToSend),
                isNew: messageCount === 1 ? 'true' : 'false'
            },
            success: function(response) {
                hideTyping();
                isLoading = false;

                if (response.success && response.data && response.data.message) {
                    addMessage('assistant', response.data.message);
                } else {
                    addMessage('assistant', 'Lo siento, hubo un error. Por favor intenta de nuevo o contacta directamente a nuestro asesor al WhatsApp +56 9 3446 0825.');
                }
            },
            error: function() {
                hideTyping();
                isLoading = false;
                addMessage('assistant', 'Lo siento, hubo un error de conexión. Por favor intenta de nuevo o contacta a Luis Barría al WhatsApp +56 9 3446 0825.');
            }
        });
    }

    function addMessage(role, text) {
        messages.push({ role: role, content: text });

        const time = new Date().toLocaleTimeString('es-CL', {
            hour: '2-digit',
            minute: '2-digit'
        });

        const iconSvg = role === 'assistant'
            ? '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="none" stroke="#fff" stroke-width="2"/><path d="M12 6v6l4 2" fill="none" stroke="#fff" stroke-width="2"/></svg>'
            : '<svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" fill="none" stroke="#fff" stroke-width="2"/><circle cx="12" cy="7" r="4" fill="none" stroke="#fff" stroke-width="2"/></svg>';

        const messageHtml = 
            '<div class="msg ' + role + '">' +
            '  <div class="msg-avatar">' + iconSvg + '</div>' +
            '  <div class="msg-text">' + escapeHtml(text) + '</div>' +
            '</div>';

        $('#ap-messages').append(messageHtml);
        scrollToBottom();
    }

    function showTyping() {
        const typingHtml = 
            '<div class="msg assistant typing-msg">' +
            '  <div class="msg-avatar">' +
            '    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="none" stroke="#fff" stroke-width="2"/><path d="M12 6v6l4 2" fill="none" stroke="#fff" stroke-width="2"/></svg>' +
            '  </div>' +
            '  <div class="msg-text">' +
            '    <div class="typing"><span></span><span></span><span></span></div>' +
            '  </div>' +
            '</div>';

        $('#ap-messages').append(typingHtml);
        scrollToBottom();
    }

    function hideTyping() {
        $('.typing-msg').remove();
    }

    function scrollToBottom() {
        const container = $('#ap-messages')[0];
        container.scrollTop = container.scrollHeight;
    }

    function submitLead(e) {
        e.preventDefault();

        const form = $(this);
        const submitBtn = form.find('.submit');
        const name = form.find('[name=name]').val();
        const phone = form.find('[name=phone]').val();
        const email = form.find('[name=email]').val();

        if (!name || !phone || !email) {
            alert('Por favor completa todos los campos.');
            return;
        }

        submitBtn.prop('disabled', true).text('Enviando...');

        $.ajax({
            url: asesorPrevisional.ajaxUrl,
            method: 'POST',
            data: {
                action: 'ap_save_lead',
                nonce: asesorPrevisional.nonce,
                name: name,
                phone: phone,
                email: email
            },
            success: function(response) {
                submitBtn.prop('disabled', false).text('Solicitar Asesoría');

                if (response.success) {
                    leadSubmitted = true;
                    $('#ap-modal').removeClass('open');
                    $('#ap-lead').slideUp();
                    addMessage('assistant', '¡Perfecto! 👍 Hemos recibido tus datos. ' + 
                        (asesorPrevisional.advisorName || 'Luis Barría') + 
                        ' te contactará en menos de 24 horas para ayudarte con tu pensión. ¿Tienes alguna otra consulta?');
                } else {
                    alert(response.data?.message || 'Error al enviar. Intenta de nuevo.');
                }
            },
            error: function() {
                submitBtn.prop('disabled', false).text('Solicitar Asesoría');
                alert('Error de conexión. Intenta de nuevo.');
            }
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML.replace(/\n/g, '<br>');
    }

})(jQuery);
