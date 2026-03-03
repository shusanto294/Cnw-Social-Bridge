<template>
  <div class="cnw-social-worker-users-view">
    <h1 class="cnw-social-worker-view-heading">Community Members</h1>
    <div class="cnw-social-worker-placeholder-card">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--border)" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      <p>User directory coming soon!</p>
    </div>
  </div>
</template>

<script>
export default { name: 'UsersView' };
</script>

<style>
.cnw-social-worker-users-view { display: flex; flex-direction: column; gap: 16px; }
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
