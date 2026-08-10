// assets/script.js - Enhanced with Font Awesome icons

document.addEventListener('DOMContentLoaded', function() {
    // Initialize mobile menu toggle
    initMobileMenu();
    
    // Set active navigation item
    setActiveNavItem();
    
    // Initialize password toggles with FA icons
    initPasswordToggles();
    
    // Initialize form validation
    initFormValidation();
    
    // Initialize status controls
    initStatusControls();
    
    // Load dynamic content based on page
    loadPageSpecificContent();
    
    // Add tooltips to icons
    initTooltips();
});

// Mobile menu functionality
function initMobileMenu() {
    const menuToggle = document.createElement('button');
    menuToggle.className = 'menu-toggle';
    menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
    menuToggle.setAttribute('data-tooltip', 'Toggle Menu');
    menuToggle.onclick = toggleMobileMenu;
    
    // Add menu toggle to body
    document.body.appendChild(menuToggle);
}

function toggleMobileMenu() {
    const sidebar = document.querySelector('.sidebar');
    const menuToggle = document.querySelector('.menu-toggle');
    const body = document.body;
    
    if (sidebar) {
        sidebar.classList.toggle('active');
        
        // Toggle body class for overflow control
        body.classList.toggle('sidebar-active');
        
        // Toggle icon
        if (sidebar.classList.contains('active')) {
            menuToggle.innerHTML = '<i class="fas fa-times"></i>';
            menuToggle.setAttribute('data-tooltip', 'Close Menu');
            
            // Prevent body scrolling
            body.style.overflow = 'hidden';
            
            // Add click outside to close functionality
            setTimeout(() => {
                document.addEventListener('click', closeMenuOnOutsideClick);
            }, 10);
        } else {
            menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
            menuToggle.setAttribute('data-tooltip', 'Open Menu');
            
            // Restore body scrolling
            body.style.overflow = '';
            
            // Remove click outside listener
            document.removeEventListener('click', closeMenuOnOutsideClick);
        }
    }
}

function closeMenuOnOutsideClick(event) {
    const sidebar = document.querySelector('.sidebar');
    const menuToggle = document.querySelector('.menu-toggle');
    
    // Check if click is outside sidebar and not on menu toggle
    if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
        toggleMobileMenu();
    }
}

// Set active navigation item based on current page
function setActiveNavItem() {
    const currentPage = window.location.pathname.split('/').pop();
    const navItems = document.querySelectorAll('.nav-item');
    
    navItems.forEach(item => {
        const href = item.getAttribute('href');
        if (href === currentPage || 
            (currentPage === '' && href === 'index.php') ||
            (href && href.includes(currentPage))) {
            item.classList.add('active');
        }
    });
}

// Password toggle functionality with Font Awesome icons
function initPasswordToggles() {
    // Create password toggle buttons for existing password fields
    document.querySelectorAll('input[type="password"]').forEach(input => {
        if (!input.nextElementSibling || !input.nextElementSibling.classList.contains('toggle-password')) {
            const toggleBtn = document.createElement('button');
            toggleBtn.type = 'button';
            toggleBtn.className = 'toggle-password';
            toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
            toggleBtn.setAttribute('data-tooltip', 'Show password');
            
            input.parentNode.appendChild(toggleBtn);
        }
    });
    
    // Handle toggle click
    document.addEventListener('click', function(e) {
        if (e.target.closest('.toggle-password')) {
            const toggleBtn = e.target.closest('.toggle-password');
            const input = toggleBtn.previousElementSibling;
            
            if (input.type === 'password') {
                input.type = 'text';
                toggleBtn.innerHTML = '<i class="fas fa-eye-slash"></i>';
                toggleBtn.setAttribute('data-tooltip', 'Hide password');
            } else {
                input.type = 'password';
                toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
                toggleBtn.setAttribute('data-tooltip', 'Show password');
            }
        }
    });
}

