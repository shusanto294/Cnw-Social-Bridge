<template>
  <div class="cnw-msg">
    <!-- Left: Chat list -->
    <div class="cnw-msg-list">
      <div class="cnw-msg-list-header">
        <h2 class="cnw-msg-list-title">Chats</h2>
        <button class="cnw-msg-compose-btn" @click="showNewChat = !showNewChat" title="New message">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
            <g clip-path="url(#clip0_20106_16335)">
              <path d="M13.0938 6.94365L13.0844 6.93428L9.05317 2.8999C9.05317 2.8999 4.14067 7.8124 1.77192 10.2312C1.47817 10.5312 1.25317 10.9499 1.12192 11.3562C0.734418 12.5437 0.412543 13.7562 0.0531678 14.953C-0.0437072 15.2749 -0.0249572 15.5593 0.228168 15.7968C0.465668 16.0218 0.734418 16.0343 1.04067 15.9405C2.18442 15.5968 3.33442 15.2718 4.48129 14.9437C5.08442 14.7718 5.59067 14.453 6.03442 14.0062C8.29067 11.7405 13.0938 6.94365 13.0938 6.94365Z" fill="#414141"/>
              <path d="M15.4466 1.93125L14.0685 0.553125C13.331 -0.184375 12.1403 -0.184375 11.4028 0.553125L9.84033 2.11563L13.8841 6.15938L15.4466 4.59688C16.1841 3.8625 16.1841 2.66875 15.4466 1.93125Z" fill="#414141"/>
              <path d="M7.20625 14.3062H15.1531C15.6219 14.3062 16 14.6843 16 15.153C16 15.6218 15.6219 15.9999 15.1531 15.9999H7.20625C6.7375 15.9999 6.35938 15.6218 6.35938 15.153C6.3625 14.6843 6.74063 14.3062 7.20625 14.3062Z" fill="#414141"/>
            </g>
            <defs>
              <clipPath id="clip0_20106_16335">
                <rect width="16" height="16" fill="white"/>
              </clipPath>
            </defs>
          </svg>
        </button>
      </div>

      <!-- New chat user search -->
      <div v-if="showNewChat" class="cnw-msg-new-chat">
        <input
          v-model="newChatSearch"
          @input="searchUsers"
          type="text"
          placeholder="Search user to message..."
          class="cnw-msg-new-chat-input"
        />
        <div v-if="searchResults.length" class="cnw-msg-search-results">
          <div
            v-for="user in searchResults"
            :key="user.id"
            class="cnw-msg-search-item"
            @click="startConversation(user)"
          >
            <img :src="user.avatar" :alt="user.name" class="cnw-social-worker-avatar" width="32" height="32" />
            <span>{{ user.name }}</span>
          </div>
        </div>
      </div>

      <!-- Recent contacts row -->
      <div v-if="conversations.length" class="cnw-msg-contacts-row">
        <div
          v-for="conv in conversations.slice(0, 5)"
          :key="'avatar-' + conv.other_user_id"
          class="cnw-msg-contact-avatar"
          :class="{ active: activeUserId === Number(conv.other_user_id) }"
          @click="openConversation(conv)"
        >
          <img :src="conv.other_avatar" :alt="conv.other_name" class="cnw-social-worker-avatar" width="50" height="50" />
          <span class="cnw-msg-status-dot" :class="conv.is_online ? 'online' : 'offline'"></span>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loadingList" class="cnw-msg-loading">Loading chats...</div>

      <!-- Conversation list -->
      <div v-else class="cnw-msg-conversations">
        <div
          v-for="conv in conversations"
          :key="conv.other_user_id"
          class="cnw-msg-conv-card"
          :class="{ active: activeUserId === Number(conv.other_user_id) }"
          @click="openConversation(conv)"
        >

          <div class="cnw-msg-conv-avatar-wrap">
            <img :src="conv.other_avatar" :alt="conv.other_name" class="cnw-social-worker-avatar" width="44" height="44" />
            <span class="cnw-msg-status-dot" :class="conv.is_online ? 'online' : 'offline'"></span>
          </div>
          <div class="cnw-msg-conv-body">
            <div class="cnw-msg-conv-top">
              <span class="cnw-msg-conv-status-label">{{ conv.is_online ? 'Online' : 'Offline' }}</span>
            </div>
            <div class="cnw-msg-conv-name">{{ conv.other_name }}</div>
            <p class="cnw-msg-conv-preview">{{ truncate(conv.last_message, 60) }}</p>
            <div class="cnw-msg-conv-meta">
              <span class="cnw-msg-conv-time">{{ formatTime(conv.last_date) }}</span>
              <span class="cnw-msg-conv-read-badge" :class="conv.unread_count > 0 ? 'unread' : 'read'">
                {{ conv.unread_count > 0 ? 'Unread' : 'Read' }}
              </span>
            </div>
          </div>
        </div>

        <div v-if="!loadingList && !conversations.length" class="cnw-msg-empty">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--border)" stroke-width="1.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          <p>No conversations yet</p>
        </div>
      </div>
    </div>

    <!-- Right: Chat detail -->
    <div class="cnw-msg-detail">
      <template v-if="activeUserId">
        <!-- Chat header -->
        <div class="cnw-msg-detail-header">
          <img :src="otherUser.avatar" :alt="otherUser.name" class="cnw-social-worker-avatar" width="44" height="44" />
          <div>
            <div class="cnw-msg-detail-label" v-if="otherUser.verified_label">
              <!-- <span class="cnw-social-worker-verified">&#10003;</span> -->
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                <g clip-path="url(#clip0_20103_14626)">
                  <path d="M7 0.4375C3.3775 0.4375 0.4375 3.3775 0.4375 7C0.4375 10.6225 3.3775 13.5625 7 13.5625C10.6225 13.5625 13.5625 10.6225 13.5625 7C13.5625 3.3775 10.6225 0.4375 7 0.4375ZM10.2812 5.8275L7.21875 9.3275C7.04812 9.52437 6.8075 9.625 6.5625 9.625C6.37 9.625 6.1775 9.56375 6.01562 9.4325L3.82812 7.6825C3.41434 7.35048 3.38153 6.71685 3.79378 6.34611C4.11801 6.05452 4.61983 6.07587 4.96038 6.3483L6.29226 7.41379C6.38225 7.48576 6.51293 7.47421 6.58892 7.38758L8.96875 4.6725C9.28375 4.30938 9.83937 4.27437 10.2025 4.59375C10.5656 4.90875 10.6006 5.46438 10.2812 5.8275Z" fill="#2C96FF"/>
                </g>
                <defs>
                  <clipPath id="clip0_20103_14626">
                    <rect width="14" height="14" fill="white"/>
                  </clipPath>
                </defs>
              </svg>
              <span>{{ otherUser.verified_label }}</span>
            </div>
            <div class="cnw-msg-detail-name">{{ otherUser.name }}</div>
          </div>
        </div>

        <!-- Messages -->
        <div class="cnw-msg-detail-messages" ref="messagesContainer">
          <div v-if="loadingMessages" class="cnw-msg-loading">Loading messages...</div>
          <div
            v-for="msg in messages"
            :key="msg.id"
            class="cnw-msg-bubble-wrap"
            :class="{ mine: Number(msg.sender_id) === currentUserId }"
          >

            <div class="cnw-msg-bubble">
              <div class="cnw-msg-bubble-iamge">
                <img
                  v-if="Number(msg.sender_id) !== currentUserId"
                  :src="msg.sender_avatar"
                  :alt="msg.sender_name"
                  class="cnw-social-worker-avatar cnw-msg-bubble-avatar"
                  width="36"
                  height="36"
                />
              </div>
              <div class="cnw-msg-bubble-text">
                <div class="cnw-msg-bubble-header">
                  <span class="cnw-msg-bubble-status">{{ Number(msg.sender_id) === currentUserId ? '' : 'Online' }}</span>
                  <span class="cnw-msg-bubble-name">{{ msg.sender_name }}</span>
                </div>
                <p class="cnw-msg-bubble-text">{{ msg.content }}</p>
                <span class="cnw-msg-bubble-time">{{ formatTime(msg.created_at) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Input -->
        <div class="cnw-msg-input-bar">
          <input
            v-model="newMessage"
            @keyup.enter="sendMsg"
            type="text"
            placeholder="Write Message:"
            class="cnw-msg-input"
          />
          <button class="cnw-msg-send-btn" :disabled="!newMessage.trim() || sending" @click="sendMsg">Send</button>
        </div>
      </template>

      <div v-else class="cnw-msg-detail-empty">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--border)" stroke-width="1.5"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
        <p>Select a conversation to start chatting</p>
      </div>
    </div>
  </div>
</template>

<script>
import { getConversations, getConversation, sendMessage, markConversationRead, getUser } from '@/api';

export default {
  name: 'MessagesView',
  data() {
    return {
      conversations: [],
      messages: [],
      activeUserId: null,
      otherUser: {},
      newMessage: '',
      sending: false,
      loadingList: true,
      loadingMessages: false,
      showNewChat: false,
      newChatSearch: '',
      searchResults: [],
      searchTimeout: null,
      currentUserId: window.cnwData?.currentUser?.id || 0,
      pollInterval: null,
    };
  },
  async mounted() {
    await this.loadConversations();
    this.pollInterval = setInterval(() => this.pollMessages(), 10000);
    this._startChatHandler = (e) => {
      if (e.detail) this.startConversation(e.detail);
    };
    window.addEventListener('cnw-start-chat', this._startChatHandler);
  },
  beforeUnmount() {
    if (this.pollInterval) clearInterval(this.pollInterval);
    window.removeEventListener('cnw-start-chat', this._startChatHandler);
  },
  methods: {
    async loadConversations() {
      this.loadingList = true;
      try {
        const data = await getConversations();
        this.conversations = Array.isArray(data) ? data : [];
      } catch { this.conversations = []; }
      this.loadingList = false;
    },
    async openConversation(conv) {
      this.activeUserId = Number(conv.other_user_id);
      this.otherUser = {
        id: Number(conv.other_user_id),
        name: conv.other_name,
        avatar: conv.other_avatar,
        verified_label: conv.other_verified_label || '',
      };
      await this.loadMessages();
      if (conv.unread_count > 0) {
        await markConversationRead(this.activeUserId);
        conv.unread_count = 0;
      }
    },
    async startConversation(user) {
      this.showNewChat = false;
      this.newChatSearch = '';
      this.searchResults = [];
      this.activeUserId = user.id;
      this.otherUser = {
        id: user.id,
        name: user.name,
        avatar: user.avatar,
        verified_label: user.verified_label || '',
      };
      await this.loadMessages();
    },
    async loadMessages() {
      this.loadingMessages = true;
      try {
        const data = await getConversation(this.activeUserId);
        this.messages = data.messages || [];
        if (data.other_user) {
          this.otherUser = data.other_user;
        }
      } catch { this.messages = []; }
      this.loadingMessages = false;
      this.$nextTick(() => this.scrollToBottom());
    },
    async sendMsg() {
      if (!this.newMessage.trim() || this.sending) return;
      this.sending = true;
      try {
        await sendMessage({ recipient_id: this.activeUserId, content: this.newMessage.trim() });
        this.newMessage = '';
        await this.loadMessages();
        await this.loadConversations();
      } catch { /* silent */ }
      this.sending = false;
    },
    async pollMessages() {
      if (!this.activeUserId) return;
      try {
        const data = await getConversation(this.activeUserId);
        if (data.messages && data.messages.length !== this.messages.length) {
          this.messages = data.messages;
          this.$nextTick(() => this.scrollToBottom());
        }
      } catch { /* silent */ }
      try {
        const data = await getConversations();
        if (Array.isArray(data)) this.conversations = data;
      } catch { /* silent */ }
    },
    scrollToBottom() {
      const el = this.$refs.messagesContainer;
      if (el) el.scrollTop = el.scrollHeight;
    },
    searchUsers() {
      clearTimeout(this.searchTimeout);
      if (!this.newChatSearch.trim()) { this.searchResults = []; return; }
      this.searchTimeout = setTimeout(async () => {
        try {
          const url = `${window.cnwData?.restUrl || ''}/users?search=${encodeURIComponent(this.newChatSearch)}`;
          const res = await fetch(url, { headers: { 'X-WP-Nonce': window.cnwData?.nonce || '' } });
          const data = await res.json();
          this.searchResults = (data.users || data || []).filter(u => u.id !== this.currentUserId).slice(0, 5);
        } catch { this.searchResults = []; }
      }, 300);
    },
    formatTime(dateStr) {
      if (!dateStr) return '';
      const d = new Date(dateStr);
      const now = new Date();
      const diffMs = now - d;
      const diffDays = Math.floor(diffMs / 86400000);
      if (diffDays === 0) {
        return d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', hour12: true });
      } else if (diffDays === 1) {
        return 'Yesterday';
      } else if (diffDays < 7) {
        return `${diffDays} Days Ago`;
      }
      return d.toLocaleDateString([], { month: 'short', day: 'numeric' });
    },
    truncate(str, len) {
      if (!str) return '';
      return str.length > len ? str.substring(0, len) + '..' : str;
    },
  },
};
</script>

<style>

.cnw-social-worker-main{
  background: transparent;
  padding: 0;
}

.cnw-msg {
  display: flex;
  gap: 0;
  height: 100%;
  /* min-height: 794px; */
  /* max-height: calc(100vh - 200px); */
}

/* ── Chat list panel ─────────────────────── */
.cnw-msg-list {
  width: 377px;
  min-width: 377px;
  background: #fff;
  border-radius: var(--radius);
  box-shadow: var(--shadow-sm);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  padding: var(--space-l) var(--space-m);
  row-gap: var(--space-s);
}
.cnw-msg-list-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.cnw-msg-list-title {
  font-size: 18px;
  font-weight: 600;
  color: var(--text-dark);
}
.cnw-msg-compose-btn {
  background: none;
  border: none;
  color: var(--text-dark);
  padding: 4px;
}
.cnw-msg-compose-btn:hover { color: var(--teal); }

/* New chat search */
.cnw-msg-new-chat { padding: 0 14px 10px; }
.cnw-msg-new-chat-input {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  font-size: 13px;
  outline: none;
}
.cnw-msg-new-chat-input:focus { border-color: var(--teal); }
.cnw-msg-search-results {
  background: #fff;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  margin-top: 4px;
}
.cnw-msg-search-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  cursor: pointer;
  font-size: 13px;
}
.cnw-msg-search-item:hover { background: var(--teal-light); }
.cnw-msg-search-item img {
  width: 32px;
  height: 32px;
  object-fit: cover;
  border-radius: 50%;
}

