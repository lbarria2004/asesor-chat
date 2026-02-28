import { NextRequest, NextResponse } from 'next/server';
import { pensionSystemPrompt, initialGreeting } from '@/lib/pensionPrompt';

// Google Gemini API configuration
const GEMINI_API_KEY = process.env.GEMINI_API_KEY;
const GEMINI_API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    const { messages, isNewConversation } = body;

    // Return initial greeting for new conversations
    if (isNewConversation) {
      return NextResponse.json({ message: initialGreeting });
    }

    if (!messages || !Array.isArray(messages)) {
      return NextResponse.json(
        { error: 'Messages array is required' },
        { status: 400 }
      );
    }

    // Check if Gemini API key is configured
    if (!GEMINI_API_KEY) {
      console.error('❌ GEMINI_API_KEY not configured in environment variables');
      return NextResponse.json({ 
        message: getFallbackResponse(messages[messages.length - 1]?.content || '')
      });
    }

    // Build conversation history for Gemini
    // Gemini uses 'user' and 'model' roles (not 'system', 'user', 'assistant')
    const conversationHistory: Array<{
      role: 'user' | 'model';
      parts: Array<{ text: string }>;
    }> = [];

    // For Gemini, we include the system prompt as context in the first user message
    const systemContext = `CONTEXTO Y PERSONALIDAD (sigue estas reglas en todas tus respuestas):
${pensionSystemPrompt}

---

Ahora responde al usuario manteniendo tu personalidad de Asesor Previsional Virtual.`;

    // Get only the last user message for simpler conversation flow
    const lastUserMessage = messages.filter((m: { role: string; content: string }) => m.role === 'user').pop();
    
    if (!lastUserMessage) {
      return NextResponse.json({ message: initialGreeting });
    }

    // Build a simple but effective conversation for Gemini
    conversationHistory.push({
      role: 'user',
      parts: [{ text: systemContext }]
    });

    conversationHistory.push({
      role: 'model',
      parts: [{ text: 'Entendido. Soy el Asesor Previsional Virtual de Luis Barría Chodil (Cod. 1360 SPensiones). Responderé de forma concisa, siempre haré preguntas de seguimiento, y ofreceré asesoría gratuita cuando sea apropiado. ¡Hola! 👋 Estoy listo para ayudar con consultas sobre pensiones en Chile.' }]
    });

    // Add recent conversation history (last 6 messages to keep context manageable)
    const recentMessages = messages.slice(-6);
    for (const msg of recentMessages) {
      const role = msg.role === 'assistant' ? 'model' : 'user';
      conversationHistory.push({
        role: role as 'user' | 'model',
        parts: [{ text: msg.content }]
      });
    }

    console.log('🚀 Calling Gemini API with', conversationHistory.length, 'messages');

    // Call Gemini API
    const response = await fetch(`${GEMINI_API_URL}?key=${GEMINI_API_KEY}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        contents: conversationHistory,
        generationConfig: {
          temperature: 0.7,
          maxOutputTokens: 350,
          topP: 0.9,
          topK: 40,
        },
        safetySettings: [
          { category: 'HARM_CATEGORY_HARASSMENT', threshold: 'BLOCK_NONE' },
          { category: 'HARM_CATEGORY_HATE_SPEECH', threshold: 'BLOCK_NONE' },
          { category: 'HARM_CATEGORY_SEXUALLY_EXPLICIT', threshold: 'BLOCK_NONE' },
          { category: 'HARM_CATEGORY_DANGEROUS_CONTENT', threshold: 'BLOCK_NONE' }
        ]
      }),
    });

    if (!response.ok) {
      const errorText = await response.text();
      console.error('❌ Gemini API error:', response.status, errorText);
      
      // Return fallback response if API fails
      return NextResponse.json({ 
        message: getFallbackResponse(lastUserMessage.content || '')
      });
    }

    const data = await response.json();
    console.log('✅ Gemini API response received');
    
    // Extract response from Gemini format
    const responseText = data.candidates?.[0]?.content?.parts?.[0]?.text;
    
    if (!responseText) {
      console.error('❌ No response text from Gemini:', JSON.stringify(data, null, 2));
      return NextResponse.json({ 
        message: getFallbackResponse(lastUserMessage.content || '')
      });
    }

    return NextResponse.json({ message: responseText });
  } catch (error) {
    console.error('❌ Chat API error:', error);
    return NextResponse.json(
      { message: getFallbackResponse('') }
    );
  }
}

// Fallback responses when API is unavailable
function getFallbackResponse(userMessage: string): string {
  const lowerMessage = userMessage.toLowerCase();
  
  if (lowerMessage.includes('renta vitalicia')) {
    return 'La Renta Vitalicia es una modalidad donde transfieres tus fondos a una aseguradora y recibes una pensión fija mensual de por vida. Es ideal si buscas estabilidad. ¿Te gustaría que Luis Barría te explique si esta opción es la mejor para tu caso?';
  }
  
  if (lowerMessage.includes('jubilar') || lowerMessage.includes('jubilación') || lowerMessage.includes('jubilarme')) {
    return 'La jubilación en Chile requiere cumplir ciertos requisitos de edad y cotizaciones. Cada caso es diferente y el monto de tu pensión depende de múltiples factores. ¿Te interesaría una asesoría gratuita con Luis Barría para evaluar tu situación particular?';
  }
  
  if (lowerMessage.includes('afp') || lowerMessage.includes('mejor afp')) {
    return 'Cada AFP tiene diferentes características de rentabilidad y comisiones. La mejor opción depende de tu perfil y horizonte de jubilación. ¿Quieres que nuestro asesor Luis Barría te ayude a elegir la mejor opción para ti?';
  }
  
  if (lowerMessage.includes('pgu') || lowerMessage.includes('pensión garantizada')) {
    return 'La Pensión Garantizada Universal (PGU) es un beneficio estatal para quienes cumplen ciertos requisitos de edad y cotizaciones. ¿Te gustaría saber si calificas para este beneficio?';
  }
  
  if (lowerMessage.includes('invalidez')) {
    return 'La pensión de invalidez requiere un dictamen médico de la COMPIN. El proceso puede ser complejo y es importante estar bien asesorado. ¿Te gustaría que Luis Barría te oriente sobre los requisitos y pasos a seguir?';
  }
  
  if (lowerMessage.includes('apv') || lowerMessage.includes('ahorro previsional')) {
    return 'El Ahorro Previsional Voluntario (APV) te permite incrementar tu pensión futura con beneficios tributarios. Es una excelente opción para mejorar tu jubilación. ¿Te interesa saber cómo puedes aprovecharlo?';
  }
  
  if (lowerMessage.includes('contacto') || lowerMessage.includes('asesor') || lowerMessage.includes('llamar') || lowerMessage.includes('whatsapp')) {
    return '¡Perfecto! Puedes contactar directamente a Luis Barría Chodil (Cod. 1360 SPensiones) al WhatsApp +56 9 3446 0825 o al email luis.barria@asesoriapensiones.cl. También puedes dejarme tus datos y te contactamos a la brevedad. ¿Te gustaría que te llamemos?';
  }
  
  if (lowerMessage.includes('precio') || lowerMessage.includes('costo') || lowerMessage.includes('cuánto cuesta') || lowerMessage.includes('valor')) {
    return 'La asesoría inicial es completamente gratuita. Nuestro objetivo es ayudarte a tomar las mejores decisiones sobre tu pensión. ¿Te gustaría agendar una consulta sin compromiso con Luis Barría?';
  }
  
  // Default response
  return 'Entiendo tu consulta sobre el sistema de pensiones. Cada situación es única y requiere análisis personalizado. ¿Te gustaría que Luis Barría (Cod. 1360 SPensiones) te brinde una asesoría gratuita para resolver tus dudas? Puedes contactarlo al WhatsApp +56 9 3446 0825.';
}
