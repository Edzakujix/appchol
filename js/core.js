function playAudio(path) {
    console.log("Intentando reproducir:", path);
    const audio = new Audio(path);
    audio.play()
        .then(() => {
            // Opcional: Animación visual de que suena
        })
        .catch(e => {
            console.warn("Audio no encontrado o error:", path);
            // Fallback visual si no hay audio
            const feedbackEl = document.getElementById('feedback');
            if(feedbackEl) feedbackEl.innerText = "🔊 (Audio simulado)";
        });
}

// Función para mostrar Feedback
function setFeedback(msg, type) {
    const el = document.getElementById('feedback');
    if(!el) return;
    el.textContent = msg;
    el.style.color = type === 'ok' ? 'var(--ok)' : (type === 'bad' ? 'var(--bad)' : 'var(--text-color)');
    
    // Pequeña animación al cambiar texto
    el.style.transform = "scale(1.1)";
    setTimeout(() => el.style.transform = "scale(1)", 200);
}

// Deshabilitar opciones tras seleccionar
function disableOptions(selector = '.img-option, .opt, .word-tile') {
    document.querySelectorAll(selector).forEach(el => {
        el.classList.add('disabled', 'prevent-click');
        el.onclick = null; // Remover listener simple
    });
}

// Navegación (Ir al inicio)
function goHome(url) {
    window.location.href = url || '../index.html';
}

// Simulación de TTS (Texto a Voz) si falta el audio
function speakText(text, lang='es-ES'){
    if(!window.speechSynthesis) return;
    const u = new SpeechSynthesisUtterance(text);
    u.lang = lang;
    window.speechSynthesis.cancel();
    window.speechSynthesis.speak(u);
}