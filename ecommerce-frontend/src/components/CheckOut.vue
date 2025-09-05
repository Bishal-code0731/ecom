<template>
  <div class="row">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header">
          <h4>Checkout</h4>
        </div>
        <div class="card-body">
          <div v-if="product" class="mb-4">
            <h5>Product: {{ product.name }}</h5>
            <p>Price: ${{ product.price }} each</p>
            <p>Quantity: {{ quantity }}</p>
            <p class="fw-bold">Total: ${{ total.toFixed(2) }}</p>
          </div>

          <div id="payment-request-button" class="mb-3"></div>

          <form @submit.prevent="submitCardPayment">
            <div class="mb-3">
              <label class="form-label">Contact Number</label>
              <input type="tel" class="form-control" v-model="form.contact_number" required :disabled="processing" />
            </div>
            <div class="mb-3">
              <label class="form-label">Address</label>
              <input type="text" class="form-control" v-model="form.address" required :disabled="processing" />
            </div>
            <div class="mb-3">
              <label class="form-label">District</label>
              <input type="text" class="form-control" v-model="form.district" required :disabled="processing" />
            </div>
            <div class="mb-3">
              <label class="form-label">Nearby Landmark</label>
              <input type="text" class="form-control" v-model="form.landmark" :disabled="processing" />
            </div>
            <div class="mb-3">
              <label class="form-label">Delivery Instructions</label>
              <textarea class="form-control" v-model="form.delivery_instructions" rows="3" :disabled="processing"></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label">Card Details</label>
              <div id="card-element" class="form-control"></div>
              <div id="card-errors" class="text-danger mt-2"></div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100" :disabled="processing">
              {{ processing ? 'Processing...' : `Pay $${total.toFixed(2)}` }}
            </button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card">
        <div class="card-header">
          <h5>Order Summary</h5>
        </div>
        <div class="card-body">
          <div v-if="product" class="d-flex justify-content-between">
            <span>{{ product.name }} × {{ quantity }}</span>
            <span>${{ total.toFixed(2) }}</span>
          </div>
          <hr />
          <div class="d-flex justify-content-between fw-bold">
            <span>Total</span>
            <span>${{ total.toFixed(2) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'
import api from '../services/api'

export default {
  name: 'Checkout',
  props: {
    productId: { type: [String, Number], required: true },
    quantity: { type: [String, Number], default: 1 }
  },
  data() {
    return {
      product: null,
      form: {
        contact_number: '',
        address: '',
        district: '',
        landmark: '',
        delivery_instructions: ''
      },
      processing: false,
      stripe: null,
      card: null
    }
  },
  computed: {
    ...mapGetters(['currentUser']),
    total() {
      return this.product ? this.product.price * Number(this.quantity) : 0
    }
  },
  async created() {
    await this.fetchProduct()
    await this.loadStripe()
  },
  methods: {
    ...mapActions(['fetchOrders']),

    async fetchProduct() {
      try {
        const response = await api.get(`/products/${this.productId}`)
        this.product = response.data.data || response.data
      } catch (error) {
        console.error('Product fetch failed:', error)
        this.$router.push('/products')
      }
    },

    async loadStripe() {
      if (!window.Stripe) {
        await new Promise(resolve => {
          const script = document.createElement('script')
          script.src = 'https://js.stripe.com/v3/'
          script.onload = resolve
          document.head.appendChild(script)
        })
      }

      this.stripe = window.Stripe(process.env.VUE_APP_STRIPE_PUBLISHABLE_KEY)
      const elements = this.stripe.elements()

      this.card = elements.create('card')
      this.card.mount('#card-element')
      this.card.on('change', e => {
        document.getElementById('card-errors').textContent = e.error ? e.error.message : ''
      })

      const paymentRequest = this.stripe.paymentRequest({
        country: 'US',
        currency: 'usd',
        total: { label: this.product?.name || 'Total', amount: parseInt(this.total * 100) },
        requestPayerName: true,
        requestPayerEmail: true
      })

      const prButton = elements.create('paymentRequestButton', { paymentRequest })
      paymentRequest.canMakePayment().then(result => {
        if (result) prButton.mount('#payment-request-button')
      })

      paymentRequest.on('paymentmethod', async ev => {
        this.processing = true
        try {
          const orderResp = await this.createOrder()
          const order = orderResp.data.data || orderResp.data

          const intentResp = await api.post('/payment/create-intent', { order_id: order.id })
          const clientSecret = intentResp.data.client_secret || intentResp.data.data?.client_secret
          if (!clientSecret) throw new Error('No client secret from backend')

          const { error } = await this.stripe.confirmCardPayment(
            clientSecret,
            { payment_method: ev.paymentMethod.id },
            { handleActions: true }
          )

          if (error) {
            console.error('Stripe error (PR):', error.message)
            ev.complete('fail')
          } else {
            ev.complete('success')
            await this.fetchOrders()
            this.$router.push({ path: '/orders', query: { paymentSuccess: true } })
          }
        } catch (err) {
          console.error('Payment Request failed:', err)
          ev.complete('fail')
        } finally {
          this.processing = false
        }
      })
    },

    async createOrder() {
      return api.post('/orders', {
        items: [{ product_id: this.productId, quantity: Number(this.quantity) }],
        shipping_address: {
          first_name: this.currentUser?.first_name || 'Customer',
          last_name: this.currentUser?.last_name || 'Customer',
          email: this.currentUser?.email || 'noemail@example.com',
          contact_number: this.form.contact_number,
          address: this.form.address,
          district: this.form.district,
          landmark: this.form.landmark,
          delivery_instructions: this.form.delivery_instructions
        },
        payment_method: 'stripe'
      })
    },

    async submitCardPayment() {
      this.processing = true
      try {
        const orderResp = await this.createOrder()
        const order = orderResp.data.data || orderResp.data

        const intentResp = await api.post('/payment/create-intent', { order_id: order.id })
        const clientSecret = intentResp.data.client_secret || intentResp.data.data?.client_secret
        if (!clientSecret) throw new Error('No client secret from backend')

        const { error, paymentIntent } = await this.stripe.confirmCardPayment(clientSecret, {
          payment_method: { card: this.card }
        })

        if (error) {
          document.getElementById('card-errors').textContent = error.message
          return
        }

        await api.post('/payment/confirm', { payment_intent_id: paymentIntent.id })
        await this.fetchOrders()
        this.$router.push({ path: '/orders', query: { paymentSuccess: true } })
      } catch (err) {
        console.error('Payment failed:', err)
      } finally {
        this.processing = false
      }
    }
  }
}
</script>