/* Contacts row */
.cnw-msg-contacts-row {
  display: flex;
  justify-content: space-between;
}
.cnw-msg-contact-avatar {
  position: relative;
  cursor: pointer;
  border-radius: 50%;
  transition: border-color 0.15s;
}
.cnw-msg-contact-status {
  position: absolute;
  top: 2px;
  right: 2px;
}
.cnw-msg-contact-avatar.active,
.cnw-msg-contact-avatar:hover {
  border-color: var(--teal);
}
.cnw-msg-contact-avatar img {
  display: block;
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 50%;
}

/* Conversations list */
.cnw-msg-conversations {
  display: flex;
  flex-direction: column;
  row-gap: var(--space-s);
}
.cnw-msg-conv-card {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 14px;
  cursor: pointer;
  border-radius: var(--radius);
  border: 1px solid var(--primary);
  background: #fff;
  transition: background 0.15s, box-shadow 0.15s;
}
.cnw-msg-conv-card:hover {
  background: #f8f9fa;
  box-shadow: var(--shadow-sm);
}
.cnw-msg-conv-card.active {
  background: var(--primary);
}

.cnw-msg-conv-card.active *{
  color: var(--light);
}
.cnw-msg-conv-menu {
  background: none;
  border: none;
  color: var(--text-light);
  padding: 2px;
  flex-shrink: 0;
  margin-top: 2px;
}
.cnw-msg-conv-avatar-wrap { position: relative; flex-shrink: 0; }

