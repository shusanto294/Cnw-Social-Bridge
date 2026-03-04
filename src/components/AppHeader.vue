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
