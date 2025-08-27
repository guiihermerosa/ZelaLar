// ===== ZELALAR - SISTEMA DE ANIMAÇÕES =====
// Sistema avançado de animações com scroll-triggered e efeitos visuais

class AnimationSystem {
    constructor() {
        this.animations = new Map();
        this.observers = new Map();
        this.isInitialized = false;
        
        this.init();
    }

    init() {
        if (this.isInitialized) return;
        
        this.setupAnimations();
        this.setupIntersectionObservers();
        this.setupScrollAnimations();
        this.setupParallaxEffects();
        this.setupHoverEffects();
        this.setupTypingEffects();
        
        this.isInitialized = true;
        console.log('🎬 Sistema de animações inicializado');
    }

    // ===== CONFIGURAÇÃO DE ANIMAÇÕES =====
    setupAnimations() {
        // Animações de entrada
        this.animations.set('fadeInUp', {
            from: { opacity: 0, transform: 'translateY(30px)' },
            to: { opacity: 1, transform: 'translateY(0)' },
            duration: 600,
            easing: 'cubic-bezier(0.4, 0, 0.2, 1)'
        });

        this.animations.set('fadeInLeft', {
            from: { opacity: 0, transform: 'translateX(-30px)' },
            to: { opacity: 1, transform: 'translateX(0)' },
            duration: 600,
            easing: 'cubic-bezier(0.4, 0, 0.2, 1)'
        });

        this.animations.set('fadeInRight', {
            from: { opacity: 0, transform: 'translateX(30px)' },
            to: { opacity: 1, transform: 'translateX(0)' },
            duration: 600,
            easing: 'cubic-bezier(0.4, 0, 0.2, 1)'
        });

        this.animations.set('scaleIn', {
            from: { opacity: 0, transform: 'scale(0.8)' },
            to: { opacity: 1, transform: 'scale(1)' },
            duration: 500,
            easing: 'cubic-bezier(0.34, 1.56, 0.64, 1)'
        });

        this.animations.set('slideInUp', {
            from: { opacity: 0, transform: 'translateY(100px)' },
            to: { opacity: 1, transform: 'translateY(0)' },
            duration: 800,
            easing: 'cubic-bezier(0.4, 0, 0.2, 1)'
        });

        // Animações de hover
        this.animations.set('pulse', {
            from: { transform: 'scale(1)' },
            to: { transform: 'scale(1.05)' },
            duration: 200,
            easing: 'ease-in-out'
        });

        this.animations.set('bounce', {
            from: { transform: 'translateY(0)' },
            to: { transform: 'translateY(-10px)' },
            duration: 300,
            easing: 'cubic-bezier(0.68, -0.55, 0.265, 1.55)'
        });
    }

