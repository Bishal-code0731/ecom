<template>
  <div>
    <h2 class="mb-4">My Orders</h2>
    
    <div v-if="orders.length === 0" class="text-center py-5">
      <p class="text-muted">You haven't placed any orders yet.</p>
      <router-link to="/products" class="btn btn-primary">Start Shopping</router-link>
    </div>
    
    <div v-else class="accordion" id="ordersAccordion">
      <div 
        v-for="(order, index) in orders" 
        :key="order?.id || index" 
        v-if="order" 
        class="accordion-item"
      >
        <h2 class="accordion-header">
          <button 
            class="accordion-button" 
            type="button" 
            data-bs-toggle="collapse" 
            :data-bs-target="`#order-${order.id}`"
          >
            Order #{{ order.id }} - {{ formatDate(order.created_at) }} - ${{ order.total_amount }}
          </button>
        </h2>
        <div 
          :id="`order-${order.id}`" 
          class="accordion-collapse collapse" 
          :class="{ show: index === 0 }"
          data-bs-parent="#ordersAccordion"
        >
          <div class="accordion-body">
            <div class="row">
              <div class="col-md-6">
                <h5>Shipping Information</h5>
                <p>
                  {{ order.shipping_first_name }} {{ order.shipping_last_name }}<br>
                  {{ order.shipping_address }}<br>
                  {{ order.shipping_city }}, {{ order.shipping_state }} {{ order.shipping_zip_code }}<br>
                  {{ order.shipping_email }}
                </p>
              </div>
              <div class="col-md-6">
                <h5>Order Details</h5>
                <div 
                  v-for="item in order.items" 
                  :key="item?.id || item?.product_id" 
                  v-if="item"
                  class="d-flex justify-content-between mb-2"
                >
                  <span>{{ item.product_name }} (x{{ item.quantity }})</span>
                  <span>${{ item.price * item.quantity }}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between fw-bold">
                  <span>Total</span>
                  <span>${{ order.total_amount }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'

export default {
  name: 'OrdersView',
  computed: {
    ...mapGetters(['userOrders']),
    orders() {
      return this.userOrders || [] // ensure always array
    }
  },
  methods: {
    ...mapActions(['fetchOrders']),
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString()
    }
  },
  async created() {
    await this.fetchOrders()
  }
}
</script>
