import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { createPinia } from 'pinia';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        const pages = import.meta.glob([
            './Pages/**/*.vue',
            './modules/**/views/**/*.vue'
        ]);
        
        let path = `./modules/${name}.vue`;
        if (!pages[path]) {
            // Buscar en la estructura modular si no está en Pages
            const modularPath = Object.keys(pages).find(p => p.endsWith(`/views/${name}.vue`));
            if (modularPath) path = modularPath;
        }

        return resolvePageComponent(path, pages);
    },
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
