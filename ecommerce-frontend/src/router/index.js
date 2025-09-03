import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import ProductsView from '../views/ProductsView.vue'
import OrdersView from '../views/OrdersView.vue'
import CheckOut from '../components/CheckOut.vue'
import AppLogin from '../components/AppLogin.vue'
import AppRegister from '../components/AppRegister.vue'
import AdminProducts from '../views/AdminProducts.vue'
import AdminOrders from '../views/AdminOrders.vue' 
import store from '../store'

const routes = [
  { path: '/', name: 'Home', component: HomeView },
  { path: '/products', name: 'Products', component: ProductsView },
  { path: '/product/:id', name: 'ProductDetail', component: () => import('../views/ProductDetail.vue'), props: true },
  { path: '/checkout/:productId/:quantity?', name: 'Checkout', component: CheckOut, props: true, meta: { requiresAuth: true } },
  { path: '/orders', name: 'Orders', component: OrdersView, meta: { requiresAuth: true } },
  { path: '/register', name: 'Register', component: AppRegister },
  { path: '/login', name: 'Login', component: AppLogin },
  { path: '/admin/products', name: 'AdminProducts', component: AdminProducts, meta: { requiresAdmin: true } },
  { path: '/admin/orders', name: 'AdminOrders', component: AdminOrders, meta: { requiresAdmin: true } } 
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Global navigation guard
router.beforeEach((to, from, next) => {
  const isAuthenticated = store.getters.isAuthenticated
  const user = store.state.auth.user

  // Protect authenticated routes
  if (to.meta.requiresAuth && !isAuthenticated) {
    return next({ name: 'Login' })
  }

  // Protect admin routes
  if (to.meta.requiresAdmin && (!isAuthenticated || !user?.is_admin)) {
    return next({ name: 'Home' })
  }

  // Prevent logged-in users from accessing login/register
  if ((to.name === 'Login' || to.name === 'Register') && isAuthenticated) {
    return next({ name: 'Home' })
  }

  next()
})

export default router