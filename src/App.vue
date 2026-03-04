<template>
  <div class="cnw-social-worker-app-wrapper">
    <AppHeader />
    <div class="cnw-social-worker-layout">
      <AppSidebar />
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
  computed: {
    hideRightSidebar() {
      return this.$route.path === '/ask';
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

@media (max-width: 1100px) {
  .cnw-social-worker-right-sidebar { display: none; }
}
@media (max-width: 760px) {
  .cnw-social-worker-sidebar { display: none; }
  .cnw-social-worker-layout { padding: 12px; }
}
</style>
