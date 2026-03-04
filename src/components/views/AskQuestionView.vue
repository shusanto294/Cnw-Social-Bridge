<template>
  <div class="ask-view">
    <div class="ask-card">
      <!-- Header -->
      <div class="ask-header-group">
        <h1 class="ask-heading">Ask a Question</h1>
        <p class="ask-subtitle">Share a question with other RVA social workers. Please avoid identifying client details.</p>
      </div>

      <div v-if="!isLoggedIn" class="login-prompt">
        Please <a href="/wp-login.php">log in</a> to ask a question.
      </div>

      <template v-else>
        <!-- Anonymous toggle -->
        <div class="ask-anon-row">
          <button type="button" class="ask-anon-toggle" :class="{ 'is-active': isAnonymous }" @click="isAnonymous = !isAnonymous">
            <span>Anonymous</span>
            <span class="toggle-track" :class="{ on: isAnonymous }">
              <span class="toggle-thumb"></span>
            </span>
          </button>
        </div>

        <!-- Avatar + Fields -->
        <form class="ask-form" @submit.prevent="submit">
          <div class="ask-input-area">
            <img
              :src="currentUserAvatar"
              class="cnw-social-worker-avatar ask-avatar"
              width="34" height="34"
              alt="You"
            />
            <div class="ask-fields">
              <input
                v-model="title"
                type="text"
                placeholder="Question title"
                class="ask-title-input"
                required
              />
              <textarea
                v-model="content"
                placeholder="Briefly describe what you need help with:"
                class="ask-textarea"
                rows="6"
                required
              ></textarea>
            </div>
          </div>

          <!-- Action buttons row -->
          <div class="ask-actions-row">
            <button type="button" class="ask-gradient-btn" @click="showTagInput = !showTagInput">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
              Add Tags
            </button>
            <div class="ask-category-wrap">
              <button type="button" class="ask-gradient-btn" @click="showCategoryDropdown = !showCategoryDropdown">
                {{ selectedCategory ? selectedCategory.name : 'Category' }}
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
              </button>
              <div v-if="showCategoryDropdown" class="ask-category-dropdown">
                <div
                  v-for="cat in categories"
                  :key="cat.id"
                  class="ask-category-option"
                  :class="{ 'is-selected': selectedCategory && selectedCategory.id === cat.id }"
                  @click="selectCategory(cat)"
                >{{ cat.name }}</div>
                <div v-if="categories.length === 0" class="ask-category-option ask-category-empty">No categories</div>
              </div>
            </div>
          </div>

          <!-- Tag input (toggled) -->
          <div v-if="showTagInput" class="ask-tag-input-area">
            <div class="ask-selected-tags">
              <span
                v-for="tag in selectedTags"
                :key="tag"
                class="ask-tag-chip"
              >
                {{ tag }}
                <button type="button" class="ask-tag-remove" @click="removeTag(tag)">×</button>
              </span>
            </div>
            <input
              v-model="tagInput"
              type="text"
              placeholder="Type a tag and press Enter…"
              class="ask-tag-field"
              @keydown.enter.prevent="addTag"
            />
          </div>

          <!-- Post button -->
          <button
            type="submit"
            class="ask-post-btn"
            :disabled="!canSubmit || submitting"
          >{{ submitting ? 'Posting…' : 'Post' }}</button>

          <p v-if="error" class="ask-error">{{ error }}</p>
        </form>
      </template>
    </div>
  </div>
</template>

<script>
import { createThread, getCategories } from '@/api/index.js';

