// Test local de Gemini API
import fetch from 'node-fetch';

const GEMINI_API_KEY = process.env.GEMINI_API_KEY;

if (!GEMINI_API_KEY) {
  console.log('❌ GEMINI_API_KEY no está configurada');
  process.exit(1);
}

console.log('🔑 API Key encontrada (primeros 10 caracteres):', GEMINI_API_KEY.substring(0, 10) + '...');

const testPrompt = `Eres un asesor previsional de Chile. Responde de forma CONCISA (máximo 3 oraciones) a esta pregunta: "¿Qué es la renta vitalicia?" Siempre termina con una pregunta de seguimiento.`;

try {
  const response = await fetch(`https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=${GEMINI_API_KEY}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      contents: [{
        role: 'user',
        parts: [{ text: testPrompt }]
      }],
      generationConfig: {
        temperature: 0.8,
        maxOutputTokens: 300
      }
    })
  });

  const data = await response.json();
  
  if (response.ok && data.candidates?.[0]?.content?.parts?.[0]?.text) {
    console.log('\n✅ Gemini API funciona correctamente!\n');
    console.log('📝 Respuesta de Gemini:');
    console.log('---');
    console.log(data.candidates[0].content.parts[0].text);
    console.log('---\n');
  } else {
    console.log('❌ Error en respuesta de Gemini:');
    console.log(JSON.stringify(data, null, 2));
  }
} catch (error) {
  console.log('❌ Error al conectar con Gemini:', error.message);
}
