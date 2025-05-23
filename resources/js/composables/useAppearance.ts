
export function initializeTheme() {
    if (typeof window === 'undefined') {
        return;
    }

    document.documentElement.classList.add('dark');
}
