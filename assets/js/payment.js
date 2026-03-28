/**
 * WwwPay - Payment Core JavaScript
 * Version: 1.0.0
 */

class WwwPay {
    constructor(options = {}) {
        this.options = {
            gateway: 'stripe',
            currency: 'USD',
            amount: 0,
            ...options
        };
        
        this.gateways = this.getGateways();
        this.callbacks = {
            onSuccess: options.onSuccess || (() => {}),
            onError: options.onError || (() => {}),
            onPending: options.onPending || (() => {})
        };
    }
    
    getGateways() {
        return {
            stripe: { name: 'Stripe', icon: '💳' },
            paypal: { name: 'PayPal', icon: '🅿️' },
            paystack: { name: 'Paystack', icon: '💚' },
            flutterwave: { name: 'Flutterwave', icon: '🌍' },
            bkash: { name: 'bKash', icon: '💰' },
            alipay: { name: 'Alipay', icon: '🟢' },
            wechat: { name: 'WeChat Pay', icon: '💬' },
            mada: { name: 'Mada', icon: '🇸🇦' }
        };
    }
    
    setGateway(gateway) {
        this.options.gateway = gateway;
        return this;
    }
    
    setAmount(amount) {
        this.options.amount = amount;
        return this;
    }
    
    setCurrency(currency) {
        this.options.currency = currency;
        return this;
    }
    
    async process(data = {}) {
        try {
            const response = await this.sendPayment({
                ...this.options,
                ...data
            });
            
            if (response.success) {
                this.callbacks.onSuccess(response);
            } else if (response.pending) {
                this.callbacks.onPending(response);
            } else {
                this.callbacks.onError(response.error);
            }
            
            return response;
        } catch (error) {
            this.callbacks.onError(error.message);
            throw error;
        }
    }
    
    async sendPayment(data) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        const response = await fetch('/api/payment/process', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(data)
        });
        
        return await response.json();
    }
}

// Card Validator
class CardValidator {
    constructor() {
        this.cardTypes = {
            visa: { pattern: /^4/, name: 'Visa' },
            mastercard: { pattern: /^5[1-5]/, name: 'Mastercard' },
            amex: { pattern: /^3[47]/, name: 'American Express' },
            discover: { pattern: /^6(?:011|5)/, name: 'Discover' },
            diners: { pattern: /^3[0689]/, name: 'Diners Club' },
            jcb: { pattern: /^35/, name: 'JCB' }
        };
    }
    
    validateNumber(number) {
        const cleaned = number.replace(/\D/g, '');
        
        if (!/^\d{13,19}$/.test(cleaned)) {
            return { valid: false, error: 'Invalid card number length' };
        }
        
        if (!this.luhnCheck(cleaned)) {
            return { valid: false, error: 'Invalid card number' };
        }
        
        return { 
            valid: true, 
            type: this.getType(cleaned),
            masked: this.mask(cleaned)
        };
    }
    
    luhnCheck(number) {
        let sum = 0;
        let isEven = false;
        
        for (let i = number.length - 1; i >= 0; i--) {
            let digit = parseInt(number[i], 10);
            
            if (isEven) {
                digit *= 2;
                if (digit > 9) {
                    digit -= 9;
                }
            }
            
            sum += digit;
            isEven = !isEven;
        }
        
        return sum % 10 === 0;
    }
    
    getType(number) {
        for (const [type, data] of Object.entries(this.cardTypes)) {
            if (data.pattern.test(number)) {
                return type;
            }
        }
        return 'unknown';
    }
    
    validateExpiry(month, year) {
        const now = new Date();
        const currentYear = now.getFullYear();
        const currentMonth = now.getMonth() + 1;
        
        const expMonth = parseInt(month, 10);
        const expYear = parseInt(year, 10) + 2000;
        
        if (expMonth < 1 || expMonth > 12) {
            return { valid: false, error: 'Invalid month' };
        }
        
        if (expYear < currentYear || (expYear === currentYear && expMonth < currentMonth)) {
            return { valid: false, error: 'Card has expired' };
        }
        
        return { valid: true };
    }
    
    validateCvv(cvv, cardType = 'visa') {
        const length = cardType === 'amex' ? 4 : 3;
        return /^\d{$length}$/.test(cvv) 
            ? { valid: true } 
            : { valid: false, error: `CVV must be ${length} digits` };
    }
    
    mask(number) {
        const cleaned = number.replace(/\D/g, '');
        return '*'.repeat(cleaned.length - 4) + cleaned.slice(-4);
    }
    
    formatNumber(number) {
        const cleaned = number.replace(/\D/g, '');
        return cleaned.replace(/(.{4})/g, '$1 ').trim();
    }
    
    formatExpiry(value) {
        const cleaned = value.replace(/\D/g, '');
        if (cleaned.length >= 2) {
            return cleaned.substring(0, 2) + '/' + cleaned.substring(2, 4);
        }
        return cleaned;
    }
}

