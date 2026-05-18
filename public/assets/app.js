document.querySelectorAll('button[type="button"]').forEach((button) => {
    button.addEventListener('click', () => {
        button.classList.add('is-clicked');
        window.setTimeout(() => button.classList.remove('is-clicked'), 180);
    });
});
