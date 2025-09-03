<template>
  <div class="admin-products">
    <h2>Product Management</h2>
    
    <button @click="showAddForm" class="btn-primary add-product-btn">
      Add New Product
    </button>
    
    <ProductForm 
      v-if="showForm" 
      :product="editingProduct" 
      @saved="handleProductSaved"
      @cancel="hideForm"
    />
    
    <div class="products-list">
      <div v-for="product in products" :key="product.id" class="product-item">
        <div class="product-info">
          <img :src="product.image || '/placeholder-product.jpg'" :alt="product.name" class="product-img">
          <div>
            <h3>{{ product.name }}</h3>
            <p>{{ product.description }}</p>
            <p class="price">${{ product.price }}</p>
          </div>
        </div>
        
        <div class="product-actions">
          <button @click="editProduct(product)" class="btn-edit">Edit</button>
          <button @click="deleteProduct(product.id)" class="btn-delete">Delete</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import ProductForm from '../components/ProductForm.vue'
import api from '../services/api'

export default {
  name: 'AdminProducts',
  components: {
    ProductForm
  },
  data() {
    return {
      showForm: false,
      editingProduct: null
    }
  },
  computed: {
    ...mapState(['products'])
  },
  mounted() {
    this.fetchProducts()
  },
  methods: {
    ...mapActions(['fetchProducts']),
    showAddForm() {
      this.editingProduct = null
      this.showForm = true
    },
    hideForm() {
      this.showForm = false
      this.editingProduct = null
    },
    editProduct(product) {
      this.editingProduct = product
      this.showForm = true
    },
    async deleteProduct(id) {
      if (confirm('Are you sure you want to delete this product?')) {
        try {
          await api.deleteProduct(id)
          this.fetchProducts() // Refresh the list
        } catch (error) {
          console.error('Error deleting product:', error)
          alert('Error deleting product. Please try again.')
        }
      }
    },
    handleProductSaved() {
      this.fetchProducts() // Refresh the list
      this.hideForm()
    }
  }
}
</script>

<style scoped>
.admin-products {
  padding: 20px 0;
}

.add-product-btn {
  margin-bottom: 20px;
}

.products-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.product-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.product-info {
  display: flex;
  align-items: center;
  gap: 15px;
}

.product-img {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 4px;
}

.product-info h3 {
  margin-bottom: 5px;
}

.product-info p {
  color: #666;
  margin-bottom: 5px;
  font-size: 14px;
}

.price {
  font-weight: bold;
  color: #3b71fe !important;
}

.product-actions {
  display: flex;
  gap: 10px;
}

.btn-edit, .btn-delete {
  padding: 8px 15px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
}

.btn-edit {
  background-color: #ffc107;
  color: #000;
}

.btn-edit:hover {
  background-color: #e0a800;
}

.btn-delete {
  background-color: #dc3545;
  color: white;
}

.btn-delete:hover {
  background-color: #c82333;
}
</style>