class SoundEffectsSystem {
    constructor() {
        this.audioContext = null;
        this.volume = 0.5;
        this.initialized = false;
        
        document.addEventListener('click', () => this.init(), { once: true });
    }

    init() {
        if (this.initialized) return;
        
        try {
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            this.initialized = true;
            console.log('🎵 Sistema de audio inicializado');
        } catch (e) {
            console.warn('Audio no disponible:', e);
        }
    }

    play(soundName) {
        if (!this.initialized || !this.audioContext) return;

        const patterns = {
            success: { 
                freq: [523, 659, 784], 
                duration: 0.15, 
                type: 'sine',
                volume: 0.3 
            },
            error: { 
                freq: [400, 350, 300], 
                duration: 0.1, 
                type: 'triangle',
                volume: 0.2 
            },
            click: { 
                freq: [800], 
                duration: 0.05, 
                type: 'square',
                volume: 0.15 
            },
            star: { 
                freq: [1047, 1319, 1568], 
                duration: 0.12, 
                type: 'sine',
                volume: 0.25 
            },
            cheer: { 
                freq: [523, 659, 784, 1047], 
                duration: 0.2, 
                type: 'sine',
                volume: 0.3 
            },
            whoosh: { 
                freq: [200, 400, 600], 
                duration: 0.15, 
                type: 'sawtooth',
                volume: 0.2 
            },
            coin: { 
                freq: [1000, 1200], 
                duration: 0.1, 
                type: 'sine',
                volume: 0.2 
            },
            level_up: { 
                freq: [523, 659, 784, 1047, 1319], 
                duration: 0.18, 
                type: 'sine',
                volume: 0.35 
            }
        };

        const pattern = patterns[soundName];
        if (!pattern) return;

        const now = this.audioContext.currentTime;

        pattern.freq.forEach((frequency, index) => {
            const oscillator = this.audioContext.createOscillator();
            const gainNode = this.audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(this.audioContext.destination);

            oscillator.type = pattern.type;
            oscillator.frequency.setValueAtTime(frequency, now + index * pattern.duration);

            const vol = (pattern.volume || 0.3) * this.volume;
            gainNode.gain.setValueAtTime(0, now + index * pattern.duration);
            gainNode.gain.linearRampToValueAtTime(vol, now + index * pattern.duration + 0.01);
            gainNode.gain.exponentialRampToValueAtTime(0.01, now + (index + 1) * pattern.duration);

            oscillator.start(now + index * pattern.duration);
            oscillator.stop(now + (index + 1) * pattern.duration);
        });
    }

    setVolume(level) {
        this.volume = Math.max(0, Math.min(1, level));
    }
}

const soundSystem = new SoundEffectsSystem();

class VisualEffectsSystem {
    constructor() {
        this.confettiContainer = null;
        this.messageContainer = null;
        this.initContainers();
        this.isMobile = window.innerWidth <= 768;
    }

    initContainers() {
        if (!document.getElementById('confetti-container')) {
            const container = document.createElement('div');
            container.id = 'confetti-container';
            container.style.cssText = `
                position: fixed;
                inset: 0;
                pointer-events: none;
                z-index: 9999;
            `;
            document.body.appendChild(container);
        }
        this.confettiContainer = document.getElementById('confetti-container');

        if (!document.getElementById('message-container')) {
            const container = document.createElement('div');
            container.id = 'message-container';
            container.style.cssText = `
                position: fixed;
                top: 20%;
                left: 50%;
                transform: translateX(-50%);
                z-index: 10000;
                pointer-events: none;
            `;
            document.body.appendChild(container);
        }
        this.messageContainer = document.getElementById('message-container');
    }

    launchConfetti(count = 50) {
        const actualCount = this.isMobile ? Math.floor(count * 0.6) : count;
        const colors = ['#FF7043', '#FFC107', '#4CAF50', '#2196F3', '#E91E63', '#9C27B0'];
        
        for (let i = 0; i < actualCount; i++) {
            setTimeout(() => {
                const confetti = document.createElement('div');
                confetti.style.cssText = `
                    position: absolute;
                    width: ${this.isMobile ? '8px' : '10px'};
                    height: ${this.isMobile ? '8px' : '10px'};
                    background: ${colors[Math.floor(Math.random() * colors.length)]};
                    border-radius: 50%;
                    left: ${Math.random() * 100}%;
                    top: -20px;
                    animation: confetti-fall ${2 + Math.random() * 2}s linear forwards;
                    animation-delay: ${Math.random() * 0.5}s;
                `;
                
                this.confettiContainer.appendChild(confetti);
                setTimeout(() => confetti.remove(), 4000);
            }, i * (this.isMobile ? 40 : 30));
        }
    }

    showStars(element) {
        const stars = ['⭐', '✨', '🌟', '💫', '⚡'];
        const count = this.isMobile ? 3 : 5;
        
        for (let i = 0; i < count; i++) {
            const star = document.createElement('div');
            star.textContent = stars[Math.floor(Math.random() * stars.length)];
            star.style.cssText = `
                position: absolute;
                font-size: ${this.isMobile ? '1.5rem' : '2rem'};
                animation: star-pop 1s ease;
                left: ${20 + Math.random() * 60}%;
                top: ${20 + Math.random() * 60}%;
                pointer-events: none;
                z-index: 9999;
            `;
            
            element.style.position = 'relative';
            element.appendChild(star);
            
            setTimeout(() => star.remove(), 1000);
        }
    }