.cnw-msg-conv-avatar-wrap img{
  width: 34px;
  height: 34px;
  border-radius: 50%;
  object-fit: cover;
}
.cnw-msg-status-dot {
  position: absolute;
  top: 0px;
  right: 0px;
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.cnw-msg-contacts-row .cnw-msg-status-dot {
  top: 3px;
  right: 3px;
}

.cnw-msg-status-dot.online { background: var(--green); }
.cnw-msg-status-dot.offline { background: #aaa; }

.cnw-msg-conv-body { flex: 1; min-width: 0; }
.cnw-msg-conv-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.cnw-msg-conv-status-label {
  font-size: var(--text-xs);
  color: #999999;
  font-weight: 300;
}
.cnw-msg-conv-name {
  font-size: var(--text-m);
  font-weight: 300;
  color: #414141;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.cnw-msg-conv-preview {
  font-size: var(--text-xs);
  color: #999999;
  margin: 10px 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.cnw-msg-conv-meta {
  display: flex;
  justify-content: flex-end;
  column-gap: 14px;
  font-size: var(--text-xs);
  color: #999999;
  font-weight: 300;
}


/* ── Chat detail panel ───────────────────── */
.cnw-msg-detail {
  flex: 1;
  background: #fff;
  border-radius: var(--radius);
  box-shadow: var(--shadow-sm);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  margin-left: 16px;
}
.cnw-msg-detail-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px 20px;
  border-bottom: 1px solid var(--border);
}
.cnw-msg-detail-header img {
  width: 44px;
  height: 44px;
  object-fit: cover;
  border-radius: 50%;
}
.cnw-msg-detail-label {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  color: var(--text-light);
}
.cnw-msg-detail-name {
  font-size: 16px;
  font-weight: 700;
  color: var(--text-dark);
}

/* Messages area */
.cnw-msg-detail-messages {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.cnw-msg-bubble-wrap {
  display: flex;
  gap: 10px;
  max-width: 75%;
}
.cnw-msg-bubble-wrap.mine {
  margin-left: auto;
  flex-direction: row-reverse;
}
.cnw-msg-bubble-avatar {
  flex-shrink: 0;
  margin-top: 4px;
  width: 36px;
  height: 36px;
  object-fit: cover;
  border-radius: 50%;
}
.cnw-msg-bubble {
  background: var(--secondary);
  color: #fff;
  border-radius: 12px;
  padding: 12px 16px;
  position: relative;
}
.cnw-msg-bubble-wrap.mine .cnw-msg-bubble {
  background: var(--primary);
}
.cnw-msg-bubble-header {
  display: flex;
  flex-direction: column;
  margin-bottom: 4px;
}
.cnw-msg-bubble-status {
  font-size: 11px;
  opacity: 0.8;
}
.cnw-msg-bubble-name {
  font-size: 13px;
  font-weight: 600;
}
.cnw-msg-bubble-wrap.mine .cnw-msg-bubble-header { display: none; }
.cnw-msg-bubble-text {
  font-size: 13px;
  line-height: 1.5;
  margin: 0;
}
.cnw-msg-bubble-time {
  display: block;
  font-size: 11px;
  opacity: 0.75;
  text-align: right;
  margin-top: 6px;
}

/* Input bar */
.cnw-msg-input-bar {
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding: 14px 20px;
  border-top: 1px solid var(--border);
}
.cnw-msg-input {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  font-size: 13px;
  font-family: inherit;
  outline: none;
  resize: none;
}
.cnw-msg-input:focus { border-color: var(--teal); }
.cnw-msg-send-btn {
  align-self: flex-end;
  padding: 8px 24px;
  background: var(--green);
  color: #fff;
  border: none;
  border-radius: var(--radius-sm);
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s;
}
.cnw-msg-send-btn:hover { background: var(--green-dark); }
.cnw-msg-send-btn:disabled { opacity: 0.5; cursor: not-allowed; }

/* Empty & loading states */
.cnw-msg-detail-empty {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  color: var(--text-light);
  font-size: 14px;
}
.cnw-msg-empty {
  padding: 40px 20px;
  text-align: center;
  color: var(--text-light);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}
.cnw-msg-loading {
  padding: 30px 20px;
  text-align: center;
  color: var(--text-light);
  font-size: 13px;
}

/* Responsive */
@media (max-width: 768px) {
  .cnw-msg { flex-direction: column; max-height: none; }
  .cnw-msg-list { width: 100%; min-width: 0; max-height: 350px; }
  .cnw-msg-detail { margin-left: 0; margin-top: 12px; min-height: 400px; }
}
</style>
