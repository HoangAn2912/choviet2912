/**
 * CSRF Token Handler for AJAX Requests
 * Tự động thêm CSRF token vào mọi POST/PUT/DELETE request
 */

(function() {
    'use strict';
    
    /**
     * Lấy CSRF token từ meta tag
     */
    function getCSRFToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        return metaTag ? metaTag.getAttribute('content') : '';
    }
    
    /**
     * Override fetch để tự động thêm CSRF token
     */
    const originalFetch = window.fetch;
    window.fetch = function(url, options = {}) {
        // Chỉ thêm token cho POST, PUT, DELETE requests
        const method = (options.method || 'GET').toUpperCase();
        
        if (['POST', 'PUT', 'DELETE', 'PATCH'].includes(method)) {
            const token = getCSRFToken();
            
            // Thêm token vào headers
            options.headers = options.headers || {};
            
            // Nếu headers là Headers object
            if (options.headers instanceof Headers) {
                options.headers.set('X-CSRF-Token', token);
            } 
            // Nếu headers là plain object
            else {
                options.headers['X-CSRF-Token'] = token;
            }
            
            // Nếu body là FormData, thêm token vào đó
            if (options.body instanceof FormData) {
                options.body.append('csrf_token', token);
            }
            // Nếu body là URLSearchParams
            else if (options.body instanceof URLSearchParams) {
                options.body.append('csrf_token', token);
            }
            // Nếu body là string (form-urlencoded)
            else if (typeof options.body === 'string' && options.headers['Content-Type']?.includes('application/x-www-form-urlencoded')) {
                options.body += (options.body ? '&' : '') + 'csrf_token=' + encodeURIComponent(token);
            }
        }
        
        return originalFetch(url, options);
    };
    
    /**    
     * Override XMLHttpRequest để tự động thêm CSRF token
     */
    const originalOpen = XMLHttpRequest.prototype.open;
    const originalSend = XMLHttpRequest.prototype.send;
    
    XMLHttpRequest.prototype.open = function(method, url, async, user, password) {
        this._method = method.toUpperCase();
        this._url = url;
        return originalOpen.apply(this, arguments);
    };
    
    XMLHttpRequest.prototype.send = function(data) {
        if (['POST', 'PUT', 'DELETE', 'PATCH'].includes(this._method)) {
            const token = getCSRFToken();
            
            // Thêm token vào header
            this.setRequestHeader('X-CSRF-Token', token);
            
            // Nếu data là FormData, thêm token vào đó
            if (data instanceof FormData) {
                data.append('csrf_token', token);
            }
            // Nếu data là string (form-urlencoded)
            else if (typeof data === 'string') {
                data += (data ? '&' : '') + 'csrf_token=' + encodeURIComponent(token);
            }
        }
        
        return originalSend.call(this, data);
    };
    
    /**
     * Helper function để thêm CSRF token vào form submit
     */
    window.addCSRFTokenToForm = function(form) {
        const token = getCSRFToken();
        
        // Xóa input cũ nếu có
        const oldInput = form.querySelector('input[name="csrf_token"]');
        if (oldInput) {
            oldInput.remove();
        }
        
        // Thêm input mới
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'csrf_token';
        input.value = token;
        form.appendChild(input);
    };
    
    /**
     * Tự động thêm CSRF token vào tất cả forms khi DOM loaded
     */
    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            // Chỉ thêm cho POST forms
            const method = (form.method || 'GET').toUpperCase();
            if (method === 'POST') {
                window.addCSRFTokenToForm(form);
                
                // Re-add token trước khi submit (trong trường hợp form bị modify động)
                form.addEventListener('submit', function() {
                    window.addCSRFTokenToForm(form);
                });
            }
        });
    });
    
    /**
     * Handle CSRF token errors
     */
    window.addEventListener('error', function(e) {
        if (e.message && e.message.includes('CSRF')) {
            console.error('CSRF Token Error:', e);
            alert('Phiên làm việc đã hết hạn. Vui lòng refresh trang.');
            // Optionally reload page
            // location.reload();
        }
    });
    
})();

console.log('✅ CSRF Handler initialized');

