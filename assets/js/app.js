const { createApp } = Vue;
const { createRouter, createWebHashHistory } = VueRouter;

// Home/Dashboard Component
const Home = {
    template: `
        <div class="cnw-home">
            <div class="cnw-tabs">
                <button 
                    v-for="tab in tabs" 
                    :key="tab.id"
                    :class="['cnw-tab', { active: currentTab === tab.id }]"
                    @click="currentTab = tab.id"
                >
                    {{ tab.label }}
                </button>
            </div>
            
            <div class="cnw-search-bar">
                <input 
                    type="text" 
                    v-model="searchQuery" 
                    placeholder="Search" 
                    class="cnw-search-input"
                >
                <button class="cnw-search-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </button>
            </div>
            
            <button class="cnw-new-question-btn" @click="$router.push('/ask')">
                + New Question
            </button>
            
            <div class="cnw-questions-list">
                <div v-if="loading" class="cnw-loading">Loading...</div>
                <div v-else-if="threads.length === 0" class="cnw-empty">
                    No questions found.
                </div>
                <div 
                    v-for="thread in filteredThreads" 
                    :key="thread.id"
                    class="cnw-question-card"
                    @click="$router.push('/thread/' + thread.id)"
                >
                    <div class="cnw-question-header">
                        <div class="cnw-question-meta">
                            <span class="cnw-author">{{ thread.author_name }}</span>
                            <span class="cnw-badge verified" v-if="thread.author_id === 1">✓</span>
                            <span class="cnw-date">{{ formatDate(thread.created_at) }}</span>
                        </div>
                    </div>
                    
                    <h3 class="cnw-question-title">{{ thread.title }}</h3>
                    <p class="cnw-question-excerpt">{{ thread.content.substring(0, 200) }}...</p>
                    
                    <div class="cnw-tags">
                        <span v-for="tag in getThreadTags(thread)" :key="tag" class="cnw-tag">
                            {{ tag }}
                        </span>
                    </div>
                    
                    <div class="cnw-question-footer">
                        <span class="cnw-stat">
                            <span class="cnw-icon">♥</span>
                            {{ thread.likes || 0 }} Helpful
                        </span>
                        <span class="cnw-stat">
                            {{ thread.views || 0 }} Views
                        </span>
                        <span class="cnw-replies-count">
                            {{ thread.reply_count || 0 }} Replies
                        </span>
                        <button class="cnw-reply-btn" @click.stop="$router.push('/thread/' + thread.id)">
                            Reply
                        </button>
                        <span class="cnw-status" :class="{ answered: thread.reply_count > 0 }">
                            {{ thread.reply_count > 0 ? '✓ answered' : '○ unanswered' }}
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="cnw-pagination" v-if="totalPages > 1">
                <button 
                    v-for="page in totalPages" 
                    :key="page"
                    :class="['cnw-page-btn', { active: currentPage === page }]"
                    @click="goToPage(page)"
                >
                    {{ page }}
                </button>
            </div>
        </div>
    `,
    data() {
        return {
            tabs: [
                { id: 'all', label: 'All Questions' },
                { id: 'newest', label: 'Newest' },
                { id: 'active', label: 'Active' },
                { id: 'unanswered', label: 'Unanswered' },
                { id: 'recommended', label: 'Recommended' },
            ],
            currentTab: 'all',
            searchQuery: '',
            threads: [],
            loading: true,
            currentPage: 1,
            totalPages: 1,
        };
    },
    computed: {
        filteredThreads() {
            let result = this.threads;
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                result = result.filter(t => 
                    t.title.toLowerCase().includes(query) || 
                    t.content.toLowerCase().includes(query)
                );
            }
            return result;
        },
    },
    mounted() {
        this.fetchThreads();
    },
    methods: {
        async fetchThreads() {
            try {
                const response = await fetch(`${cnwData.restUrl}/threads?page=${this.currentPage}`, {
                    headers: { 'X-WP-Nonce': cnwData.nonce },
                });
                const data = await response.json();
                this.threads = data.threads || [];
                this.totalPages = data.pages || 1;
                this.loading = false;
            } catch (error) {
                console.error('Error fetching threads:', error);
                this.loading = false;
            }
        },
        goToPage(page) {
            this.currentPage = page;
            this.fetchThreads();
        },
        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric' 
            }) + ' · ' + date.toLocaleTimeString('en-US', { 
                hour: 'numeric', 
                minute: '2-digit' 
            });
        },
        getThreadTags(thread) {
            // Sample tags based on thread ID for demo
            const tagSets = [
                ['Trauma-Informed Care', 'Therapy Referrals', 'Uninsured Adults'],
                ['Emergency Housing', 'Shelter Overflow', 'Adult Services'],
                ['Youth Services', 'McKinney-Vento', 'School Stability'],
            ];
            return tagSets[thread.id % tagSets.length] || tagSets[0];
        },
    },
};