export default {
  name: 'AskQuestionView',
  data() {
    return {
      title: '',
      content: '',
      tagInput: '',
      selectedTags: [],
      showTagInput: false,
      categories: [],
      selectedCategory: null,
      showCategoryDropdown: false,
      isAnonymous: false,
      submitting: false,
      error: '',
      isLoggedIn: !!(window.cnwData?.currentUser?.id > 0),
    };
  },
  computed: {
    canSubmit() {
      return this.title.trim().length > 0 && this.content.trim().length > 0;
    },
    currentUserAvatar() {
      return window.cnwData?.currentUser?.avatar || 'https://www.gravatar.com/avatar/?d=mp&s=34';
    },
  },
  async mounted() {
    try {
      this.categories = await getCategories() || [];
    } catch (e) { /* silent */ }
  },
  methods: {
    addTag() {
      const tag = this.tagInput.trim();
      if (tag && !this.selectedTags.includes(tag) && this.selectedTags.length < 10) {
        this.selectedTags.push(tag);
      }
      this.tagInput = '';
    },
    selectCategory(cat) {
      this.selectedCategory = cat;
      this.showCategoryDropdown = false;
    },
    removeTag(tag) {
      this.selectedTags = this.selectedTags.filter(t => t !== tag);
    },
    async submit() {
      if (!this.canSubmit) return;
      this.submitting = true;
      this.error = '';
      try {
        const data = await createThread({
          title: this.title,
          content: this.content,
          tags: this.selectedTags,
          category_id: this.selectedCategory?.id,
          anonymous: this.isAnonymous ? 1 : 0,
        });
        if (data.success && data.id) {
          this.$router.push('/thread/' + data.id);
        } else {
          this.error = data.message || 'Failed to post question. Please try again.';
        }
      } catch (e) {
        this.error = 'An error occurred. Please try again.';
      } finally {
        this.submitting = false;
      }
    },
  },
};
</script>

<style>
.ask-view {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.ask-card {
  background: #fff;
  border-radius: var(--radius-m);
  padding: var(--space-m) var(--space-m);
  display: flex;
  flex-direction: column;
  gap: var(--space-s);
}

.ask-header-group {
  display: flex;
  flex-direction: column;
  gap: var(--space-2xs);
}

.ask-heading {
  font-size: 18px;
  font-weight: 600;
  color: var(--text-dark);
  line-height: 24.5px;
}

.ask-subtitle {
  font-size: var(--text-m);
  font-weight: 300;
  color: #000;
  line-height: 1.32;
}

/* Anonymous toggle */
.ask-anon-row {
  display: flex;
  justify-content: flex-end;
}

.ask-anon-toggle {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2xs);
  background: var(--bg);
  border: none;
  border-radius: var(--radius-m);
  padding: var(--space-3xs) var(--space-xs);
  font-size: var(--text-xs);
  font-weight: 600;
  color: #000;
  cursor: pointer;
  transition: background 0.12s;
}
.ask-anon-toggle.is-active {
  color: var(--primary);
}

/* Toggle switch */
.toggle-track {
  display: inline-block;
  width: 32px;
  height: 18px;
  border-radius: 9px;
  background: #ccc;
  position: relative;
  transition: background 0.2s;
}
.toggle-track.on {
  background: var(--primary);
}
.toggle-thumb {
  display: block;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  background: #fff;
  position: absolute;
  top: 2px;
  left: 2px;
  transition: left 0.2s;
}
.toggle-track.on .toggle-thumb {
  left: 16px;
}

/* Form */
.ask-form {
  display: flex;
  flex-direction: column;
  gap: var(--space-xs);
}

/* Avatar + textarea area */
.ask-input-area {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: var(--space-2xs);
}

.ask-avatar {
  flex-shrink: 0;
  border-radius: 50%;
}

.ask-fields {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: var(--space-2xs);
}

.ask-title-input {
  width: 100%;
  padding: var(--space-2xs) var(--space-xs);
  background: var(--bg);
  border: none;
  border-radius: var(--radius-m);
  font-size: var(--text-xs);
  font-weight: 600;
  font-family: 'Poppins', sans-serif;
  color: var(--text-dark);
  line-height: 1.5;
}
.ask-title-input::placeholder {
  color: #999;
  font-weight: 300;
  opacity: 1;
}
.ask-title-input:focus {
  outline: none;
}

