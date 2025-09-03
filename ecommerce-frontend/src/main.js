import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap'

const app = createApp(App)

// Initialize auth from localStorage before mounting
store.dispatch('initializeAuth')

app.use(store)
app.use(router)
app.mount('#app')
