<template>
  <div class="not-found-view">
    <div class="not-found-card">
      <svg class="not-found-icon" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/>
        <path d="M16 16s-1.5-2-4-2-4 2-4 2"/>
        <line x1="9" y1="9" x2="9.01" y2="9"/>
        <line x1="15" y1="9" x2="15.01" y2="9"/>
      </svg>
      <h1 class="not-found-title">404</h1>
      <p class="not-found-message">{{ message }}</p>
      <button class="cnw-social-worker-btn cnw-social-worker-btn-primary not-found-btn" @click="$router.push('/')">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
        Back to Home
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'NotFoundView',
  props: {
    message: {
      type: String,
      default: 'The page you are looking for does not exist.',
    },
  },
};
</script>

<style>
.not-found-view {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 400px;
  padding: var(--space-l, 32px);
}

.not-found-card {
  text-align: center;
  max-width: 400px;
}

.not-found-icon {
  color: var(--text-light, #999);
  margin-bottom: var(--space-s, 16px);
}

.not-found-title {
  font-size: 48px;
  font-weight: 800;
  color: var(--text-dark, #222);
  margin: 0 0 8px;
  line-height: 1;
}

.not-found-message {
  font-size: 15px;
  color: var(--text-light, #777);
  margin: 0 0 24px;
  line-height: 1.5;
}

.not-found-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 10px 24px;
  font-size: 14px;
  border-radius: var(--radius-sm, 6px);
}
</style>
