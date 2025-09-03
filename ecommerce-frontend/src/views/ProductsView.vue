<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Our Products</h2>
      <div class="d-flex gap-2">
        <input type="text" class="form-control" placeholder="Search products..." v-model="searchQuery">
        <select class="form-select" v-model="sortOption">
          <option value="name">Sort by Name</option>
          <option value="price_low">Price: Low to High</option>
          <option value="price_high">Price: High to Low</option>
        </select>
      </div>
    </div>
    
    <ProductList :products="filteredProducts" />
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import ProductList from '../components/ProductList.vue'

export default {
  name: 'ProductsView',
  components: {
    ProductList
  },
  data() {
    return {
      searchQuery: '',
      sortOption: 'name'
    }
  },
  computed: {
    ...mapGetters(['allProducts']),
    filteredProducts() {
      let products = [...this.allProducts]
      
      // Filter by search query
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase()
        products = products.filter(product => 
          product.name.toLowerCase().includes(query) ||
          product.description.toLowerCase().includes(query)
        )
      }
      
      // Sort products
      switch (this.sortOption) {
        case 'price_low':
          products.sort((a, b) => a.price - b.price)
          break
        case 'price_high':
          products.sort((a, b) => b.price - a.price)
          break
        default:
          products.sort((a, b) => a.name.localeCompare(b.name))
      }
      
      return products
    }
  },
  async created() {
    await this.$store.dispatch('fetchProducts')
  }
}
</script>