<template>
  <div v-if="product" class="row">
    <div class="col-md-6">
      <img :src="product.image || '/placeholder-product.png'" :alt="product.name" class="img-fluid rounded">
    </div>
    <div class="col-md-6">
      <h2>{{ product.name }}</h2>
      <p class="text-muted">{{ product.category }}</p>
      <p class="lead">${{ product.price }}</p>
      <p>{{ product.description }}</p>
      
      <!-- Enhanced Quantity Selection -->
      <div class="mb-3" v-if="$store.getters.isAuthenticated">
        <label for="quantity" class="form-label">Quantity</label>
        <div class="input-group" style="max-width: 140px;">
          <button 
            class="btn btn-outline-secondary" 
            type="button" 
            @click="decrementQuantity"
            :disabled="quantity <= 1"
          >−</button>
          <input 
            type="number" 
            id="quantity" 
            class="form-control text-center" 
            v-model.number="quantity" 
            min="1" 
            :max="product.stock_quantity"
            required
            @input="validateQuantity"
          >
          <button 
            class="btn btn-outline-secondary" 
            type="button" 
            @click="incrementQuantity"
            :disabled="quantity >= product.stock_quantity"
          >+</button>
        </div>
        <small class="text-muted" v-if="product.stock_quantity">
          Available: {{ product.stock_quantity }} in stock
        </small>
      </div>
      
      <div class="d-grid gap-2">
        <button 
          v-if="$store.getters.isAuthenticated" 
          @click="buyNow" 
          class="btn btn-primary btn-lg"
          :disabled="quantity <= 0 || quantity > product.stock_quantity"
        >
          {{ quantity > 1 ? `Buy ${quantity} Items` : 'Buy Now' }}
        </button>
        <router-link 
          v-else 
          to="/login" 
          class="btn btn-primary btn-lg"
        >
          Login to Purchase
        </router-link>
      </div>
    </div>
  </div>
  <div v-else class="text-center">
    <p>Product not found</p>
    <router-link to="/products" class="btn btn-primary">Back to Products</router-link>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  name: 'ProductDetail',
  props: {
    id: {
      type: [String, Number],
      required: true
    }
  },
  data() {
    return {
      quantity: 1
    }
  },
  computed: {
    ...mapGetters(['productById']),
    product() {
      return this.productById(parseInt(this.id))
    }
  },
  methods: {
    buyNow() {
      this.$router.push({ 
        name: 'Checkout', 
        params: { 
          productId: this.id,
          quantity: this.quantity
        } 
      })
    },
    // New methods for quantity buttons
    incrementQuantity() {
      if (this.quantity < this.product.stock_quantity) {
        this.quantity++;
      }
    },
    decrementQuantity() {
      if (this.quantity > 1) {
        this.quantity--;
      }
    },
    validateQuantity() {
      // Ensuring quantity is always a valid number within bounds
      if (isNaN(this.quantity) || this.quantity < 1) {
        this.quantity = 1;
      } else if (this.product && this.quantity > this.product.stock_quantity) {
        this.quantity = this.product.stock_quantity;
      }
    }
  },
  async created() {
    if (!this.product) {
      await this.$store.dispatch('fetchProducts')
    }
  }
}
</script>