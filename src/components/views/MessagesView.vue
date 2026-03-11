<template>
  <div class="cnw-msg" :class="{ 'mobile-show-detail': mobileShowDetail, 'show-profile': showProfile }">
    <!-- Short Profile Panel -->
    <div v-if="showProfile && activeUserId" class="cnw-msg-profile-panel">
      <button class="cnw-msg-profile-close" @click="showProfile = false" title="Close">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
      <!-- Avatar + Name -->
      <div class="cnw-msg-profile-top">
        <div class="cnw-msg-profile-avatar-wrap">
          <img :src="otherUser.avatar" :alt="otherUser.name" class="cnw-social-worker-avatar" width="87" height="87" />
        </div>
        <p class="cnw-msg-profile-name">{{ otherUser.name }}</p>
      </div>
      <!-- Action buttons: Mute + Search -->
      <div class="cnw-msg-profile-actions">
        <div class="cnw-msg-profile-action-btn" :class="{ 'cnw-muted': isMutedUser }" @click="toggleMuteUser">
          <svg width="24" height="24" viewBox="0 0 20 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M19.379 16.913C17.867 15.635 17 13.767 17 11.788V9C17 5.481 14.386 2.568 11 2.08V1C11 0.447 10.552 0 10 0C9.448 0 9 0.447 9 1V2.08C5.613 2.568 3 5.481 3 9V11.788C3 13.767 2.133 15.635 0.612 16.921C0.223 17.254 0 17.738 0 18.25C0 19.215 0.785 20 1.75 20H18.25C19.215 20 20 19.215 20 18.25C20 17.738 19.777 17.254 19.379 16.913Z" :fill="isMutedUser ? '#999' : '#414141'"/>
            <path d="M10 24C11.811 24 13.326 22.709 13.674 21H6.326C6.674 22.709 8.189 24 10 24Z" :fill="isMutedUser ? '#999' : '#414141'"/>
          </svg>
          <span>{{ isMutedUser ? 'Unmute' : 'Mute' }}</span>
        </div>
        <div class="cnw-msg-profile-action-btn" :class="{ 'cnw-active-action': profileSearchMessages }" @click="toggleProfileSearch">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M9.65925 19.3084C11.8044 19.3084 13.8882 18.5927 15.5806 17.2745L21.9653 23.6593C22.4423 24.12 23.2023 24.1067 23.663 23.6298C24.1123 23.1645 24.1123 22.4269 23.663 21.9617L17.2782 15.5769C20.5491 11.3664 19.7874 5.30149 15.5769 2.03058C11.3663 -1.24033 5.30149 -0.478645 2.03058 3.7319C-1.24033 7.94244 -0.478646 14.0073 3.7319 17.2782C5.42702 18.5951 7.51269 19.3095 9.65925 19.3084ZM4.52915 4.52545C7.36245 1.6921 11.9561 1.69204 14.7895 4.52535C17.6229 7.35866 17.6229 11.9524 14.7896 14.7857C11.9563 17.6191 7.36261 17.6191 4.52925 14.7858C4.5292 14.7858 4.5292 14.7858 4.52915 14.7857C1.69584 11.973 1.67915 7.3961 4.49181 4.56279C4.50424 4.55031 4.51667 4.53788 4.52915 4.52545Z" :fill="profileSearchMessages ? 'var(--primary)' : '#414141'"/>
          </svg>
          <span>Search</span>
        </div>
      </div>
      <!-- Search messages input (shown when Search is active) -->
      <div v-if="profileSearchMessages" class="cnw-msg-profile-search-box">
        <input
          v-model="profileSearchQuery"
          @input="onProfileSearchMessages"
          type="text"
          placeholder="Search in conversation..."
          class="cnw-msg-profile-search-input"
          ref="profileSearchInput"
        />
        <div v-if="profileSearchQuery.trim() && profileSearchResults.length" class="cnw-msg-profile-search-count">
          {{ profileSearchResults.length }} message{{ profileSearchResults.length !== 1 ? 's' : '' }} found
        </div>
        <div v-if="profileSearchQuery.trim() && !profileSearchResults.length" class="cnw-msg-profile-search-count">
          No messages found
        </div>
        <div v-if="profileSearchResults.length" class="cnw-msg-profile-search-results">
          <div
            v-for="msg in profileSearchResults"
            :key="'search-' + msg.id"
            class="cnw-msg-profile-search-result-item"
            @click="scrollToMessage(msg.id)"
          >
            <div class="cnw-msg-profile-search-result-name">{{ msg.sender_name }}</div>
            <div class="cnw-msg-profile-search-result-text">{{ truncate(msg.content, 80) }}</div>
            <div class="cnw-msg-profile-search-result-time">{{ formatTime(msg.created_at) }}</div>
          </div>
        </div>
      </div>
      <!-- Chat Info section -->
      <div class="cnw-msg-profile-section">
        <div class="cnw-msg-profile-section-header" @click="profileChatInfoOpen = !profileChatInfoOpen">
          <span>Chat Info</span>
          <svg :class="{ 'cnw-chevron-open': profileChatInfoOpen }" class="cnw-msg-profile-chevron" width="12" height="12" viewBox="0 0 10.5133 6.01313" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M10.3583 0.954562L5.83072 5.7651C5.51947 6.09581 4.99391 6.09581 4.68266 5.7651L0.155056 0.954562C-0.185106 0.5931 0.0711314 0 0.567481 0H9.94582C10.4422 0 10.6984 0.5931 10.3583 0.954562Z" fill="#414141"/>
          </svg>
        </div>
        <template v-if="profileChatInfoOpen">
          <div class="cnw-msg-profile-section-item" @click="viewPinnedMessages">
            <svg width="14" height="14" viewBox="0 0 10.82 10.8779" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M10.675 3.98125L6.825 0.13125C6.65 -0.04375 6.3875 -0.04375 6.2125 0.13125L4.8125 1.53125C4.59375 1.75 4.68125 2.0125 4.8125 2.14375L5.11875 2.45L3.80625 3.7625C3.15 3.63125 1.35625 3.325 0.39375 4.2875C0.21875 4.4625 0.21875 4.725 0.39375 4.9L2.8875 7.39375L0.13125 10.15C-0.04375 10.325 -0.04375 10.5875 0.13125 10.7625C0.30625 10.9375 0.6125 10.8938 0.74375 10.7625L3.5 8.00625L5.99375 10.5C6.25625 10.7188 6.51875 10.6313 6.60625 10.5C7.56875 9.5375 7.2625 7.74375 7.13125 7.0875L8.44375 5.775L8.75 6.08125C8.925 6.25625 9.1875 6.25625 9.3625 6.08125L10.7625 4.68125C10.85 4.41875 10.85 4.15625 10.675 3.98125Z" fill="#414141"/>
            </svg>
            <span>View Pinned Message</span>
          </div>
          <div v-if="showPinnedMessages" class="cnw-msg-profile-section-notice">No pinned messages in this conversation</div>
        </template>
      </div>
      <!-- Media Files section -->
      <div class="cnw-msg-profile-section">
        <div class="cnw-msg-profile-section-header" @click="profileMediaOpen = !profileMediaOpen">
          <span>Media Files</span>
          <svg :class="{ 'cnw-chevron-open': profileMediaOpen }" class="cnw-msg-profile-chevron" width="12" height="12" viewBox="0 0 10.5133 6.01313" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M10.3583 0.954562L5.83072 5.7651C5.51947 6.09581 4.99391 6.09581 4.68266 5.7651L0.155056 0.954562C-0.185106 0.5931 0.0711314 0 0.567481 0H9.94582C10.4422 0 10.6984 0.5931 10.3583 0.954562Z" fill="#414141"/>
          </svg>
        </div>
        <template v-if="profileMediaOpen">
          <div class="cnw-msg-profile-section-item" @click="showMediaGallery = !showMediaGallery">
            <svg width="14" height="14" viewBox="0 0 11.6667 11.6667" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M0.338217 9.9225L0.32655 9.93417C0.16905 9.59 0.0698832 9.19917 0.0290499 8.7675C0.0698832 9.19333 0.180717 9.57833 0.338217 9.9225Z" fill="#414141"/>
              <path d="M4.0834 4.88833C4.85018 4.88833 5.47173 4.26675 5.47173 3.5C5.47173 2.73324 4.85018 2.11167 4.0834 2.11167C3.31665 2.11167 2.69507 2.73324 2.69507 3.5C2.69507 4.26675 3.31665 4.88833 4.0834 4.88833Z" fill="#414141"/>
              <path d="M8.2775 0H3.38917C1.26583 0 0 1.26583 0 3.38917V8.2775C0 8.91333 0.110833 9.4675 0.326667 9.93417C0.828333 11.0425 1.90167 11.6667 3.38917 11.6667H8.2775C10.4008 11.6667 11.6667 10.4008 11.6667 8.2775V6.94167V3.38917C11.6667 1.26583 10.4008 0 8.2775 0ZM10.7158 6.125C10.2608 5.73417 9.52583 5.73417 9.07083 6.125L6.64417 8.2075C6.18917 8.59833 5.45417 8.59833 4.99917 8.2075L4.80083 8.04417C4.38667 7.6825 3.7275 7.6475 3.26083 7.9625L1.07917 9.42667C0.950833 9.1 0.875 8.72083 0.875 8.2775V3.38917C0.875 1.74417 1.74417 0.875 3.38917 0.875H8.2775C9.9225 0.875 10.7917 1.74417 10.7917 3.38917V6.18917L10.7158 6.125Z" fill="#414141"/>
            </svg>
            <span>Media</span>
          </div>
          <div v-if="showMediaGallery" class="cnw-msg-profile-section-notice">No shared media yet</div>
          <div class="cnw-msg-profile-section-item" @click="showSharedFiles = !showSharedFiles">
            <svg width="14" height="14" viewBox="0 0 10.82 10.8779" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M10.675 3.98125L6.825 0.13125C6.65 -0.04375 6.3875 -0.04375 6.2125 0.13125L4.8125 1.53125C4.59375 1.75 4.68125 2.0125 4.8125 2.14375L5.11875 2.45L3.80625 3.7625C3.15 3.63125 1.35625 3.325 0.39375 4.2875C0.21875 4.4625 0.21875 4.725 0.39375 4.9L2.8875 7.39375L0.13125 10.15C-0.04375 10.325 -0.04375 10.5875 0.13125 10.7625C0.30625 10.9375 0.6125 10.8938 0.74375 10.7625L3.5 8.00625L5.99375 10.5C6.25625 10.7188 6.51875 10.6313 6.60625 10.5C7.56875 9.5375 7.2625 7.74375 7.13125 7.0875L8.44375 5.775L8.75 6.08125C8.925 6.25625 9.1875 6.25625 9.3625 6.08125L10.7625 4.68125C10.85 4.41875 10.85 4.15625 10.675 3.98125Z" fill="#414141"/>
            </svg>
            <span>Files</span>
          </div>
          <div v-if="showSharedFiles" class="cnw-msg-profile-section-notice">No shared files yet</div>
        </template>
      </div>
      <!-- Privacy & Support section -->
      <div class="cnw-msg-profile-section">
        <div class="cnw-msg-profile-section-header" @click="profilePrivacyOpen = !profilePrivacyOpen">
          <span>Privacy &amp; Support</span>
          <svg :class="{ 'cnw-chevron-open': profilePrivacyOpen }" class="cnw-msg-profile-chevron" width="12" height="12" viewBox="0 0 10.5133 6.01313" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M10.3583 0.954562L5.83072 5.7651C5.51947 6.09581 4.99391 6.09581 4.68266 5.7651L0.155056 0.954562C-0.185106 0.5931 0.0711314 0 0.567481 0H9.94582C10.4422 0 10.6984 0.5931 10.3583 0.954562Z" fill="#414141"/>
          </svg>
        </div>
        <template v-if="profilePrivacyOpen">
          <div class="cnw-msg-profile-section-item" @click="toggleMuteUser">
            <svg width="14" height="14" viewBox="0 0 11.6667 11.6667" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M0.338217 9.9225L0.32655 9.93417C0.16905 9.59 0.0698832 9.19917 0.0290499 8.7675C0.0698832 9.19333 0.180717 9.57833 0.338217 9.9225Z" fill="#414141"/>
              <path d="M4.0834 4.88833C4.85018 4.88833 5.47173 4.26675 5.47173 3.5C5.47173 2.73324 4.85018 2.11167 4.0834 2.11167C3.31665 2.11167 2.69507 2.73324 2.69507 3.5C2.69507 4.26675 3.31665 4.88833 4.0834 4.88833Z" fill="#414141"/>
              <path d="M8.2775 0H3.38917C1.26583 0 0 1.26583 0 3.38917V8.2775C0 8.91333 0.110833 9.4675 0.326667 9.93417C0.828333 11.0425 1.90167 11.6667 3.38917 11.6667H8.2775C10.4008 11.6667 11.6667 10.4008 11.6667 8.2775V6.94167V3.38917C11.6667 1.26583 10.4008 0 8.2775 0ZM10.7158 6.125C10.2608 5.73417 9.52583 5.73417 9.07083 6.125L6.64417 8.2075C6.18917 8.59833 5.45417 8.59833 4.99917 8.2075L4.80083 8.04417C4.38667 7.6825 3.7275 7.6475 3.26083 7.9625L1.07917 9.42667C0.950833 9.1 0.875 8.72083 0.875 8.2775V3.38917C0.875 1.74417 1.74417 0.875 3.38917 0.875H8.2775C9.9225 0.875 10.7917 1.74417 10.7917 3.38917V6.18917L10.7158 6.125Z" fill="#414141"/>
            </svg>
            <span>{{ isMutedUser ? 'Unmute Notifications' : 'Mute Notifications' }}</span>
          </div>
          <div class="cnw-msg-profile-section-item" @click="handleRestrictUser">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M6.58984 3.30859V7.41016H7.41016V3.30859H6.58984ZM6.58984 3.30859V7.41016H7.41016V3.30859H6.58984ZM9.88805 0H4.11195L0 4.11195V9.88805L4.11195 14H9.88805L14 9.88805V4.11195L9.88805 0ZM8.23047 11.5117H5.76953V9.05078H8.23047V11.5117ZM8.23047 8.23047H5.76953V2.48828H8.23047V8.23047ZM7.41016 9.87109H6.58984V10.6914H7.41016V9.87109ZM7.41016 3.30859H6.58984V7.41016H7.41016V3.30859Z" fill="#414141"/>
            </svg>
            <span>{{ isRestrictedUser ? 'Unrestrict' : 'Restrict' }}</span>
          </div>
          <div class="cnw-msg-profile-section-item" @click="handleProfileBlockUser">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M9.91667 7.14583C8.38892 7.14583 7.14583 8.38892 7.14583 9.91667C7.14583 11.4444 8.38892 12.6875 9.91667 12.6875C11.4444 12.6875 12.6875 11.4444 12.6875 9.91667C12.6875 8.38892 11.4444 7.14583 9.91667 7.14583ZM8.02083 9.91667C8.02083 8.87133 8.87133 8.02083 9.91667 8.02083C10.2807 8.02083 10.6178 8.12875 10.9072 8.30783L8.30783 10.9072C8.12933 10.6178 8.02083 10.2807 8.02083 9.91667ZM9.91667 11.8125C9.55267 11.8125 9.2155 11.7046 8.92617 11.5255L11.5255 8.92617C11.704 9.2155 11.8125 9.55267 11.8125 9.91667C11.8125 10.962 10.962 11.8125 9.91667 11.8125ZM7 6.85417C5.47225 6.85417 4.22917 5.61108 4.22917 4.08333C4.22917 2.55558 5.47225 1.3125 7 1.3125C8.52775 1.3125 9.77083 2.55558 9.77083 4.08333C9.77083 5.61108 8.52775 6.85417 7 6.85417ZM6.27083 9.91667C6.27083 10.6283 6.475 11.2933 6.83667 11.8592V12.25C6.83667 12.4892 6.63833 12.6875 6.39917 12.6875H3.5C2.61333 12.6875 1.89583 11.97 1.89583 11.0833C1.89583 9.23417 3.40083 7.72917 5.25 7.72917H6.41667C6.5975 7.72917 6.755 7.84 6.81917 7.9975C6.46917 8.5575 6.27083 9.21667 6.27083 9.91667Z" fill="#414141"/>
            </svg>
            <span>{{ blockedBy === 'me' ? 'Unblock User' : 'Block User' }}</span>
          </div>
          <div class="cnw-msg-profile-section-item" @click="handleReportUser">
            <svg width="14" height="14" viewBox="0 0 12.6872 11.5208" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M6.85429 8.89583C6.8541 9.3657 6.95768 9.8298 7.15763 10.255C6.77869 10.5878 6.29109 10.7703 5.78679 10.7683H2.08846C1.53507 10.7668 1.00479 10.5463 0.613485 10.155C0.22218 9.76367 0.0016657 9.23339 0.000126368 8.68C-0.0048244 8.10173 0.135767 7.53151 0.408959 7.02183C0.682152 6.51214 1.07917 6.07936 1.56346 5.76333C2.22057 6.33147 3.06022 6.64411 3.92888 6.64411C4.79754 6.64411 5.63718 6.33147 6.29429 5.76333C6.57424 5.94755 6.82412 6.17382 7.03513 6.43417C7.19042 6.61315 7.32541 6.80879 7.43763 7.0175C7.04731 7.56512 6.84286 8.22345 6.85429 8.89583Z" fill="#414141"/>
              <path d="M3.92596 6.11333C5.61411 6.11333 6.98263 4.74482 6.98263 3.05667C6.98263 1.36852 5.61411 0 3.92596 0C2.23781 0 0.869293 1.36852 0.869293 3.05667C0.869293 4.74482 2.23781 6.11333 3.92596 6.11333Z" fill="#414141"/>
              <path d="M10.0626 6.27083C9.65519 6.27013 9.25332 6.36534 8.88951 6.54877C8.5257 6.73219 8.21017 6.99868 7.96846 7.32667C7.62256 7.77647 7.43584 8.32841 7.43763 8.89583C7.43601 9.51253 7.65303 10.1098 8.05013 10.5817C8.33363 10.92 8.69789 11.1815 9.10921 11.3417C9.52053 11.502 9.96563 11.5559 10.4033 11.4986C10.841 11.4412 11.2572 11.2744 11.6133 11.0136C11.9695 10.7527 12.2541 10.4063 12.4408 10.0063C12.6275 9.60626 12.7104 9.16563 12.6817 8.72512C12.653 8.28461 12.5136 7.85846 12.2766 7.48608C12.0395 7.1137 11.7123 6.80712 11.3253 6.59471C10.9384 6.38229 10.5041 6.2709 10.0626 6.27083ZM10.0626 10.4242C9.9476 10.4242 9.83719 10.3789 9.75531 10.2981C9.67343 10.2173 9.62666 10.1075 9.62513 9.9925C9.62513 9.87647 9.67122 9.76519 9.75327 9.68314C9.83531 9.60109 9.94659 9.555 10.0626 9.555C10.1787 9.555 10.2899 9.60109 10.372 9.68314C10.454 9.76519 10.5001 9.87647 10.5001 9.9925C10.4986 10.1075 10.4518 10.2173 10.3699 10.2981C10.2881 10.3789 10.1777 10.4242 10.0626 10.4242ZM10.5001 8.82583C10.5001 8.94186 10.454 9.05314 10.372 9.13519C10.2899 9.21724 10.1787 9.26333 10.0626 9.26333C9.94659 9.26333 9.83531 9.21724 9.75327 9.13519C9.67122 9.05314 9.62513 8.94186 9.62513 8.82583V7.65917C9.62513 7.54313 9.67122 7.43185 9.75327 7.34981C9.83531 7.26776 9.94659 7.22167 10.0626 7.22167C10.1787 7.22167 10.2899 7.26776 10.372 7.34981C10.454 7.43185 10.5001 7.54313 10.5001 7.65917V8.82583Z" fill="#414141"/>
            </svg>
            <span>Report</span>
          </div>
        </template>
      </div>
    </div>

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
          <span class="cnw-msg-status-dot" :class="(!isUserRestricted(conv.other_user_id) && conv.is_online) ? 'online' : 'offline'"></span>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loadingList" class="cnw-msg-loading">Loading chats...</div>

      <!-- Conversation list -->
      <div v-else class="cnw-msg-conversations" ref="conversationsList" @scroll="onConversationsScroll">
        <div
          v-for="conv in conversations"
          :key="conv.other_user_id"
          class="cnw-msg-conv-card"
          :class="{ active: activeUserId === Number(conv.other_user_id), unread: !isUserRestricted(conv.other_user_id) && conv.unread_count > 0 }"
          @click="openConversation(conv)"
        >

          <div class="cnw-msg-conv-avatar-col">
            <div class="cnw-msg-conv-avatar-wrap">
              <img :src="conv.other_avatar" :alt="conv.other_name" class="cnw-social-worker-avatar" width="44" height="44" />
              <span class="cnw-msg-status-dot" :class="(!isUserRestricted(conv.other_user_id) && conv.is_online) ? 'online' : 'offline'"></span>
            </div>
            <div class="cnw-msg-avatar-dots" v-if="!isUserRestricted(conv.other_user_id) && typingUserId === Number(conv.other_user_id)">
              <span></span>
              <span></span>
              <span></span>
            </div>
          </div>
          <div class="cnw-msg-conv-body">
            <div class="cnw-msg-conv-top">
              <span class="cnw-msg-conv-status-label">{{ (!isUserRestricted(conv.other_user_id) && conv.is_online) ? 'Online' : 'Offline' }}</span>
            </div>
            <div class="cnw-msg-conv-name">{{ conv.other_name }}</div>
            <p class="cnw-msg-conv-preview">{{ truncate(conv.last_message, 60) }}</p>
            <div class="cnw-msg-conv-meta">
              <span class="cnw-msg-conv-time">{{ formatTime(conv.last_date) }}</span>
              <span v-if="!isUserRestricted(conv.other_user_id) && conv.unread_count > 0" class="cnw-msg-conv-read-badge unread">Unread</span>
              <span v-else-if="Number(conv.last_sender_id) === currentUserId" class="cnw-msg-conv-read-badge" :class="(!isUserRestricted(conv.other_user_id) && parseInt(conv.last_is_read)) ? 'read' : 'unread'">
                {{ (!isUserRestricted(conv.other_user_id) && parseInt(conv.last_is_read)) ? 'Read' : 'Sent' }}
              </span>
            </div>
          </div>
        </div>

        <div v-if="!loadingList && !conversations.length" class="cnw-msg-empty">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--border)" stroke-width="1.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          <p>No conversations yet</p>
        </div>
        <div v-if="loadingMoreConvs" class="cnw-msg-loading-older">Loading more...</div>
      </div>
    </div>

    <!-- Right: Chat detail -->
    <div class="cnw-msg-detail">
      <template v-if="activeUserId">
        <!-- Chat header -->
        <div class="cnw-msg-detail-header">
          <button class="cnw-msg-back-btn" @click="backToList" title="Back">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
          </button>
          <img :src="otherUser.avatar" :alt="otherUser.name" class="cnw-social-worker-avatar cnw-msg-header-clickable" width="44" height="44" @click="toggleProfile" />
          <div class="cnw-msg-header-clickable" @click="toggleProfile">
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
          <router-link :to="'/users/' + activeUserId" class="cnw-msg-header-profile-link" title="View Profile">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
          </router-link>
        </div>

        <!-- Messages -->
        <div class="cnw-msg-detail-messages" ref="messagesContainer" @scroll="onMessagesScroll">
          <div v-if="loadingOlder" class="cnw-msg-loading-older">Loading older messages...</div>
          <div v-if="loadingMessages" class="cnw-msg-loading">Loading messages...</div>
          <div
            v-for="msg in messages"
            :key="msg.id"
            class="cnw-msg-bubble-wrap"
            :class="{ mine: Number(msg.sender_id) === currentUserId }"
          >
            <div class="cnw-msg-bubble-card" :data-msg-id="msg.id">
              <template v-if="Number(msg.sender_id) !== currentUserId">
                <div class="cnw-msg-conv-avatar-wrap">
                  <img
                    :src="msg.sender_avatar"
                    :alt="msg.sender_name"
                    class="cnw-social-worker-avatar"
                    width="34"
                    height="34"
                  />
                  <span class="cnw-msg-status-dot" :class="(!isRestrictedUser && otherUser.is_online) ? 'online' : 'offline'"></span>
                </div>
              </template>
              <div class="cnw-msg-conv-body">
                <template v-if="Number(msg.sender_id) !== currentUserId">
                  <div class="cnw-msg-conv-top">
                    <span class="cnw-msg-conv-status-label">{{ (!isRestrictedUser && otherUser.is_online) ? 'Online' : 'Offline' }}</span>
                  </div>
                  <div class="cnw-msg-conv-name">{{ msg.sender_name }}</div>
                </template>
                <p class="cnw-msg-conv-preview">{{ msg.content }}</p>
                <div class="cnw-msg-conv-meta">
                  <span class="cnw-msg-conv-time">{{ formatTime(msg.created_at) }}</span>
                  <span v-if="Number(msg.sender_id) === currentUserId" class="cnw-msg-conv-read-badge" :class="(!isRestrictedUser && parseInt(msg.is_read)) ? 'read' : 'unread'">
                    {{ (!isRestrictedUser && parseInt(msg.is_read)) ? 'Read' : 'Sent' }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Typing indicator -->
          <div v-if="otherUserTyping" class="cnw-msg-typing-indicator">
            <div class="cnw-msg-typing-dots">
              <span></span>
              <span></span>
              <span></span>
            </div>
            <span class="cnw-msg-typing-label">{{ otherUser.name }} is typing</span>
          </div>
        </div>

        <!-- Input -->
        <div v-if="isConnected" class="cnw-msg-input-bar">
          <textarea
            v-model="newMessage"
            @keydown.enter.exact.prevent="sendMsg"
            @input="onTyping"
            placeholder="Write Message:"
            class="cnw-msg-input"
            name="message"
          ></textarea>
          <button class="cnw-msg-send-btn" @click="sendMsg">Send</button>
        </div>
        <div v-else class="cnw-msg-not-connected">
          <div class="cnw-msg-not-connected-line">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
            You can't send messages to this user because you are not connected.
          </div>
          <div v-if="blockedBy" class="cnw-msg-blocked-notice">
            {{ blockedBy === 'me' ? 'You blocked this person.' : 'This person blocked you.' }}
          </div>
        </div>
      </template>

      <div v-else class="cnw-msg-compose-panel">
        <!-- To: search header -->
        <div class="cnw-msg-compose-to">
          <span class="cnw-msg-compose-to-label">To:</span>
          <input
            v-model="composeSearch"
            @input="onComposeSearch"
            type="text"
            placeholder=""
            class="cnw-msg-compose-to-input"
          />
        </div>
        <div class="cnw-msg-compose-divider"></div>

        <!-- Filtered search results -->
        <template v-if="composeSearch.trim() && composeFilteredUsers.length">
          <div class="cnw-msg-compose-section-label">Results</div>
          <div class="cnw-msg-compose-user-list">
            <div
              v-for="user in composeFilteredUsers"
              :key="'compose-' + user.id"
              class="cnw-msg-compose-user-item"
              @click="startConversation(user)"
            >
              <div class="cnw-msg-conv-avatar-wrap">
                <img :src="user.avatar" :alt="user.name" class="cnw-social-worker-avatar" width="34" height="34" />
                <span class="cnw-msg-status-dot" :class="(!isUserRestricted(user.id) && user.is_online) ? 'online' : 'offline'"></span>
              </div>
              <span class="cnw-msg-compose-user-name">{{ user.name }}</span>
            </div>
          </div>
        </template>
        <template v-else-if="composeSearch.trim() && !composeFilteredUsers.length">
          <div class="cnw-msg-compose-section-label">Results</div>
          <div class="cnw-msg-compose-no-results">No users found</div>
        </template>

        <!-- Suggested users (shown when not searching) -->
        <template v-if="!composeSearch.trim()">
          <div class="cnw-msg-compose-section-label">Suggested</div>
          <div v-if="loadingSuggested" class="cnw-msg-loading">Loading...</div>
          <div v-else class="cnw-msg-compose-user-list">
            <div
              v-for="user in suggestedUsers"
              :key="'suggested-' + user.id"
              class="cnw-msg-compose-user-item"
              @click="startConversation(user)"
            >
              <div class="cnw-msg-conv-avatar-wrap">
                <img :src="user.avatar" :alt="user.name" class="cnw-social-worker-avatar" width="34" height="34" />
                <span class="cnw-msg-status-dot" :class="(!isUserRestricted(user.id) && user.is_online) ? 'online' : 'offline'"></span>
              </div>
              <span class="cnw-msg-compose-user-name">{{ user.name }}</span>
            </div>
            <div v-if="!suggestedUsers.length" class="cnw-msg-compose-no-results">No connections yet</div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import { getConversations, getConversation, sendMessage, markConversationRead, setTyping, getConnections, removeConnection, submitReport, restrictUser, unrestrictUser, getRestrictions } from '@/api';
import { getUserChannel } from '@/pusher';

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
      otherUserTyping: false,
      typingUserId: null,
      typingTimeout: null,
      typingClearTimeout: null,
      pusherChannel: null,
      hasMoreMessages: false,
      loadingOlder: false,
      convPage: 1,
      hasMoreConvs: false,
      loadingMoreConvs: false,
      mobileShowDetail: false,
      showHeaderMenu: false,
      isConnected: true,
      blockedBy: null,
      showProfile: false,
      profileChatInfoOpen: true,
      profileMediaOpen: true,
      profilePrivacyOpen: true,
      profileSearchMessages: false,
      profileSearchQuery: '',
      profileSearchResults: [],
      showPinnedMessages: false,
      showMediaGallery: false,
      showSharedFiles: false,
      mutedUsers: JSON.parse(localStorage.getItem('cnw_muted_users') || '[]'),
      restrictedUsers: [],
      restrictedByUsers: [],
      suggestedUsers: [],
      loadingSuggested: false,
      composeSearch: '',
      composeFilteredUsers: [],
      composeSearchTimeout: null,
    };
  },
  computed: {
    isMutedUser() {
      return this.mutedUsers.includes(this.activeUserId);
    },
    isRestrictedUser() {
      return this.restrictedUsers.includes(this.activeUserId) || this.restrictedByUsers.includes(this.activeUserId);
    },
  },
  async mounted() {
    // Register event listener BEFORE async calls so it's ready immediately
    this._startChatHandler = (e) => {
      if (e.detail) this.startConversation(e.detail);
    };
    window.addEventListener('cnw-start-chat', this._startChatHandler);

    // Check if a start-chat request was queued before we mounted
    if (window._cnwPendingChat) {
      const user = window._cnwPendingChat;
      window._cnwPendingChat = null;
      this.startConversation(user);
    }

    await this.loadRestrictions();
    await this.loadConversations();
    this.loadSuggestedUsers();
    this.initPusher();
    this.syncHeightWithSidebar();

    this._closeHeaderMenu = () => { this.showHeaderMenu = false; };
    document.addEventListener('click', this._closeHeaderMenu);
  },
  beforeUnmount() {
    if (this.typingTimeout) clearTimeout(this.typingTimeout);
    if (this.typingClearTimeout) clearTimeout(this.typingClearTimeout);
    if (this.pusherChannel) {
      this.pusherChannel.unbind('new-message', this._onPusherMessage);
      this.pusherChannel.unbind('client-typing', this._onPusherTyping);
      this.pusherChannel.unbind('user-status', this._onPusherStatus);
      this.pusherChannel.unbind('messages-read', this._onPusherRead);
      this.pusherChannel.unbind('connection-blocked', this._onPusherBlocked);
      this.pusherChannel.unbind('connection-unblocked', this._onPusherUnblocked);
      this.pusherChannel.unbind('connection-removed', this._onPusherConnectionRemoved);
      this.pusherChannel.unbind('user-restricted', this._onPusherRestricted);
      this.pusherChannel.unbind('user-unrestricted', this._onPusherUnrestricted);
    }
    window.removeEventListener('cnw-start-chat', this._startChatHandler);
    document.removeEventListener('click', this._closeHeaderMenu);
    if (this._sidebarObserver) this._sidebarObserver.disconnect();
  },
  methods: {
    toggleProfile() {
      this.showProfile = !this.showProfile;
      if (!this.showProfile) {
        this.profileSearchMessages = false;
        this.profileSearchQuery = '';
        this.profileSearchResults = [];
        this.showPinnedMessages = false;
        this.showMediaGallery = false;
        this.showSharedFiles = false;
      }
    },
    toggleMuteUser() {
      const userId = this.activeUserId;
      if (!userId) return;
      const idx = this.mutedUsers.indexOf(userId);
      if (idx > -1) {
        this.mutedUsers.splice(idx, 1);
      } else {
        this.mutedUsers.push(userId);
      }
      localStorage.setItem('cnw_muted_users', JSON.stringify(this.mutedUsers));
    },
    toggleProfileSearch() {
      this.profileSearchMessages = !this.profileSearchMessages;
      this.profileSearchQuery = '';
      this.profileSearchResults = [];
      if (this.profileSearchMessages) {
        this.$nextTick(() => {
          if (this.$refs.profileSearchInput) this.$refs.profileSearchInput.focus();
        });
      }
    },
    onProfileSearchMessages() {
      const q = this.profileSearchQuery.trim().toLowerCase();
      if (!q) { this.profileSearchResults = []; return; }
      this.profileSearchResults = this.messages.filter(m =>
        m.content && m.content.toLowerCase().includes(q)
      );
    },
    scrollToMessage(msgId) {
      this.showProfile = false;
      this.$nextTick(() => {
        const container = this.$refs.messagesContainer;
        if (!container) return;
        const el = container.querySelector(`.cnw-msg-bubble-wrap [data-msg-id="${msgId}"]`);
        if (el) {
          el.scrollIntoView({ behavior: 'smooth', block: 'center' });
          el.classList.add('cnw-msg-highlight');
          setTimeout(() => el.classList.remove('cnw-msg-highlight'), 2000);
        }
      });
    },
    viewPinnedMessages() {
      this.showPinnedMessages = !this.showPinnedMessages;
    },
    async loadRestrictions() {
      try {
        const data = await getRestrictions();
        this.restrictedUsers = data.restricted || [];
        this.restrictedByUsers = data.restricted_by || [];
      } catch { /* ignore */ }
    },
    async handleRestrictUser() {
      if (!this.activeUserId) return;
      const userId = this.activeUserId;
      const idx = this.restrictedUsers.indexOf(userId);
      if (idx > -1) {
        await unrestrictUser(userId);
        this.restrictedUsers.splice(idx, 1);
        // Reload to restore real online status
        this.loadConversations();
        this.loadMessages();
      } else {
        if (!confirm('Restrict ' + this.otherUser.name + '? They will not be able to see when you are online or if you have read their messages.')) return;
        await restrictUser(userId);
        this.restrictedUsers.push(userId);
      }
    },
    handleProfileBlockUser() {
      if (!this.activeUserId) return;
      if (this.blockedBy === 'me') {
        this.handleUnblockUser();
      } else {
        this.handleBlockUser();
      }
    },
    async handleReportUser() {
      if (!this.activeUserId) return;
      const description = prompt('Please describe why you are reporting ' + this.otherUser.name + ':');
      if (!description || !description.trim()) return;
      const profileLink = window.location.origin + window.location.pathname + '#/users/' + this.activeUserId;
      try {
        await submitReport({
          type: 'user',
          subject: 'Report user: ' + this.otherUser.name,
          link: profileLink,
          description: description.trim(),
          priority: 'medium',
        });
        alert('Report submitted successfully. Our team will review it shortly.');
      } catch {
        alert('Failed to submit report. Please try again.');
      }
    },
    async loadSuggestedUsers() {
      this.loadingSuggested = true;
      try {
        const data = await getConnections({ page: 1 });
        this.suggestedUsers = (data.users || []).slice(0, 10);
      } catch { this.suggestedUsers = []; }
      this.loadingSuggested = false;
    },
    onComposeSearch() {
      clearTimeout(this.composeSearchTimeout);
      if (!this.composeSearch.trim()) { this.composeFilteredUsers = []; return; }
      this.composeSearchTimeout = setTimeout(async () => {
        try {
          const data = await getConnections({ search: this.composeSearch });
          this.composeFilteredUsers = (data.users || []).slice(0, 10);
        } catch { this.composeFilteredUsers = []; }
      }, 300);
    },
    initPusher() {
      const channel = getUserChannel();
      if (!channel) return;
      this.pusherChannel = channel;

      this._onPusherMessage = (data) => {
        const senderId = Number(data.sender_id);

        // Update conversation list locally
        this.updateConversationFromMessage(data);

        // If from the active conversation, append it live
        if (this.activeUserId && senderId === this.activeUserId) {
          const exists = this.messages.some(m => Number(m.id) === Number(data.id));
          if (!exists) {
            this.messages.push(data);
            this.$nextTick(() => this.scrollToBottom());
          }
          this.otherUserTyping = false;
          if (!this.restrictedUsers.includes(senderId)) {
            markConversationRead(this.activeUserId).then(() => {
              window.dispatchEvent(new CustomEvent('cnw-messages-read'));
            }).catch(() => {});
          }
        }
      };

      this._onPusherTyping = (data) => {
        const userId = Number(data.user_id);
        if (this.restrictedUsers.includes(userId)) return;
        this.typingUserId = userId;
        if (this.activeUserId && userId === this.activeUserId) {
          this.otherUserTyping = true;
          this.$nextTick(() => this.scrollToBottom());
        }
        if (this.typingClearTimeout) clearTimeout(this.typingClearTimeout);
        this.typingClearTimeout = setTimeout(() => {
          this.otherUserTyping = false;
          this.typingUserId = null;
        }, 4000);
      };

      this._onPusherStatus = (data) => {
        const userId = Number(data.user_id);
        const isOnline = data.status === 'online';
        // Update in conversation list
        this.conversations.forEach(conv => {
          if (Number(conv.other_user_id) === userId) {
            conv.is_online = isOnline;
          }
        });
        // Update active chat header
        if (this.activeUserId === userId) {
          this.otherUser = { ...this.otherUser, is_online: isOnline };
        }
      };

      this._onPusherRead = (data) => {
        const readerId = Number(data.reader_id);
        if (this.restrictedUsers.includes(readerId)) return;
        // Update message bubbles
        if (this.activeUserId === readerId) {
          this.messages.forEach(m => {
            if (Number(m.sender_id) === this.currentUserId) {
              m.is_read = 1;
            }
          });
        }
        // Update conversation card badge
        const conv = this.conversations.find(c => Number(c.other_user_id) === readerId);
        if (conv && Number(conv.last_sender_id) === this.currentUserId) {
          conv.last_is_read = 1;
        }
      };

      this._onPusherBlocked = (data) => {
        const blockerId = Number(data.blocker_id);
        // Update conversation
        const conv = this.conversations.find(c => Number(c.other_user_id) === blockerId);
        if (conv) { conv.is_connected = false; conv.blocked_by = 'them'; }
        // Update active chat
        if (this.activeUserId === blockerId) {
          this.isConnected = false;
          this.blockedBy = 'them';
        }
      };

      this._onPusherUnblocked = (data) => {
        const unblockerId = Number(data.unblocker_id);
        const conv = this.conversations.find(c => Number(c.other_user_id) === unblockerId);
        if (conv) { conv.blocked_by = null; }
        if (this.activeUserId === unblockerId) {
          this.blockedBy = null;
        }
      };

      this._onPusherConnectionRemoved = (data) => {
        const removerId = Number(data.remover_id);
        const conv = this.conversations.find(c => Number(c.other_user_id) === removerId);
        if (conv) { conv.is_connected = false; }
        if (this.activeUserId === removerId) {
          this.isConnected = false;
        }
      };

      this._onPusherRestricted = (data) => {
        const restricterId = Number(data.restricter_id);
        if (!this.restrictedByUsers.includes(restricterId)) {
          this.restrictedByUsers.push(restricterId);
        }
        // Force offline in conversation list
        const conv = this.conversations.find(c => Number(c.other_user_id) === restricterId);
        if (conv) { conv.is_online = false; }
        // Force offline in active chat
        if (this.activeUserId === restricterId) {
          this.otherUser = { ...this.otherUser, is_online: false };
        }
      };

      this._onPusherUnrestricted = (data) => {
        const restricterId = Number(data.restricter_id);
        const idx = this.restrictedByUsers.indexOf(restricterId);
        if (idx > -1) this.restrictedByUsers.splice(idx, 1);
        // Reload conversations to restore real online status
        this.loadConversations();
        if (this.activeUserId === restricterId) {
          this.loadMessages();
        }
      };

      channel.bind('new-message', this._onPusherMessage);
      channel.bind('client-typing', this._onPusherTyping);
      channel.bind('user-status', this._onPusherStatus);
      channel.bind('messages-read', this._onPusherRead);
      channel.bind('connection-blocked', this._onPusherBlocked);
      channel.bind('connection-unblocked', this._onPusherUnblocked);
      channel.bind('connection-removed', this._onPusherConnectionRemoved);
      channel.bind('user-restricted', this._onPusherRestricted);
      channel.bind('user-unrestricted', this._onPusherUnrestricted);
    },
    updateConversationFromMessage(data) {
      const senderId = Number(data.sender_id);
      const existing = this.conversations.find(c => Number(c.other_user_id) === senderId);
      if (existing) {
        existing.last_message = data.content;
        existing.last_date = data.created_at;
        existing.last_sender_id = senderId;
        existing.last_is_read = this.activeUserId === senderId ? 1 : 0;
        if (this.activeUserId !== senderId) {
          existing.unread_count = (parseInt(existing.unread_count) || 0) + 1;
        }
        // Move to top
        const idx = this.conversations.indexOf(existing);
        if (idx > 0) {
          this.conversations.splice(idx, 1);
          this.conversations.unshift(existing);
        }
      } else {
        // New conversation from someone not in the list
        this.conversations.unshift({
          other_user_id: senderId,
          other_name: data.sender_name,
          other_avatar: data.sender_avatar,
          last_message: data.content,
          last_date: data.created_at,
          unread_count: 1,
          is_online: true,
        });
      }
    },
    async loadConversations() {
      this.loadingList = true;
      this.convPage = 1;
      try {
        const data = await getConversations({ page: 1 });
        this.conversations = data.conversations || [];
        this.hasMoreConvs = !!data.has_more;
      } catch { this.conversations = []; }
      this.loadingList = false;
    },
    onConversationsScroll() {
      const el = this.$refs.conversationsList;
      if (!el || !this.hasMoreConvs || this.loadingMoreConvs) return;
      if (el.scrollTop + el.clientHeight >= el.scrollHeight - 40) {
        this.loadMoreConversations();
      }
    },
    async loadMoreConversations() {
      if (this.loadingMoreConvs) return;
      this.loadingMoreConvs = true;
      this.convPage++;
      try {
        const data = await getConversations({ page: this.convPage });
        const more = data.conversations || [];
        this.conversations = [...this.conversations, ...more];
        this.hasMoreConvs = !!data.has_more;
      } catch { /* silent */ }
      this.loadingMoreConvs = false;
    },
    async openConversation(conv) {
      this.showProfile = false;
      this.profileSearchMessages = false;
      this.profileSearchQuery = '';
      this.profileSearchResults = [];
      this.showPinnedMessages = false;
      this.showMediaGallery = false;
      this.showSharedFiles = false;
      this.activeUserId = Number(conv.other_user_id);
      this.otherUser = {
        id: Number(conv.other_user_id),
        name: conv.other_name,
        avatar: conv.other_avatar,
        verified_label: conv.other_verified_label || '',
        is_online: !!conv.is_online,
      };
      this.isConnected = !!conv.is_connected;
      this.blockedBy = conv.blocked_by || null;
      this.otherUserTyping = false;
      this.mobileShowDetail = true;
      await this.loadMessages();
      if (conv.unread_count > 0 && !this.isRestrictedUser) {
        await markConversationRead(this.activeUserId);
        conv.unread_count = 0;
        window.dispatchEvent(new CustomEvent('cnw-messages-read'));
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
      this.isConnected = true;
      this.blockedBy = null;
      this.otherUserTyping = false;
      this.mobileShowDetail = true;
      await this.loadMessages();
    },
    backToList() {
      this.mobileShowDetail = false;
    },
    async loadMessages() {
      this.loadingMessages = true;
      this.hasMoreMessages = false;
      try {
        const data = await getConversation(this.activeUserId);
        this.messages = data.messages || [];
        this.hasMoreMessages = !!data.has_more;
        if (data.other_user) {
          this.otherUser = { ...data.other_user, is_online: this.otherUser.is_online ?? data.other_user.is_online ?? false };
        }
      } catch { this.messages = []; }
      this.loadingMessages = false;
      this.$nextTick(() => this.scrollToBottom());
    },
    onMessagesScroll() {
      const container = this.$refs.messagesContainer;
      if (!container || !this.hasMoreMessages || this.loadingOlder) return;
      if (container.scrollTop <= 40) {
        this.loadOlderMessages();
      }
    },
    async loadOlderMessages() {
      if (!this.messages.length || this.loadingOlder) return;
      this.loadingOlder = true;
      const oldestId = this.messages[0].id;
      const container = this.$refs.messagesContainer;
      const prevHeight = container.scrollHeight;
      try {
        const data = await getConversation(this.activeUserId, { before: oldestId });
        const older = data.messages || [];
        this.hasMoreMessages = !!data.has_more;
        if (older.length) {
          this.messages = [...older, ...this.messages];
          this.$nextTick(() => {
            container.scrollTop = container.scrollHeight - prevHeight;
          });
        }
      } catch { /* silent */ }
      this.loadingOlder = false;
    },
    async sendMsg() {
      if (!this.newMessage.trim() || this.sending) return;
      this.sending = true;
      const content = this.newMessage.trim();
      this.newMessage = '';
      try {
        const result = await sendMessage({ recipient_id: this.activeUserId, content });
        if (result.id) {
          this.messages.push({
            id: result.id,
            sender_id: this.currentUserId,
            sender_name: window.cnwData?.currentUser?.name || '',
            sender_avatar: window.cnwData?.currentUser?.avatar || '',
            content,
            is_read: 0,
            created_at: new Date().toISOString(),
          });
          this.$nextTick(() => this.scrollToBottom());
          // Update own conversation list locally
          this.updateOwnConversation(content);
        }
      } catch { /* silent */ }
      this.sending = false;
    },
    updateOwnConversation(content) {
      const existing = this.conversations.find(c => Number(c.other_user_id) === this.activeUserId);
      if (existing) {
        existing.last_message = content;
        existing.last_date = new Date().toISOString();
        existing.last_sender_id = this.currentUserId;
        existing.last_is_read = 0;
        const idx = this.conversations.indexOf(existing);
        if (idx > 0) {
          this.conversations.splice(idx, 1);
          this.conversations.unshift(existing);
        }
      } else {
        this.conversations.unshift({
          other_user_id: this.activeUserId,
          other_name: this.otherUser.name,
          other_avatar: this.otherUser.avatar,
          other_verified_label: this.otherUser.verified_label,
          last_message: content,
          last_date: new Date().toISOString(),
          last_sender_id: this.currentUserId,
          last_is_read: 0,
          unread_count: 0,
          is_online: true,
        });
      }
    },
    onTyping() {
      if (!this.activeUserId) return;
      if (this.isRestrictedUser) return;
      if (this.typingTimeout) return;
      setTyping(this.activeUserId).catch(() => {});
      this.typingTimeout = setTimeout(() => { this.typingTimeout = null; }, 3000);
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
          const data = await getConnections({ search: this.newChatSearch });
          this.searchResults = (data.users || []).slice(0, 5);
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
    isUserRestricted(userId) {
      const id = Number(userId);
      return this.restrictedUsers.includes(id) || this.restrictedByUsers.includes(id);
    },
    truncate(str, len) {
      if (!str) return '';
      return str.length > len ? str.substring(0, len) + '..' : str;
    },
    syncHeightWithSidebar() {
      const sidebar = document.querySelector('.cnw-social-worker-sidebar');
      const msgEl = this.$el;
      if (!sidebar || !msgEl) return;
      const applyHeight = () => {
        const h = sidebar.offsetHeight;
        if (h > 0) msgEl.style.maxHeight = h + 'px';
      };
      applyHeight();
      this._sidebarObserver = new ResizeObserver(applyHeight);
      this._sidebarObserver.observe(sidebar);
    },
    async handleRemoveConnection() {
      this.showHeaderMenu = false;
      if (!confirm('Remove connection with ' + this.otherUser.name + '? You will no longer be able to message each other.')) return;
      try {
        await removeConnection(this.activeUserId);
        this.isConnected = false;
        // Update the conversation object too
        const conv = this.conversations.find(c => Number(c.other_user_id) === this.activeUserId);
        if (conv) conv.is_connected = false;
        window.dispatchEvent(new CustomEvent('cnw-connections-updated'));
      } catch { /* silent */ }
    },
    handleBlockUser() {
      this.showHeaderMenu = false;
      if (!confirm('Block ' + this.otherUser.name + '? This will remove your connection and they will not be able to send you connection requests.')) return;
      this.blockUser();
    },
    async blockUser() {
      try {
        const url = (window.cnwData?.restUrl || '') + '/connections/' + this.activeUserId + '/block';
        await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.cnwData?.nonce || '' } });
        this.isConnected = false;
        this.blockedBy = 'me';
        const conv = this.conversations.find(c => Number(c.other_user_id) === this.activeUserId);
        if (conv) { conv.is_connected = false; conv.blocked_by = 'me'; }
        window.dispatchEvent(new CustomEvent('cnw-connections-updated'));
      } catch { /* silent */ }
    },
    async handleUnblockUser() {
      this.showHeaderMenu = false;
      try {
        const url = (window.cnwData?.restUrl || '') + '/connections/' + this.activeUserId + '/unblock';
        await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.cnwData?.nonce || '' } });
        this.blockedBy = null;
        const conv = this.conversations.find(c => Number(c.other_user_id) === this.activeUserId);
        if (conv) conv.blocked_by = null;
        window.dispatchEvent(new CustomEvent('cnw-connections-updated'));
      } catch { /* silent */ }
    },
  },
};
</script>

