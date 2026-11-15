import { computed, onMounted, ref } from 'vue';

export const DEFAULT_APPEARANCE = 'light';

/**
 * Solve CMS ships light mode by default. Dark is applied only when the user
 * explicitly chooses it, so the operating system preference never switches the
 * interface on its own.
 */
export function updateTheme(value) {
    if (typeof window === 'undefined') {
        return;
    }

    if (value === 'system') {
        document.documentElement.classList.toggle(
            'dark',
            window.matchMedia('(prefers-color-scheme: dark)').matches,
        );

        return;
    }

    document.documentElement.classList.toggle('dark', value === 'dark');
}

const setCookie = (name, value, days = 365) => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;

    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const getStoredAppearance = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return localStorage.getItem('appearance');
};

const prefersDark = () => {
    if (typeof window === 'undefined') {
        return false;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches;
};

const handleSystemThemeChange = () => {
    if ((getStoredAppearance() ?? DEFAULT_APPEARANCE) === 'system') {
        updateTheme('system');
    }
};

export function initializeTheme() {
    if (typeof window === 'undefined') {
        return;
    }

    // Light unless the visitor has explicitly chosen otherwise.
    const savedAppearance = getStoredAppearance();
    updateTheme(savedAppearance ?? DEFAULT_APPEARANCE);

    window
        .matchMedia('(prefers-color-scheme: dark)')
        .addEventListener('change', handleSystemThemeChange);
}

const appearance = ref(DEFAULT_APPEARANCE);

export function useAppearance() {
    onMounted(() => {
        const savedAppearance = localStorage.getItem('appearance');

        if (savedAppearance) {
            appearance.value = savedAppearance;
        }
    });

    const resolvedAppearance = computed(() => {
        if (appearance.value === 'system') {
            return prefersDark() ? 'dark' : 'light';
        }

        return appearance.value;
    });

    function updateAppearance(value) {
        appearance.value = value;

        // Store in localStorage for client-side persistence...
        localStorage.setItem('appearance', value);

        // Store in cookie for SSR...
        setCookie('appearance', value);

        updateTheme(value);
    }

    return {
        appearance,
        resolvedAppearance,
        updateAppearance,
    };
}