// Thread Detail Component
const ThreadDetail = {
    template: `
        <div class="cnw-thread-detail">
            <button class="cnw-back-btn" @click="$router.push('/')">
                ← Back to Questions
            </button>
            
            <div v-if="loading" class="cnw-loading">Loading...</div>
            <div v-else-if="!thread" class="cnw-error">Thread not found.</div>
            <div v-else>
                <div class="cnw-thread-card">
                    <div class="cnw-thread-header">
                        <h1 class="cnw-thread-title">{{ thread.title }}</h1>
                        <div class="cnw-thread-meta">
                            <span class="cnw-author">{{ thread.author_name }}</span>
                            <span class="cnw-date">{{ formatDate(thread.created_at) }}</span>
                        </div>
                    </div>
                    
                    <div class="cnw-thread-content" v-html="thread.content"></div>
                    
                    <div class="cnw-thread-footer">
                        <span class="cnw-stat">
                            <span class="cnw-icon">♥</span>
                            {{ thread.likes || 0 }} Helpful
                        </span>
                        <span class="cnw-stat">{{ thread.views || 0 }} Views</span>
                    </div>
                </div>
                
                <div class="cnw-replies-section">
                    <h2>{{ replies.length }} Replies</h2>
                    
                    <div v-for="reply in replies" :key="reply.id" class="cnw-reply-card">
                        <div class="cnw-reply-header">
                            <span class="cnw-author">{{ reply.author_name }}</span>
                            <span class="cnw-badge verified" v-if="reply.author_id === 1">✓</span>
                            <span class="cnw-date">{{ formatDate(reply.created_at) }}</span>
                        </div>
                        <div class="cnw-reply-content" v-html="reply.content"></div>
                        <div class="cnw-reply-footer">
                            <span class="cnw-stat">
                                <span class="cnw-icon">♥</span>
                                {{ reply.likes || 0 }} Helpful
                            </span>
                            <button class="cnw-reply-btn-small">Reply</button>
                        </div>
                    </div>
                </div>
                
                <div class="cnw-reply-form" v-if="isLoggedIn">
                    <h3>Write a Reply</h3>
                    <textarea 
                        v-model="replyContent" 
                        placeholder="Write your reply here..."
                        class="cnw-textarea"
                        rows="4"
                    ></textarea>
                    <button 
                        class="cnw-submit-btn" 
                        @click="submitReply"
                        :disabled="!replyContent.trim() || submitting"
                    >
                        {{ submitting ? 'Submitting...' : 'Reply' }}
                    </button>
                </div>
                <div v-else class="cnw-login-prompt">
                    Please <a href="/wp-login.php">log in</a> to reply.
                </div>
            </div>
        </div>
    `,
    data() {
        return {
            thread: null,
            replies: [],
            loading: true,
            replyContent: '',
            submitting: false,
            isLoggedIn: cnwData.currentUser && cnwData.currentUser.id > 0,
        };
    },
    mounted() {
        this.fetchThread();
        this.fetchReplies();
    },
    methods: {
        async fetchThread() {
            try {
                const response = await fetch(
                    `${cnwData.restUrl}/threads/${this.$route.params.id}`,
                    { headers: { 'X-WP-Nonce': cnwData.nonce } }
                );
                this.thread = await response.json();
                this.loading = false;
            } catch (error) {
                console.error('Error fetching thread:', error);
                this.loading = false;
            }
        },
        async fetchReplies() {
            try {
                const response = await fetch(
                    `${cnwData.restUrl}/threads/${this.$route.params.id}/replies`,
                    { headers: { 'X-WP-Nonce': cnwData.nonce } }
                );
                const data = await response.json();
                this.replies = data.replies || [];
            } catch (error) {
                console.error('Error fetching replies:', error);
            }
        },
        async submitReply() {
            if (!this.replyContent.trim()) return;
            
            this.submitting = true;
            try {
                const response = await fetch(`${cnwData.restUrl}/replies`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': cnwData.nonce,
                    },
                    body: JSON.stringify({
                        thread_id: this.$route.params.id,
                        content: this.replyContent,
                    }),
                });
                
                if (response.ok) {
                    this.replyContent = '';
                    this.fetchReplies();
                }
            } catch (error) {
                console.error('Error submitting reply:', error);
            } finally {
                this.submitting = false;
            }
        },
        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric' 
            }) + ' · ' + date.toLocaleTimeString('en-US', { 
                hour: 'numeric', 
                minute: '2-digit' 
            });
        },
    },
};

