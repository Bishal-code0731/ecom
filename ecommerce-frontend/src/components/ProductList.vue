<template>
  <div class="row">
    <div v-for="product in products" :key="product.id" class="col-md-4 mb-4">
      <div class="card h-100">
        <img :src="product.image || '/placeholder-product.png'" class="card-img-top" :alt="product.name" style="height: 200px; object-fit: cover;">
        <div class="card-body d-flex flex-column">
          <h5 class="card-title">{{ product.name }}</h5>
          <p class="card-text flex-grow-1">{{ product.description }}</p>
          <div class="mt-auto">
            <p class="card-text fw-bold">${{ product.price }}</p>
            <router-link :to="`/product/${product.id}`" class="btn btn-primary">View Details</router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ProductList',
  props: {
    products: {
      type: Array,
      required: true
    }
  }
}
</script>