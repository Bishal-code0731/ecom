<template>
  <div>
    <div class="hero-section bg-light py-5 mb-5">
      <div class="container text-center">
        <h1 class="display-4">Welcome to Our Store</h1>
        <p class="lead">Discover amazing products at great prices</p>
        <router-link to="/products" class="btn btn-primary btn-lg">Shop Now</router-link>
      </div>
    </div>
    
    <div class="container">
      <h2 class="text-center mb-4">Featured Products</h2>
      <ProductList :products="featuredProducts" />
      
      <div class="text-center mt-5">
        <router-link to="/products" class="btn btn-outline-primary">View All Products</router-link>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import ProductList from '../components/ProductList.vue'

export default {
  name: 'HomeView',
  components: {
    ProductList
  },
  computed: {
    ...mapGetters(['allProducts']),
    featuredProducts() {
      // Add null/undefined check to prevent slice error
      return this.allProducts ? this.allProducts.slice(0, 3) : []
    }
  },
  async created() {
    await this.$store.dispatch('fetchProducts')
  }
}
</script>

<style scoped>
.hero-section {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 10px;
}
</style>