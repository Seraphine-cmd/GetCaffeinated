const filterButtons = document.querySelectorAll('[data-filter]');
const menuCards = document.querySelectorAll('[data-category]');
const addButtons = document.querySelectorAll('[data-add-cart]');
const cartCount = document.querySelector('[data-cart-count]');

let cartItems = 0;

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
        cartItems += 1;
        cartCount.textContent = cartItems;

        button.textContent = 'Added';
        window.setTimeout(() => {
            button.textContent = 'Add';
        }, 900);
    });
});
