import { createApp } from 'vue'
import App from './components/App.vue'

const root = document.getElementById('quotes-bridge-app')

if (root) {
    createApp(App).mount(root)
}
