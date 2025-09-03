
<template>
  <div class="product-catalog">
    <ProductList />
  </div>
</template>

<script>
import ProductList from './ProductList.vue'

export default {
  name: 'ProductCatalog',
  components: {
    ProductList
  }
}
</script>

<style scoped>
.product-catalog {
  padding: 20px 0;
}
</style>