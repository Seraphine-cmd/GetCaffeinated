const cartStorageKey = 'getCaffeinatedCart';
const cartItemsContainer = document.querySelector('[data-cart-items]');
const emptyCart = document.querySelector('[data-empty-cart]');
const clearCartButton = document.querySelector('[data-clear-cart]');
const checkoutButton = document.querySelector('[data-checkout]');
const summaryItems = document.querySelector('[data-summary-items]');
const summarySubtotal = document.querySelector('[data-summary-subtotal]');
const summaryTotal = document.querySelector('[data-summary-total]');

const getCartItems = () => JSON.parse(localStorage.getItem(cartStorageKey)) || [];

const saveCartItems = (items) => {
    localStorage.setItem(cartStorageKey, JSON.stringify(items));
};

const formatPrice = (price) => price.toFixed(2);

const renderCart = () => {
    const cartItems = getCartItems();
    const totalQuantity = cartItems.reduce((total, item) => total + item.quantity, 0);
    const subtotal = cartItems.reduce((total, item) => total + item.price * item.quantity, 0);

    cartItemsContainer.innerHTML = '';
    emptyCart.hidden = cartItems.length > 0;
    clearCartButton.disabled = cartItems.length === 0;
    checkoutButton.disabled = cartItems.length === 0;

    summaryItems.textContent = totalQuantity;
    summarySubtotal.textContent = formatPrice(subtotal);
    summaryTotal.textContent = formatPrice(subtotal);

    cartItems.forEach((item, index) => {
        const cartItem = document.createElement('article');
        cartItem.className = 'cart-item';
        cartItem.innerHTML = `
            <div>
                <span>${item.category}</span>
                <h4>${item.name}</h4>
                <p>${formatPrice(item.price)} each</p>
            </div>
            <div class="cart-item-actions">
                <div class="qty-control" aria-label="Quantity controls for ${item.name}">
                    <button class="qty-btn" type="button" data-action="decrease" data-index="${index}">-</button>
                    <strong>${item.quantity}</strong>
                    <button class="qty-btn" type="button" data-action="increase" data-index="${index}">+</button>
                </div>
                <button class="remove-item" type="button" data-action="remove" data-index="${index}">Remove</button>
            </div>
        `;

        cartItemsContainer.appendChild(cartItem);
    });
};

cartItemsContainer.addEventListener('click', (event) => {
    const button = event.target.closest('button[data-action]');

    if (!button) {
        return;
    }

    const cartItems = getCartItems();
    const itemIndex = Number(button.dataset.index);
    const action = button.dataset.action;

    if (action === 'increase') {
        cartItems[itemIndex].quantity += 1;
    }

    if (action === 'decrease') {
        cartItems[itemIndex].quantity -= 1;

        if (cartItems[itemIndex].quantity <= 0) {
            cartItems.splice(itemIndex, 1);
        }
    }

    if (action === 'remove') {
        cartItems.splice(itemIndex, 1);
    }

    saveCartItems(cartItems);
    renderCart();
});

clearCartButton.addEventListener('click', () => {
    saveCartItems([]);
    renderCart();
});

checkoutButton.addEventListener('click', async () => {
    const cartItems = getCartItems();

    if (cartItems.length === 0) {
        return;
    }

    checkoutButton.disabled = true;
    checkoutButton.textContent = 'Saving order...';

    try {
        const response = await fetch('../php/create_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ items: cartItems })
        });

        if (response.status === 401) {
            window.location.href = '../Pages/LoginPage.html?status=login_required';
            return;
        }

        const result = await response.json();

        if (!result.ok) {
            throw new Error(result.message || 'Order could not be saved.');
        }

        saveCartItems([]);
        renderCart();
        checkoutButton.textContent = 'Order saved';
    } catch (error) {
        checkoutButton.disabled = false;
        checkoutButton.textContent = 'Try again';
    }
});

renderCart();
