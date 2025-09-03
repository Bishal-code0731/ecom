<template>
  <div>
    <div class="container-fluid bg-primary py-4 mb-4">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center">
          <h1 class="h3 text-white mb-0">Checkout</h1>
          <div class="text-white">
            <span class="me-2">Step 1: Cart</span>
            <span class="me-2">></span>
            <span class="fw-bold">Step 2: Payment</span>
          </div>
        </div>
      </div>
    </div>
    <div class="container">
      <Checkout />
    </div>
  </div>
</template>

<script>
import Checkout from '../components/CheckOut.vue';

export default {
  name: 'CheckoutView',
  components: {
    Checkout,
  },
  computed: {
    isAuthenticated() {
      return this.$store.getters.isAuthenticated;
    },
  },
  beforeRouteEnter(to, from, next) {
    next(vm => {
      if (!vm.isAuthenticated) {
        next('/login');
      } else if (!to.query.productId || !to.query.quantity) {
        next('/products');
      } else {
        next();
      }
    });
  },
};
</script>