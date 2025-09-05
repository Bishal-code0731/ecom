<template>
  <div class="order-history">
    <h2>Order History</h2>
    
    <div v-if="loading" class="loading">
      <p>Loading your orders...</p>
    </div>
    
    <div v-else-if="error" class="error-alert">
      <p>{{ error }}</p>
    </div>
    
    <div v-else-if="orders.length === 0" class="empty-state">
      <p>You haven't placed any orders yet.</p>
      <router-link to="/products" class="btn-primary">Start Shopping</router-link>
    </div>
    
    <div v-else class="orders-list">
      <div v-for="order in orders" :key="order.id" class="order-card">
        <div class="order-header">
          <div class="order-info">
            <h3>Order #{{ order.id }}</h3>
            <p class="order-date">Placed on {{ formatDate(order.created_at) }}</p>
            <p class="order-status" :class="order.status">
              Status: {{ order.status }}
            </p>
          </div>
          <div class="order-total">
            Total: ${{ order.total_amount }}
          </div>
        </div>
        
        <div class="order-items">
          <h4>Items:</h4>
          <div v-for="item in order.items" :key="item.id" class="order-item">
            <img 
              :src="item.product.image || '/placeholder-product.jpg'" 
              :alt="item.product.name" 
              class="item-image"
            >
            <div class="item-details">
              <h4 class="item-name">{{ item.product.name }}</h4>
              <p class="item-quantity">Quantity: {{ item.quantity }}</p>
              <p class="item-price">${{ item.price }} each</p>
            </div>
            <div class="item-subtotal">${{ (item.price * item.quantity).toFixed(2) }}</div>
          </div>
        </div>
        
        <div class="order-actions" v-if="order.status === 'pending'">
          <button @click="cancelOrder(order.id)" class="btn-cancel">Cancel Order</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import api from '../services/api'

export default {
  name: 'OrderHistory',
  data() {
    return {
      loading: false,
      error: null
    }
  },
  computed: {
    ...mapState(['orders'])
  },
  async mounted() {
    await this.loadOrders()
  },
  methods: {
    ...mapActions(['fetchOrders']),
    async loadOrders() {
      this.loading = true
      this.error = null
      try {
        await this.fetchOrders()
      } catch (error) {
        console.error('Error loading orders:', error)
        this.error = 'Failed to load orders. Please try again.'
      } finally {
        this.loading = false
      }
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    },
    async cancelOrder(orderId) {
      if (confirm('Are you sure you want to cancel this order?')) {
        try {
          await api.put(`/orders/${orderId}`, { status: 'cancelled' })
          alert('Order cancelled successfully!')
          await this.loadOrders() // Refresh the orders list
        } catch (error) {
          console.error('Error cancelling order:', error)
          alert('Failed to cancel order. Please try again.')
        }
      }
    }
  }
}
</script>

<style scoped>
.order-history {
  padding: 20px;
  max-width: 1000px;
  margin: 0 auto;
}

h2 {
  margin-bottom: 30px;
  text-align: center;
  color: #333;
  font-size: 28px;
}

.loading {
  text-align: center;
  padding: 40px;
  color: #666;
}

.error-alert {
  text-align: center;
  padding: 20px;
  background-color: #f8d7da;
  color: #721c24;
  border-radius: 8px;
  margin-bottom: 20px;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.empty-state p {
  margin-bottom: 20px;
  font-size: 18px;
  color: #666;
}

.orders-list {
  display: flex;
  flex-direction: column;
  gap: 25px;
}

.order-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transition: transform 0.2s ease;
}

.order-card:hover {
  transform: translateY(-2px);
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 20px;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-bottom: 1px solid #dee2e6;
}

.order-info h3 {
  margin-bottom: 8px;
  color: #3b71fe;
  font-size: 20px;
}

.order-date {
  color: #6c757d;
  margin-bottom: 8px;
  font-size: 14px;
}

.order-status {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 500;
  display: inline-block;
}

.order-status.pending {
  background-color: #fff3cd;
  color: #856404;
}

.order-status.completed {
  background-color: #d4edda;
  color: #155724;
}

.order-status.cancelled {
  background-color: #f8d7da;
  color: #721c24;
}

.order-status.shipped {
  background-color: #cce5ff;
  color: #004085;
}

.order-total {
  font-size: 24px;
  font-weight: bold;
  color: #3b71fe;
}

.order-items {
  padding: 20px;
}

.order-items h4 {
  margin-bottom: 15px;
  color: #495057;
  font-size: 18px;
  border-bottom: 2px solid #e9ecef;
  padding-bottom: 10px;
}

.order-item {
  display: flex;
  align-items: center;
  padding: 15px 0;
  border-bottom: 1px solid #e9ecef;
  gap: 15px;
}

.order-item:last-child {
  border-bottom: none;
}

.item-image {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid #dee2e6;
}

.item-details {
  flex: 1;
}

.item-name {
  font-weight: 600;
  margin-bottom: 5px;
  color: #212529;
  font-size: 16px;
}

.item-quantity, .item-price {
  color: #6c757d;
  font-size: 14px;
  margin-bottom: 3px;
}

.item-subtotal {
  font-weight: bold;
  color: #3b71fe;
  font-size: 18px;
}

.order-actions {
  padding: 15px 20px;
  background-color: #f8f9fa;
  border-top: 1px solid #dee2e6;
  text-align: right;
}

.btn-cancel {
  padding: 8px 16px;
  background-color: #dc3545;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
  transition: background-color 0.3s;
}

.btn-cancel:hover {
  background-color: #c82333;
}

/* Responsive design */
@media (max-width: 768px) {
  .order-header {
    flex-direction: column;
    gap: 15px;
  }
  
  .order-total {
    align-self: flex-end;
  }
  
  .order-item {
    flex-direction: column;
    text-align: center;
    gap: 10px;
  }
  
  .item-details {
    text-align: center;
  }
  
  .item-subtotal {
    align-self: center;
  }
}
</style>