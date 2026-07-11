import './bootstrap';
import {
    BadgeCheck,
    CalendarDays,
    ChartNoAxesColumn,
    createIcons,
    LayoutDashboard,
    LogOut,
    Rocket,
    TriangleAlert,
} from 'lucide';

createIcons({
    icons: {
        BadgeCheck,
        CalendarDays,
        ChartNoAxesColumn,
        LayoutDashboard,
        LogOut,
        Rocket,
        TriangleAlert,
    },
    attrs: {
        'stroke-width': 1.8,
    },
});

const sidebarShell = document.querySelector('[data-sidebar-shell]');

if (sidebarShell) {
    const toggle = sidebarShell.querySelector('[data-sidebar-toggle]');
    const closeButton = sidebarShell.querySelector('[data-sidebar-close]');
    const navigationLinks = sidebarShell.querySelectorAll('.sidebar-nav a');
    const mobileQuery = window.matchMedia('(max-width: 900px)');
    const storageKey = 'reba.sidebar.collapsed';

    const storedDesktopState = () => {
        try {
            return window.localStorage.getItem(storageKey) === 'true';
        } catch {
            return false;
        }
    };

    const persistDesktopState = (collapsed) => {
        try {
            window.localStorage.setItem(storageKey, String(collapsed));
        } catch {
            // La navegacion sigue funcionando aunque el navegador bloquee el almacenamiento.
        }
    };

    const applyState = (collapsed, persist = false) => {
        sidebarShell.classList.toggle('sidebar-collapsed', collapsed);
        document.body.classList.toggle('sidebar-open-mobile', mobileQuery.matches && ! collapsed);

        const actionLabel = collapsed ? 'Desplegar menu lateral' : 'Contraer menu lateral';
        toggle.setAttribute('aria-expanded', String(! collapsed));
        toggle.setAttribute('aria-label', actionLabel);
        toggle.dataset.tooltip = collapsed ? 'Desplegar menu' : 'Contraer menu';

        if (persist && ! mobileQuery.matches) {
            persistDesktopState(collapsed);
        }
    };

    applyState(mobileQuery.matches ? true : storedDesktopState());
    window.requestAnimationFrame(() => sidebarShell.classList.add('sidebar-ready'));

    toggle.addEventListener('click', () => {
        applyState(! sidebarShell.classList.contains('sidebar-collapsed'), true);
    });

    closeButton.addEventListener('click', () => applyState(true));

    navigationLinks.forEach((link) => {
        link.addEventListener('click', () => {
            if (mobileQuery.matches) {
                applyState(true);
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && mobileQuery.matches) {
            applyState(true);
            toggle.focus();
        }
    });

    mobileQuery.addEventListener('change', (event) => {
        applyState(event.matches ? true : storedDesktopState());
    });
}
