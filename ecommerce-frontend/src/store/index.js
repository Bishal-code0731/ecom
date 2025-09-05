import { createStore } from 'vuex'
import api from '../services/api'

export default createStore({
  state: {
    auth: {
      isAuthenticated: false,
      user: null,
      token: localStorage.getItem('token') || null
    },
    products: [],
    orders: [],       // User-specific orders
    allOrders: []     // Admin: all orders
  },

  mutations: {
    SET_AUTH(state, { user, token }) {
      state.auth.isAuthenticated = true
      state.auth.user = user
      state.auth.token = token
      localStorage.setItem('token', token)
      localStorage.setItem('user', JSON.stringify(user))
    },
    CLEAR_AUTH(state) {
      state.auth.isAuthenticated = false
      state.auth.user = null
      state.auth.token = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    },
    SET_PRODUCTS(state, products) {
      state.products = Array.isArray(products) ? products : []
    },
    ADD_PRODUCT(state, product) {
      state.products.push(product)
    },
    UPDATE_PRODUCT(state, updatedProduct) {
      const index = state.products.findIndex(p => p.id === updatedProduct.id)
      if (index !== -1) state.products.splice(index, 1, updatedProduct)
    },
    DELETE_PRODUCT(state, productId) {
      state.products = state.products.filter(p => p.id !== productId)
    },
    SET_ORDERS(state, orders) {
      state.orders = Array.isArray(orders) ? orders.filter(o => o) : []
    },
    SET_ALL_ORDERS(state, orders) {
      state.allOrders = Array.isArray(orders) ? orders.filter(o => o) : []
    },
    UPDATE_ORDER_STATUS(state, { orderId, status }) {
      const order = state.allOrders.find(o => o.id === orderId)
      if (order) order.status = status

      const userOrder = state.orders.find(o => o.id === orderId)
      if (userOrder) userOrder.status = status
    }
  },

  actions: {
    async login({ commit }, credentials) {
      try {
        const response = await api.post('/login', credentials)
        const { user, token } = response.data.data
        commit('SET_AUTH', { user, token })
        api.defaults.headers.common['Authorization'] = `Bearer ${token}`
        return { success: true }
      } catch (error) {
        return { 
          success: false, 
          message: error.response?.data?.message || 'Login failed. Please check your credentials.' 
        }
      }
    },

    async register({ commit }, userData) {
      try {
        const response = await api.post('/register', userData)
        const { user, token } = response.data.data
        commit('SET_AUTH', { user, token })
        api.defaults.headers.common['Authorization'] = `Bearer ${token}`
        return { success: true }
      } catch (error) {
        return { 
          success: false, 
          message: error.response?.data?.message || 'Registration failed' 
        }
      }
    },

    logout({ commit }) {
      commit('CLEAR_AUTH')
      delete api.defaults.headers.common['Authorization']
    },

    initializeAuth({ commit, state }) {
      const token = localStorage.getItem('token')
      const user = JSON.parse(localStorage.getItem('user') || null)
       if (token && user) {
        state.auth.isAuthenticated = true
        state.auth.user = user
        state.auth.token = token
        api.defaults.headers.common['Authorization'] = `Bearer ${token}`
      }
    },

    async fetchProducts({ commit }) {
      try {
        const response = await api.get('/products')
        const products = response.data.data || response.data
        commit('SET_PRODUCTS', products)
      } catch (error) {
        commit('SET_PRODUCTS', [])
      }
    },

    // User-specific orders - FIXED VERSION
    async fetchOrders({ commit, state }) {
      if (!state.auth.isAuthenticated || !state.auth.user) {
        commit('SET_ORDERS', [])
        return
      }

      try {
        const response = await api.get('/orders')
        console.log('Orders API response:', response.data)
        
        // Handle different API response structures
        let orders = response.data;
        
        // Handle Laravel pagination vs plain array
        if (response.data && response.data.data && Array.isArray(response.data.data)) {
          // This is a paginated response: { data: [...], meta: {...} }
          orders = response.data.data;
        } else if (response.data && response.data.data && response.data.data.data) {
          // This is a nested paginated response (less common)
          orders = response.data.data.data;
        } else if (response.data && Array.isArray(response.data)) {
          // This is a plain array response
          orders = response.data;
        } else {
          // Fallback to empty array
          orders = [];
        }

        const userOrders = Array.isArray(orders)
          ? orders
              .filter(order => order && (
                order.user_id === state.auth.user.id || 
                order.userId === state.auth.user.id
              ))
              .map(order => ({
                ...order,
                shipping: order.shipping_address || {},
                items: (order.items || []).map(item => ({
                  ...item,
                  product: item.product || { name: 'Unknown Product' }
                })),
                total_amount: order.total || order.total_amount
              }))
          : []

        commit('SET_ORDERS', userOrders)
      } catch (error) {
        console.error('Error fetching orders:', error)
        commit('SET_ORDERS', [])
      }
    },

    async createProduct({ commit }, productData) {
      try {
        const response = await api.post('/products', productData)
        commit('ADD_PRODUCT', response.data.data)
        return { success: true }
      } catch (error) {
        return { success: false, message: error.response?.data?.message || 'Failed to create product' }
      }
    },

    async updateProduct({ commit }, { id, ...productData }) {
      try {
        const response = await api.put(`/products/${id}`, productData)
        commit('UPDATE_PRODUCT', response.data.data)
        return { success: true }
      } catch (error) {
        return { success: false, message: error.response?.data?.message || 'Failed to update product' }
      }
    },

    async deleteProduct({ commit }, productId) {
      try {
        await api.delete(`/products/${productId}`)
        commit('DELETE_PRODUCT', productId)
        return { success: true }
      } catch (error) {
        return { success: false, message: error.response?.data?.message || 'Failed to delete product' }
      }
    },

    // Admin: all orders
    async fetchAllOrders({ commit }) {
      try {
        const response = await api.get('/admin/orders')
        const orders = response.data.data || response.data
        commit('SET_ALL_ORDERS', orders)
        return orders
      } catch (error) {
        commit('SET_ALL_ORDERS', [])
        throw error
      }
    },

    async updateOrderStatus({ commit }, { orderId, status }) {
      try {
        const response = await api.put(`/admin/orders/${orderId}`, { status })
        commit('UPDATE_ORDER_STATUS', { orderId, status })
        return { success: true, data: response.data }
      } catch (error) {
        return { success: false, message: error.response?.data?.message || 'Failed to update order status' }
      }
    }
  },

  getters: {
    isAuthenticated: state => state.auth.isAuthenticated,
    currentUser: state => state.auth.user,
    allProducts: state => state.products,
    productById: state => id => state.products.find(p => p.id === id),
    userOrders: state => state.orders,
    allOrders: state => state.allOrders
  }
})