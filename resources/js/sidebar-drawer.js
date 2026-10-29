function initSidebarDrawer() {
    const layout = document.querySelector('[data-sidebar-layout]');
    if (!layout) return;

    const toggle = layout.querySelector('[data-sidebar-toggle]');
    const backdrop = layout.querySelector('[data-sidebar-backdrop]');
    const panel = layout.querySelector('[data-sidebar-panel]');
    const inertTarget = layout.querySelector('[data-sidebar-inert]');
    const desktop = window.matchMedia('(min-width: 1200px)');

    const setInert = (open) => {
        if (!inertTarget) return;
        if (desktop.matches || open) {
            inertTarget.removeAttribute('inert');
        } else {
            inertTarget.setAttribute('inert', '');
        }
    };

    const setOpen = (open) => {
        layout.classList.toggle('is-sidebar-open', open);
        toggle?.setAttribute('aria-expanded', String(open));
        toggle?.setAttribute('aria-label', open ? 'Close form sections menu' : 'Open form sections menu');
        if (backdrop) backdrop.hidden = !open;
        document.body.classList.toggle('sidebar-drawer-open', open && !desktop.matches);
        setInert(open);
    };

    const close = () => setOpen(false);

    toggle?.addEventListener('click', () => setOpen(!layout.classList.contains('is-sidebar-open')));
    backdrop?.addEventListener('click', close);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && layout.classList.contains('is-sidebar-open')) {
            close();
        }
    });

    desktop.addEventListener('change', () => {
        close();
        setInert(false);
    });

    panel?.querySelectorAll('a[href], [data-bs-toggle="modal"]').forEach((el) => {
        el.addEventListener('click', () => {
            if (!desktop.matches) close();
        });
    });

    setInert(false);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSidebarDrawer);
} else {
    initSidebarDrawer();
}
