<template>
  <footer class="cnw-social-worker-footer">
    <p class="footer-copy">&copy;{{ year }} Social Bridge. All rights reserved.</p>
    <p class="footer-credit">
      <span>Designed by</span>
      <svg class="footer-heart-icon" viewBox="0 0 24 24" fill="var(--red)" width="14" height="14"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
      <span class="footer-brand">Cloud Nine Web</span>
    </p>
  </footer>
</template>

<script>
export default {
  name: 'AppFooter',
  data() {
    return { year: new Date().getFullYear() };
  },
};
</script>

<style>
.cnw-social-worker-footer {
  background: var(--bg-body, #3aa9da);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: var(--space-l) var(--space-2xl);
  text-align: center;
}

.footer-copy {
  font-family: 'Poppins', sans-serif;
  font-size: var(--text-m);
  font-weight: 300;
  color: var(--text-title, #fff);
  line-height: 1.32;
  white-space: nowrap;
}

.footer-credit {
  display: flex;
  align-items: center;
  gap: var(--space-2xs);
  font-family: 'Poppins', sans-serif;
  font-size: var(--text-m);
  font-weight: 300;
  color: var(--text-title, #fff);
  line-height: 1.32;
  white-space: nowrap;
}

.footer-heart-icon {
  flex-shrink: 0;
}

.footer-brand {
  font-weight: 600;
}
</style>
