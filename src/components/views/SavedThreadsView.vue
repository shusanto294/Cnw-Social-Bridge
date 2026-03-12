<template>
  <div class="cnw-social-worker-saved-view">
    <h1 class="cnw-social-worker-view-heading">Saved Threads</h1>

    <template v-if="loading">
      <div v-for="n in 3" :key="n" class="cnw-skeleton-card" style="gap:12px;margin-bottom:12px">
        <div class="cnw-skeleton-row">
          <div class="cnw-skeleton cnw-skeleton-circle" style="width:34px;height:34px"></div>
          <div style="flex:1;display:flex;flex-direction:column;gap:6px">
            <div class="cnw-skeleton cnw-skeleton-line" style="width:30%"></div>
            <div class="cnw-skeleton cnw-skeleton-line-sm" style="width:20%"></div>
          </div>
        </div>
        <div class="cnw-skeleton cnw-skeleton-line-xl" style="width:70%"></div>
        <div class="cnw-skeleton cnw-skeleton-line" style="width:90%"></div>
        <div class="cnw-skeleton cnw-skeleton-line-sm" style="width:40%"></div>
      </div>
    </template>

    <template v-else-if="threads.length">
      <QuestionCard
        v-for="thread in threads"
        :key="thread.id"
        :thread="thread"
        @deleted="onThreadDeleted"
      />
    </template>

    <div v-else class="cnw-social-worker-placeholder-card">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--border)" stroke-width="1.5" aria-hidden="true"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
      <p>Your saved threads will appear here!</p>
    </div>
  </div>
</template>

<script>
import QuestionCard from '@/components/shared/QuestionCard.vue';
import { getSavedThreads } from '@/api/index.js';

export default {
  name: 'SavedThreadsView',
  components: { QuestionCard },
  data() {
    return {
      threads: [],
      loading: true,
    };
  },
  methods: {
    onThreadDeleted(id) {
      this.threads = this.threads.filter(t => t.id !== id);
    },
  },
  async mounted() {
    try {
      const data = await getSavedThreads();
      this.threads = data.threads || [];
    } catch (e) {
      /* silent */
    } finally {
      this.loading = false;
    }
  },
};
</script>

<style>
.cnw-social-worker-saved-view { display: flex; flex-direction: column; gap: 16px; }
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
