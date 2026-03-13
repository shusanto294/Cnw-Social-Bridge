<template>
  <div class="cnw-auth-form-wrap">
    <!-- Login View -->
    <template v-if="currentView === 'login'">
      <h2 class="cnw-login-title">Login</h2>
      <p v-if="loginError" class="cnw-login-error">{{ loginError }}</p>
      <form @submit.prevent="handleLogin" class="cnw-login-form">
        <label class="cnw-login-label">
          Username or Email
          <input v-model="loginUsername" type="text" class="cnw-login-input" required autocomplete="username" />
        </label>
        <label class="cnw-login-label">
          Password
          <input v-model="loginPassword" type="password" class="cnw-login-input" required autocomplete="current-password" />
        </label>
        <button type="submit" class="cnw-social-worker-btn cnw-social-worker-btn-primary cnw-login-submit" :disabled="loginLoading">
          {{ loginLoading ? 'Logging in...' : 'Login' }}
        </button>
      </form>
      <p class="cnw-login-forgot">
        <a href="#" @click.prevent="currentView = 'forgot'">Forgot your password?</a>
      </p>
      <p class="cnw-login-forgot">
        Don't have an account? <a href="#" @click.prevent="currentView = 'register'">Register</a>
      </p>
    </template>

    <!-- Register View -->
    <template v-else-if="currentView === 'register'">
      <h2 class="cnw-login-title">Create Account</h2>
      <p v-if="registerSuccess" class="cnw-login-success">{{ registerSuccess }}</p>
      <p v-if="loginError" class="cnw-login-error">{{ loginError }}</p>
      <form v-if="!registerSuccess" @submit.prevent="handleRegister" class="cnw-login-form">
        <div class="cnw-login-row">
          <label class="cnw-login-label">
            First Name
            <input v-model="regFirstName" type="text" class="cnw-login-input" required autocomplete="given-name" />
          </label>
          <label class="cnw-login-label">
            Last Name
            <input v-model="regLastName" type="text" class="cnw-login-input" required autocomplete="family-name" />
          </label>
        </div>
        <label class="cnw-login-label">
          Username
          <input v-model="regUsername" type="text" class="cnw-login-input" required autocomplete="username" />
        </label>
        <label class="cnw-login-label">
          Email
          <input v-model="regEmail" type="email" class="cnw-login-input" required autocomplete="email" />
        </label>
        <label class="cnw-login-label">
          Password
          <input v-model="regPassword" type="password" class="cnw-login-input" required autocomplete="new-password" />
        </label>
        <label class="cnw-login-label">
          Confirm Password
          <input v-model="regPasswordConfirm" type="password" class="cnw-login-input" required autocomplete="new-password" />
        </label>
        <button type="submit" class="cnw-social-worker-btn cnw-social-worker-btn-primary cnw-login-submit" :disabled="loginLoading">
          {{ loginLoading ? 'Creating...' : 'Register' }}
        </button>
      </form>
      <p class="cnw-login-forgot">
        Already have an account? <a href="#" @click.prevent="currentView = 'login'">Login</a>
      </p>
    </template>

    <!-- Forgot Password View -->
    <template v-else>
      <h2 class="cnw-login-title">Reset Password</h2>
      <p v-if="resetSuccess" class="cnw-login-success">{{ resetSuccess }}</p>
      <p v-if="loginError" class="cnw-login-error">{{ loginError }}</p>
      <form v-if="!resetSuccess" @submit.prevent="handleForgotPassword" class="cnw-login-form">
        <label class="cnw-login-label">
          Username or Email
          <input v-model="resetEmail" type="text" class="cnw-login-input" required autocomplete="username" />
        </label>
        <button type="submit" class="cnw-social-worker-btn cnw-social-worker-btn-primary cnw-login-submit" :disabled="loginLoading">
          {{ loginLoading ? 'Sending...' : 'Send Reset Link' }}
        </button>
      </form>
      <p class="cnw-login-forgot">
        <a href="#" @click.prevent="currentView = 'login'">Back to Login</a>
      </p>
    </template>
  </div>
</template>

<script>
import { login, register, forgotPassword } from '@/api/index.js';

export default {
  name: 'AuthForm',
  props: {
    initialView: {
      type: String,
      default: 'login',
    },
  },
  data() {
    return {
      currentView: this.initialView,
      loginUsername: '',
      loginPassword: '',
      loginError: '',
      loginLoading: false,
      resetEmail: '',
      resetSuccess: '',
      regFirstName: '',
      regLastName: '',
      regUsername: '',
      regEmail: '',
      regPassword: '',
      regPasswordConfirm: '',
      registerSuccess: '',
    };
  },
  watch: {
    initialView(val) {
      this.currentView = val;
      this.clearState();
    },
    currentView() {
      this.clearState();
    },
  },
  methods: {
    clearState() {
      this.loginError = '';
      this.resetSuccess = '';
      this.registerSuccess = '';
    },
    redirectAfterAuth() {
      const redirect = this.$route && this.$route.query && this.$route.query.redirect;
      if (redirect) {
        window.location.hash = '#' + redirect;
      }
      window.location.reload();
    },
    async handleLogin() {
      this.loginError = '';
      this.loginLoading = true;
      try {
        const data = await login({ username: this.loginUsername, password: this.loginPassword });
        window.cnwData.nonce = data.nonce;
        window.cnwData.currentUser = data.currentUser;
        this.$emit('auth-success');
        this.redirectAfterAuth();
      } catch (e) {
        this.loginError = e.message || 'Invalid username or password.';
      } finally {
        this.loginLoading = false;
      }
    },
    async handleRegister() {
      this.loginError = '';
      this.registerSuccess = '';
      if (this.regPassword !== this.regPasswordConfirm) {
        this.loginError = 'Passwords do not match.';
        return;
      }
      if (this.regPassword.length < 8) {
        this.loginError = 'Password must be at least 8 characters.';
        return;
      }
      this.loginLoading = true;
      try {
        const data = await register({ username: this.regUsername, email: this.regEmail, password: this.regPassword, first_name: this.regFirstName, last_name: this.regLastName });
        if (data.success) {
          const loginData = await login({ username: this.regUsername, password: this.regPassword });
          window.cnwData.nonce = loginData.nonce;
          window.cnwData.currentUser = loginData.currentUser;
          this.$emit('auth-success');
          this.redirectAfterAuth();
        } else {
          this.loginError = data.message || 'Registration failed.';
        }
      } catch (e) {
        this.loginError = e.message || 'Registration failed.';
      } finally {
        this.loginLoading = false;
      }
    },
    async handleForgotPassword() {
      this.loginError = '';
      this.resetSuccess = '';
      this.loginLoading = true;
      try {
        const data = await forgotPassword({ user_login: this.resetEmail });
        this.resetSuccess = data.message || 'If an account exists with that username or email, a password reset link has been sent.';
      } catch {
        this.resetSuccess = 'If an account exists with that username or email, a password reset link has been sent.';
      } finally {
        this.loginLoading = false;
      }
    },
  },
};
</script>