// Payment Form Handler
class PaymentForm {
    constructor(formElement, options = {}) {
        this.form = formElement;
        this.validator = new CardValidator();
        this.options = options;
        this.fields = {};
        
        this.init();
    }
    
    init() {
        this.findFields();
        this.attachListeners();
        this.updateCardIcon();
    }
    
    findFields() {
        this.fields = {
            number: this.form.querySelector('[data-card-number]'),
            expiry: this.form.querySelector('[data-card-expiry]'),
            cvc: this.form.querySelector('[data-card-cvc'),
            name: this.form.querySelector('[data-card-name]'),
            icon: this.form.querySelector('[data-card-icon]')
        };
    }
    
    attachListeners() {
        // Card number formatting
        if (this.fields.number) {
            this.fields.number.addEventListener('input', (e) => {
                const formatted = this.validator.formatNumber(e.target.value);
                e.target.value = formatted;
                this.updateCardIcon();
                this.validateField(e.target);
            });
        }
        
        // Expiry formatting
        if (this.fields.expiry) {
            this.fields.expiry.addEventListener('input', (e) => {
                const formatted = this.validator.formatExpiry(e.target.value);
                e.target.value = formatted;
            });
            
            this.fields.expiry.addEventListener('blur', () => {
                this.validateField(this.fields.expiry);
            });
        }
        
        // CVC validation
        if (this.fields.cvc) {
            this.fields.cvc.addEventListener('input', (e) => {
                e.target.value = e.target.value.replace(/\D/g, '').substring(0, 4);
            });
        }
        
        // Form submission
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            if (this.validate()) {
                this.submit();
            }
        });
    }
    
    updateCardIcon() {
        if (!this.fields.icon || !this.fields.number) return;
        
        const number = this.fields.number.value.replace(/\D/g, '');
        const type = this.validator.getType(number);
        
        const icons = {
            visa: '💳',
            mastercard: '💳',
            amex: '💳',
            discover: '💳',
            unknown: '💳'
        };
        
        this.fields.icon.textContent = icons[type] || '💳';
    }
    
    validateField(field) {
        const value = field.value.trim();
        const name = field.dataset.cardNumber ? 'number' : 
                     field.dataset.cardExpiry ? 'expiry' : 
                     field.dataset.cardCvc ? 'cvc' : 'name';
        
        let isValid = true;
        let error = '';
        
        if (!value && field.required) {
            isValid = false;
            error = 'This field is required';
        }
        
        if (isValid && name === 'number') {
            const result = this.validator.validateNumber(value);
            isValid = result.valid;
            error = result.error || '';
        }
        
        if (isValid && name === 'expiry') {
            const [month, year] = value.split('/');
            const result = this.validator.validateExpiry(month, year);
            isValid = result.valid;
            error = result.error || '';
        }
        
        if (isValid && name === 'cvc') {
            const cardType = this.validator.getType(this.fields.number?.value || '');
            const result = this.validator.validateCvv(value, cardType);
            isValid = result.valid;
            error = result.error || '';
        }
        
        this.showFieldError(field, isValid, error);
        return isValid;
    }
    
    showFieldError(field, isValid, error) {
        const existingError = field.parentElement.querySelector('.payment-error');
        if (existingError) {
            existingError.remove();
        }
        
        field.classList.toggle('error', !isValid);
        
        if (!isValid && error) {
            const errorEl = document.createElement('div');
            errorEl.className = 'payment-error';
            errorEl.textContent = error;
            field.parentElement.appendChild(errorEl);
        }
    }
    
    validate() {
        let isValid = true;
        
        for (const field of Object.values(this.fields)) {
            if (field && !this.validateField(field)) {
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    async submit() {
        const submitBtn = this.form.querySelector('[type="submit"]');
        const originalText = submitBtn.textContent;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="payment-loading"></span> Processing...';
        
        try {
            const data = this.getFormData();
            const result = await this.processPayment(data);
            
            if (result.success) {
                this.options.onSuccess?.(result);
            } else {
                this.options.onError?.(result.error);
            }
        } catch (error) {
            this.options.onError?.(error.message);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    }
    
    getFormData() {
        return {
            card: {
                number: this.fields.number?.value.replace(/\D/g, '') || '',
                expiry: this.fields.expiry?.value || '',
                cvc: this.fields.cvc?.value || '',
                name: this.fields.name?.value || ''
            }
        };
    }
    
    async processPayment(data) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        const response = await fetch(this.form.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(data)
        });
        
        return await response.json();
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    // Auto-initialize payment forms
    document.querySelectorAll('[data-payment-form]').forEach(form => {
        new PaymentForm(form);
    });
});

// Export for external use
window.WwwPay = WwwPay;
window.CardValidator = CardValidator;
window.PaymentForm = PaymentForm;
