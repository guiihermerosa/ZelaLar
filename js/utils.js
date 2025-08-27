// ===== ZELALAR - UTILITÁRIOS JAVASCRIPT =====
// Funções auxiliares e utilitários para o sistema

class Utils {
    // ===== MANIPULAÇÃO DO DOM =====
    
    /**
     * Cria um elemento HTML com atributos e conteúdo
     */
    static createElement(tag, attributes = {}, content = '') {
        const element = document.createElement(tag);
        
        // Adicionar atributos
        Object.entries(attributes).forEach(([key, value]) => {
            if (key === 'className') {
                element.className = value;
            } else if (key === 'innerHTML') {
                element.innerHTML = value;
            } else {
                element.setAttribute(key, value);
            }
        });
        
        // Adicionar conteúdo
        if (content) {
            if (typeof content === 'string') {
                element.textContent = content;
            } else if (content instanceof Node) {
                element.appendChild(content);
            } else if (Array.isArray(content)) {
                content.forEach(item => {
                    if (item instanceof Node) {
                        element.appendChild(item);
                    } else {
                        element.appendChild(document.createTextNode(item));
                    }
                });
            }
        }
        
        return element;
    }
    
    /**
     * Remove todos os filhos de um elemento
     */
    static clearElement(element) {
        while (element.firstChild) {
            element.removeChild(element.firstChild);
        }
    }
    
    /**
     * Adiciona ou remove classes de forma segura
     */
    static toggleClass(element, className, force = null) {
        if (force === null) {
            element.classList.toggle(className);
        } else {
            element.classList.toggle(className, force);
        }
    }
    
    /**
     * Verifica se um elemento está visível na viewport
     */
    static isElementInViewport(element) {
        const rect = element.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }
    
    // ===== MANIPULAÇÃO DE STRINGS =====
    
