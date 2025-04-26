// ===== GLOBAL VARIABLES =====
let currentUser = null;

// ===== UTILITY FUNCTIONS =====
/**
 * Show an error message under a form field
 * @param {string} inputId - ID of the input field
 * @param {string} message - Error message to display
 */
function showError(inputId, message) {
    const errorSpan = document.getElementById(`${inputId}Error`);
    if (errorSpan) {
        errorSpan.textContent = message;
    }
}

/**
 * Clear error message under a form field
 * @param {string} inputId - ID of the input field
 */
function clearError(inputId) {
    const errorSpan = document.getElementById(`${inputId}Error`);
    if (errorSpan) {
        errorSpan.textContent = '';
    }
}

/**
 * Validate an email address format
 * @param {string} email - Email to validate
 * @returns {boolean} - True if valid, false otherwise
 */
function isValidEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

/**
 * Check if a password meets minimum requirements
 * @param {string} password - Password to validate
 * @returns {boolean} - True if valid, false otherwise
 */
function isValidPassword(password) {
    // At least 8 characters, one uppercase, one lowercase, one number
    const re = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
    return re.test(password);
}

// ===== DOM MANIPULATION =====
/**
 * Update all elements that display the user's name
 */
function updateUserDisplay() {
    if (currentUser) {
        const userDisplayElements = document.querySelectorAll('#userDisplayName, #welcomeUserName, #profileUserName');
        userDisplayElements.forEach(element => {
            if (element) {
                element.textContent = currentUser.fullname || currentUser.username;
            }
        });
    }
}

/**
 * Toggle sidebar visibility on mobile devices
 */
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('expanded');
}

/**
 * Switch between profile tabs
 * @param {string} tabId - ID of the tab to activate
 */
function switchProfileTab(tabId) {
    // Deactivate all tabs and content
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Activate the selected tab and content
    document.querySelector(`.tab-btn[data-tab="${tabId}"]`)?.classList.add('active');
    document.getElementById(tabId)?.classList.add('active');
}

// ===== USER AUTHENTICATION =====
/**
 * Simulate user login
 * @param {string} username - User's username
 * @param {string} password - User's password
 * @returns {boolean} - True if login successful, false otherwise
 */
function login(username, password) {
    // In a real application, this would call an API
    // For this demo, we'll check if user exists in localStorage
    const users = JSON.parse(localStorage.getItem('vlab_users') || '[]');
    const user = users.find(u => u.username === username && u.password === password);
    
    if (user) {
        currentUser = user;
        localStorage.setItem('vlab_current_user', JSON.stringify(user));
        return true;
    }
    
    return false;
}

/**
 * Register a new user
 * @param {Object} userData - User data (username, email, password, etc.)
 * @returns {boolean} - True if registration successful, false otherwise
 */
function register(userData) {
    // In a real application, this would call an API
    // For this demo, we'll store user in localStorage
    const users = JSON.parse(localStorage.getItem('vlab_users') || '[]');
    
    // Check if username or email already exists
    if (users.some(u => u.username === userData.username)) {
        return { success: false, message: 'Username already exists' };
    }
    
    if (users.some(u => u.email === userData.email)) {
        return { success: false, message: 'Email already exists' };
    }
    
    // Add user to the array and save
    users.push(userData);
    localStorage.setItem('vlab_users', JSON.stringify(users));
    
    // Log in the new user
    currentUser = userData;
    localStorage.setItem('vlab_current_user', JSON.stringify(userData));
    
    return { success: true };
}

/**
 * Log out the current user
 */
function logout() {
    currentUser = null;
    localStorage.removeItem('vlab_current_user');
    window.location.href = 'index.html';
}

// ===== FORM HANDLING =====
/**
 * Handle login form submission
 * @param {Event} event - Form submit event
 */
