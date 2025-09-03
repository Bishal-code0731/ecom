<template>
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card shadow">
        <div class="card-header bg-primary text-white text-center py-3">
          <h4 class="mb-0">Login</h4>
        </div>
        <div class="card-body p-4">
          <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">
            {{ message }}
          </div>

          <form @submit.prevent="submitForm">
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" v-model="form.email" required placeholder="Enter your email">
            </div>

            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input type="password" class="form-control" id="password" v-model="form.password" required placeholder="Enter your password">
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2" :disabled="processing">
              <span v-if="processing" class="spinner-border spinner-border-sm me-2" role="status"></span>
              {{ processing ? 'Logging in...' : 'Login' }}
            </button>
          </form>

          <div class="text-center mt-4">
            <p class="text-muted">
              Don't have an account?
              <router-link to="/register" class="text-decoration-none">Register here</router-link>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapActions } from 'vuex'
import { getCsrfCookie } from '../services/api'

export default {
  name: 'AppLogin',
  data() {
    return {
      form: { email: '', password: '' },
      processing: false,
      message: '',
      messageType: ''
    }
  },
  methods: {
    ...mapActions(['login']),

    clearMessage() { this.message = ''; this.messageType = '' },
    showMessage(text, type = 'success') { this.message = text; this.messageType = type },

    async submitForm() {
      this.processing = true
      this.clearMessage()

      try {
        // Get CSRF cookie before login
        await getCsrfCookie()

        const result = await this.login(this.form)
        if (result.success) {
          this.showMessage('Login successful!', 'success')
          setTimeout(() => this.$router.push({ name: 'Home' }), 1000)
        } else {
          this.showMessage(result.message, 'danger')
        }
      } catch (error) {
        this.showMessage('An unexpected error occurred. Please try again.', 'danger')
        console.error('Login error:', error)
      } finally {
        this.processing = false
      }
    }
  }
}
</script>

<style scoped>
.card { border: none; border-radius: 10px; }
.form-control { border-radius: 8px; padding: 12px; border: 1px solid #ddd; }
.form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.25); }
.btn { border-radius: 8px; font-weight: 600; }
.alert { border-radius: 8px; margin-bottom: 1rem; }
</style>
