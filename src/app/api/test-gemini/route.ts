import { NextResponse } from 'next/server';

export async function GET() {
  const GEMINI_API_KEY = process.env.GEMINI_API_KEY;
  
  if (!GEMINI_API_KEY) {
    return NextResponse.json({ 
      status: 'error', 
      message: 'GEMINI_API_KEY no está configurada en Vercel',
      instruction: 'Ve a Vercel Dashboard > Settings > Environment Variables y agrega GEMINI_API_KEY con tu clave de Google Gemini'
    });
  }
  
  // Test API call
  try {
    const response = await fetch(`https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key=${GEMINI_API_KEY}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        contents: [{
          role: 'user',
          parts: [{ text: 'Di "Hola, soy Gemini" en español.' }]
        }]
      })
    });
    
    const data = await response.json();
    
    if (response.ok && data.candidates?.[0]?.content?.parts?.[0]?.text) {
      return NextResponse.json({ 
        status: 'success', 
        message: 'Gemini API está funcionando correctamente',
        response: data.candidates[0].content.parts[0].text
      });
    } else {
      return NextResponse.json({ 
        status: 'error', 
        message: 'Error en la respuesta de Gemini',
        details: data
      });
    }
  } catch (error) {
    return NextResponse.json({ 
      status: 'error', 
      message: 'Error al conectar con Gemini',
      details: error instanceof Error ? error.message : 'Unknown error'
    });
  }
}