// Ask Question Component
const AskQuestion = {
    template: `
        <div class="cnw-ask-question">
            <button class="cnw-back-btn" @click="$router.push('/')">
                ← Back to Questions
            </button>
            
            <h1>Ask a Question</h1>
            
            <div class="cnw-form" v-if="isLoggedIn">
                <div class="cnw-form-group">
                    <label>Title</label>
                    <input 
                        type="text" 
                        v-model="title" 
                        placeholder="What's your question? Be specific."
                        class="cnw-input"
                    >
                </div>
                
                <div class="cnw-form-group">
                    <label>Details</label>
                    <textarea 
                        v-model="content" 
                        placeholder="Provide more details about your question..."
                        class="cnw-textarea"
                        rows="10"
                    ></textarea>
                </div>
                
                <div class="cnw-form-group">
                    <label>Tags</label>
                    <div class="cnw-tag-input">
                        <span v-for="tag in selectedTags" :key="tag" class="cnw-tag selected">
                            {{ tag }}
                            <button @click="removeTag(tag)">×</button>
                        </span>
                        <input 
                            type="text" 
                            v-model="tagInput" 
                            @keydown.enter.prevent="addTag"
                            placeholder="Add tags (press Enter)"
                            class="cnw-tag-input-field"
                        >
                    </div>
                </div>
                
                <button 
                    class="cnw-submit-btn" 
                    @click="submitQuestion"
                    :disabled="!canSubmit || submitting"
                >
                    {{ submitting ? 'Posting...' : 'Post Your Question' }}
                </button>
            </div>
            
            <div v-else class="cnw-login-prompt">
                Please <a href="/wp-login.php">log in</a> to ask a question.
            </div>
        </div>
    `,
    data() {
        return {
            title: '',
            content: '',
            tagInput: '',
            selectedTags: [],
            submitting: false,
            isLoggedIn: cnwData.currentUser && cnwData.currentUser.id > 0,
        };
    },
    computed: {
        canSubmit() {
            return this.title.trim() && this.content.trim();
        },
    },
    methods: {
        addTag() {
            const tag = this.tagInput.trim();
            if (tag && !this.selectedTags.includes(tag)) {
                this.selectedTags.push(tag);
            }
            this.tagInput = '';
        },
        removeTag(tag) {
            this.selectedTags = this.selectedTags.filter(t => t !== tag);
        },
        async submitQuestion() {
            this.submitting = true;
            try {
                const response = await fetch(`${cnwData.restUrl}/threads`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': cnwData.nonce,
                    },
                    body: JSON.stringify({
                        title: this.title,
                        content: this.content,
                        tags: this.selectedTags,
                    }),
                });
                
                const data = await response.json();
                if (data.success && data.id) {
                    this.$router.push('/thread/' + data.id);
                }
            } catch (error) {
                console.error('Error submitting question:', error);
            } finally {
                this.submitting = false;
            }
        },
    },
};

