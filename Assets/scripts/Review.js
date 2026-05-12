const statusMessage = document.querySelector('[data-review-status]');
const status = new URLSearchParams(window.location.search).get('status');

const messages = {
    success: 'Thank you. Your review was submitted successfully.',
    invalid: 'Please complete your name, rating, and feedback before submitting.',
    invalid_email: 'Please enter a valid email address or leave it blank.',
    error: 'Something went wrong while saving your review. Please try again.'
};

if (statusMessage && messages[status]) {
    statusMessage.textContent = messages[status];
    statusMessage.dataset.type = status === 'success' ? 'success' : 'error';
    statusMessage.hidden = false;
}
