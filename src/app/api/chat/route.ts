import { NextRequest, NextResponse } from 'next/server';
import ZAI from 'z-ai-web-dev-sdk';
import { pensionSystemPrompt, initialGreeting } from '@/lib/pensionPrompt';

export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    const { messages, isNewConversation } = body;

    if (isNewConversation) {
      return NextResponse.json({ message: initialGreeting });
    }

    if (!messages || !Array.isArray(messages)) {
      return NextResponse.json(
        { error: 'Messages array is required' },
        { status: 400 }
      );
    }

    const zai = await ZAI.create();

    const formattedMessages = [
      {
        role: 'system' as const,
        content: pensionSystemPrompt,
      },
      ...messages.map((msg: { role: string; content: string }) => ({
        role: msg.role as 'user' | 'assistant',
        content: msg.content,
      })),
    ];

    const completion = await zai.chat.completions.create({
      messages: formattedMessages,
      temperature: 0.7,
      max_tokens: 500,
    });

    const responseMessage = completion.choices[0]?.message?.content || 
      'Lo siento, no pude procesar tu consulta. Por favor, intenta de nuevo.';

    return NextResponse.json({ message: responseMessage });
  } catch (error) {
    console.error('Chat API error:', error);
    return NextResponse.json(
      { error: 'Error al procesar tu mensaje. Por favor, intenta de nuevo.' },
      { status: 500 }
    );
  }
}
