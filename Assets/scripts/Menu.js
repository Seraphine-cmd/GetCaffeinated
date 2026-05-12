const filterButtons = document.querySelectorAll('[data-filter]');
const menuCards = document.querySelectorAll('[data-category]');
const addButtons = document.querySelectorAll('[data-add-cart]');
const cartCount = document.querySelector('[data-cart-count]');
const cartStorageKey = 'getCaffeinatedCart';

const getCartItems = () => JSON.parse(localStorage.getItem(cartStorageKey)) || [];

const saveCartItems = (items) => {
    localStorage.setItem(cartStorageKey, JSON.stringify(items));
};

const updateCartCount = () => {
    const totalItems = getCartItems().reduce((total, item) => total + item.quantity, 0);
    cartCount.textContent = totalItems;
};

updateCartCount();

filterButtons.forEach((button) => {
    button.addEventListener('click', () => {
        const selectedFilter = button.dataset.filter;

        filterButtons.forEach((item) => item.classList.remove('active'));
        button.classList.add('active');

        menuCards.forEach((card) => {
            const categories = card.dataset.category.split(' ');
            const shouldShow = selectedFilter === 'all' || categories.includes(selectedFilter);

            card.classList.toggle('is-hidden', !shouldShow);
        });
    });
});

addButtons.forEach((button) => {
    button.addEventListener('click', () => {
        const card = button.closest('.menu-card');
        const name = card.querySelector('h3').textContent.trim();
        const category = card.querySelector('span').textContent.trim();
        const price = Number(card.querySelector('strong').textContent.trim());
        const cartItems = getCartItems();
        const existingItem = cartItems.find((item) => item.name === name);

        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cartItems.push({ name, category, price, quantity: 1 });
        }

        saveCartItems(cartItems);
        updateCartCount();

        button.textContent = 'Added';
        window.setTimeout(() => {
            button.textContent = 'Add';
        }, 900);
    });
});
