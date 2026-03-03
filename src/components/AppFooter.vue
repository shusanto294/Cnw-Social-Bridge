<template>
  <footer class="cnw-social-worker-footer">
    <p>&copy;{{ year }} Social Bridge. All rights reserved.</p>
    <p class="footer-credit">
      Designed by
      <svg class="heart-icon" viewBox="0 0 24 24" fill="var(--red)" width="13" height="13"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
      Cloud Nine Web
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

<style scoped>
.cnw-social-worker-footer {
  background: #1a1a2e;
  color: rgba(255,255,255,0.65);
  text-align: center;
  padding: 18px 24px;
  font-size: 13px;
  line-height: 1.8;
}
.footer-credit {
  display: inline-flex;
  align-items: center;
  gap: 4px;
}
.heart-icon {
  vertical-align: middle;
}
</style>
