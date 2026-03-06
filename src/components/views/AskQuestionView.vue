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

            <!-- Category picker -->
            <div class="ask-category-wrap" ref="categoryWrap">
              <button type="button" class="ask-gradient-btn" @click="toggleCategoryDropdown">
                {{ selectedCategory ? selectedCategory.name : 'Category' }}
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
              </button>
              <div v-if="showCategoryDropdown" class="ask-category-dropdown">
                <input
                  v-model="categorySearch"
                  type="text"
                  placeholder="Search categories…"
                  class="ask-dropdown-search"
                  ref="catSearchInput"
                  @keydown.escape="closeCategoryDropdown"
                />
                <div
                  v-for="cat in filteredCategories"
                  :key="cat.id"
                  class="ask-category-option"
                  :class="{ 'is-selected': selectedCategory && selectedCategory.id === cat.id }"
                  @mousedown.prevent="selectCategory(cat)"
                >{{ cat.name }}</div>

                <!-- Create new category option -->
                <div
                  v-if="categorySearch.trim() && !exactCategoryMatch"
                  class="ask-category-option ask-create-option"
                  @mousedown.prevent="addNewCategory(categorySearch.trim())"
                >
                  <template v-if="creatingCategory">Creating…</template>
                  <template v-else>+ Create "{{ categorySearch.trim() }}"</template>
                </div>

                <div v-if="filteredCategories.length === 0 && !categorySearch.trim()" class="ask-category-option ask-category-empty">No categories yet</div>

                <p v-if="categoryError" class="ask-inline-error" style="padding: 4px 12px; margin: 0;">{{ categoryError }}</p>
              </div>
            </div>
          </div>

          <!-- Tag panel (toggled) -->
          <div v-if="showTagInput" class="ask-tag-panel">

            <!-- Selected tag chips -->
            <div v-if="selectedTagObjects.length" class="ask-selected-tags">
              <span
                v-for="tag in selectedTagObjects"
                :key="tag.name"
                class="ask-tag-chip"
              >
                {{ tag.name }}
                <button type="button" class="ask-tag-remove" @click="removeTag(tag)">×</button>
              </span>
            </div>

            <!-- Tag input + dropdown -->
            <div class="ask-tag-search-wrap" ref="tagSearchWrap">
              <input
                v-model="tagSearch"
                type="text"
                :placeholder="selectedTagObjects.length >= 10 ? 'Max 10 tags reached' : 'Type a tag name…'"
                class="ask-tag-field"
                :disabled="selectedTagObjects.length >= 10"
                @focus="showTagDropdown = true"
                @blur="onTagSearchBlur"
                @keydown.enter.prevent="onTagSearchEnter"
                @keydown.escape="showTagDropdown = false"
              />
              <div v-if="showTagDropdown && tagSearch.trim() && filteredTagSuggestions.length > 0 && selectedTagObjects.length < 10" class="ask-tag-dropdown">
                <div
                  v-for="tag in filteredTagSuggestions"
                  :key="tag.id"
                  class="ask-tag-dropdown-item"
                  @mousedown.prevent="selectExistingTag(tag)"
                >{{ tag.name }}</div>
              </div>
            </div>
          </div>
          <p v-if="tagError" class="ask-tag-error">{{ tagError }}</p>

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

    <!-- Tag Detail Modal (shown after creating a new tag) -->
    <div v-if="showTagModal" class="td-modal-overlay" @click.self="closeTagModal">
      <div class="td-modal">
        <div class="td-modal-header">
          <h3>Tag Details</h3>
          <button class="td-modal-close" @click="closeTagModal">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div class="td-modal-body" style="display:flex;flex-direction:column;gap:14px;">
          <div>
            <label class="td-modal-label">Tag Name</label>
            <input v-model="tagModalForm.name" class="td-modal-input" type="text" />
          </div>
          <div>
            <label class="td-modal-label">Slug</label>
            <input v-model="tagModalForm.slug" class="td-modal-input" type="text" placeholder="auto-generated-from-name" />
          </div>
          <div>
            <label class="td-modal-label">Description</label>
            <textarea v-model="tagModalForm.description" class="td-modal-textarea" rows="4" placeholder="Describe what this tag is about…"></textarea>
          </div>
          <p v-if="tagModalError" class="ask-inline-error">{{ tagModalError }}</p>
        </div>
        <div class="td-modal-footer">
          <button class="td-modal-cancel" @click="closeTagModal">Skip</button>
          <button class="td-modal-save" :disabled="!tagModalForm.name.trim() || savingTagModal" @click="saveTagModal">
            {{ savingTagModal ? 'Updating…' : 'Update' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { createThread, getCategories, getTags, createTag, updateTag, createCategory } from '@/api/index.js';

export default {
  name: 'AskQuestionView',
  data() {
    return {
      title: '',
      content: '',
      isAnonymous: !!(window.cnwData?.currentUser?.anonymous),
      submitting: false,
      error: '',
      isLoggedIn: !!(window.cnwData?.currentUser?.id > 0),

      // Categories
      categories: [],
      selectedCategory: null,
      showCategoryDropdown: false,
      categorySearch: '',
      creatingCategory: false,
      categoryError: '',

      // Tags
      allTags: [],
      selectedTagObjects: [],
      showTagInput: false,
      tagSearch: '',
      showTagDropdown: false,
      tagError: '',

      // Tag detail modal
      showTagModal: false,
      tagModalForm: { id: null, name: '', slug: '', description: '' },
      savingTagModal: false,
      tagModalError: '',
    };
  },
  computed: {
    canSubmit() {
      return this.title.trim().length > 0 && this.content.trim().length > 0;
    },
    currentUserAvatar() {
      return window.cnwData?.currentUser?.avatar || 'https://www.gravatar.com/avatar/?d=mp&s=34';
    },
    filteredCategories() {
      const q = this.categorySearch.trim().toLowerCase();
      if (!q) return this.categories;
      return this.categories.filter(c => c.name.toLowerCase().includes(q));
    },
    exactCategoryMatch() {
      const q = this.categorySearch.trim().toLowerCase();
      return q && this.categories.some(c => c.name.toLowerCase() === q);
    },
    filteredTagSuggestions() {
      const selectedNames = new Set(this.selectedTagObjects.map(t => t.name.toLowerCase()));
      const q = this.tagSearch.trim().toLowerCase();
      if (!q) return [];
      return this.allTags.filter(t => {
        if (selectedNames.has(t.name.toLowerCase())) return false;
        return t.name.toLowerCase().includes(q);
      }).slice(0, 10);
    },
    exactTagMatch() {
      const q = this.tagSearch.trim().toLowerCase();
      return q && this.allTags.some(t => t.name.toLowerCase() === q);
    },
  },
  async mounted() {
    [this.categories, this.allTags] = await Promise.all([
      getCategories().catch(() => []),
      getTags().catch(() => []),
    ]);

    // Close category dropdown on outside click
    this._closeCat = (e) => {
      if (this.$refs.categoryWrap && !this.$refs.categoryWrap.contains(e.target)) {
        this.closeCategoryDropdown();
      }
    };
    document.addEventListener('click', this._closeCat);
  },
  beforeUnmount() {
    document.removeEventListener('click', this._closeCat);
  },
  methods: {
    // ── Category ────────────────────────────────────────────────
    toggleCategoryDropdown() {
      this.showCategoryDropdown = !this.showCategoryDropdown;
      if (this.showCategoryDropdown) {
        this.$nextTick(() => this.$refs.catSearchInput?.focus());
      } else {
        this.closeCategoryDropdown();
      }
    },
    closeCategoryDropdown() {
      this.showCategoryDropdown = false;
      this.categorySearch = '';
      this.showNewCategoryForm = false;
      this.categoryError = '';
    },
    selectCategory(cat) {
      this.selectedCategory = cat;
      this.closeCategoryDropdown();
    },
    async addNewCategory(name) {
      if (!name || this.creatingCategory) return;
      this.creatingCategory = true;
      this.categoryError = '';
      try {
        const res = await createCategory({ name });
        if (res.success && res.id) {
          const cat = { id: res.id, name };
          this.categories.push(cat);
          this.selectCategory(cat);
        } else {
          this.categoryError = res.message || 'Failed to create category.';
        }
      } catch {
        this.categoryError = 'Error creating category.';
      } finally {
        this.creatingCategory = false;
      }
    },

    // ── Tags ────────────────────────────────────────────────────
    selectExistingTag(tag) {
      if (this.selectedTagObjects.length < 10) {
        this.selectedTagObjects.push(tag);
      }
      this.tagSearch = '';
    },
    removeTag(tag) {
      this.selectedTagObjects = this.selectedTagObjects.filter(t => t.name !== tag.name);
    },
    async createAndAddTag(name) {
      name = name.trim();
      if (!name || this.selectedTagObjects.length >= 10) return;
      if (this.selectedTagObjects.some(t => t.name.toLowerCase() === name.toLowerCase())) {
        this.tagSearch = '';
        return;
      }
      this.tagError = '';
      // Add chip immediately
      const tag = { id: null, name };
      this.selectedTagObjects.push(tag);
      this.tagSearch = '';
      // Save to DB
      try {
        const res = await createTag({ name });
        if (res && res.success && res.id) {
          tag.id = res.id;
          if (!res.existing) {
            this.allTags.push({ id: res.id, name });
            // Show modal for newly created tags
            this.openTagModal(res.id, name);
          }
        } else {
          this.tagError = res?.message || 'Could not save tag.';
        }
      } catch (e) {
        this.tagError = 'Network error: ' + (e?.message || 'unknown');
      }
    },
    onTagSearchBlur() {
      setTimeout(() => { this.showTagDropdown = false; }, 150);
    },
    onTagSearchEnter() {
      const q = this.tagSearch.trim();
      if (!q) return;
      if (this.exactTagMatch) {
        const tag = this.allTags.find(t => t.name.toLowerCase() === q.toLowerCase());
        if (tag && !this.selectedTagObjects.some(s => s.name.toLowerCase() === tag.name.toLowerCase())) {
          this.selectExistingTag(tag);
        } else {
          this.tagSearch = '';
        }
      } else {
        this.createAndAddTag(q);
      }
    },

    // ── Tag Detail Modal ──────────────────────────────────────────
    openTagModal(id, name) {
      this.tagModalForm = {
        id,
        name,
        slug: name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, ''),
        description: '',
      };
      this.tagModalError = '';
      this.showTagModal = true;
    },
    closeTagModal() {
      this.showTagModal = false;
    },
    async saveTagModal() {
      if (!this.tagModalForm.name.trim()) return;
      this.savingTagModal = true;
      this.tagModalError = '';
      try {
        const res = await updateTag({
          id: this.tagModalForm.id,
          name: this.tagModalForm.name.trim(),
          slug: this.tagModalForm.slug.trim(),
          description: this.tagModalForm.description.trim(),
        });
        if (res.success) {
          // Update the tag name in selected chips and allTags if changed
          const newName = this.tagModalForm.name.trim();
          const tagInAll = this.allTags.find(t => t.id === this.tagModalForm.id);
          if (tagInAll) tagInAll.name = newName;
          const tagInSelected = this.selectedTagObjects.find(t => t.id === this.tagModalForm.id);
          if (tagInSelected) tagInSelected.name = newName;
          this.showTagModal = false;
        } else {
          this.tagModalError = res.message || 'Failed to update tag.';
        }
      } catch {
        this.tagModalError = 'Error updating tag.';
      } finally {
        this.savingTagModal = false;
      }
    },

    // ── Submit ──────────────────────────────────────────────────
    async submit() {
      if (!this.canSubmit) return;
      this.submitting = true;
      this.error = '';
      try {
        const data = await createThread({
          title: this.title,
          content: this.content,
          tags: this.selectedTagObjects.map(t => t.name),
          category_id: this.selectedCategory?.id,
          anonymous: this.isAnonymous ? 1 : 0,
        });
        if (data.success && data.id) {
          this.$router.push('/thread/' + data.id);
        } else {
          this.error = data.message || 'Failed to post question. Please try again.';
        }
      } catch {
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

.ask-input-area img{
  width: 34px;
  height: 34px;
  border-radius: 50%;
  object-fit: cover;
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
  min-width: 200px;
  background: #fff;
  border-radius: var(--radius-m);
  box-shadow: 0 4px 12px rgba(0,0,0,0.12);
  z-index: 20;
  overflow: hidden;
}

.ask-dropdown-search {
  display: block;
  width: 100%;
  padding: var(--space-3xs) var(--space-xs);
  border: none;
  border-bottom: 1px solid var(--border, #eee);
  background: transparent;
  font-size: var(--text-xs);
  font-family: 'Poppins', sans-serif;
  color: var(--text-dark);
  box-sizing: border-box;
}
.ask-dropdown-search:focus {
  outline: none;
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

/* Create new option */
.ask-create-option {
  color: var(--primary) !important;
  font-weight: 500 !important;
}
.ask-create-option:hover {
  background: var(--bg) !important;
}

/* Inline new-item form (shared by tag + category) */
.ask-new-item-form {
  padding: var(--space-2xs) var(--space-xs);
  border-top: 1px solid var(--border, #eee);
  display: flex;
  flex-direction: column;
  gap: var(--space-3xs);
}

.ask-new-item-label {
  font-size: 10px;
  font-weight: 600;
  color: var(--text-light, #888);
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin: 0;
}

.ask-inline-input {
  width: 100%;
  padding: var(--space-3xs) var(--space-2xs);
  border: 1px solid var(--border, #ddd);
  border-radius: var(--radius-xs);
  font-size: var(--text-xs);
  font-family: 'Poppins', sans-serif;
  color: var(--text-dark);
  background: var(--bg);
  box-sizing: border-box;
}
.ask-inline-input:focus {
  outline: none;
  border-color: var(--primary);
}

.ask-inline-textarea {
  width: 100%;
  padding: var(--space-3xs) var(--space-2xs);
  border: 1px solid var(--border, #ddd);
  border-radius: var(--radius-xs);
  font-size: var(--text-xs);
  font-family: 'Poppins', sans-serif;
  color: var(--text-dark);
  background: var(--bg);
  resize: vertical;
  box-sizing: border-box;
}
.ask-inline-textarea:focus {
  outline: none;
  border-color: var(--primary);
}

.ask-inline-actions {
  display: flex;
  gap: var(--space-3xs);
}

.ask-inline-btn {
  padding: 4px var(--space-xs);
  border: 1px solid var(--border, #ddd);
  border-radius: var(--radius-xs);
  background: #fff;
  font-size: var(--text-xs);
  font-family: 'Poppins', sans-serif;
  color: var(--text-dark);
  cursor: pointer;
  transition: background 0.1s;
}
.ask-inline-btn:hover {
  background: var(--bg);
}
.ask-inline-btn--primary {
  background: var(--primary);
  color: #fff;
  border-color: var(--primary);
}
.ask-inline-btn--primary:hover {
  background: var(--secondary);
  border-color: var(--secondary);
}
.ask-inline-btn--primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.ask-inline-error {
  font-size: 11px;
  color: var(--red, #e55);
  margin: 0;
}

/* Tag panel */
.ask-tag-panel {
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

/* Tag search */
.ask-tag-search-wrap {
  position: relative;
}

.ask-tag-field {
  width: 100%;
  border: none;
  background: transparent;
  font-size: var(--text-xs);
  font-weight: 300;
  font-family: 'Poppins', sans-serif;
  color: var(--text-dark);
  padding: var(--space-4xs) 0;
  box-sizing: border-box;
}
.ask-tag-field:focus {
  outline: none;
}
.ask-tag-field:disabled {
  color: #aaa;
  cursor: not-allowed;
}

.ask-tag-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  margin-top: 4px;
  background: #fff;
  border-radius: var(--radius-m);
  box-shadow: 0 4px 14px rgba(0,0,0,0.13);
  z-index: 20;
  overflow: hidden;
  max-height: 210px;
  overflow-y: auto;
}

.ask-tag-dropdown-item {
  padding: var(--space-3xs) var(--space-xs);
  font-size: var(--text-xs);
  font-weight: 300;
  font-family: 'Poppins', sans-serif;
  color: var(--text-dark);
  cursor: pointer;
  transition: background 0.1s;
}
.ask-tag-dropdown-item:hover {
  background: var(--bg);
}

.ask-tag-error {
  font-size: 12px;
  color: #c0392b;
  background: #fdf0ef;
  padding: 6px 10px;
  border-radius: var(--radius-xs);
  margin: 0;
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

@media (max-width: 760px) {
  .ask-card {
    padding: var(--space-xs);
  }
}

@media (max-width: 480px) {
  .ask-card {
    padding: var(--space-2xs);
    gap: var(--space-xs);
  }
  .ask-heading {
    font-size: 16px;
  }
  .ask-subtitle {
    font-size: 13px;
  }
  .ask-actions-row {
    flex-wrap: wrap;
  }
  .ask-category-dropdown {
    min-width: 160px;
  }
  .ask-view .login-prompt {
    padding: 24px;
  }
}
</style>
