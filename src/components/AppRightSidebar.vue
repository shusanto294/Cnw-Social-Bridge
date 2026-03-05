<template>
  <aside class="cnw-social-worker-right-sidebar">
    <!-- Following Tags -->
    <div class="rsidebar-card">
      <div class="rsidebar-card-header">
        <h3>Following Tags</h3>
        <button class="edit-tags-btn" @click="$router.push('/tags')">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
            <g clip-path="url(#clip0_20234_1487)">
              <path d="M11.0833 7.02705C10.7607 7.02705 10.5 7.28842 10.5 7.61035V12.2771C10.5 12.5984 10.2386 12.8604 9.9167 12.8604H1.75C1.42796 12.8604 1.1667 12.5984 1.1667 12.2771V4.11035C1.1667 3.78896 1.42796 3.52705 1.75 3.52705H6.4167C6.73927 3.52705 7 3.26569 7 2.94376C7 2.62172 6.73927 2.36035 6.4167 2.36035H1.75C0.785172 2.36035 0 3.14552 0 4.11035V12.2771C0 13.2419 0.785172 14.0271 1.75 14.0271H9.9167C10.8815 14.0271 11.6667 13.2419 11.6667 12.2771V7.61035C11.6667 7.28778 11.4059 7.02705 11.0833 7.02705Z" fill="white"/>
              <path d="M5.46916 6.4957C5.42836 6.5365 5.40091 6.58841 5.38927 6.64438L4.97687 8.70712C4.95764 8.80272 4.98798 8.90131 5.05676 8.97073C5.1122 9.02617 5.18686 9.05586 5.26334 9.05586C5.28192 9.05586 5.30126 9.05415 5.32048 9.05009L7.38259 8.6377C7.43973 8.62595 7.49164 8.5986 7.53191 8.55769L12.1472 3.94237L10.0851 1.88037L5.46916 6.4957Z" fill="white"/>
              <path d="M13.573 0.453922C13.0043 -0.114849 12.0791 -0.114849 11.5109 0.453922L10.7036 1.2612L12.7657 3.32331L13.573 2.51592C13.8484 2.2412 14 1.87484 14 1.48519C14 1.09554 13.8484 0.729176 13.573 0.453922Z" fill="white"/>
            </g>
            <defs>
              <clipPath id="clip0_20234_1487">
                <rect width="14" height="14" fill="white"/>
              </clipPath>
            </defs>
          </svg>
          Edit
        </button>
      </div>
      <div class="tags-list">
        <span
          v-for="tag in tags"
          :key="tag.id"
          class="cnw-social-worker-tag-badge cnw-social-worker-tag-outline tag-item"
        >{{ tag.name }}</span>
        <p v-if="tags.length === 0" class="tags-empty">You are not following any tags.</p>
      </div>
    </div>

    <!-- Hot Questions -->
    <div class="rsidebar-card">
      <div class="hot-questions-header">
        <h3>Hot Questions</h3>
      </div>
      <div class="hot-questions-list">
        <div
          v-for="q in hotQuestions"
          :key="q.id"
          class="hot-question-item"
          @click="$router.push('/thread/' + q.id)"
        >
          <h4 class="hq-title">{{ q.title }}</h4>
          <p class="hq-excerpt">{{ truncate(q.content, 90) }}</p>
          <div class="hq-tags">
            <span class="qcard-tag">Emergency Housing</span>
            <span class="qcard-tag">Crisis Intervention</span>
          </div>
          <!-- Helpful | Views -->
          <div class="hq-stats-row">
            <span class="hq-stat-group">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="var(--red)" stroke="none"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
              <span>{{ q.likes || 17 }}</span>
              <span>Helpful</span>
            </span>
            <span class="hq-divider"></span>
            <span class="hq-stat-group">
              <span class="hq-views-label">Views</span>
              <span>{{ formatNum(q.views) || '3,402' }}</span>
            </span>
          </div>
          <!-- Discussion (own line) -->
          <div class="hq-meta-row">
            <svg width="14" height="14" viewBox="0 0 14 14"><circle cx="7" cy="7" r="7" fill="#F5A623"/></svg>
            <span>Discussion</span>
          </div>
          <!-- Replies (own line) -->
          <div class="hq-meta-row">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
            <span>{{ q.reply_count || 13 }}</span>
            <span>Replies</span>
          </div>
        </div>
        <p v-if="hotQuestions.length === 0" class="cnw-social-worker-empty" style="padding:20px">No hot questions yet.</p>
      </div>
    </div>
  </aside>
