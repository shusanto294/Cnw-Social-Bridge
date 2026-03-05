<template>
  <div class="question-list-view">
    <!-- Top bar -->
    <div class="list-topbar">
      <h1 class="list-heading">All Questions</h1>
      <div class="filter-tabs">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          class="filter-tab"
          :class="{ 'is-active': activeFilter === tab.id }"
          @click="setFilter(tab.id)"
        >{{ tab.label }}</button>
        <div class="more-dropdown-wrap">
          <button class="filter-tab more-btn" :class="{ 'is-active': moreFilters.some(f => f.id === activeFilter) }" @click.stop="moreOpen = !moreOpen">
            {{ moreLabel }}
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <div v-if="moreOpen" class="more-dropdown">
            <button
              v-for="opt in moreFilters"
              :key="opt.id"
              class="more-dropdown-item"
              :class="{ 'is-active': activeFilter === opt.id }"
              @click="setFilter(opt.id); moreOpen = false"
            >{{ opt.label }}</button>
          </div>
        </div>
        <button class="filter-dots-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 8 8" fill="none">
            <g clip-path="url(#clip0_20234_601)">
              <path d="M3.72969 6.125H4.26875C4.6375 6.125 4.93594 6.42344 4.93594 6.79219V7.33125C4.93594 7.7 4.6375 7.99844 4.26875 7.99844H3.72969C3.36094 7.99844 3.0625 7.7 3.0625 7.33125V6.79219C3.0625 6.425 3.36094 6.125 3.72969 6.125Z" fill="white"/>
              <path d="M3.72969 3.0625H4.26875C4.6375 3.0625 4.93594 3.36094 4.93594 3.72969V4.26875C4.93594 4.6375 4.6375 4.93594 4.26875 4.93594H3.72969C3.36094 4.93594 3.0625 4.6375 3.0625 4.26875V3.72969C3.0625 3.36094 3.36094 3.0625 3.72969 3.0625Z" fill="white"/>
              <path d="M3.73008 0H4.26915C4.63946 0 4.9379 0.298438 4.9379 0.667188V1.20625C4.9379 1.575 4.63946 1.87344 4.27071 1.87344H3.73165C3.3629 1.87344 3.06446 1.575 3.06446 1.20625V0.667188C3.0629 0.298438 3.36133 0 3.73008 0Z" fill="white"/>
            </g>
            <defs>
              <clipPath id="clip0_20234_601">
                <rect width="8" height="8" fill="white"/>
              </clipPath>
            </defs>
          </svg>
        </button>
      </div>
    </div>

    <!-- Search -->
    <div class="list-search">
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Search"
        class="search-input"
        @input="onSearch"
      />
      <svg class="search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    </div>

    <!-- New Question link -->
    <div class="new-question-row">
      <button class="new-question-btn" @click="$router.push('/ask')">New Question</button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="cnw-social-worker-loading">Loading questions…</div>

    <!-- Empty -->
    <div v-else-if="threads.length === 0" class="cnw-social-worker-empty">No questions found.</div>

    <!-- Question cards -->
    <div v-else class="questions-list">
      <QuestionCard
        v-for="thread in threads"
        :key="thread.id"
        :thread="thread"
      />
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="pagination">
      <button
        class="page-btn"
        :disabled="currentPage <= 1"
        @click="goToPage(currentPage - 1)"
      >‹</button>
      <button
        v-for="p in visiblePages"
        :key="p"
        class="page-btn"
        :class="{ 'is-active': p === currentPage }"
        @click="goToPage(p)"
      >{{ p }}</button>
      <button
        class="page-btn"
        :disabled="currentPage >= totalPages"
        @click="goToPage(currentPage + 1)"
      >›</button>
    </div>
  </div>
</template>

<script>
import QuestionCard from '@/components/shared/QuestionCard.vue';
import { getThreads } from '@/api/index.js';

