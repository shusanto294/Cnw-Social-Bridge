<template>
  <div class="cnw-social-worker-guidelines-view">
    <h1 class="cnw-social-worker-view-heading">Community Guidelines</h1>
    <div class="cnw-social-worker-placeholder-card">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--border)" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      <p>Community guidelines coming soon!</p>
    </div>
  </div>
</template>

<script>
export default { name: 'GuidelinesView' };
</script>

<style>
.cnw-social-worker-guidelines-view { display: flex; flex-direction: column; gap: 16px; }
.cnw-social-worker-view-heading { font-size: 20px; font-weight: 800; color: var(--text-dark); }
.cnw-social-worker-placeholder-card {
  background: #fff;
  border-radius: var(--radius);
  padding: 60px 20px;
  text-align: center;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  color: var(--text-light);
  font-size: 15px;
}
</style>
