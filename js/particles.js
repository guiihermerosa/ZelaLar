// ===== ZELALAR - SISTEMA DE PARTÍCULAS =====
// Sistema avançado de partículas para background dinâmico

class ParticleSystem {
    constructor(container) {
        this.container = container;
        this.particles = [];
        this.animationId = null;
        this.isActive = false;
        
        this.init();
    }

    init() {
        this.createParticles();
        this.startAnimation();
        this.addEventListeners();
    }

    createParticles() {
        const particleCount = Math.min(100, Math.floor(window.innerWidth / 20));
        
        for (let i = 0; i < particleCount; i++) {
            const particle = this.createParticle();
            this.particles.push(particle);
            this.container.appendChild(particle);
        }
    }

    createParticle() {
        const particle = document.createElement('div');
        const size = Math.random() * 6 + 2;
        const speed = Math.random() * 2 + 0.5;
        const opacity = Math.random() * 0.6 + 0.2;
        
        particle.className = 'particle';
        particle.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            background: rgba(255, 255, 255, ${opacity});
            border-radius: 50%;
            pointer-events: none;
            z-index: 1;
        `;

        // Propriedades da partícula
        particle.dataset.speed = speed;
        particle.dataset.opacity = opacity;
        particle.dataset.size = size;
        
        // Posição inicial
        this.resetParticle(particle);
        
        return particle;
    }

    resetParticle(particle) {
        const size = parseFloat(particle.dataset.size);
        const speed = parseFloat(particle.dataset.speed);
        
        // Posição aleatória
        particle.style.left = Math.random() * 100 + '%';
        particle.style.top = Math.random() * 100 + '%';
        
        // Velocidade e direção
        particle.dataset.vx = (Math.random() - 0.5) * speed;
        particle.dataset.vy = (Math.random() - 0.5) * speed;
        
        // Opacidade inicial
        particle.style.opacity = particle.dataset.opacity;
    }

    startAnimation() {
        if (this.isActive) return;
        
        this.isActive = true;
        this.animate();
    }

    stopAnimation() {
        this.isActive = false;
        if (this.animationId) {
            cancelAnimationFrame(this.animationId);
        }
    }

    animate() {
        if (!this.isActive) return;

        this.updateParticles();
        this.animationId = requestAnimationFrame(() => this.animate());
    }

    updateParticles() {
        this.particles.forEach(particle => {
            this.updateParticle(particle);
        });
    }

    updateParticle(particle) {
        const rect = particle.getBoundingClientRect();
        const containerRect = this.container.getBoundingClientRect();
        
        let x = parseFloat(particle.dataset.vx || 0);
        let y = parseFloat(particle.dataset.vy || 0);
        const speed = parseFloat(particle.dataset.speed);
        
        // Atualizar posição
        const currentLeft = parseFloat(particle.style.left) || 0;
        const currentTop = parseFloat(particle.style.top) || 0;
        
        let newLeft = currentLeft + x;
        let newTop = currentTop + y;
        
        // Verificar limites
        if (newLeft < -5 || newLeft > 105 || newTop < -5 || newTop > 105) {
            this.resetParticle(particle);
            return;
        }
        
        // Aplicar nova posição
        particle.style.left = newLeft + '%';
        particle.style.top = newTop + '%';
        
        // Efeito de flutuação
        const time = Date.now() * 0.001;
        const floatOffset = Math.sin(time + newLeft * 0.1) * 0.5;
        particle.style.transform = `translateY(${floatOffset}px)`;
        
        // Efeito de rotação
        particle.style.transform += ` rotate(${time * 20 + newLeft}deg)`;
    }

    addEventListeners() {
        // Pausar animação quando não visível
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.startAnimation();
                } else {
                    this.stopAnimation();
                }
            });
        });

        observer.observe(this.container);
        
        // Ajustar partículas no resize
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                this.handleResize();
            }, 250);
        });
    }

    handleResize() {
        // Remover partículas existentes
        this.particles.forEach(particle => {
            if (particle.parentNode) {
                particle.remove();
            }
        });
        
        this.particles = [];
        
        // Recriar partículas
        this.createParticles();
        
        // Reiniciar animação
        if (this.isActive) {
            this.startAnimation();
        }
    }

    // Efeitos especiais
    addExplosion(x, y, count = 20) {
        for (let i = 0; i < count; i++) {
            const particle = this.createParticle();
            particle.style.left = x + '%';
            particle.style.top = y + '%';
            
            // Velocidade explosiva
            const angle = (Math.PI * 2 * i) / count;
            const speed = Math.random() * 3 + 2;
            
            particle.dataset.vx = Math.cos(angle) * speed;
            particle.dataset.vy = Math.sin(angle) * speed;
            
            // Vida curta para explosão
            setTimeout(() => {
                if (particle.parentNode) {
                    particle.remove();
                    const index = this.particles.indexOf(particle);
                    if (index > -1) {
                        this.particles.splice(index, 1);
                    }
                }
            }, 2000);
        }
    }

    addWaveEffect() {
        this.particles.forEach((particle, index) => {
            setTimeout(() => {
                const wave = Math.sin(Date.now() * 0.001 + index * 0.1) * 10;
                particle.style.transform = `translateY(${wave}px)`;
            }, index * 50);
        });
    }

    // Controles de performance
    setParticleCount(count) {
        const currentCount = this.particles.length;
        
        if (count < currentCount) {
            // Remover partículas extras
            for (let i = currentCount - 1; i >= count; i--) {
                this.particles[i].remove();
                this.particles.pop();
            }
        } else if (count > currentCount) {
            // Adicionar partículas
            for (let i = currentCount; i < count; i++) {
                const particle = this.createParticle();
                this.particles.push(particle);
                this.container.appendChild(particle);
            }
        }
    }

    // Efeitos de tema
    setTheme(theme) {
        const colors = {
            light: 'rgba(255, 255, 255, 0.8)',
            dark: 'rgba(0, 0, 0, 0.6)',
            blue: 'rgba(95, 168, 211, 0.8)',
            gold: 'rgba(255, 215, 0, 0.8)'
        };
        
        const color = colors[theme] || colors.light;
        
        this.particles.forEach(particle => {
            const opacity = particle.dataset.opacity;
            particle.style.background = color.replace('0.8', opacity);
        });
    }
}

// ===== INICIALIZAÇÃO =====
document.addEventListener('DOMContentLoaded', () => {
    const particlesContainer = document.getElementById('hero-particles');
    if (particlesContainer) {
        window.particleSystem = new ParticleSystem(particlesContainer);
    }
});

// ===== CONTROLES GLOBAIS =====
window.ParticleControls = {
    pause: () => {
        if (window.particleSystem) {
            window.particleSystem.stopAnimation();
        }
    },
    
    resume: () => {
        if (window.particleSystem) {
            window.particleSystem.startAnimation();
        }
    },
    
    setCount: (count) => {
        if (window.particleSystem) {
            window.particleSystem.setParticleCount(count);
        }
    },
    
    setTheme: (theme) => {
        if (window.particleSystem) {
            window.particleSystem.setTheme(theme);
        }
    },
    
    addExplosion: (x, y) => {
        if (window.particleSystem) {
            window.particleSystem.addExplosion(x, y);
        }
    },
    
    addWave: () => {
        if (window.particleSystem) {
            window.particleSystem.addWaveEffect();
        }
    }
};

// ===== PERFORMANCE MONITORING =====
if ('performance' in window) {
    let frameCount = 0;
    let lastTime = performance.now();
    
    const countFrames = () => {
        frameCount++;
        const currentTime = performance.now();
        
        if (currentTime - lastTime >= 1000) {
            const fps = Math.round((frameCount * 1000) / (currentTime - lastTime));
            
            // Ajustar performance baseado no FPS
            if (fps < 30 && window.particleSystem) {
                window.particleSystem.setParticleCount(Math.floor(window.particleSystem.particles.length * 0.8));
            }
            
            frameCount = 0;
            lastTime = currentTime;
        }
        
        requestAnimationFrame(countFrames);
    };
    
    requestAnimationFrame(countFrames);
}