</template>

<script>
import { getFollowedTags, getHotQuestions } from '@/api/index.js';

export default {
  name: 'AppRightSidebar',
  data() {
    return {
      tags: [],
      hotQuestions: [],
    };
  },
  async mounted() {
    const isLoggedIn = !!(window.cnwData?.currentUser?.id > 0);
    try {
      const [tags, hq] = await Promise.all([
        isLoggedIn ? getFollowedTags() : Promise.resolve([]),
        getHotQuestions(),
      ]);
      this.tags = tags || [];
      this.hotQuestions = hq || [];
    } catch (e) { /* silent */ }
  },
  methods: {
    truncate(str, len) {
      if (!str) return '';
      return str.length > len ? str.slice(0, len) + '…' : str;
    },
    formatNum(n) {
      if (!n) return '';
      return Number(n).toLocaleString();
    },
  },
};
</script>

<style>
.cnw-social-worker-right-sidebar {
  width: 291px;
  flex-shrink: 0;
  border-radius: var(--radius);
  display: flex;
  flex-direction: column;
  gap: 16px;
  position: sticky;
  top: 20px;
  height: fit-content;
}

.rsidebar-card {
  background: #fff;
  border-radius: var(--radius);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
}

.rsidebar-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 16px;
  background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
  border-bottom: 1px solid var(--border);
}
.rsidebar-card-header h3 {
  font-size: 14px;
  font-weight: 700;
  color: #fff;
}

.edit-tags-btn {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: transparent;
  color: var(--white);
  font-size: 14px;
  border-radius: var(--radius-sm);
}

.tags-list {
    display: flex;
    flex-direction: row;
    gap: 8px;
    padding: 14px 16px;
    flex-wrap: wrap;
    justify-content: flex-start;
}

.tag-item {
  cursor: pointer;
  font-size: 12.5px;
  padding: 5px 12px;
  border-radius: var(--radius-pill);
  transition: border-color 0.12s, color 0.12s;
  width: fit-content;
}
.tag-item:hover {
  border-color: var(--teal);
  color: var(--teal-dark);
}

.tags-empty {
  font-size: var(--text-xs);
  font-weight: 300;
  color: #999;
  line-height: 16px;
  width: 100%;
}

/* Hot Questions */
.hot-questions-header {
  background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
  padding: 12px 16px;
}
.hot-questions-header h3 {
  color: #fff;
  font-size: 14px;
  font-weight: 700;
}

.hot-questions-list {
  display: flex;
  flex-direction: column;
}

.hot-question-item {
  display: flex;
  flex-direction: column;
  gap: var(--space-xs);
  padding: var(--space-s) var(--space-s);
  border-bottom: 1px solid var(--border);
  cursor: pointer;
  transition: background 0.12s;
}
.hot-question-item:last-child {
  border-bottom: none;
}
.hot-question-item:hover {
  background: var(--bg);
}

.hq-title {
  font-size: var(--text-xs);
  font-weight: 600;
  color: #000;
  line-height: 18.5px;
}
.hq-excerpt {
  font-size: var(--text-xs);
  font-weight: 300;
  color: #000;
  line-height: 16px;
}
.hq-tags {
  display: flex;
  flex-direction: column;
  gap: var(--space-4xs);
}
.hq-tags .qcard-tag {
  align-self: flex-start;
}

/* Helpful | Views row */
.hq-stats-row {
  display: flex;
  align-items: center;
  gap: var(--space-3xs);
}
.hq-stat-group {
  display: inline-flex;
  align-items: center;
  gap: var(--space-4xs);
  font-size: var(--text-xs);
  font-weight: 300;
  color: var(--text-body);
  line-height: 16px;
  white-space: nowrap;
}
.hq-views-label {
  color: #999;
}
.hq-divider {
  width: 2px;
  align-self: stretch;
  background: var(--primary);
  flex-shrink: 0;
}

/* Discussion / Replies rows — each on own line */
.hq-meta-row {
  display: flex;
  align-items: center;
  gap: var(--space-4xs);
  font-size: var(--text-xs);
  font-weight: 300;
  color: var(--text-body);
  line-height: 16px;
  white-space: nowrap;
}
.hq-meta-row svg {
  flex-shrink: 0;
}
</style>
