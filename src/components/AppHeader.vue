<template>
  <header class="cnw-social-worker-header">
    <div class="cnw-social-worker-header-inner">
    <!-- Logo -->
    <div class="cnw-social-worker-header-logo" @click="$router.push('/')" role="link" tabindex="0">
      <!-- Desktop logo -->
      <div class="logo-desktop">
        <img v-if="logoUrl" :src="logoUrl" alt="Social Bridge" class="custom-logo" />
        <template v-else>
          <span class="logo-text">SOCIAL</span>
          <svg class="logo-icon" viewBox="0 0 65.1794 31.9372" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M45.041 21.7709C45.0611 21.7302 44.3947 19.0327 43.2705 16.2905C42.2653 13.839 40.347 10.9998 39.9137 10.5799C39.6193 10.2947 38.8291 10.833 37.2112 11.6874C36.9575 11.8214 35.2898 12.7021 34.1286 13.5201C30.3863 16.1566 27.4511 18.6533 24.0634 22.8423C21.4614 26.0596 19.9835 29.036 19.9226 29.1434C19.2373 30.3534 19.0276 31.6476 17.616 31.8909C17.2399 31.9557 7.74751 31.9093 3.42191 31.9231C2.06435 31.9275 0.989599 32.0845 0.309714 31.0424C0.0106363 30.5842 0.00319033 29.9458 0.00208774 29.8472C-0.0315413 26.9906 0.351197 24.5597 0.422865 24.1047C1.9239 14.5704 5.9077 9.63884 6.0968 9.35786C7.20214 7.71564 9.44454 5.5461 10.3598 4.85959C12.5055 3.2505 15.4093 1.10395 21.1954 0.241575C25.4644 -0.394836 29.746 0.415117 29.9177 0.523512C30.1905 0.695822 29.82 0.772872 29.3409 0.986104C28.9954 1.1398 28.9489 1.18182 28.7891 1.25696C26.9506 2.12248 25.2912 3.49603 25.1866 3.54297C24.7951 3.71871 18.4475 3.12746 12.8259 7.57919C9.20183 10.4492 7.57275 13.9224 7.08444 14.9004C4.16699 20.744 3.76413 27.391 3.77185 27.9355C3.77722 28.3189 4.18408 28.2312 4.3864 28.2311C14.2786 28.2184 14.2777 28.2459 15.1378 28.2345C16.055 28.2223 14.8708 27.2808 16.9012 19.4382C17.8365 15.8256 19.5084 12.6327 19.7813 12.1693C20.7705 10.4888 23.5232 5.22652 30.3076 2.08703C30.915 1.80591 34.1468 0.310415 38.4323 0.0618722C42.3775 -0.166962 47.7098 0.156307 52.9212 3.53476C53.5196 3.92277 56.9272 5.9378 59.8765 10.584C65.1801 18.9388 65.1763 28.8738 65.1794 29.8474C65.1831 31.0989 64.3429 31.7433 63.6455 31.8777C63.1467 31.9739 54.6273 31.8983 49.3172 31.928C47.9176 31.9358 46.6634 32.0973 45.9979 30.6742C44.9084 28.3441 43.5062 26.1124 43.018 25.4381C41.6071 23.4888 41.6792 23.4365 40.1018 21.6191C36.1132 17.0235 34.0669 15.9999 33.9169 15.6034C33.8822 15.5113 33.8489 15.4232 34.8369 14.7793C36.0795 13.9693 36.8008 13.4674 37.0614 13.5204C37.2552 13.5598 40.5092 16.3015 42.9893 19.2997C44.5925 21.238 44.8206 21.8614 44.9185 21.8716C44.9311 21.873 44.9875 21.8789 45.041 21.7709ZM28.9094 7.11537C28.6153 7.33366 28.1221 7.62682 28.4905 7.83266C28.6371 7.91464 29.752 8.45155 30.5495 8.94549C30.9049 9.1657 32.3641 10.0196 32.4905 10.047C32.695 10.0912 32.7096 10.0675 34.8089 8.84408C36.7571 7.70894 36.9125 7.76601 36.8453 7.57071C36.8151 7.48366 35.6935 6.73297 35.4276 6.53767C34.7267 6.02252 34.4713 6.05153 34.6623 5.86321C34.7308 5.7956 34.7392 5.80778 35.8235 5.53488C36.9056 5.26252 39.2565 4.92789 39.6364 5.1366C40.3561 5.53214 43.9144 8.94741 46.0982 13.5829C47.4573 16.4675 49.1547 20.9633 49.6439 27.9264C49.6705 28.3038 49.8538 28.2429 50.971 28.2408C61.1688 28.2231 61.1892 28.2776 61.3014 28.1959C61.4428 28.0931 61.4496 28.0543 61.2401 26.0217C60.9206 22.923 59.557 17.1087 57.2081 13.266C56.0771 11.4159 53.0914 6.32854 45.7526 4.32679C39.6967 2.67486 33.3589 3.8813 28.9094 7.11537ZM21.5949 19.9785C25.0192 15.7298 29.0216 12.7262 29.0348 12.7152C29.0571 12.697 29.2971 12.4995 29.1484 12.3747C29.1029 12.3367 27.6981 11.5519 26.1885 10.7744C25.6256 10.4846 25.4484 10.2891 25.1159 10.7305C23.6225 12.7135 22.821 14.3042 22.6267 14.6799C21.0818 17.6662 20.0759 21.7786 20.079 21.7799C20.2462 21.8538 20.3803 21.6774 20.3908 21.6528C20.4511 21.5118 21.3455 20.3137 21.5949 19.9785Z" fill="url(#paint0_linear_logo)"/>
            <defs>
              <linearGradient id="paint0_linear_logo" x1="5.69586" y1="18.5561" x2="59.585" y2="18.8337" gradientUnits="userSpaceOnUse">
                <stop stop-color="#3AA9DA"/>
                <stop offset="1" stop-color="#5EBF91"/>
              </linearGradient>
            </defs>
          </svg>
          <span class="logo-text">BRIDGE</span>
        </template>
      </div>
      <!-- Mobile logo -->
      <div class="logo-mobile">
        <button v-if="isLoggedIn" class="mobile-menu-btn" @click.stop="toggleMobileSidebar" aria-label="Menu">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <img v-if="mobileLogoUrl" :src="mobileLogoUrl" alt="Social Bridge" class="custom-logo-mobile" />
        <svg v-else class="logo-icon-mobile" viewBox="0 0 65.1794 31.9372" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" clip-rule="evenodd" d="M45.041 21.7709C45.0611 21.7302 44.3947 19.0327 43.2705 16.2905C42.2653 13.839 40.347 10.9998 39.9137 10.5799C39.6193 10.2947 38.8291 10.833 37.2112 11.6874C36.9575 11.8214 35.2898 12.7021 34.1286 13.5201C30.3863 16.1566 27.4511 18.6533 24.0634 22.8423C21.4614 26.0596 19.9835 29.036 19.9226 29.1434C19.2373 30.3534 19.0276 31.6476 17.616 31.8909C17.2399 31.9557 7.74751 31.9093 3.42191 31.9231C2.06435 31.9275 0.989599 32.0845 0.309714 31.0424C0.0106363 30.5842 0.00319033 29.9458 0.00208774 29.8472C-0.0315413 26.9906 0.351197 24.5597 0.422865 24.1047C1.9239 14.5704 5.9077 9.63884 6.0968 9.35786C7.20214 7.71564 9.44454 5.5461 10.3598 4.85959C12.5055 3.2505 15.4093 1.10395 21.1954 0.241575C25.4644 -0.394836 29.746 0.415117 29.9177 0.523512C30.1905 0.695822 29.82 0.772872 29.3409 0.986104C28.9954 1.1398 28.9489 1.18182 28.7891 1.25696C26.9506 2.12248 25.2912 3.49603 25.1866 3.54297C24.7951 3.71871 18.4475 3.12746 12.8259 7.57919C9.20183 10.4492 7.57275 13.9224 7.08444 14.9004C4.16699 20.744 3.76413 27.391 3.77185 27.9355C3.77722 28.3189 4.18408 28.2312 4.3864 28.2311C14.2786 28.2184 14.2777 28.2459 15.1378 28.2345C16.055 28.2223 14.8708 27.2808 16.9012 19.4382C17.8365 15.8256 19.5084 12.6327 19.7813 12.1693C20.7705 10.4888 23.5232 5.22652 30.3076 2.08703C30.915 1.80591 34.1468 0.310415 38.4323 0.0618722C42.3775 -0.166962 47.7098 0.156307 52.9212 3.53476C53.5196 3.92277 56.9272 5.9378 59.8765 10.584C65.1801 18.9388 65.1763 28.8738 65.1794 29.8474C65.1831 31.0989 64.3429 31.7433 63.6455 31.8777C63.1467 31.9739 54.6273 31.8983 49.3172 31.928C47.9176 31.9358 46.6634 32.0973 45.9979 30.6742C44.9084 28.3441 43.5062 26.1124 43.018 25.4381C41.6071 23.4888 41.6792 23.4365 40.1018 21.6191C36.1132 17.0235 34.0669 15.9999 33.9169 15.6034C33.8822 15.5113 33.8489 15.4232 34.8369 14.7793C36.0795 13.9693 36.8008 13.4674 37.0614 13.5204C37.2552 13.5598 40.5092 16.3015 42.9893 19.2997C44.5925 21.238 44.8206 21.8614 44.9185 21.8716C44.9311 21.873 44.9875 21.8789 45.041 21.7709ZM28.9094 7.11537C28.6153 7.33366 28.1221 7.62682 28.4905 7.83266C28.6371 7.91464 29.752 8.45155 30.5495 8.94549C30.9049 9.1657 32.3641 10.0196 32.4905 10.047C32.695 10.0912 32.7096 10.0675 34.8089 8.84408C36.7571 7.70894 36.9125 7.76601 36.8453 7.57071C36.8151 7.48366 35.6935 6.73297 35.4276 6.53767C34.7267 6.02252 34.4713 6.05153 34.6623 5.86321C34.7308 5.7956 34.7392 5.80778 35.8235 5.53488C36.9056 5.26252 39.2565 4.92789 39.6364 5.1366C40.3561 5.53214 43.9144 8.94741 46.0982 13.5829C47.4573 16.4675 49.1547 20.9633 49.6439 27.9264C49.6705 28.3038 49.8538 28.2429 50.971 28.2408C61.1688 28.2231 61.1892 28.2776 61.3014 28.1959C61.4428 28.0931 61.4496 28.0543 61.2401 26.0217C60.9206 22.923 59.557 17.1087 57.2081 13.266C56.0771 11.4159 53.0914 6.32854 45.7526 4.32679C39.6967 2.67486 33.3589 3.8813 28.9094 7.11537ZM21.5949 19.9785C25.0192 15.7298 29.0216 12.7262 29.0348 12.7152C29.0571 12.697 29.2971 12.4995 29.1484 12.3747C29.1029 12.3367 27.6981 11.5519 26.1885 10.7744C25.6256 10.4846 25.4484 10.2891 25.1159 10.7305C23.6225 12.7135 22.821 14.3042 22.6267 14.6799C21.0818 17.6662 20.0759 21.7786 20.079 21.7799C20.2462 21.8538 20.3803 21.6774 20.3908 21.6528C20.4511 21.5118 21.3455 20.3137 21.5949 19.9785Z" fill="url(#paint0_linear_logo_m)"/>
          <defs>
            <linearGradient id="paint0_linear_logo_m" x1="5.69586" y1="18.5561" x2="59.585" y2="18.8337" gradientUnits="userSpaceOnUse">
              <stop stop-color="#3AA9DA"/>
              <stop offset="1" stop-color="#5EBF91"/>
            </linearGradient>
          </defs>
        </svg>
      </div>
    </div>

    <!-- Actions -->
    <div class="cnw-social-worker-header-actions">
      <button v-if="isLoggedIn" class="cnw-social-worker-btn cnw-social-worker-btn-primary ask-btn" @click="$router.push('/ask')">
        + Ask a Question
      </button>
      <template v-else>
        <button class="cnw-social-worker-btn cnw-social-worker-btn-primary ask-btn login-header-btn" @click="openModal('login')">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Login
        </button>
        <button class="cnw-social-worker-btn ask-btn login-header-btn cnw-register-header-btn" @click="openModal('register')">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
          Register
        </button>
      </template>
      <div class="notif-wrapper" v-if="isLoggedIn">
        <button class="notif-btn" title="Notifications" @click="toggleDropdown">
        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30" fill="none">
          <path d="M19.9999 23.75C19.9999 26.512 17.7609 28.75 14.9999 28.75C12.2379 28.75 9.99994 26.512 9.99994 23.75C9.99994 20.989 12.2379 18.75 14.9999 18.75C17.7609 18.75 19.9999 20.989 19.9999 23.75Z" fill="#5FBF91"/>
          <path d="M24.7629 17.48H24.7749C25.2059 18.273 25.7579 19.004 26.4319 19.632C25.7539 19.001 25.1929 18.274 24.7629 17.48Z" fill="#3AA9DA"/>
          <path d="M3.56806 19.632C4.24206 19.004 4.79406 18.273 5.22506 17.48H5.23706C4.80706 18.274 4.24606 19.001 3.56806 19.632Z" fill="#3AA9DA"/>
          <path fill-rule="evenodd" clip-rule="evenodd" d="M9.99994 23.75C9.99994 20.989 12.2379 18.75 14.9999 18.75C17.7609 18.75 19.9999 20.989 19.9999 23.75H9.99994Z" fill="#3AA9DA"/>
          <path fill-rule="evenodd" clip-rule="evenodd" d="M9.99994 23.75H4.68794C3.48094 23.75 2.49994 22.769 2.49994 21.562C2.49994 20.925 2.77594 20.321 3.26494 19.901L3.27794 19.89C3.37694 19.806 3.47394 19.72 3.56894 19.632C4.24594 19.001 4.80694 18.274 5.23794 17.48C5.89594 16.267 6.24994 14.899 6.24994 13.485V10C6.24994 5.175 10.1749 1.25 14.9999 1.25C19.8249 1.25 23.7499 5.175 23.7499 10V13.485C23.7499 14.899 24.1039 16.267 24.7629 17.48C25.1929 18.274 25.7539 19.001 26.4319 19.632C26.5259 19.72 26.6229 19.806 26.7219 19.89L26.7349 19.901C27.2239 20.321 27.4999 20.925 27.4999 21.562C27.4999 22.769 26.5189 23.75 25.3129 23.75H19.9999C19.9999 20.989 17.7609 18.75 14.9999 18.75C12.2379 18.75 9.99994 20.989 9.99994 23.75Z" fill="#3AA9DA"/>
        </svg>


          <span v-if="unreadCount > 0" class="notif-badge">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
        </button>

        <!-- Dropdown -->
        <div v-if="showDropdown" class="notif-dropdown">
          <div class="notif-dropdown-header">
            <span class="notif-dropdown-title">Notifications</span>
            <button v-if="unreadCount > 0" class="notif-mark-all" @click="markAllRead">Mark all read</button>
          </div>
          <div v-if="loading" class="notif-dropdown-loading">Loading...</div>
          <div v-else-if="notifications.length === 0" class="notif-dropdown-empty">No notifications yet.</div>
          <div v-else class="notif-dropdown-list">
            <div
              v-for="n in notifications"
              :key="n.id"
              class="notif-item"
              :class="{ 'notif-unread': !parseInt(n.is_read) }"
              @click="handleNotifClick(n)"
            >
              <img :src="n.actor_avatar" class="notif-item-avatar" width="32" height="32" />
              <div class="notif-item-body">
                <p class="notif-item-msg">{{ n.message }}</p>
                <span class="notif-item-time">{{ timeAgo(n.created_at) }}</span>
              </div>
              <span v-if="!parseInt(n.is_read)" class="notif-item-dot"></span>
            </div>
            <div v-if="notifHasMore" class="notif-show-more">
              <button class="notif-show-more-btn" :disabled="loadingMore" @click.stop="loadMoreNotifications">
                {{ loadingMore ? 'Loading...' : 'Show more' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    </div><!-- /.cnw-social-worker-header-inner -->

    <!-- Login Modal -->
    <div v-if="showLoginModal" class="cnw-login-overlay" @click.self="closeLoginModal">
      <div class="cnw-login-modal">
        <button class="cnw-login-close" @click="closeLoginModal">&times;</button>

        <!-- Login View -->
        <template v-if="modalView === 'login'">
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
            <a href="#" @click.prevent="switchView('forgot')">Forgot your password?</a>
          </p>
          <p class="cnw-login-forgot">
            Don't have an account? <a href="#" @click.prevent="switchView('register')">Register</a>
          </p>
        </template>

        <!-- Register View -->
        <template v-else-if="modalView === 'register'">
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
            Already have an account? <a href="#" @click.prevent="switchView('login')">Login</a>
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
            <a href="#" @click.prevent="switchView('login')">Back to Login</a>
          </p>
        </template>
      </div>
    </div>
  </header>
</template>

<script>
import { getNotifications, getUnreadNotificationCount, markNotificationRead, markAllNotificationsRead, login, forgotPassword, register } from '@/api/index.js';

export default {
  name: 'AppHeader',
  data() {
    return {
      unreadCount: 0,
      notifications: [],
      showDropdown: false,
      loading: false,
      notifPage: 1,
      notifHasMore: false,
      loadingMore: false,
      logoUrl: window.cnwData?.logoUrl || '',
      mobileLogoUrl: window.cnwData?.mobileLogoUrl || '',
      isLoggedIn: !!(window.cnwData?.currentUser?.id > 0),
      pollTimer: null,
      showLoginModal: false,
      modalView: 'login',
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
  mounted() {
    if (this.isLoggedIn) {
      this.fetchUnreadCount();
      // Poll every 30 seconds
      this.pollTimer = setInterval(() => this.fetchUnreadCount(), 30000);
    }
    document.addEventListener('click', this.onOutsideClick);
  },
  beforeUnmount() {
    if (this.pollTimer) clearInterval(this.pollTimer);
    document.removeEventListener('click', this.onOutsideClick);
  },
  methods: {
    toggleMobileSidebar() {
      document.body.classList.toggle('cnw-mobile-sidebar-open');
    },
    async fetchUnreadCount() {
      try {
        const data = await getUnreadNotificationCount();
        this.unreadCount = data.count || 0;
      } catch { /* silent */ }
    },
    async toggleDropdown() {
      this.showDropdown = !this.showDropdown;
      if (this.showDropdown && this.notifications.length === 0) {
        this.loading = true;
        this.notifPage = 1;
        try {
          const data = await getNotifications({ page: 1, per_page: 10 });
          this.notifications = data.notifications || [];
          this.notifHasMore = data.pages > 1;
        } catch { /* silent */ }
        finally { this.loading = false; }
      }
    },
    async loadMoreNotifications() {
      this.loadingMore = true;
      this.notifPage++;
      try {
        const data = await getNotifications({ page: this.notifPage, per_page: 10 });
        const more = data.notifications || [];
        this.notifications = [...this.notifications, ...more];
        this.notifHasMore = this.notifPage < data.pages;
      } catch { /* silent */ }
      finally { this.loadingMore = false; }
    },
    onOutsideClick(e) {
      if (this.showDropdown && !e.target.closest('.notif-wrapper')) {
        this.showDropdown = false;
      }
    },
    async handleNotifClick(n) {
      if (!parseInt(n.is_read)) {
        n.is_read = '1';
        this.unreadCount = Math.max(0, this.unreadCount - 1);
        try { await markNotificationRead(n.id); } catch { /* silent */ }
      }
      // Navigate to the thread if reference_type is thread
      if (n.reference_type === 'thread' && n.reference_id) {
        this.showDropdown = false;
        this.$router.push('/thread/' + n.reference_id);
      }
    },
    async markAllRead() {
      this.notifications.forEach(n => n.is_read = '1');
      this.unreadCount = 0;
      try { await markAllNotificationsRead(); } catch { /* silent */ }
    },
    openModal(view) {
      this.modalView = view;
      this.loginError = '';
      this.resetSuccess = '';
      this.registerSuccess = '';
      this.showLoginModal = true;
    },
    switchView(view) {
      this.modalView = view;
      this.loginError = '';
      this.resetSuccess = '';
      this.registerSuccess = '';
    },
    closeLoginModal() {
      this.showLoginModal = false;
      this.modalView = 'login';
      this.loginError = '';
      this.resetSuccess = '';
      this.registerSuccess = '';
    },
    async handleLogin() {
      this.loginError = '';
      this.loginLoading = true;
      try {
        const data = await login({ username: this.loginUsername, password: this.loginPassword });
        // Update global cnwData with fresh nonce and user info
        window.cnwData.nonce = data.nonce;
        window.cnwData.currentUser = data.currentUser;
        this.showLoginModal = false;
        // Reload the page so all components pick up the new auth state
        window.location.reload();
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
      if (this.regPassword.length < 6) {
        this.loginError = 'Password must be at least 6 characters.';
        return;
      }
      this.loginLoading = true;
      try {
        const data = await register({ username: this.regUsername, email: this.regEmail, password: this.regPassword, first_name: this.regFirstName, last_name: this.regLastName });
        if (data.success) {
          // Auto-login with the same credentials
          const loginData = await login({ username: this.regUsername, password: this.regPassword });
          window.cnwData.nonce = loginData.nonce;
          window.cnwData.currentUser = loginData.currentUser;
          this.showLoginModal = false;
          window.location.reload();
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
    timeAgo(dateStr) {
      if (!dateStr) return '';
      const now = new Date();
      const date = new Date(dateStr);
      const diff = Math.floor((now - date) / 1000);
      if (diff < 60) return 'just now';
      if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
      if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
      if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    },
  },
};
</script>

<style>
.cnw-social-worker-header {
  z-index: 200;
}

.cnw-social-worker-header-inner {
  max-width: 1360px;
  width: 100%;
  margin: 0 auto;
  padding: 10px 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

/* Logo */
.cnw-social-worker-header-logo {
  display: flex;
  align-items: center;
  gap: 4px;
  cursor: pointer;
  text-decoration: none;
  user-select: none;
}
.cnw-social-worker-header-logo:focus {
  outline: 2px solid var(--teal);
  outline-offset: 3px;
  border-radius: 3px;
}

.custom-logo {
  max-height: 44px;
  width: auto;
  display: block;
  object-fit: contain;
}

.logo-text {
  font-size: 17px;
  font-weight: 800;
  color: var(--text-dark);
  letter-spacing: 1.5px;
  line-height: 1;
}

.logo-icon {
  width: 44px;
  height: auto;
  flex-shrink: 0;
}

/* Desktop/mobile logo toggle */
.logo-desktop {
  display: flex;
  align-items: center;
  gap: 4px;
}
.logo-mobile {
  display: none;
  align-items: center;
  gap: 10px;
}
.custom-logo-mobile {
  max-height: 38px;
  width: auto;
  display: block;
  object-fit: contain;
}
.logo-icon-mobile {
  width: 40px;
  height: auto;
  flex-shrink: 0;
}

/* Mobile hamburger menu button */
.mobile-menu-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  color: var(--text-dark, #333);
  border-radius: 4px;
  transition: background 0.15s;
}
.mobile-menu-btn svg {
  width: 30px;
  height: 30px;
}
.mobile-menu-btn:hover {
  background: rgba(0,0,0,0.06);
}

/* Actions */
.cnw-social-worker-header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.ask-btn {
  padding: 9px 20px;
  font-size: 14px;
  border-radius: var(--radius-sm);
}

/* Notification wrapper */
.notif-wrapper {
  position: relative;
}

.notif-btn {
  position: relative;
  border-radius: var(--radius-sm);
  border: none;
  background: transparent;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  flex-shrink: 0;
}

.notif-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  background: var(--red);
  color: #fff;
  font-size: 10px;
  font-weight: 700;
  min-width: 18px;
  height: 18px;
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid var(--light);
  padding: 0 3px;
}

/* Dropdown */
.notif-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  width: 360px;
  max-height: 440px;
  background: var(--white, #fff);
  border-radius: var(--radius-m, 8px);
  box-shadow: 0 8px 32px rgba(0,0,0,0.15);
  border: 1px solid var(--border, #e0e0e0);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  z-index: 1000;
}

.notif-dropdown-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  border-bottom: 1px solid var(--border, #e0e0e0);
}
.notif-dropdown-title {
  font-size: 15px;
  font-weight: 600;
  color: var(--text-dark);
}
.notif-mark-all {
  background: none;
  border: none;
  color: var(--primary);
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  padding: 0;
}
.notif-mark-all:hover {
  text-decoration: underline;
}

.notif-dropdown-loading,
.notif-dropdown-empty {
  padding: 32px 16px;
  text-align: center;
  color: var(--text-light, #999);
  font-size: 13px;
}

.notif-dropdown-list {
  overflow-y: auto;
  max-height: 380px;
}

.notif-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 10px 16px;
  cursor: pointer;
  transition: background 0.12s;
}

.notif-item img{
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
}

.notif-item:hover {
  background: var(--bg, #f5f5f5);
}
.notif-unread {
  background: rgba(58, 169, 218, 0.06);
}
.notif-item-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}
.notif-item-body {
  flex: 1;
  min-width: 0;
}
.notif-item-msg {
  font-size: 13px;
  color: var(--text-body);
  line-height: 1.4;
  margin: 0;
}
.notif-item-time {
  font-size: 11px;
  color: var(--text-light, #999);
}
.notif-item-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--primary, #3aa9da);
  flex-shrink: 0;
  margin-top: 6px;
}

.notif-show-more {
  padding: 8px 16px;
  text-align: center;
  border-top: 1px solid var(--border, #e0e0e0);
}
.notif-show-more-btn {
  background: none;
  border: none;
  color: var(--primary, #3aa9da);
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  padding: 4px 0;
}
.notif-show-more-btn:hover {
  text-decoration: underline;
}
.notif-show-more-btn:disabled {
  opacity: 0.6;
  cursor: default;
  text-decoration: none;
}

@media (max-width: 760px) {
  .cnw-social-worker-header-inner { padding: 8px 10px; }
  .ask-btn { padding: 7px 12px; font-size: 13px; }
  .cnw-social-worker-header-actions { gap: 10px; }
  .notif-dropdown { width: 320px; right: -20px; }
  .cnw-login-modal { padding: 24px 18px; width: 340px; }
  .cnw-login-row { flex-direction: column; gap: 12px; }
  .logo-desktop { display: none; }
  .logo-mobile { display: flex; }
}

@media (max-width: 480px) {
  .cnw-social-worker-header-inner { padding: 6px 8px; }
  .ask-btn { padding: 6px 10px; font-size: 12px; }
  .logo-text { font-size: 14px; letter-spacing: 1px; }
  .logo-icon { width: 34px; }
  .custom-logo { max-height: 34px; }
  .cnw-social-worker-header-actions { gap: 10px; }
  .notif-dropdown { position: fixed; left: 8px; right: 8px; top: 56px; width: auto; }
  .cnw-login-modal { padding: 20px 14px; width: auto; max-width: 95vw; }
}

/* Login Modal */
.cnw-login-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
}
.cnw-login-modal {
  background: #fff;
  border-radius: var(--radius-m, 8px);
  padding: 32px;
  width: 380px;
  max-width: 90vw;
  position: relative;
  box-shadow: 0 12px 40px rgba(0,0,0,0.2);
}
.cnw-login-close {
  position: absolute;
  top: 12px;
  right: 16px;
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #999;
  line-height: 1;
}
.cnw-login-close:hover { color: #333; }
.cnw-login-title {
  font-size: 20px;
  font-weight: 700;
  margin: 0 0 20px;
  color: var(--text-dark, #222);
}
.cnw-login-error {
  background: #fef2f2;
  color: #dc2626;
  padding: 8px 12px;
  border-radius: 6px;
  font-size: 13px;
  margin-bottom: 16px;
}
.cnw-login-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.cnw-login-row {
  display: flex;
  gap: 12px;
}
.cnw-login-row .cnw-login-label {
  flex: 1;
}
.cnw-login-label {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 13px;
  font-weight: 600;
  color: var(--text-dark, #333);
}
.cnw-login-input {
  padding: 6px 12px;
  border: 1px solid var(--border, #ddd);
  border-radius: 6px;
  font-size: 14px;
  outline: none;
  transition: border-color 0.15s;
}
.cnw-login-input:focus {
  border-color: var(--primary, #3aa9da);
  box-shadow: 0 0 0 3px rgba(58,169,218,0.15);
}
.cnw-login-submit {
  width: 100%;
  padding: 9px;
  font-size: 15px;
  margin-top: 4px;
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
}
.cnw-login-submit:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}
.cnw-login-forgot {
  text-align: center;
  margin: 14px 0 0;
  font-size: 13px;
}
.cnw-login-forgot a {
  color: var(--primary, #3aa9da);
  text-decoration: none;
  font-weight: 500;
}
.cnw-login-forgot a:hover {
  text-decoration: underline;
}
.cnw-login-success {
  background: #f0fdf4;
  color: #16a34a;
  padding: 8px 12px;
  border-radius: 6px;
  font-size: 13px;
  margin-bottom: 16px;
}
.login-header-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.cnw-register-header-btn {
  background: var(--secondary, #22a55b);
  border: none;
  color: #fff;
}
.cnw-register-header-btn:hover {
  opacity: 0.9;
}
</style>
