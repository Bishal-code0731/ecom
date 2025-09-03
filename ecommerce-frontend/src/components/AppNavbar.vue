<template>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <router-link class="navbar-brand" to="/">E-Commerce Store</router-link>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">

        <!-- Left menu -->
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <router-link class="nav-link" to="/">Home</router-link>
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/products">Products</router-link>
          </li>
          <li v-if="isAuthenticated" class="nav-item">
            <router-link class="nav-link" to="/orders">My Orders</router-link>
          </li>
        </ul>

        <!-- Right menu -->
        <ul class="navbar-nav">

          <!--  show login/register when NOT authenticated -->
          <li v-if="!isAuthenticated" class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
              Account
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><router-link class="dropdown-item" to="/login">Login</router-link></li>
              <li><router-link class="dropdown-item" to="/register">Register</router-link></li>
            </ul>
          </li>

          <!-- Show user menu when authenticated - even if currentUser is still loading -->
          <li v-else class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
              {{ currentUser ? currentUser.name : 'Account' }}
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <!-- Show loading if user data not loaded yet -->
              <li v-if="!currentUser">
                <span class="dropdown-item-text">Loading...</span>
              </li>
              
              <!-- Show user menu when data is loaded -->
              <template v-else>
                <li><span class="dropdown-item-text">Welcome, {{ currentUser.name }}</span></li>
                <li><hr class="dropdown-divider"></li>
                <li><router-link class="dropdown-item" to="/orders">My Orders</router-link></li>
                
                <!-- Admin links -->
                <li v-if="currentUser.is_admin">
                  <router-link class="dropdown-item" to="/admin/products">Manage Products</router-link>
                </li>
                <li v-if="currentUser.is_admin">
                  <router-link class="dropdown-item" to="/admin/orders">Manage Orders</router-link>
                </li>
                
                <li><hr class="dropdown-divider"></li>
              </template>
              
              <li><a class="dropdown-item" href="#" @click.prevent="handleLogout">Logout</a></li>
            </ul>
          </li>

        </ul>

      </div>
    </div>
  </nav>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'

export default {
  name: 'AppNavbar',
  computed: {
    ...mapGetters(['isAuthenticated', 'currentUser'])
  },
  methods: {
    ...mapActions(['logout']),
    handleLogout() {
      this.logout()
      this.$router.push('/')
    }
  }
}
</script>