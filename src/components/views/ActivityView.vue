<template>
  <div class="cnw-social-worker-activity-view">
    <h1 class="cnw-social-worker-view-heading">My Activity</h1>

    <div v-if="loading" class="activity-list">
      <div v-for="n in 5" :key="n" class="cnw-skeleton-card" style="flex-direction:row;padding:12px;gap:10px">
        <div class="cnw-skeleton cnw-skeleton-circle" style="width:36px;height:36px"></div>
        <div style="flex:1;display:flex;flex-direction:column;gap:6px">
          <div class="cnw-skeleton cnw-skeleton-line" :style="{width: [70,55,80,60,75][n-1]+'%'}"></div>
          <div class="cnw-skeleton cnw-skeleton-line-sm" style="width:25%"></div>
        </div>
      </div>
    </div>
    <div v-else-if="activities.length === 0" class="activity-empty-state">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--border)" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      <p>No activity yet. Your actions will be recorded here.</p>
    </div>
    <div v-else class="activity-list">
      <div v-for="act in activities" :key="act.id" class="activity-row">
        <div class="activity-icon" :class="'activity-icon--' + act.action_type">
          <svg v-if="act.action_type === 'login'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
          <svg v-else-if="act.action_type === 'thread_created'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          <svg v-else-if="act.action_type === 'reply_created'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4l-4 4V6c0-1.1.9-2 2-2z"/></svg>
          <svg v-else-if="act.action_type === 'voted' || act.action_type === 'received_vote' || act.action_type === 'vote_removed' || act.action_type === 'vote_changed'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
          <svg v-else-if="act.action_type === 'best_answer' || act.action_type === 'marked_solution'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          <svg v-else-if="act.action_type === 'thread_saved' || act.action_type === 'thread_unsaved'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
          <svg v-else-if="act.action_type === 'thread_deleted' || act.action_type === 'reply_deleted'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
          <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        </div>
        <div class="activity-body">
          <p class="activity-desc">
            {{ act.description }}
            <router-link v-if="act.link" :to="act.link.replace('#', '')" class="activity-link">View &rarr;</router-link>
          </p>
          <div class="activity-meta">
            <span class="activity-date">{{ formatDate(act.created_at) }}</span>
            <span v-if="act.points > 0" class="activity-points activity-points--positive">+{{ act.points }} pts</span>
            <span v-else-if="act.points < 0" class="activity-points activity-points--negative">{{ act.points }} pts</span>
            <span v-else-if="act.reason" class="activity-reason">{{ act.reason }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <nav v-if="totalPages > 1" class="activity-pagination" aria-label="Pagination">
      <button :disabled="page <= 1" @click="fetchActivity(page - 1)" aria-label="Previous page">&laquo; Prev</button>
      <button v-for="p in totalPages" :key="p" class="activity-page-btn" :class="{ 'is-active': p === page }" @click="fetchActivity(p)" :aria-label="'Page ' + p" :aria-current="p === page ? 'page' : undefined">{{ p }}</button>
      <button :disabled="page >= totalPages" @click="fetchActivity(page + 1)" aria-label="Next page">Next &raquo;</button>
    </nav>
  </div>
</template>

<script>
import { getUserActivity } from '@/api/index.js';

export default {
  name: 'ActivityView',
  data() {
    return {
      activities: [],
      loading: true,
      page: 1,
      totalPages: 1,
    };
  },
  created() {
    this.fetchActivity(1);
  },
  methods: {
    async fetchActivity(p) {
      this.page = p;
      this.loading = true;
      try {
        const data = await getUserActivity({ page: this.page });
        this.activities = data.activities || [];
        this.totalPages = data.pages || 1;
      } catch { /* silent */ }
      finally { this.loading = false; }
    },
    formatDate(d) {
      if (!d) return '';
      const date = new Date(d);
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    },
  },
};
</script>

<style>
.cnw-social-worker-activity-view {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.cnw-social-worker-view-heading {
  font-family: 'Poppins', sans-serif;
  font-size: 20px;
  font-weight: 800;
  color: var(--text-dark);
}

/* ── Empty State ─────────────────────────────────────────── */
.activity-empty-state {
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
  font-family: 'Poppins', sans-serif;
  font-size: 15px;
}

/* ── Activity List ───────────────────────────────────────── */
.activity-list {
  display: flex;
  flex-direction: column;
}
.activity-row {
  display: flex;
  align-items: flex-start;
  gap: var(--space-xs, 14px);
  padding: var(--space-xs, 14px) 0;
  border-bottom: 1px solid var(--border);
}
.activity-row:last-child {
  border-bottom: none;
}
.activity-icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: var(--bg);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: var(--text-body, #414141);
}
.activity-icon--thread_created { background: #e3f2fd; color: #1976d2; }
.activity-icon--reply_created { background: #e8f5e9; color: #388e3c; }
.activity-icon--voted,
.activity-icon--received_vote,
.activity-icon--vote_removed,
.activity-icon--vote_changed { background: #fff3e0; color: #f57c00; }
.activity-icon--best_answer,
.activity-icon--marked_solution { background: #e8f5e9; color: #22a55b; }
.activity-icon--login { background: #f3e5f5; color: #7b1fa2; }
.activity-icon--logout { background: #f3e5f5; color: #7b1fa2; }
.activity-icon--thread_saved,
.activity-icon--thread_unsaved { background: #fce4ec; color: #c62828; }
.activity-icon--registered { background: #e0f7fa; color: #00838f; }
.activity-icon--thread_deleted,
.activity-icon--reply_deleted { background: #ffebee; color: #c62828; }
.activity-icon--thread_updated,
.activity-icon--reply_updated { background: #e3f2fd; color: #1976d2; }
.activity-icon--profile_updated,
.activity-icon--avatar_updated,
.activity-icon--anonymous_toggled { background: #e0f7fa; color: #00838f; }
.activity-icon--tag_followed,
.activity-icon--tag_unfollowed { background: #fff3e0; color: #f57c00; }

.activity-body {
  flex: 1;
  min-width: 0;
}
.activity-desc {
  font-family: 'Poppins', sans-serif;
  font-weight: 400;
  font-size: var(--text-xs, 14px);
  line-height: 20px;
  color: #000;
}
.activity-link {
  color: var(--primary);
  text-decoration: none;
  font-weight: 500;
  margin-left: 6px;
}
.activity-link:hover {
  text-decoration: underline;
}
.activity-meta {
  display: flex;
  margin-top: 4px;
}
.activity-date {
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: 12px;
  color: #999;
}
.activity-points {
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  font-size: 12px;
  padding: 2px 8px;
  border-radius: 10px;
}
.activity-points--positive {
  background: #e8f5e9;
  color: #22a55b;
}
.activity-points--negative {
  background: #ffebee;
  color: #c62828;
}
.activity-reason {
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: 12px;
  color: #999;
  font-style: italic;
}

/* ── Pagination ──────────────────────────────────────────── */
.activity-pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: var(--space-4xs, 4.95px);
}
.activity-pagination button {
  padding: var(--space-3xs, 7px) var(--space-2xs, 9.9px);
  border: 1px solid var(--border);
  background: #fff;
  border-radius: var(--radius-s, 2px);
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: var(--text-body, #414141);
  cursor: pointer;
  min-width: 36px;
  text-align: center;
}
.activity-pagination button:hover:not(:disabled) {
  border-color: var(--primary);
  color: var(--primary);
}
.activity-pagination button:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
.activity-page-btn.is-active {
  background: var(--primary);
  color: #fff;
  border-color: var(--primary);
}

/* ── Responsive ──────────────────────────────────────────── */
@media (max-width: 760px) {
  .activity-body {
    text-align: left;
  }
  .activity-meta {
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
  }
  .activity-link {
    display: block;
    margin-left: 0;
    margin-top: 4px;
  }
}
</style>
