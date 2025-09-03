<template>
  
  <ProductDetail :id="id" />
</template>

<script>
import ProductDetail from '@/components/ProductDetail.vue'

export default {
  name: 'ProductDetailsView',
  components: {
    ProductDetail
  },
  props: {
    id: {
      type: [String, Number],
      required: true
    }
  }
}
</script>