export default {
  name: 'QuestionListView',
  components: { QuestionCard },
  data() {
    return {
      tabs: [
        { id: 'newest', label: 'Newest' },
        { id: 'active', label: 'Active' },
        { id: 'unanswered', label: 'Unanswered' },
        { id: 'recommended', label: 'Recommended' },
      ],
      moreFilters: [
        { id: 'my_questions', label: 'Mine' },
        { id: 'my_answered', label: 'Mine AW' },
        { id: 'my_unanswered', label: 'Mine UW' },
      ],
      moreOpen: false,
      activeFilter: 'newest',
      searchQuery: '',
      threads: [],
      loading: true,
      currentPage: 1,
      totalPages: 1,
      searchTimer: null,
    };
  },
  computed: {
    moreLabel() {
      const active = this.moreFilters.find(f => f.id === this.activeFilter);
      return active ? active.label : 'More';
    },
    visiblePages() {
      const pages = [];
      const start = Math.max(1, this.currentPage - 2);
      const end = Math.min(this.totalPages, this.currentPage + 2);
      for (let i = start; i <= end; i++) pages.push(i);
      return pages;
    },
  },
  mounted() {
    this.fetchThreads();
    this._closeMore = () => { this.moreOpen = false; };
    document.addEventListener('click', this._closeMore);
  },
  beforeUnmount() {
    document.removeEventListener('click', this._closeMore);
  },
  methods: {
    async fetchThreads() {
      this.loading = true;
      try {
        const data = await getThreads({
          page: this.currentPage,
          filter: this.activeFilter,
          search: this.searchQuery,
        });
        this.threads = data.threads || [];
        this.totalPages = data.pages || 1;
      } catch (e) {
        console.error(e);
      } finally {
        this.loading = false;
      }
    },
    setFilter(id) {
      this.activeFilter = id;
      this.currentPage = 1;
      this.fetchThreads();
    },
    onSearch() {
      clearTimeout(this.searchTimer);
      this.searchTimer = setTimeout(() => {
        this.currentPage = 1;
        this.fetchThreads();
      }, 350);
    },
    goToPage(p) {
      this.currentPage = p;
      this.fetchThreads();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
  },
};
</script>

<style>
.question-list-view {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

/* Top bar */
.list-topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 10px;
}

.list-heading {
  font-size: 17px;
  font-weight: 700;
  color: var(--text-dark);
}

.filter-tabs {
  display: flex;
  gap: 5px;
  background: #fff;
}

.filter-tab {
  padding: 5px 10px;
  font-size: 14px;
  font-weight: 300;
  color: var(--text-body);
  background: none;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  line-height: 1;
  border: 1px solid var(--tertiary);
}
.filter-tab:hover {
  background: var(--bg);
}
.filter-tab.is-active {
  background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
  color: #fff;
  border: none;
}

/* More dropdown */
.more-dropdown-wrap {
  position: relative;
}
.more-btn {
  padding: 7px 10px;
}
.more-dropdown {
  position: absolute;
  top: calc(100% + 4px);
  left: 0;
  min-width: 170px;
  background: #fff;
  border-radius: var(--radius-sm);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.14);
  z-index: 50;
  display: flex;
  flex-direction: column;
  padding: 4px 0;
  animation: dropFade 0.15s ease;
}
@keyframes dropFade {
  from { opacity: 0; transform: translateY(-4px); }
  to   { opacity: 1; transform: translateY(0); }
}
.more-dropdown-item {
  padding: 8px 14px;
  font-size: 13px;
  font-weight: 400;
  font-family: 'Poppins', sans-serif;
  color: var(--text-body);
  background: none;
  border: none;
  text-align: left;
  cursor: pointer;
  transition: background 0.1s, color 0.1s;
}
.more-dropdown-item:hover {
  background: var(--bg);
  color: var(--primary);
}
.more-dropdown-item.is-active {
  background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
  color: #fff;
}

.filter-dots-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 7px 10px;
  background: var(--tertiary);
  border: 1px solid var(--tertiary);
  cursor: pointer;
  flex-shrink: 0;
}
.filter-dots-btn:hover {
  opacity: 0.85;
}

/* Search */
.list-search {
  display: flex;
  align-items: center;
  gap: var(--space-m);
  max-width: 430px;
  width: 100%;
  margin: var(--space-m) auto;
  background: var(--light, #fff);
  border: none;
  border-radius: var(--radius-l);
  padding: var(--space-4xs) var(--space-s);
  box-shadow: 0 0 var(--radius-s) 0 var(--dark-20, rgba(0, 0, 0, 0.1));
}
.search-input {
  flex: 1;
  padding: 0;
  border: none;
  font-size: var(--text-xs);
  font-weight: 300;
  font-family: 'Poppins', sans-serif;
  color: var(--text-body);
  background: transparent;
  line-height: 16px;
}
.search-input::placeholder {
  color: var(--text-body, #999);
  font-weight: 300;
  font-size: var(--text-xs);
  font-family: 'Poppins', sans-serif;
  opacity: 1;
}
.search-input:focus {
  outline: none;
}
.search-icon {
  flex-shrink: 0;
  color: var(--text-body, #999);
}

/* New Question */
.new-question-row {
  text-align: center;
}
.new-question-btn {
  background: none;
  border: none;
  color: var(--teal);
  font-size: 14px;
  font-weight: 600;
  padding: 4px 0;
  transition: color 0.12s;
}
.new-question-btn:hover {
  color: var(--teal-dark);
  text-decoration: underline;
}

/* Questions list */
.questions-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  gap: 4px;
  margin-top: 8px;
}
.page-btn {
  padding: 7px 13px;
  border: 1px solid var(--border);
  background: #fff;
  border-radius: var(--radius-sm);
  font-size: 13px;
  color: var(--text-med);
  transition: background 0.12s, color 0.12s;
}
.page-btn:hover:not(:disabled) {
  background: var(--teal-light);
  color: var(--teal-dark);
}
.page-btn.is-active {
  background: var(--teal);
  color: #fff;
  border-color: var(--teal);
}
.page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
</style>