// Messages Component
const Messages = {
    template: `
        <div class="cnw-messages">
            <h1>Messages</h1>
            <div class="cnw-messages-list">
                <p class="cnw-coming-soon">Messages feature coming soon!</p>
            </div>
        </div>
    `,
};

// Users Component
const Users = {
    template: `
        <div class="cnw-users">
            <h1>Community Members</h1>
            <div class="cnw-users-grid">
                <p class="cnw-coming-soon">User directory coming soon!</p>
            </div>
        </div>
    `,
};

// Create Router
const router = createRouter({
    history: createWebHashHistory(),
    routes: [
        { path: '/', component: Home },
        { path: '/thread/:id', component: ThreadDetail },
        { path: '/ask', component: AskQuestion },
        { path: '/messages', component: Messages },
        { path: '/users', component: Users },
    ],
});

// Sidebar Component
const Sidebar = {
    template: `
        <aside class="cnw-sidebar">
            <div class="cnw-user-card">
                <img 
                    :src="currentUser.avatar" 
                    :alt="currentUser.name"
                    class="cnw-user-avatar"
                >
                <h3 class="cnw-user-name">{{ currentUser.name || 'Guest' }}</h3>
            </div>
            
            <nav class="cnw-nav">
                <div class="cnw-nav-section">
                    <router-link to="/" class="cnw-nav-item" active-class="active">
                        <span class="cnw-nav-icon">?</span>
                        <span>Questions</span>
                        <span class="cnw-badge-count" v-if="questionCount">{{ questionCount }}</span>
                    </router-link>
                    <router-link to="/tags" class="cnw-nav-item" active-class="active">
                        <span class="cnw-nav-icon">🏷</span>
                        <span>Tags</span>
                        <span class="cnw-badge-count" v-if="tagCount">{{ tagCount }}</span>
                    </router-link>
                    <router-link to="/ask" class="cnw-nav-item" active-class="active">
                        <span class="cnw-nav-icon">✎</span>
                        <span>Ask a Question</span>
                    </router-link>
                    <router-link to="/activity" class="cnw-nav-item" active-class="active">
                        <span class="cnw-nav-icon">⏱</span>
                        <span>My Activity</span>
                    </router-link>
                    <router-link to="/messages" class="cnw-nav-item" active-class="active">
                        <span class="cnw-nav-icon">✉</span>
                        <span>Message</span>
                    </router-link>
                    <router-link to="/users" class="cnw-nav-item" active-class="active">
                        <span class="cnw-nav-icon">👤</span>
                        <span>Users</span>
                    </router-link>
                </div>
                
                <div class="cnw-nav-section">
                    <h4 class="cnw-nav-title">Support</h4>
                    <a href="#" class="cnw-nav-item">
                        <span class="cnw-nav-icon">🔖</span>
                        <span>Saved Threads</span>
                    </a>
                    <a href="#" class="cnw-nav-item">
                        <span class="cnw-nav-icon">📋</span>
                        <span>Community Guidelines</span>
                    </a>
                    <a href="#" class="cnw-nav-item">
                        <span class="cnw-nav-icon">⚠</span>
                        <span>Report an Issue</span>
                    </a>
                    <a href="/wp-login.php?action=logout" class="cnw-nav-item">
                        <span class="cnw-nav-icon">→</span>
                        <span>Logout</span>
                    </a>
                </div>
            </nav>
        </aside>
    `,
    data() {
        return {
            currentUser: cnwData.currentUser || { name: '', avatar: '' },
            questionCount: 0,
            tagCount: 8,
        };
    },
    mounted() {
        this.fetchQuestionCount();
    },
    methods: {
        async fetchQuestionCount() {
            try {
                const response = await fetch(`${cnwData.restUrl}/threads`, {
                    headers: { 'X-WP-Nonce': cnwData.nonce },
                });
                const data = await response.json();
                this.questionCount = data.total || 0;
            } catch (error) {
                console.error('Error fetching count:', error);
            }
        },
    },
};

