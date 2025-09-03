<template>
  <div class="admin-orders">
    <h2>Order Management</h2>
    
    <!-- Loading Spinner -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2">Loading orders...</p>
    </div>

    <!-- No orders found -->
    <div v-else-if="!orders.length" class="text-center py-5">
      <p class="text-muted">No orders found.</p>
    </div>

    <!-- Orders List -->
    <div v-else class="orders-list">
      <div
        v-for="order in orders"
        :key="order?.id || Math.random()"
        class="order-item"
      >
        <div class="order-info">
          <h4>Order #{{ order.id || 'N/A' }}</h4>
          <p><strong>Customer:</strong> {{ order.user?.email || order.user_email || 'N/A' }}</p>
          <p><strong>Date:</strong> {{ order.created_at ? formatDate(order.created_at) : 'N/A' }}</p>
          <p><strong>Total:</strong> ${{ order.total || order.total_amount || '0.00' }}</p>
          <p><strong>Status:</strong> 
            <span :class="`status-badge status-${order.status || 'unknown'}`">
              {{ order.status || 'unknown' }}
            </span>
          </p>
        </div>

        <div class="order-actions">
          <button @click="viewOrderDetails(order)" class="btn-view">View Details</button>
          <select
            v-model="order.status"
            @change="handleStatusChange(order)"
            class="status-select"
          >
            <option value="pending">Pending</option>
            <option value="processing">Processing</option>
            <option value="shipped">Shipped</option>
            <option value="delivered">Delivered</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Order Details Modal -->
    <div v-if="selectedOrder" class="modal-backdrop show" @click="closeModal"></div>
    <div v-if="selectedOrder" class="modal show d-block" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Order Details #{{ selectedOrder.id }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6">
                <h6>Customer Information</h6>
                <p><strong>Email:</strong> {{ selectedOrder.user?.email || selectedOrder.user_email || 'N/A' }}</p>
                <p><strong>Order Date:</strong> {{ formatDate(selectedOrder.created_at) }}</p>
                <p><strong>Status:</strong> 
                  <span :class="`status-badge status-${selectedOrder.status}`">
                    {{ selectedOrder.status }}
                  </span>
                </p>
                <p><strong>Total Amount:</strong> ${{ selectedOrder.total || selectedOrder.total_amount }}</p>
              </div>
              <div class="col-md-6">
                <h6>Shipping Address</h6>
                <p v-if="selectedOrder.shipping_address">
                  {{ selectedOrder.shipping_address.first_name }} {{ selectedOrder.shipping_address.last_name }}<br>
                  {{ selectedOrder.shipping_address.address }}<br>
                  {{ selectedOrder.shipping_address.district }}<br>
                  {{ selectedOrder.shipping_address.contact_number }}
                </p>
                <p v-else>No shipping address available</p>
              </div>
            </div>

            <hr>

            <h6>Order Items</h6>
            <div v-if="selectedOrder.items && selectedOrder.items.length" class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in selectedOrder.items" :key="item.id">
                    <td>{{ item.product?.name || 'Unknown Product' }}</td>
                    <td>{{ item.quantity }}</td>
                    <td>${{ item.price }}</td>
                    <td>${{ (item.quantity * item.price).toFixed(2) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-else>
              <p class="text-muted">No items found in this order.</p>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal">Close</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapActions } from 'vuex'

export default {
  name: 'AdminOrders',
  data() {
    return {
      loading: false,
      orders: [], // Initialize as empty array
      selectedOrder: null // Store the selected order for modal
    }
  },
  methods: {
    ...mapActions(['fetchAllOrders', 'updateOrderStatus']),

    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString()
    },

    viewOrderDetails(order) {
      this.selectedOrder = order;
    },

    closeModal() {
      this.selectedOrder = null;
    },

    async handleStatusChange(order) {
      if (!order || !order.id) {
        console.error('Cannot update status: Invalid order')
        return
      }

      try {
        const result = await this.updateOrderStatus({
          orderId: order.id,
          status: order.status
        })

        if (result && result.success) {
          console.log(`Order #${order.id} status updated successfully`)
          // Refresh orders to get updated data
          await this.loadOrders();
        } else {
          console.error(`Failed to update order #${order.id}:`, result?.message || 'Unknown error')
        }
      } catch (error) {
        console.error(`Error updating order #${order.id}:`, error)
      }
    },

    async loadOrders() {
      this.loading = true;
      try {
        const response = await this.fetchAllOrders()

        // Normalize response to always be an array
        if (Array.isArray(response)) {
          this.orders = response
        } else if (response?.data && Array.isArray(response.data)) {
          this.orders = response.data
        } else if (response?.data) {
          this.orders = [response.data]
        } else {
          this.orders = []
        }
      } catch (error) {
        console.error('Error fetching orders:', error)
        this.orders = []
      } finally {
        this.loading = false
      }
    }
  },
  async created() {
    await this.loadOrders();
  }
}
</script>

<style scoped>
.admin-orders {
  padding: 20px;
}

.orders-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.order-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.order-info h4 {
  margin-bottom: 10px;
}

.order-info p {
  margin-bottom: 5px;
}

.status-badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 500;
}

.status-pending { background-color: #fff3cd; color: #856404; }
.status-processing { background-color: #cce5ff; color: #004085; }
.status-shipped { background-color: #d4edda; color: #155724; }
.status-delivered { background-color: #d1ecf1; color: #0c5460; }
.status-cancelled { background-color: #f8d7da; color: #721c24; }
.status-unknown { background-color: #e9ecef; color: #495057; }

.order-actions {
  display: flex;
  gap: 10px;
  align-items: center;
}

.btn-view {
  padding: 8px 15px;
  background-color: #3b71fe;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-view:hover {
  background-color: #2a5fd0;
}

.status-select {
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.spinner-border {
  width: 3rem;
  height: 3rem;
}

/* Modal Styles */
.modal-backdrop {
  opacity: 0.5;
}

.modal {
  background-color: rgba(0, 0, 0, 0.5);
}
</style>