<style>


main.cnw-social-worker-main.messages-view
{
  background: transparent;
  padding: 0;
}


.cnw-msg {
  display: flex;
  gap: 0;
  height: 100%;
  overflow: hidden;
}

/* ── Profile panel ────────────────────────── */
.cnw-msg-profile-panel {
  width: 377px;
  min-width: 377px;
  background: #fff;
  border-radius: var(--radius);
  box-shadow: var(--shadow-sm);
  display: flex;
  flex-direction: column;
  gap: 20px;
  overflow-y: auto;
  padding: 40px 28px;
  position: relative;
  scrollbar-width: none;
  -ms-overflow-style: none;
}
.cnw-msg-profile-panel::-webkit-scrollbar { display: none; }
.cnw-msg-profile-close {
  position: absolute;
  top: 12px;
  right: 12px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  color: #999;
  border-radius: 50%;
}
.cnw-msg-profile-close:hover { background: #f0f0f0; color: #414141; }
.cnw-msg-profile-top {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 7px;
}
.cnw-msg-profile-avatar-wrap img {
  width: 87px;
  height: 87px;
  border-radius: 50%;
  object-fit: cover;
}
.cnw-msg-profile-name {
  font-family: 'Poppins', sans-serif;
  font-size: 18px;
  font-weight: 600;
  color: #000;
  text-align: center;
  line-height: 24.5px;
}
.cnw-msg-profile-actions {
  display: flex;
  gap: 14px;
  justify-content: center;
}
.cnw-msg-profile-action-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 5px;
  cursor: pointer;
  padding: 0 3px;
}
.cnw-msg-profile-action-btn span {
  font-family: 'Poppins', sans-serif;
  font-size: 14px;
  font-weight: 300;
  color: #414141;
  line-height: 16px;
}
.cnw-msg-profile-action-btn:hover { opacity: 0.7; }
.cnw-msg-profile-section {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.cnw-msg-profile-section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
  padding: 0;
}
.cnw-msg-profile-section-header span {
  font-family: 'Poppins', sans-serif;
  font-size: 14px;
  font-weight: 300;
  color: #414141;
}
.cnw-msg-profile-chevron {
  transition: transform 0.2s;
  color: #414141;
}
.cnw-msg-profile-chevron.cnw-chevron-open {
  transform: rotate(180deg);
}
.cnw-msg-profile-section-item {
  display: flex;
  align-items: center;
  gap: 5px;
  cursor: pointer;
  padding: 2px 0;
}
.cnw-msg-profile-section-item span {
  font-family: 'Poppins', sans-serif;
  font-size: 14px;
  font-weight: 300;
  color: #414141;
}
.cnw-msg-profile-section-item:hover { opacity: 0.7; }
.cnw-msg-profile-section-notice {
  font-size: 13px;
  color: #999;
  padding: 4px 0 4px 19px;
  font-style: italic;
}
.cnw-muted { opacity: 0.5; }
.cnw-active-action svg path { fill: var(--primary); }

