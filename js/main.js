// ===== ZELALAR - JAVASCRIPT PRINCIPAL =====
// Sistema moderno de interações e animações

class ZelaLar {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initLoadingScreen();
        this.initHeaderScroll();
        this.initSmoothScrolling();
        this.initAnimations();
        this.initTestimonialsSlider();
        this.initStatsCounter();
        this.initMobileMenu();
        this.initBackToTop();
        this.initWhatsAppFloat();
        this.initParticles();
        this.initIntersectionObserver();
        this.initFormValidation();
        this.initPhoneMask();
        this.initTooltips();
        this.initLazyLoading();
        this.initPerformanceOptimizations();
        
    }

    // ===== LOADING SCREEN =====
    initLoadingScreen() {
        const loadingScreen = document.getElementById('loading-screen');
        if (!loadingScreen) return;

        // Simular tempo de carregamento
        setTimeout(() => {
            loadingScreen.classList.add('fade-out');
            setTimeout(() => {
                loadingScreen.remove();
                this.triggerPageLoadAnimations();
            }, 500);
        }, 1500);
    }

    // ===== HEADER SCROLL EFFECT =====
    initHeaderScroll() {
        const header = document.getElementById('header');
        if (!header) return;

        let lastScrollTop = 0;
        const scrollThreshold = 100;

        window.addEventListener('scroll', () => {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            // Adicionar classe scrolled
            if (scrollTop > scrollThreshold) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }

            // Esconder/mostrar header baseado na direção do scroll
            if (scrollTop > lastScrollTop && scrollTop > 200) {
                header.style.transform = 'translateY(-100%)';
            } else {
                header.style.transform = 'translateY(0)';
            }

            lastScrollTop = scrollTop;
        });
    }

    // ===== SMOOTH SCROLLING =====
    initSmoothScrolling() {
        const navLinks = document.querySelectorAll('.nav-link[href^="#"]');
        
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = link.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    const headerHeight = document.getElementById('header')?.offsetHeight || 0;
                    const targetPosition = targetElement.offsetTop - headerHeight - 20;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });

                    // Atualizar navegação ativa
                    this.updateActiveNavigation(targetId);
                }
            });
        });
    }

    // ===== ACTIVE NAVIGATION =====
    updateActiveNavigation(activeId) {
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === activeId) {
                link.classList.add('active');
            }
        });
    }

    // ===== ANIMAÇÕES DE ENTRADA =====
    initAnimations() {
        const animatedElements = document.querySelectorAll('.category-card, .step-card, .testimonial-card');
        
        animatedElements.forEach((element, index) => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                element.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, index * 100);
        });
    }

    // ===== TESTIMONIALS SLIDER =====
    initTestimonialsSlider() {
        const slider = document.getElementById('testimonials-slider');
        const prevBtn = document.getElementById('prev-testimonial');
        const nextBtn = document.getElementById('next-testimonial');
        
        if (!slider || !prevBtn || !nextBtn) return;

        let currentIndex = 0;
        const cards = slider.querySelectorAll('.testimonial-card');
        const cardWidth = 350 + 48; // card width + gap

        const updateSlider = () => {
            slider.style.transform = `translateX(-${currentIndex * cardWidth}px)`;
            
            // Atualizar estado dos botões
            prevBtn.disabled = currentIndex === 0;
            nextBtn.disabled = currentIndex >= cards.length - 1;
        };

        prevBtn.addEventListener('click', () => {
            if (currentIndex > 0) {
                currentIndex--;
                updateSlider();
            }
        });

        nextBtn.addEventListener('click', () => {
            if (currentIndex < cards.length - 1) {
                currentIndex++;
                updateSlider();
            }
        });

        // Auto-play
        setInterval(() => {
            if (currentIndex < cards.length - 1) {
                currentIndex++;
            } else {
                currentIndex = 0;
            }
            updateSlider();
        }, 5000);

        // Touch/swipe support
        let startX = 0;
        let endX = 0;

        slider.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
        });

        slider.addEventListener('touchend', (e) => {
            endX = e.changedTouches[0].clientX;
            const diff = startX - endX;
            
            if (Math.abs(diff) > 50) {
                if (diff > 0 && currentIndex < cards.length - 1) {
                    currentIndex++;
                } else if (diff < 0 && currentIndex > 0) {
                    currentIndex--;
                }
                updateSlider();
            }
        });
    }

    // ===== STATS COUNTER =====
    initStatsCounter() {
        const statNumbers = document.querySelectorAll('.stat-number[data-target]');
        
        const animateCounter = (element) => {
            const target = parseInt(element.getAttribute('data-target'));
            const duration = 2000;
            const step = target / (duration / 16);
            let current = 0;
            
            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current);
            }, 16);
        };

        // Animar quando visível
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        });

        statNumbers.forEach(stat => observer.observe(stat));
    }

    // ===== MOBILE MENU =====
    initMobileMenu() {
        const mobileToggle = document.getElementById('mobile-menu-toggle');
        const nav = document.getElementById('main-nav');
        
        if (!mobileToggle || !nav) return;

        mobileToggle.addEventListener('click', () => {
            nav.classList.toggle('mobile-open');
            mobileToggle.classList.toggle('active');
            
            // Animar hamburger
            const spans = mobileToggle.querySelectorAll('span');
            if (mobileToggle.classList.contains('active')) {
                spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(-45deg) translate(7px, -6px)';
            } else {
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            }
        });

        // Fechar menu ao clicar em um link
        const navLinks = nav.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                nav.classList.remove('mobile-open');
                mobileToggle.classList.remove('active');
            });
        });
    }

    // ===== BACK TO TOP =====
    initBackToTop() {
        const backToTopBtn = document.getElementById('back-to-top');
        if (!backToTopBtn) return;

        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        });

        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // ===== WHATSAPP FLOAT =====
    initWhatsAppFloat() {
        const whatsappFloat = document.getElementById('whatsapp-float');
        if (!whatsappFloat) return;

        // Adicionar efeito de pulso
        setInterval(() => {
            whatsappFloat.style.transform = 'scale(1.1)';
            setTimeout(() => {
                whatsappFloat.style.transform = 'scale(1)';
            }, 200);
        }, 3000);
    }

    // ===== PARTICLES BACKGROUND =====
    initParticles() {
        const particlesContainer = document.getElementById('hero-particles');
        if (!particlesContainer) return;

        // Criar partículas
        for (let i = 0; i < 50; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.cssText = `
                position: absolute;
                width: ${Math.random() * 4 + 2}px;
                height: ${Math.random() * 4 + 2}px;
                background: rgba(255, 255, 255, ${Math.random() * 0.5 + 0.2});
                border-radius: 50%;
                left: ${Math.random() * 100}%;
                top: ${Math.random() * 100}%;
                animation: float-particle ${Math.random() * 10 + 10}s infinite linear;
                animation-delay: ${Math.random() * 5}s;
            `;
            particlesContainer.appendChild(particle);
        }

        // Adicionar CSS para animação
        const style = document.createElement('style');
        style.textContent = `
            @keyframes float-particle {
                0% { transform: translateY(0px) rotate(0deg); opacity: 1; }
                100% { transform: translateY(-100vh) rotate(360deg); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    }

    // ===== INTERSECTION OBSERVER =====
    initIntersectionObserver() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    
                    // Adicionar delay para elementos filhos
                    const children = entry.target.querySelectorAll('.animate-delay');
                    children.forEach((child, index) => {
                        setTimeout(() => {
                            child.classList.add('animate-in');
                        }, index * 100);
                    });
                }
            });
        }, observerOptions);

        // Observar elementos
        const elementsToObserve = document.querySelectorAll('.category-card, .step-card, .testimonial-card, .about-content');
        elementsToObserve.forEach(el => observer.observe(el));
    }

    // ===== FORM VALIDATION =====
    initFormValidation() {
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input, select, textarea');
            
            inputs.forEach(input => {
                // Validação em tempo real
                input.addEventListener('blur', () => this.validateField(input));
                input.addEventListener('input', () => this.clearFieldError(input));
                
                // Validação no submit
                form.addEventListener('submit', (e) => {
                    if (!this.validateForm(form)) {
                        e.preventDefault();
                        this.showFormError('Por favor, corrija os erros no formulário.');
                    }
                });
            });
        });
    }

    validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        let errorMessage = '';

        // Validações específicas
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            errorMessage = 'Este campo é obrigatório.';
        } else if (field.type === 'email' && value && !this.isValidEmail(value)) {
            isValid = false;
            errorMessage = 'Email inválido.';
        } else if (field.type === 'tel' && value && !this.isValidPhone(value)) {
            isValid = false;
            errorMessage = 'Telefone inválido.';
        }

        if (!isValid) {
            this.showFieldError(field, errorMessage);
        }

        return isValid;
    }

    validateForm(form) {
        const fields = form.querySelectorAll('input, select, textarea');
        let isValid = true;

        fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    showFieldError(field, message) {
        this.clearFieldError(field);
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = message;
        errorDiv.style.cssText = `
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            animation: slideInDown 0.3s ease-out;
        `;
        
        field.parentNode.appendChild(errorDiv);
        field.classList.add('error');
        field.style.borderColor = '#dc3545';
    }

    clearFieldError(field) {
        const errorDiv = field.parentNode.querySelector('.field-error');
        if (errorDiv) {
            errorDiv.remove();
        }
        field.classList.remove('error');
        field.style.borderColor = '';
    }

    showFormError(message) {
        this.showNotification(message, 'error');
    }

    // ===== PHONE MASK =====
    initPhoneMask() {
        const phoneFields = document.querySelectorAll('input[type="tel"]');
        
        phoneFields.forEach(field => {
            field.addEventListener('input', (e) => {
                let value = e.target.value.replace(/\D/g, '');
                
                if (value.length <= 11) {
                    if (value.length <= 2) {
                        value = `(${value}`;
                    } else if (value.length <= 6) {
                        value = `(${value.slice(0,2)}) ${value.slice(2)}`;
                    } else if (value.length <= 10) {
                        value = `(${value.slice(0,2)}) ${value.slice(2,6)}-${value.slice(6)}`;
                    } else {
                        value = `(${value.slice(0,2)}) ${value.slice(2,7)}-${value.slice(7)}`;
                    }
                }
                
                e.target.value = value;
            });
        });
    }

    // ===== TOOLTIPS =====
    initTooltips() {
        const tooltipElements = document.querySelectorAll('[data-tooltip]');
        
        tooltipElements.forEach(element => {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = element.getAttribute('data-tooltip');
            document.body.appendChild(tooltip);
            
            element.addEventListener('mouseenter', () => {
                const rect = element.getBoundingClientRect();
                tooltip.style.cssText = `
                    position: fixed;
                    top: ${rect.top - 40}px;
                    left: ${rect.left + rect.width / 2}px;
                    transform: translateX(-50%);
                    background: #333;
                    color: white;
                    padding: 8px 12px;
                    border-radius: 6px;
                    font-size: 14px;
                    z-index: 1000;
                    opacity: 1;
                    transition: opacity 0.3s;
                `;
            });
            
            element.addEventListener('mouseleave', () => {
                tooltip.style.opacity = '0';
            });
        });
    }

    // ===== LAZY LOADING =====
    initLazyLoading() {
        const images = document.querySelectorAll('img[data-src]');
        
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));
    }

    // ===== PERFORMANCE OPTIMIZATIONS =====
    initPerformanceOptimizations() {
        // Debounce para eventos de scroll
        let scrollTimeout;
        window.addEventListener('scroll', () => {
            if (scrollTimeout) {
                clearTimeout(scrollTimeout);
            }
            scrollTimeout = setTimeout(() => {
                // Executar ações de scroll otimizadas
            }, 16);
        });

        // Throttle para resize
        let resizeTimeout;
        window.addEventListener('resize', () => {
            if (resizeTimeout) {
                clearTimeout(resizeTimeout);
            }
            resizeTimeout = setTimeout(() => {
                // Executar ações de resize
            }, 250);
        });
    }

    // ===== UTILITY FUNCTIONS =====
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    isValidPhone(phone) {
        const cleanPhone = phone.replace(/\D/g, '');
        return cleanPhone.length >= 10 && cleanPhone.length <= 11;
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            transform: translateX(100%);
            transition: transform 0.3s ease-out;
            max-width: 400px;
        `;

        // Cores baseadas no tipo
        const colors = {
            info: '#17a2b8',
            success: '#28a745',
            warning: '#ffc107',
            error: '#dc3545'
        };

        notification.style.background = colors[type] || colors.info;

        document.body.appendChild(notification);

        // Animar entrada
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);

        // Remover automaticamente
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        }, 5000);
    }

    // ===== TRIGGER ANIMATIONS =====
    triggerPageLoadAnimations() {
        // Animar elementos da página
        const heroTitle = document.querySelector('.hero-title');
        const heroSubtitle = document.querySelector('.hero-subtitle');
        const heroActions = document.querySelector('.hero-actions');
        const heroStats = document.querySelector('.hero-stats');

        if (heroTitle) {
            setTimeout(() => heroTitle.classList.add('animate-in'), 200);
        }
        if (heroSubtitle) {
            setTimeout(() => heroSubtitle.classList.add('animate-in'), 400);
        }
        if (heroStats) {
            setTimeout(() => heroStats.classList.add('animate-in'), 600);
        }
        if (heroActions) {
            setTimeout(() => heroActions.classList.add('animate-in'), 800);
        }
    }

    // ===== EVENT LISTENERS =====
    setupEventListeners() {
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                // Fechar modais/menus
                const mobileMenu = document.getElementById('main-nav');
                if (mobileMenu?.classList.contains('mobile-open')) {
                    mobileMenu.classList.remove('mobile-open');
                }
            }
        });

        // Click outside to close
        document.addEventListener('click', (e) => {
            const mobileMenu = document.getElementById('main-nav');
            const mobileToggle = document.getElementById('mobile-menu-toggle');
            
            if (mobileMenu?.classList.contains('mobile-open') && 
                !mobileMenu.contains(e.target) && 
                !mobileToggle?.contains(e.target)) {
                mobileMenu.classList.remove('mobile-open');
                mobileToggle?.classList.remove('active');
            }
        });

        // Preload critical resources
        this.preloadResources();
    }

    // ===== RESOURCE PRELOADING =====
    preloadResources() {
        const criticalImages = [
            '/img/og-image.jpg',
            '/img/hero-bg.jpg'
        ];

        criticalImages.forEach(src => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.as = 'image';
            link.href = src;
            document.head.appendChild(link);
        });
    }
}

