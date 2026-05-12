const statusTarget = document.querySelector('[data-auth-status]');
const status = new URLSearchParams(window.location.search).get('status');

const authMessages = {
    signup_success: 'Account created. You can now login.',
    login_required: 'Please login to continue.',
    invalid: 'Please complete all required fields.',
    invalid_email: 'Please enter a valid email address.',
    invalid_login: 'Email or password is incorrect.',
    email_exists: 'An account with this email already exists.',
    password_mismatch: 'Passwords do not match.',
    password_short: 'Password must be at least 8 characters.',
    reset_success: 'Password reset successful. You can now login.',
    profile_updated: 'Profile updated successfully.',
    account_missing: 'No account was found with that email.',
    error: 'Something went wrong. Please try again.'
};

if (statusTarget && authMessages[status]) {
    statusTarget.textContent = authMessages[status];
    statusTarget.dataset.type = status.includes('success') ? 'success' : 'error';
    statusTarget.hidden = false;
}
