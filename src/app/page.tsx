'use client';

import { useState, useRef, useEffect, useCallback } from 'react';
import { Header } from '@/components/Header';
import { MessageBubble, TypingIndicator, type Message } from '@/components/MessageBubble';
import { QuickActions } from '@/components/QuickActions';
import { LeadCaptureForm } from '@/components/LeadCaptureForm';
import { WhatsAppButton } from '@/components/WhatsAppButton';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { SendIcon } from '@/components/icons/PensionIcon';
import { toast } from 'sonner';

export default function Home() {
  const [messages, setMessages] = useState<Message[]>([]);
  const [inputValue, setInputValue] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [showLeadForm, setShowLeadForm] = useState(false);
  const [leadSubmitted, setLeadSubmitted] = useState(false);
  const [messageCount, setMessageCount] = useState(0);
  const messagesEndRef = useRef<HTMLDivElement>(null);
  const textareaRef = useRef<HTMLTextAreaElement>(null);

  const scrollToBottom = useCallback(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, []);

  useEffect(() => {
    scrollToBottom();
  }, [messages, scrollToBottom]);

  // Inicializar conversación con saludo del bot
  useEffect(() => {
    const initConversation = async () => {
      setIsLoading(true);
      try {
        const response = await fetch('/api/chat', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ isNewConversation: true }),
        });
        const data = await response.json();
        
        const greetingMessage: Message = {
          id: Date.now().toString(),
          role: 'assistant',
          content: data.message,
          timestamp: new Date(),
        };
        setMessages([greetingMessage]);
      } catch {
        toast.error('Error al iniciar la conversación');
      } finally {
        setIsLoading(false);
      }
    };
    
    initConversation();
  }, []);

  // Mostrar formulario de lead después de 4 mensajes del usuario
  useEffect(() => {
    if (messageCount >= 4 && !leadSubmitted) {
      setShowLeadForm(true);
    }
  }, [messageCount, leadSubmitted]);

  const sendMessage = async (content: string) => {
    if (!content.trim() || isLoading) return;

    const userMessage: Message = {
      id: Date.now().toString(),
      role: 'user',
      content: content.trim(),
      timestamp: new Date(),
    };

    setMessages((prev) => [...prev, userMessage]);
    setInputValue('');
    setIsLoading(true);
    setMessageCount((prev) => prev + 1);

    // Reset textarea height
    if (textareaRef.current) {
      textareaRef.current.style.height = 'auto';
    }

    try {
      const messagesToSend = [...messages, userMessage].map((m) => ({
        role: m.role,
        content: m.content,
      }));

      const response = await fetch('/api/chat', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ messages: messagesToSend }),
      });

      const data = await response.json();

      const assistantMessage: Message = {
        id: (Date.now() + 1).toString(),
        role: 'assistant',
        content: data.message || data.error || 'Lo siento, no pude procesar tu mensaje.',
        timestamp: new Date(),
      };

      setMessages((prev) => [...prev, assistantMessage]);
    } catch {
      toast.error('Error al enviar el mensaje');
      const errorMessage: Message = {
        id: (Date.now() + 1).toString(),
        role: 'assistant',
        content: 'Lo siento, hubo un error al procesar tu mensaje. Por favor, intenta de nuevo.',
        timestamp: new Date(),
      };
      setMessages((prev) => [...prev, errorMessage]);
    } finally {
      setIsLoading(false);
    }
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    sendMessage(inputValue);
  };

  const handleKeyDown = (e: React.KeyboardEvent<HTMLTextAreaElement>) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage(inputValue);
    }
  };

  const handleQuickAction = (question: string) => {
    sendMessage(question);
  };

  const handleLeadSuccess = () => {
    setShowLeadForm(false);
    setLeadSubmitted(true);
    toast.success('¡Gracias! Un asesor te contactará pronto.');
    
    // Agregar mensaje de confirmación
    const confirmMessage: Message = {
      id: Date.now().toString(),
      role: 'assistant',
      content: '¡Perfecto! Hemos recibido tus datos. Un asesor previsional te contactará en menos de 24 horas para ayudarte con tu pensión. Mientras tanto, ¿tienes alguna otra consulta?',
      timestamp: new Date(),
    };
    setMessages((prev) => [...prev, confirmMessage]);
  };

  const handleTextareaChange = (e: React.ChangeEvent<HTMLTextAreaElement>) => {
    setInputValue(e.target.value);
    // Auto-resize textarea
    e.target.style.height = 'auto';
    e.target.style.height = `${Math.min(e.target.scrollHeight, 120)}px`;
  };

  return (
    <div className="min-h-screen flex flex-col bg-gradient-to-b from-slate-50 to-slate-100">
      <Header />
      
      {/* Hero Banner */}
      <div className="bg-gradient-to-r from-amber-500 to-amber-400 text-white py-3 px-4">
        <div className="max-w-4xl mx-auto flex items-center justify-center gap-2 text-sm md:text-base">
          <span className="font-semibold">🎁 Asesoría Gratuita</span>
          <span className="hidden md:inline">•</span>
          <span className="hidden md:inline">Sin Compromiso</span>
          <span className="hidden md:inline">•</span>
          <span className="hidden md:inline">Expertos en Pensiones</span>
        </div>
      </div>

      {/* Chat Container */}
      <main className="flex-1 flex flex-col max-w-4xl mx-auto w-full">
        {/* Messages Area */}
        <div className="flex-1 overflow-y-auto p-4 space-y-4">
          {messages.map((message) => (
            <MessageBubble key={message.id} message={message} />
          ))}
          {isLoading && <TypingIndicator />}
          <div ref={messagesEndRef} />
        </div>

        {/* Quick Actions - Show only at beginning */}
        {messages.length <= 2 && (
          <QuickActions onAction={handleQuickAction} disabled={isLoading} />
        )}

        {/* Lead CTA - Show after 3 messages */}
        {messageCount >= 2 && !leadSubmitted && messages.length > 0 && (
          <div className="px-4 py-3 bg-amber-50 border-t border-amber-100">
            <div className="flex flex-col sm:flex-row items-center justify-between gap-3 max-w-2xl mx-auto">
              <div className="text-center sm:text-left">
                <p className="text-sm font-medium text-slate-800">
                  ¿Quieres una evaluación personalizada de tu pensión?
                </p>
                <p className="text-xs text-slate-600">
                  Nuestros asesores te ayudan sin costo
                </p>
              </div>
              <Button
                onClick={() => setShowLeadForm(true)}
                className="bg-amber-500 hover:bg-amber-600 text-white whitespace-nowrap"
              >
                Solicitar Asesoría Gratis
              </Button>
            </div>
          </div>
        )}

        {/* Input Area */}
        <div className="border-t border-slate-200 bg-white p-4">
          <form onSubmit={handleSubmit} className="flex gap-3 max-w-2xl mx-auto">
            <div className="flex-1 relative">
              <Textarea
                ref={textareaRef}
                value={inputValue}
                onChange={handleTextareaChange}
                onKeyDown={handleKeyDown}
                placeholder="Escribe tu consulta sobre pensiones..."
                disabled={isLoading}
                rows={1}
                className="min-h-[44px] max-h-[120px] resize-none border-slate-200 focus:border-amber-400 focus:ring-amber-400/20 pr-12"
              />
            </div>
            <Button
              type="submit"
              disabled={!inputValue.trim() || isLoading}
              className="bg-slate-700 hover:bg-slate-800 text-white h-11 w-11 p-0 shrink-0"
            >
              <SendIcon className="w-5 h-5" />
            </Button>
          </form>
          <p className="text-xs text-slate-400 text-center mt-2">
            Presiona Enter para enviar • Shift+Enter para nueva línea
          </p>
        </div>
      </main>

      {/* Footer */}
      <footer className="bg-slate-800 text-slate-300 py-4 px-4">
        <div className="max-w-4xl mx-auto flex flex-col md:flex-row items-center justify-between gap-3 text-sm">
          <p>© 2024 Luis Barría Chodil - Cod. 1360 SPensiones</p>
          <div className="flex items-center gap-4">
            <a href="https://wa.me/56934460825" className="hover:text-white transition-colors">
              📱 +56 9 3446 0825
            </a>
            <a href="mailto:luis.barria@asesoriapensiones.cl" className="hover:text-white transition-colors">
              ✉️ luis.barria@asesoriapensiones.cl
            </a>
          </div>
        </div>
      </footer>

      {/* WhatsApp Button */}
      <WhatsAppButton />

      {/* Lead Capture Modal */}
      <LeadCaptureForm
        isOpen={showLeadForm}
        onClose={() => setShowLeadForm(false)}
        onSuccess={handleLeadSuccess}
      />
    </div>
  );
}