function handleLogin(event) {
    event.preventDefault();
    
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    let isValid = true;
    
    // Validate username
    if (!username) {
        showError('username', 'Username is required');
        isValid = false;
    } else {
        clearError('username');
    }
    
    // Validate password
    if (!password) {
        showError('password', 'Password is required');
        isValid = false;
    } else {
        clearError('password');
    }
    
    if (isValid) {
        if (login(username, password)) {
            window.location.href = 'dashboard.html';
        } else {
            // Try to create a demo user if none exists
            if (username === 'demo' && password === 'Demo1234') {
                const demoUser = {
                    username: 'demo',
                    password: 'Demo1234',
                    fullname: 'Demo User',
                    email: 'demo@example.com'
                };
                register(demoUser);
                window.location.href = 'dashboard.html';
            } else {
                showError('password', 'Invalid username or password');
            }
        }
    }
}

/**
 * Handle signup form submission
 * @param {Event} event - Form submit event
 */
function handleSignup(event) {
    event.preventDefault();
    
    const fullname = document.getElementById('fullname').value.trim();
    const email = document.getElementById('email').value.trim();
    const username = document.getElementById('newUsername').value.trim();
    const password = document.getElementById('newPassword').value.trim();
    const confirmPassword = document.getElementById('confirmPassword').value.trim();
    const terms = document.getElementById('terms')?.checked;
    
    let isValid = true;
    
    // Validate fullname
    if (!fullname) {
        showError('fullname', 'Full name is required');
        isValid = false;
    } else {
        clearError('fullname');
    }
    
    // Validate email
    if (!email) {
        showError('email', 'Email is required');
        isValid = false;
    } else if (!isValidEmail(email)) {
        showError('email', 'Please enter a valid email address');
        isValid = false;
    } else {
        clearError('email');
    }
    
    // Validate username
    if (!username) {
        showError('newUsername', 'Username is required');
        isValid = false;
    } else if (username.length < 3) {
        showError('newUsername', 'Username must be at least 3 characters');
        isValid = false;
    } else {
        clearError('newUsername');
    }
    
    // Validate password
    if (!password) {
        showError('newPassword', 'Password is required');
        isValid = false;
    } else if (!isValidPassword(password)) {
        showError('newPassword', 'Password must be at least 8 characters with at least one uppercase letter, one lowercase letter, and one number');
        isValid = false;
    } else {
        clearError('newPassword');
    }
    
    // Validate password confirmation
    if (password !== confirmPassword) {
        showError('confirmPassword', 'Passwords do not match');
        isValid = false;
    } else {
        clearError('confirmPassword');
    }
    
    // Validate terms acceptance
    if (!terms) {
        showError('terms', 'You must accept the Terms and Conditions');
        isValid = false;
    }
    
    if (isValid) {
        const userData = {
            fullname,
            email,
            username,
            password
        };
        
        const result = register(userData);
        
        if (result.success) {
            window.location.href = 'dashboard.html';
        } else {
            showError('newUsername', result.message);
        }
    }
}

/**
 * Handle profile form submission
 * @param {Event} event - Form submit event
 */
function handleProfileUpdate(event) {
    event.preventDefault();
    
    if (!currentUser) return;
    
    // Update user data
    currentUser.fullname = document.getElementById('profileFullName').value.trim();
    currentUser.email = document.getElementById('profileEmail').value.trim();
    currentUser.bio = document.getElementById('profileBio').value.trim();
    currentUser.country = document.getElementById('profileCountry').value;
    currentUser.timezone = document.getElementById('profileTimezone').value;
    
    // Update in localStorage
    localStorage.setItem('vlab_current_user', JSON.stringify(currentUser));
    
    // Update users array
    const users = JSON.parse(localStorage.getItem('vlab_users') || '[]');
    const userIndex = users.findIndex(u => u.username === currentUser.username);
    
    if (userIndex !== -1) {
        users[userIndex] = currentUser;
        localStorage.setItem('vlab_users', JSON.stringify(users));
    }
    
    // Update display
    updateUserDisplay();
    
    // Show success message
    alert('Profile updated successfully!');
}

/**
 * Handle account settings form submission
 * @param {Event} event - Form submit event
 */
