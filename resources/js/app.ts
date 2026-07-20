import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h, type DefineComponent } from 'vue';
import { initializeTheme } from '@/composables/useAppearance';
import { initializeFlashToast } from '@/lib/flashToast';

import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import ClientPageLayout from '@/layouts/ClientPageLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Definimos o glob ANTES do createInertiaApp, apontando para a pasta 'pages' (minúscula)
const pages = import.meta.glob<DefineComponent>('./pages/**/*.vue');

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),

    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, pages),

    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) });

        // ── Captura global de erros não tratados (Vue) ──────────
        vueApp.config.errorHandler = (err: unknown) => {
            const payload = JSON.stringify({
                message: err instanceof Error ? err.message : String(err),
                stack: err instanceof Error ? (err.stack ?? null) : null,
                url: window.location.href,
                timestamp: new Date().toISOString(),
            });

            navigator.sendBeacon('/api/log-frontend-error', payload);
        };

        // ── Captura de promessas rejeitadas não tratadas ─────────
        window.addEventListener(
            'unhandledrejection',
            (event: PromiseRejectionEvent) => {
                const payload = JSON.stringify({
                    message:
                        event.reason instanceof Error
                            ? event.reason.message
                            : String(event.reason),
                    stack:
                        event.reason instanceof Error
                            ? (event.reason.stack ?? null)
                            : null,
                    url: window.location.href,
                    timestamp: new Date().toISOString(),
                });

                navigator.sendBeacon('/api/log-frontend-error', payload);
            },
        );

        vueApp.use(plugin).mount(el);
    },

    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('Client/'):
                return ClientPageLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },

    progress: {
        color: '#4B5563',
    },
});

initializeTheme();
initializeFlashToast();
