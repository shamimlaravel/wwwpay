/**
 * WwwPay Documentation JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initNavigation();
    initCodeCopy();
    initScrollSpy();
    initSearch();
});

// Navigation functionality
function initNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');
    const sections = document.querySelectorAll('section[id]');
    
    // Smooth scroll on click
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const target = document.getElementById(targetId);
            
            if (target) {
                const offset = 20;
                const top = target.offsetTop - offset;
                window.scrollTo({
                    top: top,
                    behavior: 'smooth'
                });
            }
        });
    });
}

// Copy code functionality
function initCodeCopy() {
    window.copyCode = function(button) {
        const codeBlock = button.parentElement.querySelector('code');
        const text = codeBlock.textContent;
        
        navigator.clipboard.writeText(text).then(() => {
            const originalText = button.textContent;
            button.textContent = 'Copied!';
            button.classList.add('copied');
            
            setTimeout(() => {
                button.textContent = originalText;
                button.classList.remove('copied');
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy:', err);
        });
    };
}

// Scroll spy for active navigation
function initScrollSpy() {
    const navLinks = document.querySelectorAll('.nav-link');
    const sections = document.querySelectorAll('section[id]');
    
    function updateActiveLink() {
        let current = '';
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            
            if (window.scrollY >= sectionTop - 100) {
                current = section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + current) {
                link.classList.add('active');
            }
        });
    }
    
    window.addEventListener('scroll', updateActiveLink);
    updateActiveLink();
}

// Search functionality
function initSearch() {
    const searchInput = document.createElement('input');
    searchInput.type = 'search';
    searchInput.placeholder = 'Search documentation...';
    searchInput.className = 'search-input';
    searchInput.style.cssText = `
        width: 100%;
        padding: 12px 16px;
        margin: 0 20px 20px;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 14px;
        background: var(--bg-tertiary);
    `;
    
    const sidebar = document.querySelector('.sidebar-nav');
    if (sidebar) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const navLinks = document.querySelectorAll('.nav-link');
            
            navLinks.forEach(link => {
                const text = link.textContent.toLowerCase();
                const section = link.closest('.nav-section');
                
                if (text.includes(searchTerm) || searchTerm === '') {
                    link.style.display = '';
                } else {
                    link.style.display = 'none';
                }
            });
            
            // Show/hide sections based on visible links
            document.querySelectorAll('.nav-section').forEach(section => {
                const visibleLinks = section.querySelectorAll('.nav-link[style=""], .nav-link');
                const hasVisibleLinks = Array.from(visibleLinks).some(link => link.style.display !== 'none');
                section.style.display = hasVisibleLinks ? '' : 'none';
            });
        });
        
        const firstSection = document.querySelector('.nav-section');
        if (firstSection) {
            firstSection.parentNode.insertBefore(searchInput, firstSection);
        }
    }
}

// Mobile menu toggle
function toggleMobileMenu() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('open');
}

// Gateway data for dynamic rendering
const GATEWAY_DATA = {
    global: [
        { name: 'Stripe', key: 'stripe', icon: '💳', description: 'Credit cards worldwide' },
        { name: 'PayPal', key: 'paypal', icon: '🅿️', description: 'Global digital payments' },
    ],
    southAsia: [
        { name: 'bKash', key: 'bkash', icon: '💰', description: 'Bangladesh' },
        { name: 'Nagad', key: 'nagad', icon: '🔔', description: 'Bangladesh' },
        { name: 'UPI', key: 'upi', icon: '💸', description: 'India' },
        { name: 'PhonePe', key: 'phonepe', icon: '📱', description: 'India' },
        { name: 'Paytm', key: 'paytm', icon: '💁', description: 'India' },
        { name: 'JazzCash', key: 'jazzcash', icon: '🎵', description: 'Pakistan' },
        { name: 'Easypaisa', key: 'easypaisa', icon: '💚', description: 'Pakistan' },
    ],
    middleEast: [
        { name: 'PayTabs', key: 'paytabs', icon: '💳', description: 'UAE, Saudi Arabia' },
        { name: 'Telr', key: 'telr', icon: '📡', description: 'Middle East' },
        { name: 'Mada', key: 'mada', icon: '🇸🇦', description: 'Saudi Arabia' },
    ],
    africa: [
        { name: 'Flutterwave', key: 'flutterwave', icon: '🌍', description: 'Pan-Africa' },
        { name: 'Paystack', key: 'paystack', icon: '💚', description: 'Nigeria, Ghana' },
        { name: 'M-Pesa', key: 'mpesa', icon: '📱', description: 'Kenya' },
        { name: 'Fawry', key: 'fawry', icon: '🇪🇬', description: 'Egypt' },
    ],
    europe: [
        { name: 'Klarna', key: 'klarna', icon: '🛒', description: 'Buy now, pay later' },
        { name: 'Adyen', key: 'adyen', icon: '🏦', description: 'EU payments' },
        { name: 'iDEAL', key: 'ideal', icon: '🇳🇱', description: 'Netherlands' },
        { name: 'Bancontact', key: 'bancontact', icon: '🇧🇪', description: 'Belgium' },
        { name: 'SEPA', key: 'sepa', icon: '💶', description: 'EU bank transfers' },
    ],
    asiaPacific: [
        { name: 'Alipay', key: 'alipay', icon: '🟢', description: 'China' },
        { name: 'WeChat Pay', key: 'wechat', icon: '💬', description: 'China' },
        { name: 'PayPay', key: 'paypay', icon: '💴', description: 'Japan' },
        { name: 'Line Pay', key: 'linepay', icon: '📱', description: 'Japan' },
        { name: 'GrabPay', key: 'grabpay', icon: '🚗', description: 'Southeast Asia' },
    ],
    americas: [
        { name: 'Square', key: 'square', icon: '⬜', description: 'North America' },
        { name: 'Authorize.net', key: 'authorize', icon: '🔐', description: 'North America' },
        { name: 'Moneris', key: 'moneris', icon: '🍁', description: 'Canada' },
        { name: 'MercadoPago', key: 'mercadopago', icon: '🇦🇷', description: 'Latin America' },
        { name: 'PagSeguro', key: 'pagseguro', icon: '🇧🇷', description: 'Brazil' },
    ],
    crypto: [
        { name: 'Bitcoin', key: 'bitcoin', icon: '₿', description: 'BTC payments' },
        { name: 'Ethereum', key: 'ethereum', icon: 'Ξ', description: 'ETH payments' },
    ],
};

// Render gateway cards
function renderGateways() {
    const container = document.getElementById('gateway-list');
    if (!container) return;
    
    Object.entries(GATEWAY_DATA).forEach(([region, gateways]) => {
        const card = document.createElement('div');
        card.className = 'gateway-card';
        card.id = region;
        
        const regionNames = {
            global: '🌐 Global',
            southAsia: '🌏 South Asia',
            middleEast: '🏜️ Middle East',
            africa: '🌍 Africa',
            europe: '🏰 Europe',
            asiaPacific: '🌸 Asia Pacific',
            americas: '🌎 Americas',
            crypto: '₿ Cryptocurrency',
        };
        
        card.innerHTML = `
            <h4>${regionNames[region]}</h4>
            <ul>
                ${gateways.map(g => `
                    <li><strong>${g.name}</strong> - ${g.description}</li>
                `).join('')}
            </ul>
        `;
        
        container.appendChild(card);
    });
}

// Initialize if gateway container exists
if (document.getElementById('gateway-list')) {
    renderGateways();
}

// API for external use
window.WwwPayDocs = {
    getGateways: () => GATEWAY_DATA,
    copyCode: (button) => window.copyCode(button),
    toggleMobileMenu: toggleMobileMenu,
};