// Tooltip initialization
function initTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(e) {
    const tooltipText = this.getAttribute('data-tooltip');
    const tooltip = document.createElement('div');
    tooltip.className = 'custom-tooltip';
    tooltip.textContent = tooltipText;
    tooltip.style.cssText = `
        position: fixed;
        background: #1e293b;
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 12px;
        z-index: 10000;
        pointer-events: none;
        transform: translate(-50%, -100%);
        top: ${e.clientY}px;
        left: ${e.clientX}px;
        margin-top: -10px;
        white-space: nowrap;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    
    document.body.appendChild(tooltip);
    this._tooltip = tooltip;
}

function hideTooltip() {
    if (this._tooltip) {
        this._tooltip.remove();
        this._tooltip = null;
    }
}

// Toast notifications with icons
function showToast(message, type = 'info', duration = 5000) {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    const icon = getToastIcon(type);
    
    toast.innerHTML = `
        <div class="toast-content">
            <span class="toast-icon">${icon}</span>
            <span class="toast-message">${message}</span>
        </div>
        <button class="toast-close"><i class="fas fa-times"></i></button>
    `;
    
    // Add styles
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        padding: 16px 20px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        z-index: 10000;
        animation: slideIn 0.3s ease;
        border-left: 4px solid ${getToastColor(type)};
        min-width: 300px;
        max-width: 400px;
        font-family: 'Segoe UI', system-ui, sans-serif;
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after duration
    setTimeout(() => {
        if (toast.parentNode) {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }
    }, duration);
    
    // Close button
    const closeBtn = toast.querySelector('.toast-close');
    closeBtn.onclick = () => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    };
}

function getToastIcon(type) {
    switch(type) {
        case 'success': return '<i class="fas fa-check-circle" style="color: #10b981;"></i>';
        case 'error': return '<i class="fas fa-exclamation-circle" style="color: #ef4444;"></i>';
        case 'warning': return '<i class="fas fa-exclamation-triangle" style="color: #f59e0b;"></i>';
        default: return '<i class="fas fa-info-circle" style="color: #6366f1;"></i>';
    }
}

function getToastColor(type) {
    switch(type) {
        case 'success': return '#10b981';
        case 'error': return '#ef4444';
        case 'warning': return '#f59e0b';
        default: return '#6366f1';
    }
}

// Add CSS animations for toast
if (!document.querySelector('#toast-styles')) {
    const style = document.createElement('style');
    style.id = 'toast-styles';
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        .toast-close {
            background: none;
            border: none;
            font-size: 16px;
            cursor: pointer;
            color: #64748b;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .toast-close:hover {
            color: #1e293b;
            background: #f1f5f9;
        }
        
        .toast-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .toast-icon {
            font-size: 20px;
        }
        
        .toast-message {
            font-size: 14px;
            color: #1e293b;
            flex: 1;
        }
    `;
    document.head.appendChild(style);
}

// Status controls functionality with icons
function initStatusControls() {
    document.addEventListener('click', function(e) {
        const statusCard = e.target.closest('.status-card');
        if (statusCard && !statusCard.classList.contains('active')) {
            const status = statusCard.dataset.status;
            updateDriverStatus(status);
        }
    });
}

// Enhanced status update function with icons
window.updateDriverStatus = function(status) {
    if (!confirm(`Are you sure you want to change your status to "${status}"?`)) {
        return;
    }
    
    showToast('Updating status... <i class="fas fa-spinner fa-spin"></i>', 'info', 3000);
    
    fetch('ajax/update_status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `status=${encodeURIComponent(status)}`
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast('<i class="fas fa-check"></i> Status updated successfully!', 'success');
            
            // Update UI
            document.querySelectorAll('.status-card').forEach(card => {
                card.classList.remove('active');
                if (card.dataset.status === status) {
                    card.classList.add('active');
                    // Update icon in status card
                    const icon = card.querySelector('.status-icon i') || card.querySelector('.status-icon');
                    if (icon) {
                        icon.className = getStatusIcon(status, true);
                    }
                }
            });
            
            // Update current status display
            updateCurrentStatusDisplay(status);
            
        } else {
            throw new Error('Failed to update status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('<i class="fas fa-times"></i> Failed to update status. Please try again.', 'error');
    });
};

function getStatusIcon(status, isActive = false) {
    switch(status) {
        case 'available': 
            return isActive ? 'fas fa-check-circle' : 'fas fa-user-check';
        case 'busy': 
            return isActive ? 'fas fa-clock' : 'fas fa-user-clock';
        case 'unavailable': 
            return isActive ? 'fas fa-ban' : 'fas fa-user-slash';
        default: 
            return 'fas fa-user';
    }
}

