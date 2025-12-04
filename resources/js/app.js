import '../css/app.css'
import './bootstrap'

import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { ZiggyVue } from 'ziggy-js'   // sin importar Ziggy desde archivo

// 🔹 Nombre fijo de la app (sin "Laravel")
const appName = import.meta.env.VITE_APP_NAME || 'Pollería Pepe'

createInertiaApp({
  // 🔹 Título que verá el usuario en la pestaña
  //    Ej: "Catálogo | Pollería Pepe"
  title: (t) => (t ? `${t} | ${appName}` : appName),

  resolve: (name) =>
    resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),

  setup({ el, App, props, plugin }) {
    return createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue) // usa el Ziggy inyectado por @routes
      .mount(el)
  },

  progress: { color: '#4B5563' },
})
