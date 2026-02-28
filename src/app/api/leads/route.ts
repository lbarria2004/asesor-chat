import { NextRequest, NextResponse } from 'next/server';
import { db } from '@/lib/db';

export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    const { name, phone, email, message } = body;

    if (!name || !phone || !email) {
      return NextResponse.json(
        { error: 'Nombre, teléfono y email son requeridos' },
        { status: 400 }
      );
    }

    const lead = await db.lead.create({
      data: {
        name,
        phone,
        email,
        message: message || null,
        source: 'chatbot',
        status: 'new',
      },
    });

    return NextResponse.json({ 
      success: true, 
      lead: { id: lead.id, name: lead.name } 
    });
  } catch (error) {
    console.error('Lead creation error:', error);
    return NextResponse.json(
      { error: 'Error al guardar tus datos. Por favor, intenta de nuevo.' },
      { status: 500 }
    );
  }
}
