import { NextResponse } from 'next/server';

export async function GET() {
  const GEMINI_API_KEY = process.env.GEMINI_API_KEY;
  
  if (!GEMINI_API_KEY) {
    return NextResponse.json({ 
      status: 'error', 
      message: 'GEMINI_API_KEY no está configurada'
    });
  }
  
  try {
    // List available models
    const response = await fetch(`https://generativelanguage.googleapis.com/v1/models?key=${GEMINI_API_KEY}`);
    const data = await response.json();
    
    if (response.ok && data.models) {
      const generateContentModels = data.models
        .filter((m: { supportedGenerationMethods?: string[] }) => 
          m.supportedGenerationMethods?.includes('generateContent')
        )
        .map((m: { name: string }) => m.name);
      
      return NextResponse.json({ 
        status: 'success',
        availableModels: generateContentModels,
        allModels: data.models
      });
    } else {
      return NextResponse.json({ 
        status: 'error', 
        message: 'Error al listar modelos',
        details: data
      });
    }
  } catch (error) {
    return NextResponse.json({ 
      status: 'error', 
      message: 'Error de conexión',
      details: error instanceof Error ? error.message : 'Unknown error'
    });
  }
}
