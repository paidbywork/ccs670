(() => {
    const button = document.getElementById('menuToggle');
    const sidebar = document.getElementById('appSidebar');
    const overlay = document.getElementById('menuOverlay');

    function closeNavigation() {
        document.body.classList.remove('nav-open');
        button?.setAttribute('aria-expanded', 'false');
    }

    button?.addEventListener('click', () => {
        const isOpen = document.body.classList.toggle('nav-open');
        button.setAttribute('aria-expanded', String(isOpen));
    });

    overlay?.addEventListener('click', closeNavigation);
    sidebar?.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', closeNavigation);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeNavigation();
        }
    });

    const toggle = document.getElementById('togglePassword');
    const password = document.getElementById('password');

    toggle?.addEventListener('click', () => {
        if (!password) {
            return;
        }

        const isCurrentlyVisible = password.type === 'text';
        password.type = isCurrentlyVisible ? 'password' : 'text';
        toggle.textContent = isCurrentlyVisible ? 'Show' : 'Hide';
        toggle.setAttribute('aria-label', isCurrentlyVisible ? 'Show password' : 'Hide password');
        toggle.setAttribute('aria-pressed', String(!isCurrentlyVisible));
    });
})();