// Right Sidebar Component
const RightSidebar = {
    template: `
        <aside class="cnw-right-sidebar">
            <div class="cnw-sidebar-section">
                <div class="cnw-section-header">
                    <h3>Following Tags</h3>
                    <button class="cnw-edit-btn">Edit</button>
                </div>
                <div class="cnw-tag-list">
                    <span v-for="tag in tags" :key="tag.id" class="cnw-tag-item">
                        {{ tag.name }}
                    </span>
                </div>
            </div>
            
            <div class="cnw-sidebar-section">
                <h3 class="cnw-section-title-green">Hot Questions</h3>
                <div class="cnw-hot-questions">
                    <div v-for="q in hotQuestions" :key="q.id" class="cnw-hot-question">
                        <h4 @click="$router.push('/thread/' + q.id)">{{ q.title }}</h4>
                        <p>{{ q.content.substring(0, 80) }}...</p>
                        <div class="cnw-hot-question-tags">
                            <span class="cnw-tag-emergency">Emergency Housing</span>
                            <span class="cnw-tag-crisis">Crisis Intervention</span>
                        </div>
                        <div class="cnw-hot-question-stats">
                            <span>♥ {{ q.likes || 17 }} Helpful</span>
                            <span>| Views {{ q.views || 3402 }}</span>
                        </div>
                        <div class="cnw-discussion-type">
                            <span>💬 Discussion</span>
                            <span>{{ q.reply_count || 13 }} Replies</span>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    `,
    data() {
        return {
            tags: [],
            hotQuestions: [],
        };
    },
    mounted() {
        this.fetchTags();
        this.fetchHotQuestions();
    },
    methods: {
        async fetchTags() {
            try {
                const response = await fetch(`${cnwData.restUrl}/tags`, {
                    headers: { 'X-WP-Nonce': cnwData.nonce },
                });
                this.tags = await response.json();
            } catch (error) {
                console.error('Error fetching tags:', error);
            }
        },
        async fetchHotQuestions() {
            try {
                const response = await fetch(`${cnwData.restUrl}/hot-questions`, {
                    headers: { 'X-WP-Nonce': cnwData.nonce },
                });
                this.hotQuestions = await response.json();
            } catch (error) {
                console.error('Error fetching hot questions:', error);
            }
        },
    },
};

// Header Component
const Header = {
    template: `
        <header class="cnw-header">
            <div class="cnw-header-left">
                <div class="cnw-logo">
                    <span class="cnw-logo-text">SOCIAL</span>
                    <svg class="cnw-logo-icon" viewBox="0 0 40 40">
                        <path d="M20 5 L35 20 L20 35 L5 20 Z" fill="#00a8c6"/>
                        <circle cx="20" cy="20" r="8" fill="white"/>
                    </svg>
                    <span class="cnw-logo-text">BRIDGE</span>
                </div>
            </div>
            
            <div class="cnw-header-right">
                <button class="cnw-ask-btn" @click="$router.push('/ask')">
                    + Ask a Question
                </button>
                <button class="cnw-notification-btn">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <span class="cnw-notification-badge" v-if="notifications > 0">{{ notifications }}</span>
                </button>
            </div>
        </header>
    `,
    data() {
        return {
            notifications: 1,
        };
    },
};

// Main App Component
const App = {
    template: `
        <div class="cnw-app">
            <Header />
            <div class="cnw-main-container">
                <Sidebar />
                <main class="cnw-main-content">
                    <router-view></router-view>
                </main>
                <RightSidebar />
            </div>
        </div>
    `,
    components: {
        Header,
        Sidebar,
        RightSidebar,
    },
};

// Mount App
const app = createApp(App);
app.use(router);
app.mount('#cnw-social-bridge-app');
