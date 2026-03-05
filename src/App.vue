<template>
  <div class="cnw-social-worker-app-wrapper">
    <AppHeader />
    <div class="cnw-social-worker-layout">
      <div v-if="isLoggedIn" class="cnw-sidebar-mobile-overlay" @click="closeMobileSidebar"></div>
      <AppSidebar v-if="isLoggedIn" />
      <main class="cnw-social-worker-main">
        <router-view />
      </main>
      <AppRightSidebar v-if="!hideRightSidebar" />
    </div>
    <AppFooter />
  </div>
</template>

<script>
import AppHeader from './components/AppHeader.vue';
import AppSidebar from './components/AppSidebar.vue';
import AppRightSidebar from './components/AppRightSidebar.vue';
import AppFooter from './components/AppFooter.vue';

export default {
  name: 'App',
  components: { AppHeader, AppSidebar, AppRightSidebar, AppFooter },
  data() {
    return {
      isLoggedIn: !!(window.cnwData?.currentUser?.id > 0),
    };
  },
  computed: {
    hideRightSidebar() {
      return this.$route.path !== '/';
    },
  },
  watch: {
    '$route'() {
      this.closeMobileSidebar();
    },
  },
  methods: {
    closeMobileSidebar() {
      document.body.classList.remove('cnw-mobile-sidebar-open');
    },
  },
};
</script>

<style>
.cnw-social-worker-app-wrapper {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.cnw-social-worker-layout {
  display: flex;
  flex: 1;
  max-width: 1360px;
  width: 100%;
  margin: 0 auto;
  padding: 24px 16px;
  gap: 20px;
}

.cnw-social-worker-main {
  flex: 1;
  min-width: 0;
  background: #fff;
  border-radius: var(--radius);
  padding: 24px;
}

/* Sidebar mobile overlay — hidden by default */
.cnw-sidebar-mobile-overlay {
  display: none;
}

@media (max-width: 1100px) {
  .cnw-social-worker-right-sidebar { display: none; }
}
@media (max-width: 760px) {
  .cnw-social-worker-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1000;
    width: 278px;
    max-width: 85vw;
    height: 100vh;
    height: 100dvh;
    min-height: 100vh;
    min-height: 100dvh;
    transform: translateX(-100%);
    transition: transform 0.25s ease;
    overflow-y: auto;
    border-radius: 0;
  }
  .cnw-mobile-sidebar-open .cnw-social-worker-sidebar {
    transform: translateX(0);
  }
  .cnw-sidebar-mobile-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.45);
    z-index: 999;
  }
  .cnw-mobile-sidebar-open .cnw-sidebar-mobile-overlay {
    display: block;
  }
  .cnw-social-worker-layout {
    padding: 10px 8px;
    gap: 12px;
  }
  .cnw-social-worker-main {
    padding: 14px 12px;
    border-radius: var(--radius-m);
  }
}
</style>
