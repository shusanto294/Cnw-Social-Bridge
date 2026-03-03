<template>
  <div class="cnw-social-worker-report-view">
    <h1 class="cnw-social-worker-view-heading">Report an Issue</h1>
    <div class="cnw-social-worker-placeholder-card">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--border)" stroke-width="1.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      <p>Report issues and feedback coming soon!</p>
    </div>
  </div>
</template>

<script>
export default { name: 'ReportIssueView' };
</script>

<style>
.cnw-social-worker-report-view { display: flex; flex-direction: column; gap: 16px; }
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
