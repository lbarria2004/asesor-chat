<?php if (!defined('ABSPATH')) exit; ?>
<div id="ap-widget">
  <div id="ap-notification">
    <div style="display:flex;align-items:center;gap:10px">
      <div style="width:32px;height:32px;background:#fef3c7;border-radius:50%;display:flex;align-items:center;justify-content:center">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#d97706" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
      </div>
      <div><strong style="display:block;color:#1f2937;font-size:12px">¿Dudas sobre tu pensión?</strong><span style="color:#6b7280;font-size:11px">Consulta gratis 👋</span></div>
    </div>
  </div>
  <button id="ap-btn" aria-label="Abrir chat">
    <span class="badge">GRATIS</span>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
  </button>
  <div id="ap-chat">
    <div id="ap-header">
      <div class="icon"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2" stroke="#fff" fill="none" stroke-width="2"/></svg></div>
      <div><h3>Asesor Previsional Virtual</h3><p><span class="dot"></span> Luis Barría Cod. 1360 SPensiones</p></div>
    </div>
    <div id="ap-messages"></div>
    <div id="ap-quick">
      <button data-q="¿Qué es la renta vitalicia?">💼 Renta Vitalicia</button>
      <button data-q="¿Cuál es la mejor AFP?">📊 Mejor AFP</button>
      <button data-q="¿Qué es la PGU?">🛡️ PGU</button>
      <button data-q="¿Cómo mejorar mi pensión?">📈 Mejorar pensión</button>
    </div>
    <div id="ap-lead" style="display:none"><p><strong>¿Asesoría gratuita?</strong></p><button id="ap-ask-lead">Solicitar</button></div>
    <div id="ap-input"><input type="text" placeholder="Escribe tu consulta..."><button id="ap-send"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg></button></div>
  </div>
  <div id="ap-modal">
    <div class="content">
      <h3>Solicita tu Asesoría Gratuita</h3>
      <p>Luis Barría (Cod. 1360 SPensiones) te contactará en menos de 24 horas.</p>
      <form>
        <div class="field"><label>Nombre</label><input type="text" name="name" required></div>
        <div class="field"><label>Teléfono</label><input type="tel" name="phone" placeholder="+56 9 1234 5678" required></div>
        <div class="field"><label>Email</label><input type="email" name="email" required></div>
        <div class="actions"><button type="button" class="cancel">Cancelar</button><button type="submit" class="submit">Solicitar</button></div>
      </form>
    </div>
  </div>
</div>