    // ===== INTERSECTION OBSERVER =====
    setupIntersectionObservers() {
        const options = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        // Observer para animações de entrada
        const entranceObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.animateElement(entry.target);
                    entranceObserver.unobserve(entry.target);
                }
            });
        }, options);

        // Observer para animações contínuas
        const continuousObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-continuous');
                } else {
                    entry.target.classList.remove('animate-continuous');
                }
            });
        }, options);

        // Aplicar observers aos elementos
        this.applyObservers(entranceObserver, continuousObserver);
    }

    applyObservers(entranceObserver, continuousObserver) {
        // Elementos com animação de entrada
        const entranceElements = document.querySelectorAll('[data-animate]');
        entranceElements.forEach(el => {
            entranceObserver.observe(el);
        });

        // Elementos com animação contínua
        const continuousElements = document.querySelectorAll('[data-animate-continuous]');
        continuousElements.forEach(el => {
            continuousObserver.observe(el);
        });
    }

    // ===== ANIMAÇÃO DE ELEMENTOS =====
    animateElement(element) {
        const animationType = element.dataset.animate || 'fadeInUp';
        const delay = parseInt(element.dataset.delay) || 0;
        const duration = parseInt(element.dataset.duration) || null;
        
        const animation = this.animations.get(animationType);
        if (!animation) return;

        // Aplicar delay
        setTimeout(() => {
            this.runAnimation(element, animation, duration);
        }, delay);
    }

    runAnimation(element, animation, customDuration = null) {
        const duration = customDuration || animation.duration;
        
        // Estado inicial
        Object.assign(element.style, animation.from);
        
        // Forçar reflow
        element.offsetHeight;
        
        // Animar para estado final
        element.style.transition = `all ${duration}ms ${animation.easing}`;
        Object.assign(element.style, animation.to);
        
        // Limpar estilos após animação
        setTimeout(() => {
            element.style.transition = '';
            element.classList.add('animated');
        }, duration);
    }

    // ===== ANIMAÇÕES DE SCROLL =====
    setupScrollAnimations() {
        let ticking = false;
        
        const updateScrollAnimations = () => {
            this.updateParallaxElements();
            this.updateStickyElements();
            this.updateProgressBars();
            
            ticking = false;
        };

        const requestTick = () => {
            if (!ticking) {
                requestAnimationFrame(updateScrollAnimations);
                ticking = true;
            }
        };

        window.addEventListener('scroll', requestTick, { passive: true });
    }

    // ===== EFEITOS PARALLAX =====
    setupParallaxEffects() {
        const parallaxElements = document.querySelectorAll('[data-parallax]');
        
        parallaxElements.forEach(element => {
            const speed = parseFloat(element.dataset.parallax) || 0.5;
            const direction = element.dataset.parallaxDirection || 'vertical';
            
            element.dataset.parallaxSpeed = speed;
            element.dataset.parallaxDirection = direction;
        });
    }

    updateParallaxElements() {
        const scrolled = window.pageYOffset;
        const parallaxElements = document.querySelectorAll('[data-parallax]');
        
        parallaxElements.forEach(element => {
            const speed = parseFloat(element.dataset.parallaxSpeed) || 0.5;
            const direction = element.dataset.parallaxDirection || 'vertical';
            
            let transform = '';
            
            if (direction === 'vertical') {
                const yPos = -(scrolled * speed);
                transform = `translateY(${yPos}px)`;
            } else if (direction === 'horizontal') {
                const xPos = -(scrolled * speed);
                transform = `translateX(${xPos}px)`;
            } else if (direction === 'rotate') {
                const rotation = scrolled * speed;
                transform = `rotate(${rotation}deg)`;
            }
            
            element.style.transform = transform;
        });
    }

    // ===== ELEMENTOS STICKY =====
    updateStickyElements() {
        const stickyElements = document.querySelectorAll('[data-sticky]');
        
        stickyElements.forEach(element => {
            const offset = parseInt(element.dataset.stickyOffset) || 0;
            const scrolled = window.pageYOffset;
            
            if (scrolled > offset) {
                element.classList.add('sticky-active');
            } else {
                element.classList.remove('sticky-active');
            }
        });
    }

    // ===== BARRAS DE PROGRESSO =====
    updateProgressBars() {
        const progressBars = document.querySelectorAll('[data-progress]');
        
        progressBars.forEach(bar => {
            const target = parseInt(bar.dataset.progress) || 0;
            const scrolled = window.pageYOffset;
            const elementTop = bar.offsetTop;
            const elementHeight = bar.offsetHeight;
            const windowHeight = window.innerHeight;
            
            if (scrolled + windowHeight > elementTop && scrolled < elementTop + elementHeight) {
                const progress = Math.min(100, ((scrolled + windowHeight - elementTop) / (elementHeight + windowHeight)) * 100);
                bar.style.width = `${Math.min(progress, target)}%`;
            }
        });
    }

    // ===== EFEITOS DE HOVER =====
    setupHoverEffects() {
        // Efeito de elevação
        const elevationElements = document.querySelectorAll('[data-hover-elevation]');
        elevationElements.forEach(element => {
            element.addEventListener('mouseenter', () => {
                this.addHoverEffect(element, 'elevation');
            });
            
            element.addEventListener('mouseleave', () => {
                this.removeHoverEffect(element, 'elevation');
            });
        });

        // Efeito de rotação
        const rotationElements = document.querySelectorAll('[data-hover-rotation]');
        rotationElements.forEach(element => {
            element.addEventListener('mouseenter', () => {
                this.addHoverEffect(element, 'rotation');
            });
            
            element.addEventListener('mouseleave', () => {
                this.removeHoverEffect(element, 'rotation');
            });
        });
    }

    addHoverEffect(element, effectType) {
        const effects = {
            elevation: { transform: 'translateY(-5px)', boxShadow: '0 10px 25px rgba(0,0,0,0.15)' },
            rotation: { transform: 'rotate(5deg)' },
            scale: { transform: 'scale(1.05)' },
            glow: { boxShadow: '0 0 20px rgba(95, 168, 211, 0.5)' }
        };
        
        const effect = effects[effectType];
        if (effect) {
            Object.assign(element.style, effect);
        }
    }

    removeHoverEffect(element, effectType) {
        const effects = {
            elevation: { transform: 'translateY(0)', boxShadow: '' },
            rotation: { transform: 'rotate(0deg)' },
            scale: { transform: 'scale(1)' },
            glow: { boxShadow: '' }
        };
        
        const effect = effects[effectType];
        if (effect) {
            Object.assign(element.style, effect);
        }
    }

    // ===== EFEITOS DE DIGITAÇÃO =====
    setupTypingEffects() {
        const typingElements = document.querySelectorAll('[data-typing]');
        
        typingElements.forEach(element => {
            const text = element.textContent;
            const speed = parseInt(element.dataset.typingSpeed) || 100;
            
            element.textContent = '';
            element.dataset.typingText = text;
            
            this.typeText(element, text, speed);
        });
    }

    typeText(element, text, speed) {
        let index = 0;
        
        const typeNextChar = () => {
            if (index < text.length) {
                element.textContent += text.charAt(index);
                index++;
                setTimeout(typeNextChar, speed);
            }
        };
        
        // Iniciar digitação quando visível
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    typeNextChar();
                    observer.unobserve(entry.target);
                }
            });
        });
        
        observer.observe(element);
    }

    // ===== ANIMAÇÕES PERSONALIZADAS =====
    addCustomAnimation(name, config) {
        this.animations.set(name, config);
    }

    // ===== CONTROLES DE ANIMAÇÃO =====
    pauseAllAnimations() {
        document.body.style.setProperty('animation-play-state', 'paused');
        document.body.style.setProperty('transition', 'none');
    }

    resumeAllAnimations() {
        document.body.style.removeProperty('animation-play-state');
        document.body.style.removeProperty('transition');
    }

    // ===== PERFORMANCE =====
    optimizeForPerformance() {
        // Reduzir animações em dispositivos com baixa performance
        if ('connection' in navigator) {
            const connection = navigator.connection;
            if (connection.effectiveType === 'slow-2g' || connection.effectiveType === '2g') {
                this.disableComplexAnimations();
            }
        }
        
        // Verificar preferências de usuário
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            this.disableAllAnimations();
        }
    }

    disableComplexAnimations() {
        const complexElements = document.querySelectorAll('[data-animate="scaleIn"], [data-animate="slideInUp"]');
        complexElements.forEach(el => {
            el.dataset.animate = 'fadeInUp';
        });
    }

    disableAllAnimations() {
        document.body.classList.add('reduced-motion');
        const animatedElements = document.querySelectorAll('[data-animate]');
        animatedElements.forEach(el => {
            el.style.animation = 'none';
            el.style.transition = 'none';
        });
    }
}