    getMotivationalMessage(isCorrect) {
        const correct = [
            '¡Excelente! 🎉',
            '¡Muy bien! ⭐',
            '¡Perfecto! 🌟',
            '¡Genial! 🎊',
            '¡Increíble! 💫',
            '¡Eres un campeón! 🏆',
            '¡Sigue así! 🚀',
            '¡Lo lograste! 🎯'
        ];
        
        const incorrect = [
            '¡Intenta de nuevo! 💪',
            '¡Casi! Sigue intentando 🌈',
            '¡No te rindas! 🦋',
            '¡Puedes hacerlo! 🌟',
            '¡Inténtalo otra vez! 🎈',
            '¡Tú puedes! 💝'
        ];
        
        const messages = isCorrect ? correct : incorrect;
        return messages[Math.floor(Math.random() * messages.length)];
    }

    showFloatingMessage(text, type = 'success') {
        this.messageContainer.innerHTML = '';
        
        const message = document.createElement('div');
        message.textContent = text;
        message.style.cssText = `
            background: ${type === 'success' ? 
                'linear-gradient(135deg, #4CAF50, #66BB6A)' : 
                'linear-gradient(135deg, #FF7043, #FF8A65)'};
            color: white;
            padding: ${this.isMobile ? '15px 30px' : '20px 40px'};
            border-radius: 20px;
            font-size: ${this.isMobile ? '1.2rem' : '1.5rem'};
            font-weight: bold;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            animation: bounce-in 0.5s ease;
            border: 4px solid white;
        `;
        
        this.messageContainer.appendChild(message);
        
        setTimeout(() => {
            message.style.animation = 'float-away 0.5s ease forwards';
            setTimeout(() => message.remove(), 500);
        }, 2000);
    }

    shake(element) {
        element.classList.add('animate-shake');
        setTimeout(() => element.classList.remove('animate-shake'), 500);
    }

    celebrate(element) {
        element.classList.add('animate-celebrate');
        setTimeout(() => element.classList.remove('animate-celebrate'), 600);
    }

    bounce(element) {
        if (this.isMobile) {
            element.style.animation = 'bounce-subtle 0.3s ease';
            setTimeout(() => element.style.animation = '', 300);
        } else {
            element.classList.add('animate-bounce-in');
            setTimeout(() => element.classList.remove('animate-bounce-in'), 500);
        }
    }
}

const visualEffects = new VisualEffectsSystem();

function handleCorrectAnswer(element) {
    soundSystem.play('success');
    visualEffects.celebrate(element);
    visualEffects.showStars(element);
    visualEffects.launchConfetti(30);
    
    const message = visualEffects.getMotivationalMessage(true);
    visualEffects.showFloatingMessage(message, 'success');
    
    element.classList.add('ok');
}

function handleIncorrectAnswer(element) {
    soundSystem.play('error');
    visualEffects.shake(element);
    
    const message = visualEffects.getMotivationalMessage(false);
    visualEffects.showFloatingMessage(message, 'error');
    
    element.classList.add('bad');
}

function handleClick(element) {
    soundSystem.play('click');
    visualEffects.bounce(element);
}

function celebrateLevelComplete() {
    soundSystem.play('level_up');
    visualEffects.launchConfetti(80);
    visualEffects.showFloatingMessage('¡Nivel Completado! 🏆', 'success');
}

function earnStar(element) {
    soundSystem.play('star');
    visualEffects.showStars(element);
}

function collectCoin() {
    soundSystem.play('coin');
}

function initEnhancedInteractions() {
    document.querySelectorAll('button:not(.audio-btn):not([class*="audio"]), .opt, .img-option, .matching-card').forEach(element => {
        element.addEventListener('click', function(e) {
            if (!this.classList.contains('disabled')) {
                handleClick(this);
            }
        });
    });

    const nextBtn = document.getElementById('nextBtn');
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            if (this.classList.contains('active')) {
                soundSystem.play('whoosh');
            }
        });
    }

    if (!visualEffects.isMobile) {
        document.querySelectorAll('.opt, .img-option').forEach(element => {
            element.addEventListener('mouseenter', function() {
                if (!this.classList.contains('disabled')) {
                    this.style.transform = 'translateY(-8px) scale(1.08) rotate(-2deg)';
                }
            });
            
            element.addEventListener('mouseleave', function() {
                if (!this.classList.contains('disabled')) {
                    this.style.transform = '';
                }
            });
        });
    }

    document.querySelectorAll('.opt, .img-option, .lesson-card').forEach((element, index) => {
        element.style.animation = `bounce-in 0.5s ease ${index * 0.1}s backwards`;
    });
}

function updateProgressBar(progressElement, percentage) {
    if (!progressElement) return;
    
    progressElement.style.transition = 'width 1s cubic-bezier(0.34, 1.56, 0.64, 1)';
    progressElement.style.width = `${percentage}%`;
    
    if (percentage >= 100) {
        setTimeout(() => celebrateLevelComplete(), 500);
    } else if (percentage >= 75) {
        soundSystem.play('coin');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('🎮 Sistema de efectos V2 inicializado');
    
    const isMobile = window.innerWidth <= 768;
    if (isMobile) {
        console.log('📱 Modo móvil activado');
        document.body.classList.add('mobile-optimized');
    }
    
    setTimeout(() => initEnhancedInteractions(), 100);
});

window.addEventListener('resize', () => {
    visualEffects.isMobile = window.innerWidth <= 768;
});

window.soundSystem = soundSystem;
window.visualEffects = visualEffects;
window.handleCorrectAnswer = handleCorrectAnswer;
window.handleIncorrectAnswer = handleIncorrectAnswer;
window.celebrateLevelComplete = celebrateLevelComplete;
window.earnStar = earnStar;
window.updateProgressBar = updateProgressBar;