/* Profile search messages */
.cnw-msg-profile-search-box {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.cnw-msg-profile-search-input {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  font-size: 13px;
  font-family: 'Poppins', sans-serif;
  outline: none;
}
.cnw-msg-profile-search-input:focus { border-color: var(--primary); }
.cnw-msg-profile-search-count {
  font-size: 12px;
  color: #999;
}
.cnw-msg-profile-search-results {
  display: flex;
  flex-direction: column;
  gap: 6px;
  max-height: 200px;
  overflow-y: auto;
}
.cnw-msg-profile-search-result-item {
  padding: 8px 10px;
  border-radius: 4px;
  cursor: pointer;
  border: 1px solid var(--border);
}
.cnw-msg-profile-search-result-item:hover { background: #f5f5f5; }
.cnw-msg-profile-search-result-name {
  font-size: 12px;
  font-weight: 600;
  color: #414141;
}
.cnw-msg-profile-search-result-text {
  font-size: 12px;
  color: #666;
  margin-top: 2px;
}
.cnw-msg-profile-search-result-time {
  font-size: 11px;
  color: #999;
  margin-top: 2px;
}

/* Message highlight when scrolled to from search */
@keyframes cnw-msg-highlight-fade {
  0% { background: rgba(59, 189, 212, 0.25); }
  100% { background: transparent; }
}
.cnw-msg-highlight {
  animation: cnw-msg-highlight-fade 2s ease-out;
  border-radius: var(--radius);
}

/* Clickable header avatar/name */
.cnw-msg-header-clickable { cursor: pointer; }
.cnw-msg-header-clickable:hover { opacity: 0.8; }
.cnw-msg-header-profile-link {
  margin-left: auto;
  color: var(--text-light);
  display: flex;
  align-items: center;
  padding: 6px;
  border-radius: 50%;
  transition: background 0.15s, color 0.15s;
}
.cnw-msg-header-profile-link:hover {
  background: var(--bg-light, #f0f0f0);
  color: var(--primary);
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
  column-gap: 15px;
  
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
  overflow-y: auto;
  flex: 1;
  min-height: 0;
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
.cnw-msg-conv-card.unread {
  background: var(--secondary);
}
.cnw-msg-conv-card.unread * {
  color: #fff;
}
.cnw-msg-conv-menu {
  background: none;
  border: none;
  color: var(--text-light);
  padding: 2px;
  flex-shrink: 0;
  margin-top: 2px;
}
.cnw-msg-conv-avatar-col {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex-shrink: 0;
  gap: 6px;
}
.cnw-msg-avatar-dots {
  display: flex;
  align-items: center;
  gap: 3px;
  margin-top: 5px;
}
.cnw-msg-avatar-dots span {
  width: 4px;
  height: 4px;
  border-radius: 50%;
  background: var(--teal);
  animation: cnw-dot-wave 1.2s ease-in-out infinite;
}
.cnw-msg-avatar-dots span:nth-child(1) { animation-delay: 0s; }
.cnw-msg-avatar-dots span:nth-child(2) { animation-delay: 0.2s; }
.cnw-msg-avatar-dots span:nth-child(3) { animation-delay: 0.4s; }
@keyframes cnw-dot-wave {
  0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
  30% { transform: translateY(-6px); opacity: 1; }
}
.cnw-msg-conv-card.active .cnw-msg-avatar-dots span {
  background: #fff;
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
  padding: var(--space-l);
}
.cnw-msg-detail-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding-bottom: var(--space-s);
  border-bottom: 1px solid var(--primary);
  margin-bottom: var(--space-s);
}
.cnw-msg-back-btn {
  display: none;
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  color: var(--text-dark);
  border-radius: 50%;
  flex-shrink: 0;
}
.cnw-msg-back-btn:hover {
  background: rgba(0,0,0,0.06);
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

/* Header menu (3-dots) */
.cnw-msg-header-menu-wrap {
  position: relative;
  margin-left: auto;
}
.cnw-msg-dots-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px 6px;
  border-radius: 6px;
  color: var(--text-light);
  display: flex;
  align-items: center;
}
.cnw-msg-dots-btn:hover {
  background: var(--bg-light);
  color: var(--text-dark);
}
.cnw-msg-header-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  background: #fff;
  border: 1px solid var(--border);
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,.12);
  min-width: 180px;
  z-index: 100;
  padding: 4px 0;
}
.cnw-msg-header-dropdown-item {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
  padding: 8px 14px;
  border: none;
  background: none;
  cursor: pointer;
  font-size: 13px;
  color: var(--text-dark);
  white-space: nowrap;
}
.cnw-msg-header-dropdown-item:hover {
  background: var(--bg-light);
}
.cnw-msg-header-dropdown-item.cnw-msg-header-dropdown-danger {
  color: #dc3545;
}
.cnw-msg-header-dropdown-item.cnw-msg-header-dropdown-danger:hover {
  background: #fff5f5;
}

/* Messages area */
.cnw-msg-detail-messages {
  flex: 1;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.cnw-msg-bubble-wrap {
  display: flex;
  max-width: 75%;
}
.cnw-msg-bubble-wrap.mine {
  margin-left: auto;
}
.cnw-msg-bubble-card {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 14px;
  border-radius: var(--radius);
  border: 1px solid var(--secondary);
  background: var(--secondary);
  width: 100%;
}
.cnw-msg-bubble-card * {
  color: var(--light);
}
.cnw-msg-bubble-wrap.mine .cnw-msg-bubble-card {
  background: var(--primary);
  border-color: var(--primary);
}
.cnw-msg-bubble-card .cnw-msg-conv-preview {
  white-space: normal;
}

/* Input bar */
.cnw-msg-input-bar {
  position: relative;
  margin-top: var(--space-s);
}
.cnw-msg-input {
  width: 100%;
  padding: 10px 14px;
  border: none;
  outline: none;
  border-radius: var(--radius-sm);
  font-size: 13px;
  font-family: inherit;
  resize: none;
  background: #F7F7F7;
  height: 137px;
}
.cnw-msg-input:focus { border: none; outline: none; }
.cnw-msg-send-btn {
  position: absolute;
  bottom: 20px;
  right: 20px;
  padding: 8px 24px;
  background: var(--primary);
  color: #fff;
  border: none;
  border-radius: 2px;
  font-size: var(--text-xs);
  font-weight: 300;
  cursor: pointer;
  transition: background 0.15s;
}
.cnw-msg-send-btn:hover { background: var(--secondary); }
.cnw-msg-send-btn:disabled { opacity: 0.5; cursor: not-allowed; }

/* Not connected notice */
.cnw-msg-not-connected {
  padding: 14px 20px;
  background: #fff5f5;
  color: #888;
  font-size: 13px;
  border-top: 1px solid var(--border);
}
.cnw-msg-not-connected-line {
  display: flex;
  align-items: center;
  gap: 8px;
}
.cnw-msg-not-connected-line svg {
  flex-shrink: 0;
  stroke: #999;
}
.cnw-msg-blocked-notice {
  margin-top: 6px;
  padding-left: 24px;
  color: #dc3545;
  font-size: 12px;
  font-weight: 600;
}

/* Disabled dropdown item */
.cnw-msg-header-dropdown-item:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
.cnw-msg-header-dropdown-item:disabled:hover {
  background: none;
}

/* Compose panel (select user to message) */
.cnw-msg-compose-panel {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 20px;
  overflow-y: auto;
}
.cnw-msg-compose-to {
  display: flex;
  align-items: center;
  gap: 0;
  height: 25px;
}
.cnw-msg-compose-to-label {
  font-family: 'Poppins', sans-serif;
  font-size: 18px;
  font-weight: 600;
  color: #414141;
  line-height: 24.5px;
  white-space: nowrap;
}
.cnw-msg-compose-to-input {
  flex: 1;
  border: none;
  outline: none;
  font-family: 'Poppins', sans-serif;
  font-size: 16px;
  font-weight: 300;
  color: #414141;
  padding: 0 8px;
  background: transparent;
}
.cnw-msg-compose-divider {
  height: 1px;
  background: var(--primary);
  width: 100%;
}
.cnw-msg-compose-section-label {
  font-family: 'Poppins', sans-serif;
  font-size: 14px;
  font-weight: 600;
  color: #999;
  line-height: 18.5px;
}
.cnw-msg-compose-user-list {
  display: flex;
  flex-direction: column;
  gap: 14px;
}
.cnw-msg-compose-user-item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 5px 0;
  border-radius: 4px;
  cursor: pointer;
  transition: background 0.15s;
}
.cnw-msg-compose-user-item:hover {
  background: #f5f5f5;
}
.cnw-msg-compose-user-name {
  font-family: 'Poppins', sans-serif;
  font-size: 16px;
  font-weight: 300;
  color: #414141;
  line-height: 1.32;
}
.cnw-msg-compose-no-results {
  font-size: 14px;
  color: #999;
  padding: 10px 0;
}

/* Empty & loading states */
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
.cnw-msg-loading-older {
  padding: 10px 20px;
  text-align: center;
  color: var(--text-light);
  font-size: 12px;
}

/* Typing indicator */
.cnw-msg-typing-indicator {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 14px;
}
.cnw-msg-typing-dots {
  display: flex;
  align-items: center;
  gap: 4px;
}
.cnw-msg-typing-dots span {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #999;
  animation: cnw-typing-bounce 1.4s infinite ease-in-out both;
}
.cnw-msg-typing-dots span:nth-child(1) { animation-delay: 0s; }
.cnw-msg-typing-dots span:nth-child(2) { animation-delay: 0.2s; }
.cnw-msg-typing-dots span:nth-child(3) { animation-delay: 0.4s; }
@keyframes cnw-typing-bounce {
  0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
  40% { transform: scale(1); opacity: 1; }
}
.cnw-msg-typing-label {
  font-size: 12px;
  color: #999;
  font-style: italic;
}

/* Hide scrollbars but keep scrolling */
.cnw-msg-conversations,
.cnw-msg-detail-messages {
  scrollbar-width: none; /* Firefox */
  -ms-overflow-style: none; /* IE/Edge */
}
.cnw-msg-conversations::-webkit-scrollbar,
.cnw-msg-detail-messages::-webkit-scrollbar {
  display: none; /* Chrome/Safari */
}

/* Responsive */
/* On desktop, only hide chat list when profile is open */
@media (min-width: 769px) {
  .cnw-msg.show-profile .cnw-msg-list { display: none; }
}

@media (max-width: 768px) {
  .cnw-msg { flex-direction: column; max-height: none; }
  .cnw-msg-list { width: 100%; min-width: 0; padding: 20px; }
  .cnw-msg-detail { margin-left: 0; min-height: 400px; display: none; padding: 20px; }
  .cnw-msg.mobile-show-detail .cnw-msg-list { display: none; }
  .cnw-msg.mobile-show-detail .cnw-msg-detail { display: flex; }
  .cnw-msg-back-btn { display: flex; align-items: center; justify-content: center; }
  .cnw-msg-profile-panel { width: 100%; min-width: 0; }
  .cnw-msg.show-profile .cnw-msg-list { display: none; }
  .cnw-msg.show-profile .cnw-msg-detail { display: none; }
}
</style>