// ===== INICIALIZAÇÃO =====
document.addEventListener('DOMContentLoaded', () => {
    window.animationSystem = new AnimationSystem();
});

// ===== CONTROLES GLOBAIS =====
window.AnimationControls = {
    pause: () => {
        if (window.animationSystem) {
            window.animationSystem.pauseAllAnimations();
        }
    },
    
    resume: () => {
        if (window.animationSystem) {
            window.animationSystem.resumeAllAnimations();
        }
    },
    
    addCustom: (name, config) => {
        if (window.animationSystem) {
            window.animationSystem.addCustomAnimation(name, config);
        }
    },
    
    optimize: () => {
        if (window.animationSystem) {
            window.animationSystem.optimizeForPerformance();
        }
    }
};

// ===== CSS ANIMATIONS =====
const animationCSS = `
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes fadeInLeft {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes fadeInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes scaleIn {
        from {
            opacity: 0;
            transform: scale(0.8);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(100px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
    }
    
    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% {
            transform: translateY(0);
        }
        40% {
            transform: translateY(-10px);
        }
        60% {
            transform: translateY(-5px);
        }
    }
    
    @keyframes float {
        0%, 100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-20px);
        }
    }
    
    @keyframes shimmer {
        0% {
            background-position: -200px 0;
        }
        100% {
            background-position: calc(200px + 100%) 0;
        }
    }
    
    .animate-in {
        animation-fill-mode: both;
    }
    
    .animate-continuous {
        animation-iteration-count: infinite;
    }
    
    .sticky-active {
        position: fixed;
        top: 0;
        z-index: 1000;
    }
    
    .reduced-motion * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
    
    .shimmer {
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
        background-size: 200px 100%;
        animation: shimmer 2s infinite;
    }
`;

// Adicionar CSS ao documento
const style = document.createElement('style');
style.textContent = animationCSS;
document.head.appendChild(style);
