<?php if (!defined('ABSPATH')) exit;
$advisor_name = get_option('ap_advisor_name', 'Luis Barría Chodil');
$advisor_code = get_option('ap_advisor_code', 'Cod. 1360 SPensiones');
?>
<div id="ap-widget">
    <!-- Notificación flotante -->
    <div id="ap-notification">
        <div style="display:flex;align-items:center;gap:10px">
            <div style="width:36px;height:36px;background:linear-gradient(135deg,#f59e0b,#fbbf24);border-radius:50%;display:flex;align-items:center;justify-content:center">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#fff" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 6v6l4 2"/>
                </svg>
            </div>
            <div>
                <strong>¿Dudas sobre tu pensión?</strong>
                <span>Consulta gratuita 👋</span>
            </div>
        </div>
    </div>

    <!-- Botón flotante -->
    <button id="ap-btn" aria-label="Abrir chat de asesoría previsional">
        <span class="badge">GRATIS</span>
        <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
    </button>

    <!-- Ventana de chat -->
    <div id="ap-chat">
        <!-- Header -->
        <div id="ap-header">
            <div class="icon">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 6v6l4 2" stroke="#fff" fill="none" stroke-width="2"/>
                </svg>
            </div>
            <div class="info">
                <h3>Asesor Previsional Virtual</h3>
                <p>
                    <span class="status">
                        <span class="dot"></span>
                        <?php echo esc_html($advisor_name); ?>
                    </span>
                </p>
            </div>
        </div>

        <!-- Área de mensajes -->
        <div id="ap-messages"></div>

        <!-- Acciones rápidas -->
        <div id="ap-quick">
            <button data-q="¿Qué es la renta vitalicia y cuáles son sus ventajas?">💼 Renta Vitalicia</button>
            <button data-q="¿Cuál es la mejor AFP para mi perfil?">📊 Mejor AFP</button>
            <button data-q="¿Qué es la PGU y cómo puedo acceder?">🛡️ PGU</button>
            <button data-q="¿Cómo puedo mejorar mi pensión futura?">📈 Mejorar pensión</button>
        </div>

        <!-- CTA de lead -->
        <div id="ap-lead" style="display:none">
            <p>¿Quieres una asesoría gratuita personalizada?</p>
            <button id="ap-ask-lead">Solicitar</button>
        </div>

        <!-- Input de mensaje -->
        <div id="ap-input">
            <input type="text" placeholder="Escribe tu consulta sobre pensiones..." maxlength="500">
            <button id="ap-send" aria-label="Enviar mensaje">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Modal de captura de lead -->
    <div id="ap-modal">
        <div class="content">
            <h3>🎓 Solicita tu Asesoría Gratuita</h3>
            <p><?php echo esc_html($advisor_name); ?> (<?php echo esc_html($advisor_code); ?>) te contactará en menos de 24 horas para ayudarte con tu pensión.</p>
            <form>
                <div class="field">
                    <label>Nombre completo</label>
                    <input type="text" name="name" required placeholder="Tu nombre">
                </div>
                <div class="field">
                    <label>Teléfono (WhatsApp)</label>
                    <input type="tel" name="phone" required placeholder="+56 9 1234 5678">
                </div>
                <div class="field">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="tu@email.com">
                </div>
                <div class="actions">
                    <button type="button" class="cancel">Cancelar</button>
                    <button type="submit" class="submit">Solicitar Asesoría</button>
                </div>
            </form>
        </div>
    </div>
</div>