.ask-textarea {
  width: 100%;
  min-height: 154px;
  padding: var(--space-s) var(--space-xs);
  background: var(--bg);
  border: none;
  border-radius: var(--radius-m);
  font-size: var(--text-xs);
  font-weight: 300;
  font-family: 'Poppins', sans-serif;
  color: var(--text-dark);
  resize: vertical;
  line-height: 1.5;
}
.ask-textarea::placeholder {
  color: #999;
  font-weight: 300;
  font-size: var(--text-xs);
  font-family: 'Poppins', sans-serif;
  opacity: 1;
}
.ask-textarea:focus {
  outline: none;
}

/* Gradient action buttons */
.ask-actions-row {
  display: flex;
  gap: var(--space-2xs);
}

.ask-gradient-btn {
  display: inline-flex;
  align-items: center;
  gap: var(--space-4xs);
  background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
  color: #fff;
  border: none;
  border-radius: var(--radius-xs);
  padding: var(--space-3xs) var(--space-xs);
  font-size: var(--text-xs);
  font-weight: 300;
  font-family: 'Poppins', sans-serif;
  line-height: 16px;
  cursor: pointer;
  transition: opacity 0.12s;
}
.ask-gradient-btn:hover {
  opacity: 0.9;
}

/* Category dropdown */
.ask-category-wrap {
  position: relative;
}

.ask-category-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  margin-top: 4px;
  min-width: 180px;
  background: #fff;
  border-radius: var(--radius-m);
  box-shadow: 0 4px 12px rgba(0,0,0,0.12);
  z-index: 10;
  overflow: hidden;
}

.ask-category-option {
  padding: var(--space-3xs) var(--space-xs);
  font-size: var(--text-xs);
  font-weight: 300;
  font-family: 'Poppins', sans-serif;
  color: var(--text-dark);
  cursor: pointer;
  transition: background 0.1s;
}
.ask-category-option:hover {
  background: var(--bg);
}
.ask-category-option.is-selected {
  background: var(--primary);
  color: #fff;
}
.ask-category-empty {
  color: #999;
  cursor: default;
}

/* Tag input area */
.ask-tag-input-area {
  display: flex;
  flex-direction: column;
  gap: var(--space-3xs);
  background: var(--bg);
  border-radius: var(--radius);
  padding: var(--space-2xs) var(--space-s);
}

.ask-selected-tags {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-4xs);
}

.ask-tag-chip {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: var(--primary);
  color: #fff;
  font-size: 12px;
  font-weight: 500;
  padding: 2px 10px;
  border-radius: var(--radius-pill);
}

.ask-tag-remove {
  background: none;
  border: none;
  color: rgba(255,255,255,0.8);
  font-size: 14px;
  line-height: 1;
  padding: 0;
  cursor: pointer;
}
.ask-tag-remove:hover {
  color: #fff;
}

.ask-tag-field {
  border: none;
  background: transparent;
  font-size: var(--text-xs);
  font-weight: 300;
  font-family: 'Poppins', sans-serif;
  color: var(--text-dark);
  padding: var(--space-4xs) 0;
}
.ask-tag-field:focus {
  outline: none;
}

/* Post button — full width teal */
.ask-post-btn {
  width: 100%;
  padding: var(--space-2xs) var(--space-s);
  background: var(--primary);
  color: #fff;
  border: none;
  border-radius: var(--radius-xs);
  font-size: var(--text-xs);
  font-weight: 600;
  font-family: 'Poppins', sans-serif;
  line-height: 18.5px;
  text-align: center;
  cursor: pointer;
  transition: background 0.12s;
}
.ask-post-btn:hover {
  background: var(--secondary);
}
.ask-post-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Error */
.ask-error {
  color: var(--red);
  font-size: 13px;
  padding: 10px 14px;
  background: var(--red-light);
  border-radius: var(--radius-sm);
}

/* Login prompt */
.ask-view .login-prompt {
  text-align: center;
  padding: 40px;
  color: var(--text-light);
  font-size: var(--text-xs);
  font-weight: 300;
}
.ask-view .login-prompt a {
  color: var(--primary);
}
</style>
