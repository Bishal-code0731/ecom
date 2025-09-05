<template>
  <div>
    <h2 class="mb-4">My Orders</h2>

    <!-- Auto-hide Bootstrap alert -->
    <div v-if="showPaymentAlert" class="alert alert-success">
      Payment successful! Your order is confirmed.
    </div>

    <!-- Use the OrderHistory component here -->
    <OrderHistory />
  </div>
</template>

<script>
import { mapActions } from 'vuex'
import OrderHistory from '@/components/OrderHistory.vue'

export default {
  name: 'OrdersView',
  components: {
    OrderHistory
  },
  data() {
    return {
      showPaymentAlert: false,
      refreshInterval: null
    }
  },
  methods: {
    ...mapActions(['fetchOrders']),
    checkPaymentAlert() {
      if (this.$route.query.paymentSuccess) {
        this.showPaymentAlert = true
        setTimeout(() => {
          this.showPaymentAlert = false
          // Clean URL without reloading page
          history.replaceState({}, document.title, window.location.pathname)
        }, 3500)
      }
    }
  },
  async created() {
    await this.fetchOrders()
    this.checkPaymentAlert()
  },
  beforeUnmount() {
    if (this.refreshInterval) clearInterval(this.refreshInterval)
  }
}
</script>

<style scoped>
.alert {
  border-radius: 10px;
  padding: 15px 20px;
  margin-bottom: 20px;
  border: none;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.alert-success {
  background-color: #d4edda;
  color: #155724;
}
</style>