function handleAccountSettingsUpdate(event) {
    event.preventDefault();
    
    if (!currentUser) return;
    
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmNewPassword = document.getElementById('confirmNewPassword').value;
    
    let isValid = true;
    
    // Validate current password
    if (currentPassword && currentPassword !== currentUser.password) {
        alert('Current password is incorrect');
        isValid = false;
    }
    
    // Validate new password
    if (newPassword) {
        if (!isValidPassword(newPassword)) {
            alert('New password must be at least 8 characters with at least one uppercase letter, one lowercase letter, and one number');
            isValid = false;
        } else if (newPassword !== confirmNewPassword) {
            alert('New passwords do not match');
            isValid = false;
        } else {
            // Update password
            currentUser.password = newPassword;
        }
    }
    
    if (isValid) {
        // Update notification settings
        currentUser.notifications = {
            email: document.getElementById('emailNotifications').checked,
            labReminders: document.getElementById('labReminders').checked,
            newLabAlerts: document.getElementById('newLabAlerts').checked,
            securityAlerts: document.getElementById('securityAlerts').checked
        };
        
        // Update in localStorage
        localStorage.setItem('vlab_current_user', JSON.stringify(currentUser));
        
        // Update users array
        const users = JSON.parse(localStorage.getItem('vlab_users') || '[]');
        const userIndex = users.findIndex(u => u.username === currentUser.username);
        
        if (userIndex !== -1) {
            users[userIndex] = currentUser;
            localStorage.setItem('vlab_users', JSON.stringify(users));
        }
        
        // Show success message
        alert('Account settings updated successfully!');
    }
}

// ===== INITIALIZATION =====
/**
 * Initialize the application when DOM is loaded
 */
document.addEventListener('DOMContentLoaded', function() {
    // Load current user from localStorage if available
    const savedUser = localStorage.getItem('vlab_current_user');
    if (savedUser) {
        currentUser = JSON.parse(savedUser);
        updateUserDisplay();
    }
    
    // Initialize sidebar toggle
    const sidebarToggle = document.getElementById('sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }
    
    // Initialize logout button
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            logout();
        });
    }
    
    // Initialize login form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
    
    // Initialize signup form
    const signupForm = document.getElementById('signupForm');
    if (signupForm) {
        signupForm.addEventListener('submit', handleSignup);
    }
    
    // Initialize profile form
    const personalInfoForm = document.getElementById('personalInfoForm');
    if (personalInfoForm) {
        personalInfoForm.addEventListener('submit', handleProfileUpdate);
    }
    
    // Initialize account settings form
    const accountSettingsForm = document.getElementById('accountSettingsForm');
    if (accountSettingsForm) {
        accountSettingsForm.addEventListener('submit', handleAccountSettingsUpdate);
    }
    
    // Initialize profile tabs
    const profileTabs = document.querySelectorAll('.tab-btn');
    if (profileTabs.length > 0) {
        profileTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                switchProfileTab(tab.dataset.tab);
            });
        });
    }
    
    // Redirect if needed
    // If user is logged in and trying to access login/signup pages, redirect to dashboard
    if (currentUser) {
        const currentPage = window.location.pathname.split('/').pop();
        if (currentPage === 'index.html' || currentPage === 'signup.html') {
            window.location.href = 'dashboard.html';
        }
    }
    // If user is not logged in and trying to access protected pages, redirect to login
    else {
        const currentPage = window.location.pathname.split('/').pop();
        if (currentPage !== 'index.html' && currentPage !== 'signup.html' && currentPage !== '404.html') {
            window.location.href = 'index.html';
        }
    }
    
    // If on profile page, populate form fields with user data
    if (currentUser && document.querySelector('.profile-page')) {
        document.getElementById('profileFullName').value = currentUser.fullname || '';
        document.getElementById('profileEmail').value = currentUser.email || '';
        document.getElementById('profileUsername').value = currentUser.username || '';
        document.getElementById('profileBio').value = currentUser.bio || '';
        
        if (currentUser.country) {
            document.getElementById('profileCountry').value = currentUser.country;
        }
        
        if (currentUser.timezone) {
            document.getElementById('profileTimezone').value = currentUser.timezone;
        }
        
        // Set notification checkboxes
        if (currentUser.notifications) {
            document.getElementById('emailNotifications').checked = currentUser.notifications.email !== false;
            document.getElementById('labReminders').checked = currentUser.notifications.labReminders !== false;
            document.getElementById('newLabAlerts').checked = currentUser.notifications.newLabAlerts !== false;
            document.getElementById('securityAlerts').checked = currentUser.notifications.securityAlerts !== false;
        }
    }
});