function updateCurrentStatusDisplay(status) {
    const statusDisplay = document.getElementById('current-status');
    if (statusDisplay) {
        statusDisplay.textContent = status;
        statusDisplay.className = `badge badge-${getStatusBadgeClass(status)}`;
        
        // Update icon in badge
        const icon = document.createElement('i');
        icon.className = getStatusIcon(status);
        statusDisplay.prepend(icon);
    }
}

function getStatusBadgeClass(status) {
    switch(status) {
        case 'available': return 'success';
        case 'busy': return 'warning';
        case 'unavailable': return 'danger';
        default: return 'primary';
    }
}

// Load drivers for passenger view with icons
window.loadAvailableDrivers = function() {
    const container = document.getElementById('drivers-container');
    if (!container) return;
    
    container.innerHTML = `
        <div class="loading">
            <div class="spinner"></div>
            <p style="margin-top: 15px; color: #64748b;">Loading available drivers...</p>
        </div>
    `;
    
    fetch('ajax/fetch_drivers')
        .then(response => response.json())
        .then(drivers => {
            if (drivers.length > 0) {
                container.innerHTML = `
                    <div class="drivers-grid">
                        ${drivers.map(driver => `
                            <div class="driver-card">
                                <div class="driver-avatar">
                                    ${getInitials(driver.fullname)}
                                </div>
                                <div class="driver-info">
                                    <h4>${driver.fullname}</h4>
                                    <div class="driver-status">
                                        <i class="fas fa-circle" style="font-size: 10px;"></i>
                                        <span class="badge badge-success">
                                            <i class="fas fa-check"></i>
                                            ${driver.status}
                                        </span>
                                    </div>
                                    <p style="margin-top: 10px; color: #64748b; font-size: 14px;">
                                        <i class="fas fa-map-marker-alt" style="margin-right: 5px;"></i>
                                        Available in your area
                                    </p>
                                </div>
                                <button class="btn" style="padding: 8px 16px; font-size: 14px;" 
                                        onclick="requestDriver('${driver.fullname}')">
                                    <i class="fas fa-phone"></i>
                                    Request
                                </button>
                            </div>
                        `).join('')}
                    </div>
                `;
                
                // Update driver count
                updateDriverCount();
            } else {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-taxi"></i>
                        <h3>No drivers available</h3>
                        <p>Please check back later or try a different location.</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading drivers:', error);
            container.innerHTML = `
                <div class="alert alert-danger">
                    <i class="alert-icon fas fa-exclamation-triangle"></i>
                    <span>Unable to load drivers. Please check your connection.</span>
                </div>
            `;
        });
};

// Simulate driver request
window.requestDriver = function(driverName) {
    showToast(`<i class="fas fa-spinner fa-spin"></i> Contacting ${driverName}...`, 'info');
    
    // Simulate API call
    setTimeout(() => {
        showToast(`<i class="fas fa-phone"></i> Connected to ${driverName}!`, 'success');
    }, 1500);
};

function getInitials(name) {
    return name.split(' ')
        .map(part => part[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
}

// Load weather data with icons
window.loadWeather = function(city = 'Santiago') {
    const container = document.getElementById('weather-container');
    if (!container) return;
    
    container.innerHTML = `
        <div class="loading">
            <div class="spinner"></div>
            <p style="margin-top: 15px; color: #64748b;">Loading weather data...</p>
        </div>
    `;
    
    fetch(`api/weather.php?city=${encodeURIComponent(city)}`)
        .then(response => response.json())
        .then(data => {
            if (data.main) {
                const temp = Math.round(data.main.temp);
                const weatherIcon = getWeatherIcon(data.weather[0].main);
                
                container.innerHTML = `
                    <div class="weather-widget">
                        <div class="weather-header">
                            <div class="weather-icon">${weatherIcon}</div>
                            <div>
                                <h3><i class="fas fa-map-marker-alt"></i> ${data.name}</h3>
                                <div class="weather-temp">${temp}°C</div>
                            </div>
                        </div>
                        <p style="opacity: 0.8; margin-bottom: 20px; font-size: 18px;">
                            <i class="fas fa-cloud"></i> ${data.weather[0].description}
                        </p>
                        <div class="weather-details">
                            <div class="weather-detail">
                                <div class="value"><i class="fas fa-tint"></i> ${data.main.humidity}%</div>
                                <div class="label">Humidity</div>
                            </div>
                            <div class="weather-detail">
                                <div class="value"><i class="fas fa-wind"></i> ${data.wind.speed} m/s</div>
                                <div class="label">Wind Speed</div>
                            </div>
                            <div class="weather-detail">
                                <div class="value"><i class="fas fa-tachometer-alt"></i> ${data.main.pressure} hPa</div>
                                <div class="label">Pressure</div>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                container.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="alert-icon fas fa-exclamation-triangle"></i>
                        <span>Unable to load weather data.</span>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading weather:', error);
            container.innerHTML = `
                <div class="alert alert-danger">
                    <i class="alert-icon fas fa-times-circle"></i>
                    <span>Failed to load weather information.</span>
                </div>
            `;
        });
};

function getWeatherIcon(condition) {
    const conditionLower = condition.toLowerCase();
    if (conditionLower.includes('clear')) return '<i class="fas fa-sun"></i>';
    if (conditionLower.includes('cloud')) return '<i class="fas fa-cloud"></i>';
    if (conditionLower.includes('rain')) return '<i class="fas fa-cloud-rain"></i>';
    if (conditionLower.includes('drizzle')) return '<i class="fas fa-cloud-sun-rain"></i>';
    if (conditionLower.includes('thunderstorm')) return '<i class="fas fa-bolt"></i>';
    if (conditionLower.includes('snow')) return '<i class="fas fa-snowflake"></i>';
    if (conditionLower.includes('mist') || conditionLower.includes('fog')) return '<i class="fas fa-smog"></i>';
    return '<i class="fas fa-cloud-sun"></i>';
}

// Update driver count
function updateDriverCount() {
    const driverCards = document.querySelectorAll('.driver-card');
    const countElement = document.getElementById('driver-count');
    if (countElement) {
        countElement.innerHTML = `<i class="fas fa-users"></i> ${driverCards.length} available`;
    }
}

// Page-specific content loading
function loadPageSpecificContent() {
    // Auto-refresh drivers every 30 seconds on passenger view
    if (document.getElementById('drivers-container')) {
        loadAvailableDrivers();
        setInterval(loadAvailableDrivers, 30000);
    }
    
    // Load weather on pages that need it
    if (document.getElementById('weather-container')) {
        loadWeather();
    }
    
    // Add click animations to buttons
    document.querySelectorAll('.btn, .status-card, .driver-card').forEach(element => {
        element.addEventListener('click', function(e) {
            if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || this.classList.contains('status-card')) {
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            }
        });
    });
    
    // Initialize all status icons
    initializeStatusIcons();
}

// Initialize status icons
function initializeStatusIcons() {
    document.querySelectorAll('.status-icon').forEach(icon => {
        const statusCard = icon.closest('.status-card');
        if (statusCard) {
            const status = statusCard.dataset.status;
            const isActive = statusCard.classList.contains('active');
            icon.innerHTML = `<i class="${getStatusIcon(status, isActive)}"></i>`;
        }
    });
    
    // Initialize navigation icons
    document.querySelectorAll('.nav-item i').forEach(icon => {
        if (!icon.className.includes('fa-')) {
            const text = icon.textContent;
            icon.textContent = '';
            let faIcon = 'fa-circle';
            
            switch(text.trim()) {
                case '📊': faIcon = 'fa-chart-line'; break;
                case '👥': faIcon = 'fa-users'; break;
                case '👨‍💼': faIcon = 'fa-user-tie'; break;
                case '📝': faIcon = 'fa-clipboard-list'; break;
                case '⚡': faIcon = 'fa-bolt'; break;
                case '🚪': faIcon = 'fa-sign-out-alt'; break;
                default: faIcon = 'fa-circle';
            }
            
            icon.className = `fas ${faIcon}`;
        }
    });
}