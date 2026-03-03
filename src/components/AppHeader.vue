<template>
  <header class="cnw-social-worker-header">
    <div class="cnw-social-worker-header-inner">
    <!-- Logo -->
    <div class="cnw-social-worker-header-logo" @click="$router.push('/')" role="link" tabindex="0">
      <img
        v-if="logoUrl"
        :src="logoUrl"
        alt="Social Bridge"
        class="custom-logo"
      />
      <template v-else>
        <span class="logo-text">SOCIAL</span>
        <svg class="logo-wave" viewBox="0 0 52 28" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path
            d="M4 22 C10 4, 20 4, 26 14 C32 24, 42 24, 48 6"
            stroke="#3bbdd4"
            stroke-width="3.5"
            stroke-linecap="round"
            fill="none"
          />
        </svg>
        <span class="logo-text">BRIDGE</span>
      </template>
    </div>

    <!-- Actions -->
    <div class="cnw-social-worker-header-actions">
      <button class="cnw-social-worker-btn cnw-social-worker-btn-primary ask-btn" @click="$router.push('/ask')">
        + Ask a Question
      </button>
      <button class="notif-btn" title="Notifications">
        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="28" viewBox="0 0 25 28" fill="none">
          <path d="M17.5 22.5C17.5 25.262 15.261 27.5 12.5 27.5C9.738 27.5 7.5 25.262 7.5 22.5C7.5 19.739 9.738 17.5 12.5 17.5C15.261 17.5 17.5 19.739 17.5 22.5Z" fill="#5FBF91"/>
          <path d="M22.2629 16.23H22.2749C22.7059 17.023 23.2579 17.754 23.9319 18.382C23.2539 17.751 22.6929 17.024 22.2629 16.23Z" fill="#3AA9DA"/>
          <path d="M1.06806 18.382C1.74206 17.754 2.29406 17.023 2.72506 16.23H2.73706C2.30706 17.024 1.74606 17.751 1.06806 18.382Z" fill="#3AA9DA"/>
          <path fill-rule="evenodd" clip-rule="evenodd" d="M7.5 22.5C7.5 19.739 9.738 17.5 12.5 17.5C15.261 17.5 17.5 19.739 17.5 22.5H7.5Z" fill="#3AA9DA"/>
          <path fill-rule="evenodd" clip-rule="evenodd" d="M7.5 22.5H2.188C0.981 22.5 0 21.519 0 20.312C0 19.675 0.276 19.071 0.765 18.651L0.778 18.64C0.877 18.556 0.974 18.47 1.069 18.382C1.746 17.751 2.307 17.024 2.738 16.23C3.396 15.017 3.75 13.649 3.75 12.235V8.75C3.75 3.925 7.675 0 12.5 0C17.325 0 21.25 3.925 21.25 8.75V12.235C21.25 13.649 21.604 15.017 22.263 16.23C22.693 17.024 23.254 17.751 23.932 18.382C24.026 18.47 24.123 18.556 24.222 18.64L24.235 18.651C24.724 19.071 25 19.675 25 20.312C25 21.519 24.019 22.5 22.813 22.5H17.5C17.5 19.739 15.261 17.5 12.5 17.5C9.738 17.5 7.5 19.739 7.5 22.5Z" fill="#3AA9DA"/>
        </svg>
        <span v-if="notifications > 0" class="notif-badge">{{ notifications }}</span>
      </button>
    </div>
    </div><!-- /.cnw-social-worker-header-inner -->
  </header>
</template>

<script>
export default {
  name: 'AppHeader',
  data() {
    return {
      notifications: 1,
      logoUrl: window.cnwData?.logoUrl || '',
    };
  },
};
</script>

<style>
.cnw-social-worker-header {
  position: sticky;
  top: 0;
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

.logo-wave {
  width: 52px;
  height: 28px;
  flex-shrink: 0;
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

.notif-btn {
  position: relative;
  border-radius: var(--radius-sm);
  border: none;
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
  width: 18px;
  height: 18px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid var(--light);
}

@media (max-width: 480px) {
  .cnw-social-worker-header-inner { padding: 8px 12px; }
  .ask-btn { padding: 8px 12px; font-size: 13px; }
}
</style>