    /**
     * Capitaliza a primeira letra de uma string
     */
    static capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
    }
    
    /**
     * Formata um número de telefone brasileiro
     */
    static formatPhone(phone) {
        const clean = phone.replace(/\D/g, '');
        
        if (clean.length === 11) {
            return `(${clean.slice(0,2)}) ${clean.slice(2,7)}-${clean.slice(7)}`;
        } else if (clean.length === 10) {
            return `(${clean.slice(0,2)}) ${clean.slice(2,6)}-${clean.slice(6)}`;
        }
        
        return phone;
    }
    
    /**
     * Formata CPF
     */
    static formatCPF(cpf) {
        const clean = cpf.replace(/\D/g, '');
        return clean.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    }
    
    /**
     * Formata CEP
     */
    static formatCEP(cep) {
        const clean = cep.replace(/\D/g, '');
        return clean.replace(/(\d{5})(\d{3})/, '$1-$2');
    }
    
    /**
     * Formata moeda brasileira
     */
    static formatCurrency(value) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(value);
    }
    
    /**
     * Formata data brasileira
     */
    static formatDate(date, options = {}) {
        const defaultOptions = {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        };
        
        const dateObj = new Date(date);
        return dateObj.toLocaleDateString('pt-BR', { ...defaultOptions, ...options });
    }
    
    /**
     * Formata data relativa (ex: "há 2 dias")
     */
    static formatRelativeDate(date) {
        const now = new Date();
        const target = new Date(date);
        const diffTime = Math.abs(now - target);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays === 0) return 'Hoje';
        if (diffDays === 1) return 'Ontem';
        if (diffDays < 7) return `Há ${diffDays} dias`;
        if (diffDays < 30) return `Há ${Math.floor(diffDays / 7)} semanas`;
        if (diffDays < 365) return `Há ${Math.floor(diffDays / 30)} meses`;
        
        return `Há ${Math.floor(diffDays / 365)} anos`;
    }
    
    // ===== VALIDAÇÕES =====
    
    /**
     * Valida email
     */
    static isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    /**
     * Valida CPF
     */
    static isValidCPF(cpf) {
        const clean = cpf.replace(/\D/g, '');
        
        if (clean.length !== 11) return false;
        
        // Verificar dígitos repetidos
        if (/^(\d)\1{10}$/.test(clean)) return false;
        
        // Validar primeiro dígito verificador
        let sum = 0;
        for (let i = 0; i < 9; i++) {
            sum += parseInt(clean.charAt(i)) * (10 - i);
        }
        let remainder = (sum * 10) % 11;
        if (remainder === 10 || remainder === 11) remainder = 0;
        if (remainder !== parseInt(clean.charAt(9))) return false;
        
        // Validar segundo dígito verificador
        sum = 0;
        for (let i = 0; i < 10; i++) {
            sum += parseInt(clean.charAt(i)) * (11 - i);
        }
        remainder = (sum * 10) % 11;
        if (remainder === 10 || remainder === 11) remainder = 0;
        if (remainder !== parseInt(clean.charAt(10))) return false;
        
        return true;
    }
    
    /**
     * Valida CNPJ
     */
    static isValidCNPJ(cnpj) {
        const clean = cnpj.replace(/\D/g, '');
        
        if (clean.length !== 14) return false;
        
        // Verificar dígitos repetidos
        if (/^(\d)\1{13}$/.test(clean)) return false;
        
        // Validar primeiro dígito verificador
        let sum = 0;
        let weight = 2;
        for (let i = 11; i >= 0; i--) {
            sum += parseInt(clean.charAt(i)) * weight;
            weight = weight === 9 ? 2 : weight + 1;
        }
        let remainder = sum % 11;
        let digit1 = remainder < 2 ? 0 : 11 - remainder;
        if (digit1 !== parseInt(clean.charAt(12))) return false;
        
        // Validar segundo dígito verificador
        sum = 0;
        weight = 2;
        for (let i = 12; i >= 0; i--) {
            sum += parseInt(clean.charAt(i)) * weight;
            weight = weight === 9 ? 2 : weight + 1;
        }
        remainder = sum % 11;
        let digit2 = remainder < 2 ? 0 : 11 - remainder;
        if (digit2 !== parseInt(clean.charAt(13))) return false;
        
        return true;
    }
    
    /**
     * Valida telefone brasileiro
     */
    static isValidPhone(phone) {
        const clean = phone.replace(/\D/g, '');
        return clean.length >= 10 && clean.length <= 11;
    }
    
    /**
     * Valida CEP
     */
    static isValidCEP(cep) {
        const clean = cep.replace(/\D/g, '');
        return clean.length === 8;
    }
    
    // ===== MANIPULAÇÃO DE ARRAYS =====
    
    /**
     * Remove duplicatas de um array
     */
    static removeDuplicates(array, key = null) {
        if (key) {
            const seen = new Set();
            return array.filter(item => {
                const value = item[key];
                if (seen.has(value)) {
                    return false;
                }
                seen.add(value);
                return true;
            });
        }
        return [...new Set(array)];
    }
    
    /**
     * Agrupa array por uma chave
     */
    static groupBy(array, key) {
        return array.reduce((groups, item) => {
            const group = item[key];
            if (!groups[group]) {
                groups[group] = [];
            }
            groups[group].push(item);
            return groups;
        }, {});
    }
    
    /**
     * Ordena array por múltiplas chaves
     */
    static sortBy(array, ...keys) {
        return array.sort((a, b) => {
            for (const key of keys) {
                const aVal = a[key];
                const bVal = b[key];
                
                if (aVal < bVal) return -1;
                if (aVal > bVal) return 1;
            }
            return 0;
        });
    }
    
    /**
     * Filtra array por múltiplos critérios
     */
    static filterBy(array, filters) {
        return array.filter(item => {
            return Object.entries(filters).every(([key, value]) => {
                if (typeof value === 'function') {
                    return value(item[key]);
                }
                if (Array.isArray(value)) {
                    return value.includes(item[key]);
                }
                return item[key] === value;
            });
        });
    }
    
    // ===== MANIPULAÇÃO DE OBJETOS =====
    
    /**
     * Faz deep clone de um objeto
     */
    static deepClone(obj) {
        if (obj === null || typeof obj !== 'object') return obj;
        if (obj instanceof Date) return new Date(obj.getTime());
        if (obj instanceof Array) return obj.map(item => this.deepClone(item));
        if (typeof obj === 'object') {
            const cloned = {};
            for (const key in obj) {
                if (obj.hasOwnProperty(key)) {
                    cloned[key] = this.deepClone(obj[key]);
                }
            }
            return cloned;
        }
    }
    
    /**
     * Mescla objetos de forma profunda
     */
    static deepMerge(target, source) {
        const result = { ...target };
        
        for (const key in source) {
            if (source.hasOwnProperty(key)) {
                if (source[key] && typeof source[key] === 'object' && !Array.isArray(source[key])) {
                    result[key] = this.deepMerge(result[key] || {}, source[key]);
                } else {
                    result[key] = source[key];
                }
            }
        }
        
        return result;
    }
    
    /**
     * Remove propriedades undefined/null de um objeto
     */
    static cleanObject(obj) {
        const cleaned = {};
        for (const [key, value] of Object.entries(obj)) {
            if (value !== undefined && value !== null) {
                cleaned[key] = value;
            }
        }
        return cleaned;
    }
    
    // ===== FUNÇÕES DE TEMPO =====
    
    /**
     * Debounce function
     */
    static debounce(func, wait, immediate = false) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                timeout = null;
                if (!immediate) func(...args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func(...args);
        };
    }
    
    /**
     * Throttle function
     */
    static throttle(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
    
    /**
     * Sleep function
     */
    static sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
    
    // ===== FUNÇÕES DE STORAGE =====
    
    /**
     * Salva dados no localStorage com expiração
     */
    static setLocalStorage(key, value, expirationHours = 24) {
        const item = {
            value,
            timestamp: new Date().getTime(),
            expiresIn: expirationHours * 60 * 60 * 1000
        };
        localStorage.setItem(key, JSON.stringify(item));
    }
    
    /**
     * Recupera dados do localStorage
     */
    static getLocalStorage(key) {
        const item = localStorage.getItem(key);
        if (!item) return null;
        
        const parsed = JSON.parse(item);
        const now = new Date().getTime();
        
        if (now - parsed.timestamp > parsed.expiresIn) {
            localStorage.removeItem(key);
            return null;
        }
        
        return parsed.value;
    }
    
    /**
     * Remove dados do localStorage
     */
    static removeLocalStorage(key) {
        localStorage.removeItem(key);
    }
    
    /**
     * Limpa todo o localStorage
     */
    static clearLocalStorage() {
        localStorage.clear();
    }
    
    // ===== FUNÇÕES DE URL =====
    
    /**
     * Obtém parâmetros da URL
     */
    static getUrlParams() {
        const params = new URLSearchParams(window.location.search);
        const result = {};
        for (const [key, value] of params) {
            result[key] = value;
        }
        return result;
    }
    
    /**
     * Adiciona parâmetros à URL
     */
    static addUrlParams(params) {
        const url = new URL(window.location);
        Object.entries(params).forEach(([key, value]) => {
            url.searchParams.set(key, value);
        });
        window.history.pushState({}, '', url);
    }
    
    /**
     * Remove parâmetros da URL
     */
    static removeUrlParams(params) {
        const url = new URL(window.location);
        params.forEach(param => {
            url.searchParams.delete(param);
        });
        window.history.pushState({}, '', url);
    }
    
    // ===== FUNÇÕES DE PERFORMANCE =====
    
    /**
     * Mede o tempo de execução de uma função
     */
    static measureTime(func, ...args) {
        const start = performance.now();
        const result = func(...args);
        const end = performance.now();
        return { result, time: end - start };
    }
    
    /**
     * Executa função em chunks para não bloquear a UI
     */
    static async chunkedExecution(items, processFunction, chunkSize = 100) {
        const results = [];
        
        for (let i = 0; i < items.length; i += chunkSize) {
            const chunk = items.slice(i, i + chunkSize);
            const chunkResults = await Promise.all(
                chunk.map(item => processFunction(item))
            );
            results.push(...chunkResults);
            
            // Yield para não bloquear a UI
            await new Promise(resolve => setTimeout(resolve, 0));
        }
        
        return results;
    }
    
    // ===== FUNÇÕES DE VALIDAÇÃO DE FORMULÁRIOS =====
    
    /**
     * Valida formulário completo
     */
    static validateForm(form) {
        const inputs = form.querySelectorAll('input, select, textarea');
        const errors = [];
        
        inputs.forEach(input => {
            const validation = this.validateInput(input);
            if (!validation.isValid) {
                errors.push(validation);
            }
        });
        
        return {
            isValid: errors.length === 0,
            errors
        };
    }
    
    /**
     * Valida um input específico
     */
    static validateInput(input) {
        const value = input.value.trim();
        const type = input.type;
        const required = input.hasAttribute('required');
        const minLength = input.getAttribute('minlength');
        const maxLength = input.getAttribute('maxlength');
        const pattern = input.getAttribute('pattern');
        
        // Validação de campo obrigatório
        if (required && !value) {
            return {
                isValid: false,
                field: input.name || input.id,
                message: 'Este campo é obrigatório',
                input
            };
        }
        
        // Validação de comprimento
        if (minLength && value.length < parseInt(minLength)) {
            return {
                isValid: false,
                field: input.name || input.id,
                message: `Mínimo de ${minLength} caracteres`,
                input
            };
        }
        
        if (maxLength && value.length > parseInt(maxLength)) {
            return {
                isValid: false,
                field: input.name || input.id,
                message: `Máximo de ${maxLength} caracteres`,
                input
            };
        }
        
        // Validações específicas por tipo
        if (value) {
            switch (type) {
                case 'email':
                    if (!this.isValidEmail(value)) {
                        return {
                            isValid: false,
                            field: input.name || input.id,
                            message: 'Email inválido',
                            input
                        };
                    }
                    break;
                    
                case 'tel':
                    if (!this.isValidPhone(value)) {
                        return {
                            isValid: false,
                            field: input.name || input.id,
                            message: 'Telefone inválido',
                            input
                        };
                    }
                    break;
                    
                case 'url':
                    try {
                        new URL(value);
                    } catch {
                        return {
                            isValid: false,
                            field: input.name || input.id,
                            message: 'URL inválida',
                            input
                        };
                    }
                    break;
            }
            
            // Validação de padrão
            if (pattern && !new RegExp(pattern).test(value)) {
                return {
                    isValid: false,
                    field: input.name || input.id,
                    message: 'Formato inválido',
                    input
                };
            }
        }
        
        return { isValid: true, field: input.name || input.id, input };
    }
}

// ===== EXPORTAÇÃO GLOBAL =====
window.Utils = Utils;

// ===== FUNÇÕES DE CONVENIÊNCIA =====
window.$ = (selector) => document.querySelector(selector);
window.$$ = (selector) => document.querySelectorAll(selector);
window.createElement = Utils.createElement.bind(Utils);
window.formatPhone = Utils.formatPhone.bind(Utils);
window.formatCurrency = Utils.formatCurrency.bind(Utils);
window.formatDate = Utils.formatDate.bind(Utils);
window.isValidEmail = Utils.isValidEmail.bind(Utils);
window.isValidCPF = Utils.isValidCPF.bind(Utils);
window.debounce = Utils.debounce.bind(Utils);
window.throttle = Utils.throttle.bind(Utils);
