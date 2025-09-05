<template>
  <div class="product-form">
    <h2>{{ editingProduct ? 'Edit Product' : 'Add New Product' }}</h2>
    <form @submit.prevent="handleSubmit">
      <div class="form-group">
        <label for="name">Product Name</label>
        <input type="text" id="name" v-model="form.name" required>
      </div>

      <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" v-model="form.description" rows="3" required></textarea>
      </div>

      <div class="form-group">
        <label for="price">Price</label>
        <input type="number" id="price" v-model="form.price" step="0.01" min="0" required>
      </div>

      <div class="form-group">
      <label for="sku">SKU</label>
      <input type="text" id="sku" v-model="form.sku" required>
      </div>

      <div class="form-group">
        <label for="stock_quantity">Stock Quantity</label>
        <input type="number" id="stock_quantity" v-model="form.stock_quantity" step="1" min="0" required>
      </div>

      <div class="form-group">
        <label for="image">Image URL</label>
        <input type="url" id="image" v-model="form.image">
      </div>

      <button type="submit" class="btn-primary" :disabled="loading">
        {{ loading ? 'Saving...' : 'Save Product' }}
      </button>
      <button type="button" class="btn-secondary" @click="$emit('cancel')" v-if="editingProduct">
        Cancel
      </button>
    </form>
  </div>
</template>

<script>
export default {
  name: 'ProductForm',
  props: {
    product: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      form: {
        name: '',
        description: '',
        price: 0,
        stock_quantity: 0,
        sku: '',
        image: ''
      },
      loading: false
    }
  },
  computed: {
    editingProduct() {
      return this.product !== null
    }
  },
  watch: {
    product: {
      immediate: true,
      handler(newProduct) {
        if (newProduct) {
          this.form = { ...newProduct }
        } else {
          this.resetForm()
        }
      }
    }
  },
  methods: {
    resetForm() {
      this.form = {
        name: '',
        description: '',
        price: 0,
        stock_quantity: 0,
        sku: '', 
        image: ''
      }
    },
    async handleSubmit() {
      this.loading = true
      try {
        if (this.editingProduct) {
          // Use Vuex action for update
          const result = await this.$store.dispatch('updateProduct', { id: this.product.id, ...this.form })
          if (result.success) {
            this.$emit('saved')
            this.$emit('cancel')
          } else {
            alert(result.message || 'Error updating product')
          }
        } else {
          // Use Vuex action for create
          const result = await this.$store.dispatch('createProduct', this.form)
          if (result.success) {
            this.$emit('saved')
            this.resetForm()
          } else {
            alert(result.message || 'Error creating product')
          }
        }
      } catch (error) {
        console.error('Error saving product:', error)
        const msg = error.response?.data?.errors
          ? Object.values(error.response.data.errors).flat().join('\n')
          : 'Error saving product. Please try again.'
        alert(msg)
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
.product-form {
  max-width: 600px;
  margin: 0 auto;
}

.form-group {
  margin-bottom: 20px;
}

label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
}

input, textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 16px;
}

textarea {
  resize: vertical;
}

.btn-secondary {
  background-color: #6c757d;
  border-color: #6c757d;
  color: white;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  margin-left: 10px;
}

.btn-secondary:hover {
  background-color: #5a6268;
  border-color: #5a6268;
}
</style>