// Garantia de apenas um ZelaLar global
if (!window._zelaLarInstance) {
  window._zelaLarInstance = new ZelaLar();
}

// ===== INITIALIZATION =====
document.addEventListener('DOMContentLoaded', () => {
    // new ZelaLar(); // This line is now redundant as ZelaLar is a singleton
});

// ===== SERVICE WORKER REGISTRATION =====
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
            })
            .catch(registrationError => {
            });
    });
}

// ===== PWA INSTALL PROMPT =====
let deferredPrompt;

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    
    // Mostrar botão de instalação se desejar
    const installButton = document.getElementById('install-app');
    if (installButton) {
        installButton.style.display = 'block';
        installButton.addEventListener('click', () => {
            deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                    }
                    deferredPrompt = null;
                });
        });
    }
});

// ===== ANALYTICS & TRACKING =====
class Analytics {
    static trackEvent(category, action, label = null) {
        if (typeof gtag !== 'undefined') {
            gtag('event', action, {
                event_category: category,
                event_label: label
            });
        }
        
        // Fallback para console
        // console.log(`Analytics: ${category} - ${action}${label ? ` - ${label}` : ''}`);
    }

    static trackPageView(page) {
        this.trackEvent('Navigation', 'Page View', page);
    }

    static trackButtonClick(buttonName) {
        this.trackEvent('Interaction', 'Button Click', buttonName);
    }

    static trackFormSubmission(formName) {
        this.trackEvent('Form', 'Submission', formName);
    }
}

// ===== ERROR TRACKING =====
window.addEventListener('error', (e) => {
    console.error('JavaScript Error:', e.error);
    Analytics.trackEvent('Error', 'JavaScript Error', e.message);
});

window.addEventListener('unhandledrejection', (e) => {
    console.error('Unhandled Promise Rejection:', e.reason);
    Analytics.trackEvent('Error', 'Promise Rejection', e.reason);
});

// ===== PERFORMANCE MONITORING =====
window.addEventListener('load', () => {
    if ('performance' in window) {
        const perfData = performance.getEntriesByType('navigation')[0];
        const loadTime = perfData.loadEventEnd - perfData.loadEventStart;
        
        // console.log(`Page load time: ${loadTime}ms`);
        Analytics.trackEvent('Performance', 'Page Load', `${loadTime}ms`);
    }
});

// ===== EXPORT FOR GLOBAL USE =====
window.ZelaLar = ZelaLar;
window.Analytics = Analytics;
