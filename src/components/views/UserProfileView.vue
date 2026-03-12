<template>
  <div class="cnw-profile-view">
    <!-- Loading skeleton -->
    <template v-if="loading">
      <div class="cnw-skeleton-card" style="flex-direction:row;gap:20px;padding:20px;align-items:flex-start">
        <div class="cnw-skeleton cnw-skeleton-circle" style="width:100px;height:100px"></div>
        <div style="flex:1;display:flex;flex-direction:column;gap:8px">
          <div class="cnw-skeleton cnw-skeleton-line-sm" style="width:20%"></div>
          <div class="cnw-skeleton cnw-skeleton-line-xl" style="width:40%"></div>
          <div class="cnw-skeleton cnw-skeleton-line" style="width:30%"></div>
          <div class="cnw-skeleton cnw-skeleton-line-sm" style="width:50%"></div>
        </div>
      </div>
      <div class="cnw-skeleton-card" style="gap:10px;margin-top:14px;padding:20px">
        <div class="cnw-skeleton cnw-skeleton-line-lg" style="width:25%"></div>
        <div class="cnw-skeleton cnw-skeleton-line" style="width:60%"></div>
        <div class="cnw-skeleton cnw-skeleton-line" style="width:55%"></div>
        <div class="cnw-skeleton cnw-skeleton-line" style="width:40%"></div>
      </div>
      <div class="cnw-skeleton-card" style="margin-top:14px;padding:16px">
        <div class="cnw-skeleton-row" style="gap:8px">
          <div v-for="n in 4" :key="n" class="cnw-skeleton cnw-skeleton-line" style="width:80px;height:30px;border-radius:var(--radius-m)"></div>
        </div>
      </div>
    </template>

    <!-- Error -->
    <div v-else-if="error" class="cnw-error-state">
      <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <p>{{ error }}</p>
      <button class="cnw-error-retry-btn" @click="loadProfile">Retry</button>
    </div>
    <template v-else>
      <!-- Profile Header -->
      <div class="profile-header">
        <div class="profile-header-left">
          <img :src="user.avatar || defaultAvatar" :alt="fullName" class="profile-avatar" width="100" height="100" />
          <button v-if="isOwn" class="profile-upload-btn" @click="triggerUpload">Upload New Photo</button>
          <input ref="fileInput" type="file" accept="image/*" style="display:none" @change="handleAvatarUpload" />
        </div>
        <div class="profile-header-info">
          <p class="profile-joined">Joined {{ joinedDate }}</p>
          <div class="profile-name-row">
            <h1 class="profile-name">{{ fullName }}</h1>
            <template v-if="user.suspension && user.suspension.is_suspended">
              <span class="profile-suspended-icon" title="Suspended">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
              </span>
              <span class="profile-suspended-label">{{ suspensionText }}</span>
            </template>
            <template v-else>
              <span class="cnw-social-worker-verified" title="Verified">&#10003;</span>
              <span class="profile-verified-label">{{ user.verified_label || 'Verified Social Worker' }}</span>
            </template>
          </div>
          <p class="profile-title">{{ user.professional_title || 'Licensed Clinical Social Worker' }}</p>
          <div v-if="!user.profile_restricted" class="profile-stats-row">
            <p class="profile-helpful">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" fill="#e53935"/></svg>
              {{ user.helpful_count || 0 }}&nbsp; Helpful answers
            </p>
            <span class="profile-stats-divider"></span>
            <p class="profile-reputation">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#f5a623" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
              {{ user.reputation || 0 }}&nbsp; Reputation
            </p>
          </div>
          <!-- Connection / Message buttons -->
          <div v-if="!isOwn && connectionStatus !== null && !user.profile_restricted" class="profile-action-buttons">
            <button v-if="user.can_message" class="profile-action-btn profile-action-message" @click="goToMessage">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
              Message
            </button>
            <template v-if="connectionStatus === 'connected'"></template>
            <template v-else-if="connectionStatus === 'pending_received'">
              <button class="profile-action-btn profile-action-accept" :disabled="connectionLoading" @click="handleAcceptConnection">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>
                {{ connectionLoading ? 'Accepting...' : 'Accept Request' }}
              </button>
              <button class="profile-action-btn profile-action-decline" :disabled="connectionLoading" @click="handleDeclineConnection">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                Decline
              </button>
            </template>
            <template v-else-if="connectionStatus === 'pending_sent'">
              <button class="profile-action-btn profile-action-pending" disabled>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Request Sent
              </button>
            </template>
            <template v-else-if="connectionStatus === 'none'">
              <button class="profile-action-btn profile-action-connect" :disabled="connectionLoading" @click="handleSendConnection">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                {{ connectionLoading ? 'Sending...' : 'Connect' }}
              </button>
            </template>
          </div>
        </div>
      </div>

      <!-- Profile Restricted Notice -->
      <div v-if="user.profile_restricted" class="profile-restricted-notice">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        <p>This profile is only visible to connections.</p>
        <button v-if="connectionStatus === 'none'" class="profile-action-btn profile-action-connect" :disabled="connectionLoading" @click="handleSendConnection">
          {{ connectionLoading ? 'Sending...' : 'Send Connection Request' }}
        </button>
        <p v-else-if="connectionStatus === 'pending_sent'" class="profile-restricted-pending">Connection request sent</p>
      </div>

      <!-- Anonymous Toggle -->
      <div v-if="isOwn && !user.profile_restricted" class="profile-anon-row">
        <button type="button" class="ask-anon-toggle" :class="{ 'is-active': user.anonymous }" @click="handleToggleAnonymous" role="switch" :aria-checked="!!user.anonymous">
          <span>Anonymous</span>
          <span class="toggle-track" :class="{ on: user.anonymous }" aria-hidden="true">
            <span class="toggle-thumb"></span>
          </span>
        </button>
      </div>

      <!-- Personal Info -->
      <div v-if="!user.profile_restricted" class="profile-info-card">
        <div class="profile-info-header">
          <h3>Personal Info</h3>
          <button v-if="isOwn" class="profile-edit-btn" @click="startEdit" aria-label="Edit personal info">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit
          </button>
        </div>
        <div class="profile-info-grid">
          <div class="profile-info-field">
            <label>Full Name:</label>
            <span>{{ fullName }}</span>
          </div>
          <div class="profile-info-field">
            <label>Email:</label>
            <span>{{ user.email || '---' }}</span>
          </div>
          <div class="profile-info-field">
            <label>Phone:</label>
            <span>{{ user.phone || '---' }}</span>
          </div>
        </div>
      </div>

      <!-- Edit Profile Modal -->
      <div v-if="editing && !user.profile_restricted" class="profile-modal-overlay" @click.self="cancelEdit">
        <div class="profile-modal" role="dialog" aria-modal="true" aria-labelledby="edit-profile-modal-title">
          <div class="profile-modal-header">
            <h3 id="edit-profile-modal-title">Edit Personal Info</h3>
            <button class="profile-modal-close" @click="cancelEdit" aria-label="Close">&times;</button>
          </div>
          <div class="profile-modal-body">
            <div class="profile-modal-field">
              <label>First Name</label>
              <input v-model="editForm.first_name" placeholder="First name" class="profile-input" />
            </div>
            <div class="profile-modal-field">
              <label>Last Name</label>
              <input v-model="editForm.last_name" placeholder="Last name" class="profile-input" />
            </div>
            <div class="profile-modal-field">
              <label>Email</label>
              <input v-model="editForm.email" placeholder="Email address" class="profile-input" />
            </div>
            <div class="profile-modal-field">
              <label>Phone</label>
              <input v-model="editForm.phone" placeholder="Phone number" class="profile-input" />
            </div>
            <div class="profile-modal-field">
              <label>Verified Label</label>
              <input v-model="editForm.verified_label" placeholder="e.g. Verified Social Worker" class="profile-input" />
            </div>
            <div class="profile-modal-field">
              <label>Professional Title</label>
              <input v-model="editForm.professional_title" placeholder="e.g. Licensed Clinical Social Worker" class="profile-input" />
            </div>
          </div>
          <div class="profile-modal-footer">
            <button class="profile-cancel-btn" @click="cancelEdit">Cancel</button>
            <button class="profile-save-btn" :disabled="saving" @click="saveProfile">{{ saving ? 'Saving...' : 'Save' }}</button>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div v-if="!user.profile_restricted" class="profile-tabs" role="tablist">
        <button v-for="tab in tabs" :key="tab.key" class="profile-tab" :class="{ 'is-active': activeTab === tab.key }" @click="activeTab = tab.key" role="tab" :aria-selected="activeTab === tab.key">
<svg v-if="tab.key === 'answers'" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
  <g clip-path="url(#clip0_20075_10658)">
    <path d="M12.2589 5.53003H5.11674C5.00295 5.52992 4.89026 5.55225 4.78511 5.59575C4.67996 5.63924 4.58443 5.70305 4.50397 5.78351C4.42351 5.86397 4.3597 5.95951 4.31621 6.06465C4.27271 6.1698 4.25038 6.28249 4.25049 6.39628V10.4716C4.25064 10.7013 4.34195 10.9215 4.50437 11.084C4.66679 11.2464 4.88704 11.3377 5.11674 11.3378H9.87018C9.89291 11.3377 9.91544 11.3421 9.93646 11.3507C9.95749 11.3594 9.97659 11.3721 9.99268 11.3882L10.8502 12.2457C10.9109 12.3062 10.9882 12.3475 11.0724 12.3642C11.1565 12.3809 11.2437 12.3724 11.323 12.3396C11.4023 12.3069 11.4701 12.2514 11.5179 12.1802C11.5658 12.109 11.5914 12.0252 11.5917 11.9394V11.5107C11.5923 11.4646 11.611 11.4207 11.6438 11.3884C11.6765 11.356 11.7207 11.3379 11.7667 11.3379H12.2589C12.4885 11.3373 12.7085 11.2459 12.8709 11.0836C13.0332 10.9212 13.1247 10.7012 13.1252 10.4716V6.39628C13.125 6.16658 13.0337 5.94634 12.8713 5.78392C12.7089 5.6215 12.4886 5.53018 12.2589 5.53003ZM11.3577 9.34722C11.3028 9.36524 11.2431 9.36087 11.1915 9.33506C11.1399 9.30925 11.1005 9.26408 11.0821 9.2094L10.8939 8.64284H9.92705L9.74549 9.17878C9.72633 9.23277 9.68677 9.2771 9.63531 9.30226C9.58384 9.32743 9.52457 9.33142 9.47019 9.31339C9.41581 9.29536 9.37067 9.25674 9.34444 9.2058C9.31821 9.15487 9.31298 9.09569 9.32988 9.04095L9.99705 7.06128C10.0277 6.97544 10.0842 6.9012 10.1588 6.84879C10.2333 6.79638 10.3223 6.76837 10.4135 6.76861C10.5046 6.76885 10.5934 6.79734 10.6677 6.85015C10.742 6.90295 10.7981 6.97749 10.8283 7.06349L11.4977 9.07159C11.5066 9.09888 11.5101 9.12767 11.5079 9.15631C11.5057 9.18495 11.4979 9.21287 11.4849 9.23848C11.4719 9.26409 11.454 9.28687 11.4321 9.30553C11.4103 9.32419 11.385 9.33836 11.3577 9.34722ZM9.53768 10.1019H6.0858C6.02835 10.1011 5.97353 10.0776 5.93321 10.0367C5.89288 9.99577 5.87027 9.94061 5.87027 9.88315C5.87027 9.82569 5.89288 9.77054 5.93321 9.72961C5.97353 9.68868 6.02835 9.66526 6.0858 9.6644H9.53768C9.59513 9.66526 9.64994 9.68868 9.69027 9.72961C9.7306 9.77054 9.75321 9.82569 9.75321 9.88315C9.75321 9.94061 9.7306 9.99577 9.69027 10.0367C9.64994 10.0776 9.59513 10.1011 9.53768 10.1019ZM6.0858 8.37159H8.6758C8.73326 8.37244 8.78807 8.39587 8.8284 8.4368C8.86872 8.47773 8.89133 8.53288 8.89133 8.59034C8.89133 8.6478 8.86872 8.70296 8.8284 8.74389C8.78807 8.78482 8.73326 8.80824 8.6758 8.80909H6.0858C6.02835 8.80824 5.97353 8.78482 5.93321 8.74389C5.89288 8.70296 5.87027 8.6478 5.87027 8.59034C5.87027 8.53288 5.89288 8.47773 5.93321 8.4368C5.97353 8.39587 6.02835 8.37244 6.0858 8.37159ZM5.86705 7.29534C5.86722 7.23738 5.89033 7.18184 5.93131 7.14085C5.9723 7.09987 6.02784 7.07676 6.0858 7.07659H8.6758C8.73382 7.07659 8.78946 7.09964 8.83048 7.14066C8.8715 7.18169 8.89455 7.23733 8.89455 7.29534C8.89455 7.35336 8.8715 7.409 8.83048 7.45002C8.78946 7.49104 8.73382 7.51409 8.6758 7.51409H6.0858C6.02784 7.51392 5.9723 7.49082 5.93131 7.44983C5.89033 7.40884 5.86722 7.3533 5.86705 7.29534Z" fill="currentColor"/>
    <path d="M10.0732 8.20523H10.747L10.4123 7.20117L10.0732 8.20523Z" fill="currentColor"/>
    <path d="M9.31219 5.09244H9.74969V2.49369C9.74986 2.38007 9.72764 2.26753 9.6843 2.1625C9.64097 2.05746 9.57736 1.962 9.49712 1.88155C9.41688 1.80111 9.32158 1.73726 9.21665 1.69366C9.11173 1.65006 8.99925 1.62756 8.88562 1.62744H1.74125C1.51155 1.62759 1.29131 1.71891 1.12889 1.88133C0.966465 2.04375 0.875151 2.26399 0.875 2.49369V6.57119C0.875611 6.80055 0.967142 7.0203 1.12953 7.18227C1.29191 7.34425 1.51189 7.43522 1.74125 7.43525H2.23563C2.25847 7.43537 2.28107 7.43998 2.30213 7.44884C2.32319 7.4577 2.3423 7.47063 2.35835 7.48689C2.3744 7.50315 2.38709 7.52241 2.39569 7.54358C2.40428 7.56475 2.40861 7.58741 2.40844 7.61025V8.03682C2.40898 8.1519 2.45508 8.26209 2.53667 8.34326C2.61825 8.42443 2.72866 8.46998 2.84375 8.46994C2.95916 8.46944 3.06983 8.42392 3.15219 8.34307L3.81281 7.68025C3.81274 7.36411 3.81287 6.71441 3.81281 6.39619C3.81293 6.05045 3.95033 5.71891 4.19481 5.47444C4.43928 5.22996 4.77082 5.09256 5.11656 5.09244H9.31219ZM8.09375 4.49307H5.50594C5.4486 4.49204 5.39396 4.46854 5.35377 4.42763C5.31359 4.38671 5.29107 4.33166 5.29107 4.27431C5.29107 4.21696 5.31359 4.16191 5.35378 4.121C5.39397 4.08009 5.44861 4.05659 5.50595 4.05557H8.09375C8.1512 4.05642 8.20602 4.07984 8.24635 4.12077C8.28667 4.1617 8.30928 4.21686 8.30928 4.27432C8.30928 4.33178 8.28667 4.38693 8.24635 4.42786C8.20602 4.46879 8.1512 4.49221 8.09375 4.49307ZM8.09375 3.20025H5.50594C5.4486 3.19923 5.39396 3.17573 5.35377 3.13481C5.31359 3.0939 5.29107 3.03885 5.29107 2.9815C5.29107 2.92415 5.31359 2.8691 5.35378 2.82818C5.39397 2.78727 5.44861 2.76378 5.50595 2.76275H8.09376C8.15122 2.76361 8.20603 2.78703 8.24636 2.82796C8.28668 2.8689 8.30929 2.92405 8.30929 2.98151C8.30928 3.03897 8.28668 3.09412 8.24635 3.13505C8.20602 3.17598 8.1512 3.1994 8.09375 3.20025ZM3.02969 5.44244C3.03083 5.38518 3.05439 5.33066 3.09529 5.29057C3.13619 5.25048 3.19118 5.22803 3.24845 5.22803C3.30572 5.22803 3.3607 5.25049 3.4016 5.29058C3.4425 5.33067 3.46604 5.3852 3.46719 5.44246C3.46604 5.49972 3.44249 5.55424 3.40159 5.59433C3.36069 5.63442 3.3057 5.65687 3.24843 5.65687C3.19116 5.65687 3.13617 5.63441 3.09528 5.59432C3.05438 5.55423 3.03083 5.4997 3.02969 5.44244ZM3.74719 4.12557C3.64125 4.19379 3.55804 4.29202 3.50817 4.40774C3.4583 4.52345 3.44404 4.6514 3.4672 4.77525C3.46629 4.83267 3.44284 4.88743 3.40191 4.92771C3.36099 4.968 3.30586 4.99057 3.24844 4.99057C3.19101 4.99057 3.13589 4.96799 3.09496 4.9277C3.05404 4.88741 3.03059 4.83265 3.02969 4.77523V4.59369C3.03308 4.4246 3.07918 4.25912 3.1637 4.11264C3.24823 3.96615 3.36843 3.84343 3.51312 3.75588C3.58652 3.7097 3.64658 3.64517 3.68739 3.56866C3.72819 3.49215 3.74831 3.40631 3.74577 3.31964C3.74323 3.23296 3.71811 3.14846 3.67289 3.07447C3.62767 3.00048 3.56392 2.93958 3.48795 2.89778C3.41198 2.85599 3.32641 2.83475 3.23971 2.83616C3.15301 2.83757 3.06819 2.86159 2.99362 2.90584C2.91905 2.95009 2.85732 3.01304 2.81454 3.08846C2.77176 3.16389 2.7494 3.24917 2.74969 3.33588C2.74884 3.39333 2.72541 3.44815 2.68448 3.48847C2.64355 3.5288 2.5884 3.55141 2.53094 3.55141C2.47348 3.55141 2.41832 3.5288 2.37739 3.48847C2.33646 3.44815 2.31304 3.39333 2.31219 3.33588C2.31186 3.17308 2.35401 3.01301 2.43448 2.87149C2.51494 2.72996 2.63094 2.61188 2.771 2.5289C2.91106 2.44591 3.07035 2.40091 3.23313 2.39832C3.39591 2.39573 3.55655 2.43566 3.69918 2.51415C3.8418 2.59264 3.96149 2.70698 4.04641 2.84587C4.13133 2.98477 4.17855 3.14342 4.1834 3.30614C4.18824 3.46887 4.15056 3.63005 4.07406 3.77375C3.99756 3.91746 3.88489 4.03872 3.74719 4.12557Z" fill="currentColor"/>
  </g>
  <defs>
    <clipPath id="clip0_20075_10658">
      <rect width="14" height="14" fill="currentColor"/>
    </clipPath>
  </defs>
</svg>

<svg v-if="tab.key === 'questions'" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
  <g clip-path="url(#clip0_20075_10667)">
    <path d="M11.2292 0.875H1.60417C1.41084 0.875203 1.22549 0.952091 1.08879 1.08879C0.952091 1.22549 0.875203 1.41084 0.875 1.60417V5.39583C0.875203 5.58916 0.952091 5.77451 1.08879 5.91121C1.22549 6.04791 1.41084 6.1248 1.60417 6.125L3.74879 6.12671C3.72287 6.29362 3.69111 6.46708 3.65238 6.64681C3.63956 6.70662 3.62617 6.76502 3.6125 6.82284C3.59222 6.90793 3.59143 6.99651 3.61019 7.08196C3.62895 7.1674 3.66678 7.2475 3.72084 7.31628C3.77491 7.38505 3.84381 7.44072 3.92241 7.47912C4.00101 7.51752 4.08728 7.53766 4.17476 7.53805C4.20895 7.53797 4.24307 7.53492 4.27673 7.52893C5.19129 7.37092 6.01055 6.86851 6.56606 6.125H11.2292C11.4225 6.12482 11.6079 6.04794 11.7446 5.91123C11.8813 5.77453 11.9582 5.58917 11.9583 5.39583V1.60417C11.9582 1.41083 11.8813 1.22547 11.7446 1.08876C11.6079 0.952058 11.4225 0.875178 11.2292 0.875ZM3.64583 5.14062C3.58815 5.14062 3.53176 5.12352 3.48379 5.09147C3.43583 5.05942 3.39844 5.01387 3.37637 4.96057C3.35429 4.90728 3.34852 4.84863 3.35977 4.79206C3.37103 4.73548 3.3988 4.68351 3.43959 4.64272C3.48038 4.60193 3.53235 4.57415 3.58893 4.5629C3.64551 4.55164 3.70415 4.55742 3.75745 4.57949C3.81074 4.60157 3.8563 4.63895 3.88835 4.68692C3.92039 4.73488 3.9375 4.79127 3.9375 4.84896C3.9375 4.92631 3.90677 5.0005 3.85207 5.0552C3.79737 5.1099 3.72319 5.14062 3.64583 5.14062ZM3.93537 3.85917L3.9375 4.04688C3.9375 4.12423 3.90677 4.19842 3.85207 4.25311C3.79737 4.30781 3.72319 4.33854 3.64583 4.33854C3.56848 4.33854 3.49429 4.30781 3.43959 4.25311C3.3849 4.19842 3.35417 4.12423 3.35417 4.04688V3.87742C3.3525 3.74928 3.39202 3.62399 3.46691 3.52C3.5418 3.41601 3.6481 3.33881 3.77016 3.29978C3.88115 3.26722 3.97474 3.19205 4.03046 3.09069C4.08618 2.98933 4.09951 2.87003 4.06753 2.75887C4.04773 2.68706 4.00963 2.62162 3.95696 2.56895C3.90428 2.51627 3.83884 2.47818 3.76703 2.45838C3.7008 2.44004 3.63122 2.43731 3.56376 2.4504C3.4963 2.4635 3.43279 2.49207 3.37824 2.53386C3.32522 2.57466 3.28231 2.62713 3.25284 2.6872C3.22338 2.74726 3.20815 2.81331 3.20833 2.88021C3.20833 2.95756 3.1776 3.03175 3.12291 3.08645C3.06821 3.14115 2.99402 3.17188 2.91667 3.17188C2.83931 3.17188 2.76513 3.14115 2.71043 3.08645C2.65573 3.03175 2.625 2.95756 2.625 2.88021C2.62488 2.69063 2.67752 2.50477 2.77703 2.34341C2.87654 2.18206 3.019 2.05158 3.18845 1.96659C3.3579 1.88159 3.54767 1.84544 3.7365 1.86217C3.92534 1.8789 4.10579 1.94786 4.25766 2.06132C4.40953 2.17478 4.52683 2.32827 4.59642 2.50461C4.66601 2.68095 4.68515 2.87317 4.6517 3.05977C4.61825 3.24637 4.53353 3.41998 4.40701 3.56116C4.2805 3.70234 4.11719 3.80553 3.93537 3.85917ZM7.58333 4.375H5.54167C5.46431 4.375 5.39013 4.34427 5.33543 4.28957C5.28073 4.23487 5.25 4.16069 5.25 4.08333C5.25 4.00598 5.28073 3.93179 5.33543 3.87709C5.39013 3.8224 5.46431 3.79167 5.54167 3.79167H7.58333C7.66069 3.79167 7.73488 3.8224 7.78957 3.87709C7.84427 3.93179 7.875 4.00598 7.875 4.08333C7.875 4.16069 7.84427 4.23487 7.78957 4.28957C7.73488 4.34427 7.66069 4.375 7.58333 4.375ZM9.33333 2.91667H5.54167C5.46431 2.91667 5.39013 2.88594 5.33543 2.83124C5.28073 2.77654 5.25 2.70235 5.25 2.625C5.25 2.54765 5.28073 2.47346 5.33543 2.41876C5.39013 2.36406 5.46431 2.33333 5.54167 2.33333H9.33333C9.41069 2.33333 9.48488 2.36406 9.53957 2.41876C9.59427 2.47346 9.625 2.54765 9.625 2.625C9.625 2.70235 9.59427 2.77654 9.53957 2.83124C9.48488 2.88594 9.41069 2.91667 9.33333 2.91667Z" fill="currentColor"/>
    <path d="M12.3957 6.70825H6.64692C6.44061 6.93578 6.20754 7.13753 5.95279 7.30909C5.4732 7.63875 4.9279 7.86065 4.35439 7.9595C4.29473 7.97014 4.23419 7.97503 4.17359 7.97409C4.02043 7.97355 3.86936 7.93842 3.73168 7.87132C3.59399 7.80422 3.47324 7.70688 3.37845 7.58657C3.28366 7.46626 3.21727 7.32609 3.18424 7.17653C3.15122 7.02697 3.15241 6.87187 3.18772 6.72284C3.18772 6.717 3.19064 6.71409 3.19064 6.70825H2.77067C2.57731 6.70833 2.39189 6.78518 2.25516 6.92191C2.11843 7.05863 2.04158 7.24406 2.0415 7.43742V11.2291C2.04158 11.4224 2.11843 11.6079 2.25516 11.7446C2.39189 11.8813 2.57731 11.9582 2.77067 11.9583L8.59525 11.9524C8.8363 12.2753 9.12899 12.5561 9.4615 12.7837C9.8906 13.0771 10.3782 13.2742 10.8907 13.3612C10.9847 13.3777 11.0814 13.3707 11.172 13.3407C11.2626 13.3106 11.3444 13.2585 11.4099 13.1891C11.477 13.1195 11.5253 13.034 11.5503 12.9407C11.5752 12.8473 11.5761 12.7491 11.5527 12.6553L11.5148 12.4803C11.474 12.2995 11.4448 12.1274 11.4186 11.9583H12.3957C12.589 11.9582 12.7745 11.8813 12.9112 11.7446C13.0479 11.6079 13.1248 11.4224 13.1248 11.2291V7.43742C13.1248 7.24406 13.0479 7.05863 12.9112 6.92191C12.7745 6.78518 12.589 6.70833 12.3957 6.70825ZM8.45817 10.4999H4.37484C4.29748 10.4999 4.2233 10.4692 4.1686 10.4145C4.1139 10.3598 4.08317 10.2856 4.08317 10.2083C4.08317 10.1309 4.1139 10.0567 4.1686 10.002C4.2233 9.94731 4.29748 9.91659 4.37484 9.91659H8.45817C8.53553 9.91659 8.60971 9.94731 8.66441 10.002C8.71911 10.0567 8.74984 10.1309 8.74984 10.2083C8.74984 10.2856 8.71911 10.3598 8.66441 10.4145C8.60971 10.4692 8.53553 10.4999 8.45817 10.4999ZM8.45817 9.04159H6.12484C6.04748 9.04159 5.9733 9.01086 5.9186 8.95616C5.8639 8.90146 5.83317 8.82727 5.83317 8.74992C5.83317 8.67256 5.8639 8.59838 5.9186 8.54368C5.9733 8.48898 6.04748 8.45825 6.12484 8.45825H8.45817C8.53553 8.45825 8.60971 8.48898 8.66441 8.54368C8.71911 8.59838 8.74984 8.67256 8.74984 8.74992C8.74984 8.82727 8.71911 8.90146 8.66441 8.95616C8.60971 9.01086 8.53553 9.04159 8.45817 9.04159ZM10.354 10.9374C10.2963 10.9374 10.2399 10.9203 10.192 10.8883C10.144 10.8562 10.1066 10.8107 10.0845 10.7574C10.0625 10.7041 10.0567 10.6454 10.0679 10.5889C10.0792 10.5323 10.107 10.4803 10.1478 10.4395C10.1886 10.3987 10.2405 10.3709 10.2971 10.3597C10.3537 10.3484 10.4123 10.3542 10.4656 10.3763C10.5189 10.3984 10.5645 10.4357 10.5965 10.4837C10.6286 10.5317 10.6457 10.5881 10.6457 10.6458C10.6454 10.723 10.6146 10.7971 10.56 10.8517C10.5053 10.9064 10.4313 10.9372 10.354 10.9374ZM10.6457 9.73575V9.84367C10.6457 9.92102 10.6149 9.99521 10.5602 10.0499C10.5055 10.1046 10.4314 10.1353 10.354 10.1353C10.2766 10.1353 10.2025 10.1046 10.1478 10.0499C10.0931 9.99521 10.0623 9.92102 10.0623 9.84367V9.7095C10.0607 9.59397 10.0964 9.48099 10.1642 9.38743C10.232 9.29387 10.3283 9.22473 10.4386 9.19034C10.5121 9.16778 10.5738 9.11721 10.6104 9.04954C10.647 8.98187 10.6554 8.90254 10.634 8.82867C10.6219 8.7808 10.5971 8.73709 10.5622 8.70218C10.5273 8.66726 10.4836 8.64244 10.4357 8.63034C10.3915 8.61795 10.3451 8.61598 10.3001 8.62458C10.255 8.63319 10.2126 8.65213 10.1761 8.67992C10.1406 8.70686 10.1118 8.7417 10.0921 8.7817C10.0723 8.82169 10.0621 8.86573 10.0623 8.91034C10.0623 8.98769 10.0316 9.06188 9.97692 9.11658C9.92222 9.17127 9.84804 9.202 9.77068 9.202C9.69333 9.202 9.61914 9.17127 9.56444 9.11658C9.50975 9.06188 9.47902 8.98769 9.47902 8.91034C9.47843 8.77685 9.50865 8.64502 9.56734 8.52513C9.62604 8.40523 9.71161 8.3005 9.8174 8.21909C9.91752 8.14127 10.0334 8.08613 10.1569 8.0575C10.2804 8.02886 10.4087 8.02741 10.5329 8.05324C10.657 8.07907 10.7741 8.13157 10.8759 8.2071C10.9778 8.28263 11.062 8.37939 11.1228 8.49068C11.1836 8.60198 11.2195 8.72515 11.2279 8.85167C11.2364 8.9782 11.2173 9.10506 11.1719 9.22347C11.1266 9.34188 11.056 9.44902 10.9651 9.53747C10.8743 9.62592 10.7653 9.69358 10.6457 9.73575Z" fill="currentColor"/>
  </g>
  <defs>
    <clipPath id="clip0_20075_10667">
      <rect width="14" height="14" fill="currentColor"/>
    </clipPath>
  </defs>
</svg>

<svg v-if="tab.key === 'saved'" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
  <g clip-path="url(#clip0_20075_10698)">
    <path fill-rule="evenodd" clip-rule="evenodd" d="M2.7671 0C1.82519 7.30078e-08 1.05518 0.770008 1.05518 1.71192V13.0001C1.05518 13.8103 2.01693 14.2912 2.66509 13.8051L6.5032 10.9265C6.79868 10.7049 7.20134 10.7049 7.49682 10.9265L11.3349 13.8051C11.9831 14.2912 12.9448 13.8103 12.9448 13.0001V1.71192C12.9448 0.770008 12.1748 -1.82383e-07 11.2329 0H2.7671ZM4.88266 1.41082H9.11368C9.66569 1.41082 10.1195 1.86465 10.1195 2.41666V2.41672C10.1195 2.96873 9.66569 3.42251 9.11368 3.42251H4.88266C4.33064 3.42251 3.87687 2.96873 3.87687 2.41672V2.41666C3.87687 1.86465 4.33064 1.41082 4.88266 1.41082Z" fill="currentColor"/>
  </g>
  <defs>
    <clipPath id="clip0_20075_10698">
      <rect width="14" height="14" fill="currentColor"/>
    </clipPath>
  </defs>
</svg>

<svg v-if="tab.key === 'activity'" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
  <g clip-path="url(#clip0_20075_10702)">
    <path fill-rule="evenodd" clip-rule="evenodd" d="M9.67984 0.970077V2.30997C9.67984 2.68012 9.3798 2.98 9.00981 2.98H4.98996C4.61997 2.98 4.31993 2.68012 4.31993 2.30997V0.970077C4.31993 0.60009 4.61997 0.300049 4.98996 0.300049H9.00981C9.3798 0.300049 9.67984 0.60009 9.67984 0.970077ZM11.0197 1.64011H11.6898C12.0597 1.64011 12.3596 1.93999 12.3596 2.30997V13.0296C12.3596 13.3996 12.0597 13.6997 11.6898 13.6997H2.31C1.94002 13.6997 1.64014 13.3996 1.64014 13.0296V2.30997C1.64014 1.93999 1.94002 1.64011 2.31 1.64011H2.98003V2.30997C2.98003 3.4201 3.87984 4.3199 4.98996 4.3199H9.00981C10.1199 4.3199 11.0197 3.4201 11.0197 2.30997V1.64011ZM4.98996 11.0197H7.66991C8.03958 11.0197 8.33978 10.7195 8.33978 10.3497C8.33978 9.98001 8.03958 9.67981 7.66991 9.67981H4.98996C4.62013 9.67981 4.31993 9.98001 4.31993 10.3497C4.31993 10.7195 4.62013 11.0197 4.98996 11.0197ZM4.98996 6.99985H9.00981C9.37964 6.99985 9.67984 6.69965 9.67984 6.32982C9.67984 5.96016 9.37964 5.65996 9.00981 5.65996H4.98996C4.62013 5.65996 4.31993 5.96016 4.31993 6.32982C4.31993 6.69965 4.62013 6.99985 4.98996 6.99985ZM4.98996 9.00978H9.00981C9.37964 9.00978 9.67984 8.70958 9.67984 8.33975C9.67984 7.97008 9.37964 7.66988 9.00981 7.66988H4.98996C4.62013 7.66988 4.31993 7.97008 4.31993 8.33975C4.31993 8.70958 4.62013 9.00978 4.98996 9.00978Z" fill="currentColor"/>
  </g>
  <defs>
    <clipPath id="clip0_20075_10702">
      <rect width="14" height="14" fill="currentColor"/>
    </clipPath>
  </defs>
</svg>

          <svg v-if="tab.key === 'warnings'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          <svg v-if="tab.key === 'stats'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="18" y="3" width="4" height="18"/><rect x="10" y="8" width="4" height="13"/><rect x="2" y="13" width="4" height="8"/></svg>
          <svg v-if="tab.key === 'settings'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
          {{ tab.label }}
        </button>
      </div>

      <!-- Tab Content -->
      <div v-if="!user.profile_restricted" class="profile-tab-content">
        <!-- Answers Tab -->
        <template v-if="activeTab === 'answers'">
          <div v-if="loadingReplies" class="profile-cards-grid">
            <div v-for="n in 3" :key="n" class="cnw-skeleton-card" style="padding:14px;gap:8px">
              <div class="cnw-skeleton cnw-skeleton-line-lg" style="width:60%"></div>
              <div class="cnw-skeleton cnw-skeleton-line" style="width:90%"></div>
              <div class="cnw-skeleton cnw-skeleton-line-sm" style="width:30%"></div>
            </div>
          </div>
          <div v-else-if="replies.length === 0" class="profile-empty-state">
            <p class="profile-empty-title">No answers yet</p>
            <p class="profile-empty-desc">When this user shares answers, they'll appear here.</p>
            <router-link to="/" class="profile-empty-link">unanswered questions</router-link>
          </div>
          <div v-else class="profile-cards-grid">
            <div v-for="reply in replies" :key="reply.id" class="profile-content-card" role="button" tabindex="0" @click="$router.push('/thread/' + reply.thread_id)" @keydown.enter="$router.push('/thread/' + reply.thread_id)">
              <h4 class="profile-card-title">{{ reply.thread_title }}</h4>
              <p class="profile-card-excerpt">{{ truncate(reply.content, 120) }}</p>
              <div class="profile-card-tags">
                <span v-for="tag in reply.tags" :key="tag" class="qcard-tag">{{ tag }}</span>
              </div>
              <p class="profile-card-meta">{{ formatDate(reply.created_at) }} &bull; {{ reply.saves_count || 0 }} helpful</p>
            </div>
          </div>
          <div v-if="repliesPages > 1" class="profile-pagination">
            <button :disabled="repliesPage <= 1" @click="fetchReplies(repliesPage - 1)">&laquo; Prev</button>
            <button v-for="p in repliesPages" :key="p" class="profile-page-btn" :class="{ 'is-active': p === repliesPage }" @click="fetchReplies(p)">{{ p }}</button>
            <button :disabled="repliesPage >= repliesPages" @click="fetchReplies(repliesPage + 1)">Next &raquo;</button>
          </div>
        </template>

        <!-- Questions Tab -->
        <template v-if="activeTab === 'questions'">
          <div v-if="loadingThreads" class="profile-cards-grid">
            <div v-for="n in 3" :key="n" class="cnw-skeleton-card" style="padding:14px;gap:8px">
              <div class="cnw-skeleton cnw-skeleton-line-lg" style="width:55%"></div>
              <div class="cnw-skeleton cnw-skeleton-line" style="width:85%"></div>
              <div class="cnw-skeleton cnw-skeleton-line-sm" style="width:35%"></div>
            </div>
          </div>
          <div v-else-if="threads.length === 0" class="profile-empty-state">
            <p class="profile-empty-title">No questions yet</p>
            <p class="profile-empty-desc">When this user asks questions, they'll appear here.</p>
          </div>
          <div v-else class="profile-cards-grid">
            <div v-for="thread in threads" :key="thread.id" class="profile-content-card" role="button" tabindex="0" @click="$router.push('/thread/' + thread.id)" @keydown.enter="$router.push('/thread/' + thread.id)">
              <h4 class="profile-card-title">{{ thread.title }}</h4>
              <p class="profile-card-excerpt">{{ truncate(thread.content, 120) }}</p>
              <div class="profile-card-tags">
                <span v-for="tag in thread.tags" :key="tag" class="qcard-tag">{{ tag }}</span>
              </div>
              <p class="profile-card-meta">{{ formatDate(thread.created_at) }} &bull; {{ thread.saves_count || 0 }} helpful</p>
            </div>
          </div>
          <div v-if="threadsPages > 1" class="profile-pagination">
            <button :disabled="threadsPage <= 1" @click="fetchThreads(threadsPage - 1)">&laquo; Prev</button>
            <button v-for="p in threadsPages" :key="p" class="profile-page-btn" :class="{ 'is-active': p === threadsPage }" @click="fetchThreads(p)">{{ p }}</button>
            <button :disabled="threadsPage >= threadsPages" @click="fetchThreads(threadsPage + 1)">Next &raquo;</button>
          </div>
        </template>

        <!-- Saved Tab -->
        <template v-if="activeTab === 'saved'">
          <div v-if="loadingSaved" class="profile-cards-grid">
            <div v-for="n in 3" :key="n" class="cnw-skeleton-card" style="padding:14px;gap:8px">
              <div class="cnw-skeleton cnw-skeleton-line-lg" style="width:50%"></div>
              <div class="cnw-skeleton cnw-skeleton-line" style="width:80%"></div>
              <div class="cnw-skeleton cnw-skeleton-line-sm" style="width:25%"></div>
            </div>
          </div>
          <div v-else-if="savedThreads.length === 0" class="profile-empty-state">
            <p class="profile-empty-title">No saved threads yet</p>
            <p class="profile-empty-desc">Threads you mark as helpful will appear here.</p>
          </div>
          <div v-else class="profile-cards-grid">
            <div v-for="thread in savedThreads" :key="thread.id" class="profile-content-card" role="button" tabindex="0" @click="$router.push('/thread/' + thread.id)" @keydown.enter="$router.push('/thread/' + thread.id)">
              <h4 class="profile-card-title">{{ thread.title }}</h4>
              <p class="profile-card-excerpt">{{ truncate(thread.content, 120) }}</p>
              <div class="profile-card-tags">
                <span v-for="tag in thread.tags" :key="tag" class="qcard-tag">{{ tag }}</span>
              </div>
              <p class="profile-card-meta">{{ formatDate(thread.created_at) }} &bull; {{ thread.saves_count || 0 }} helpful</p>
            </div>
          </div>
          <div v-if="savedPages > 1" class="profile-pagination">
            <button :disabled="savedPage <= 1" @click="fetchSaved(savedPage - 1)">&laquo; Prev</button>
            <button v-for="p in savedPages" :key="p" class="profile-page-btn" :class="{ 'is-active': p === savedPage }" @click="fetchSaved(p)">{{ p }}</button>
            <button :disabled="savedPage >= savedPages" @click="fetchSaved(savedPage + 1)">Next &raquo;</button>
          </div>
        </template>

        <!-- Activity Tab -->
        <template v-if="activeTab === 'activity'">
          <div v-if="loadingActivity" style="display:flex;flex-direction:column;gap:10px">
            <div v-for="n in 4" :key="n" class="cnw-skeleton-card" style="flex-direction:row;padding:10px;gap:10px">
              <div class="cnw-skeleton cnw-skeleton-circle" style="width:32px;height:32px"></div>
              <div style="flex:1;display:flex;flex-direction:column;gap:5px">
                <div class="cnw-skeleton cnw-skeleton-line" :style="{width: [65,80,50,70][n-1]+'%'}"></div>
                <div class="cnw-skeleton cnw-skeleton-line-sm" style="width:20%"></div>
              </div>
            </div>
          </div>
          <div v-else-if="activities.length === 0" class="profile-empty-state">
            <p class="profile-empty-title">No activity yet</p>
            <p class="profile-empty-desc">Your actions will be recorded here.</p>
          </div>
          <div v-else class="profile-activity-list">
            <div v-for="act in activities" :key="act.id" class="profile-activity-row">
              <div class="activity-icon" :class="'activity-icon--' + act.action_type">
                <svg v-if="act.action_type === 'login'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                <svg v-else-if="act.action_type === 'thread_created'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <svg v-else-if="act.action_type === 'reply_created'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4l-4 4V6c0-1.1.9-2 2-2z"/></svg>
                <svg v-else-if="act.action_type === 'voted' || act.action_type === 'received_vote'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
                <svg v-else-if="act.action_type === 'best_answer' || act.action_type === 'marked_solution'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                <svg v-else-if="act.action_type === 'thread_saved'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
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
          <div v-if="activityPages > 1" class="profile-pagination">
            <button :disabled="activityPage <= 1" @click="fetchActivity(activityPage - 1)">&laquo; Prev</button>
            <button v-for="p in activityPages" :key="p" class="profile-page-btn" :class="{ 'is-active': p === activityPage }" @click="fetchActivity(p)">{{ p }}</button>
            <button :disabled="activityPage >= activityPages" @click="fetchActivity(activityPage + 1)">Next &raquo;</button>
          </div>
        </template>

        <!-- Warnings & Suspensions Tab -->
        <template v-if="activeTab === 'warnings'">
          <div v-if="loadingWarnings" style="display:flex;flex-direction:column;gap:10px">
            <div v-for="n in 3" :key="n" class="cnw-skeleton-card" style="padding:12px;gap:8px">
              <div class="cnw-skeleton cnw-skeleton-line-lg" style="width:35%"></div>
              <div class="cnw-skeleton cnw-skeleton-line" style="width:80%"></div>
              <div class="cnw-skeleton cnw-skeleton-line-sm" style="width:25%"></div>
            </div>
          </div>
          <div v-else-if="userWarnings.length === 0" class="profile-empty-state">
            <p class="profile-empty-title">No warnings or suspensions</p>
            <p class="profile-empty-desc">This account has a clean record.</p>
          </div>
          <div v-else class="profile-warnings-list">
            <div v-for="w in userWarnings" :key="w.id" class="profile-warning-card" :class="w.type === 'suspension' ? 'profile-warning-card--suspension' : 'profile-warning-card--warning'">
              <div class="profile-warning-header">
                <span class="profile-warning-type" :class="w.type === 'suspension' ? 'pw-type-suspension' : 'pw-type-warning'">{{ w.type === 'suspension' ? 'Suspension' : 'Warning' }}</span>
                <span class="profile-warning-date">{{ formatWarningDate(w.created_at) }}</span>
                <button v-if="canModerate" class="profile-warning-delete" @click="deleteUserWarning(w)" :disabled="w._deleting" title="Delete" aria-label="Delete warning">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                </button>
              </div>
              <div class="profile-warning-reason">{{ w.reason }}</div>
              <div v-if="w.expires_at" class="profile-warning-expires">Expires: {{ formatWarningDate(w.expires_at) }}</div>
              <div v-if="w.type === 'suspension' && !w.expires_at" class="profile-warning-expires profile-warning-permanent">Permanent</div>
              <div class="profile-warning-by">Issued by: {{ w.moderator_name }}</div>
            </div>
          </div>
          <div v-if="warningsPages > 1" class="profile-pagination">
            <button :disabled="warningsPage <= 1" @click="fetchWarnings(warningsPage - 1)">&laquo; Prev</button>
            <button v-for="p in warningsPages" :key="p" class="profile-page-btn" :class="{ 'is-active': p === warningsPage }" @click="fetchWarnings(p)">{{ p }}</button>
            <button :disabled="warningsPage >= warningsPages" @click="fetchWarnings(warningsPage + 1)">Next &raquo;</button>
          </div>
        </template>

        <!-- Stats Tab -->
        <template v-if="activeTab === 'stats'">
          <div class="profile-stats-grid">
            <div class="stats-card">
              <div class="stats-card-icon stats-card-icon--reputation">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
              </div>
              <div class="stats-card-value">{{ user.reputation || 0 }}</div>
              <div class="stats-card-label">Reputation</div>
            </div>
            <div class="stats-card">
              <div class="stats-card-icon stats-card-icon--questions">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              </div>
              <div class="stats-card-value">{{ user.thread_count || 0 }}</div>
              <div class="stats-card-label">Questions</div>
            </div>
            <div class="stats-card">
              <div class="stats-card-icon stats-card-icon--answers">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4l-4 4V6c0-1.1.9-2 2-2z"/></svg>
              </div>
              <div class="stats-card-value">{{ user.reply_count || 0 }}</div>
              <div class="stats-card-label">Answers</div>
            </div>
            <div class="stats-card">
              <div class="stats-card-icon stats-card-icon--helpful">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" fill="none" stroke="currentColor" stroke-width="2"/></svg>
              </div>
              <div class="stats-card-value">{{ user.helpful_count || 0 }}</div>
              <div class="stats-card-label">Helpful Answers</div>
            </div>
            <div class="stats-card">
              <div class="stats-card-icon stats-card-icon--joined">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              </div>
              <div class="stats-card-value">{{ joinedDate }}</div>
              <div class="stats-card-label">Member Since</div>
            </div>
            <div class="stats-card">
              <div class="stats-card-icon stats-card-icon--ratio">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
              </div>
              <div class="stats-card-value">{{ answerRatio }}</div>
              <div class="stats-card-label">Answer / Question Ratio</div>
            </div>
          </div>
        </template>

        <!-- Settings Tab -->
        <template v-if="activeTab === 'settings' && isOwn">
          <div v-if="loadingPrefs" style="display:flex;flex-direction:column;gap:14px;padding:20px">
            <div class="cnw-skeleton cnw-skeleton-line-lg" style="width:30%"></div>
            <div v-for="n in 4" :key="n" class="cnw-skeleton-card" style="flex-direction:row;justify-content:space-between;padding:10px;gap:10px">
              <div class="cnw-skeleton cnw-skeleton-line" style="width:40%"></div>
              <div class="cnw-skeleton cnw-skeleton-line" style="width:80px;height:24px;border-radius:12px"></div>
            </div>
          </div>
          <div v-else-if="preferences" class="profile-settings">

            <!-- Privacy Section -->
            <div class="settings-section">
              <div class="settings-section-header">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <h3>Privacy</h3>
              </div>

              <div class="settings-row">
                <div class="settings-row-info">
                  <span class="settings-label">Profile Visibility</span>
                  <span class="settings-desc">Who can see your profile details</span>
                </div>
                <select v-model="preferences.profile_visibility" class="settings-select">
                  <option value="everyone">Everyone</option>
                  <option value="connections">Connections Only</option>
                </select>
              </div>

              <div class="settings-row">
                <div class="settings-row-info">
                  <span class="settings-label">Message Privacy</span>
                  <span class="settings-desc">Who can send you messages</span>
                </div>
                <select v-model="preferences.message_privacy" class="settings-select">
                  <option value="everyone">Everyone</option>
                  <option value="connections">Connections Only</option>
                </select>
              </div>

              <div class="settings-row">
                <div class="settings-row-info">
                  <span class="settings-label">Show Online Status</span>
                  <span class="settings-desc">Let others see when you're online</span>
                </div>
                <button type="button" class="settings-toggle" :class="{ on: preferences.show_online_status }" @click="preferences.show_online_status = !preferences.show_online_status" role="switch" :aria-checked="!!preferences.show_online_status">
                  <span class="settings-toggle-track" aria-hidden="true"><span class="settings-toggle-thumb"></span></span>
                </button>
              </div>

              <div class="settings-row">
                <div class="settings-row-info">
                  <span class="settings-label">Show Activity</span>
                  <span class="settings-desc">Display your activity on your profile</span>
                </div>
                <button type="button" class="settings-toggle" :class="{ on: preferences.show_activity }" @click="preferences.show_activity = !preferences.show_activity" role="switch" :aria-checked="!!preferences.show_activity">
                  <span class="settings-toggle-track" aria-hidden="true"><span class="settings-toggle-thumb"></span></span>
                </button>
              </div>
            </div>

            <!-- Notifications Section -->
            <div class="settings-section">
              <div class="settings-section-header">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <h3>Notifications</h3>
              </div>

              <div class="settings-row">
                <div class="settings-row-info">
                  <span class="settings-label">Replies</span>
                  <span class="settings-desc">When someone replies to your thread</span>
                </div>
                <button type="button" class="settings-toggle" :class="{ on: preferences.notify_replies }" @click="preferences.notify_replies = !preferences.notify_replies" role="switch" :aria-checked="!!preferences.notify_replies">
                  <span class="settings-toggle-track" aria-hidden="true"><span class="settings-toggle-thumb"></span></span>
                </button>
              </div>

              <div class="settings-row">
                <div class="settings-row-info">
                  <span class="settings-label">Mentions</span>
                  <span class="settings-desc">When someone mentions you</span>
                </div>
                <button type="button" class="settings-toggle" :class="{ on: preferences.notify_mentions }" @click="preferences.notify_mentions = !preferences.notify_mentions" role="switch" :aria-checked="!!preferences.notify_mentions">
                  <span class="settings-toggle-track" aria-hidden="true"><span class="settings-toggle-thumb"></span></span>
                </button>
              </div>

              <div class="settings-row">
                <div class="settings-row-info">
                  <span class="settings-label">Votes</span>
                  <span class="settings-desc">When someone votes on your content</span>
                </div>
                <button type="button" class="settings-toggle" :class="{ on: preferences.notify_votes }" @click="preferences.notify_votes = !preferences.notify_votes" role="switch" :aria-checked="!!preferences.notify_votes">
                  <span class="settings-toggle-track" aria-hidden="true"><span class="settings-toggle-thumb"></span></span>
                </button>
              </div>

              <div class="settings-row">
                <div class="settings-row-info">
                  <span class="settings-label">Solutions</span>
                  <span class="settings-desc">When your answer is marked as solution</span>
                </div>
                <button type="button" class="settings-toggle" :class="{ on: preferences.notify_solutions }" @click="preferences.notify_solutions = !preferences.notify_solutions" role="switch" :aria-checked="!!preferences.notify_solutions">
                  <span class="settings-toggle-track" aria-hidden="true"><span class="settings-toggle-thumb"></span></span>
                </button>
              </div>

              <div class="settings-row">
                <div class="settings-row-info">
                  <span class="settings-label">Connections</span>
                  <span class="settings-desc">Connection requests and acceptances</span>
                </div>
                <button type="button" class="settings-toggle" :class="{ on: preferences.notify_connections }" @click="preferences.notify_connections = !preferences.notify_connections" role="switch" :aria-checked="!!preferences.notify_connections">
                  <span class="settings-toggle-track" aria-hidden="true"><span class="settings-toggle-thumb"></span></span>
                </button>
              </div>

              <div class="settings-row">
                <div class="settings-row-info">
                  <span class="settings-label">Messages</span>
                  <span class="settings-desc">New direct messages</span>
                </div>
                <button type="button" class="settings-toggle" :class="{ on: preferences.notify_messages }" @click="preferences.notify_messages = !preferences.notify_messages" role="switch" :aria-checked="!!preferences.notify_messages">
                  <span class="settings-toggle-track" aria-hidden="true"><span class="settings-toggle-thumb"></span></span>
                </button>
              </div>

              <div class="settings-row">
                <div class="settings-row-info">
                  <span class="settings-label">Email Notifications</span>
                  <span class="settings-desc">How often to receive email notifications</span>
                </div>
                <select v-model="preferences.email_notifications" class="settings-select">
                  <option value="always">Always</option>
                  <option value="inactive">When I'm Inactive</option>
                  <option value="none">None</option>
                </select>
              </div>
            </div>

            <div class="settings-footer">
              <span v-if="prefsSaved" class="settings-saved-msg" aria-live="polite">Settings saved!</span>
              <button class="settings-save-btn" :disabled="savingPrefs" @click="savePreferences">
                {{ savingPrefs ? 'Saving...' : 'Save Settings' }}
              </button>
            </div>
          </div>
        </template>
      </div>
    </template>
  </div>
</template>

<script>
import { getUser, getUserThreads, getUserReplies, getSavedThreads, getUserActivity, updateUserProfile, toggleAnonymous, uploadAvatar, getConnectionStatus, sendConnectionRequest, acceptConnection, declineConnection, getUserWarnings, deleteWarning, getPreferences, updatePreferences } from '@/api/index.js';

export default {
  name: 'UserProfileView',
  data() {
    return {
      user: {},
      loading: true,
      error: '',
      activeTab: 'answers',
      tabs: [],
      threads: [],
      replies: [],
      savedThreads: [],
      loadingThreads: false,
      loadingReplies: false,
      loadingSaved: false,
      repliesPage: 1,
      repliesPages: 1,
      threadsPage: 1,
      threadsPages: 1,
      savedPage: 1,
      savedPages: 1,
      activities: [],
      loadingActivity: false,
      activityPage: 1,
      activityPages: 1,
      userWarnings: [],
      loadingWarnings: false,
      warningsPage: 1,
      warningsPages: 1,
      editing: false,
      saving: false,
      editForm: { first_name: '', last_name: '', email: '', phone: '', verified_label: '', professional_title: '' },
      defaultAvatar: window.cnwData?.defaultAvatar || '',
      connectionStatus: null,
      connectionLoading: false,
      // Settings
      preferences: null,
      loadingPrefs: false,
      savingPrefs: false,
      prefsSaved: false,
    };
  },
  computed: {
    userId() {
      return this.$route?.params?.id ? Number(this.$route.params.id) : (window.cnwData?.currentUser?.id || 0);
    },
    isOwn() {
      return this.user.is_own || false;
    },
    fullName() {
      const fn = this.user.first_name || '';
      const ln = this.user.last_name || '';
      const full = (fn + ' ' + ln).trim();
      return full || this.user.display_name || 'User';
    },
    joinedDate() {
      if (!this.user.user_registered) return '';
      const d = new Date(this.user.user_registered);
      return d.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
    },
    userRole() {
      return 'Verified Social Worker';
    },
    canModerate() {
      return !!(window.cnwData?.currentUser?.canModerate);
    },
    suspensionText() {
      if (!this.user.suspension) return '';
      if (this.user.suspension.permanent) return 'Permanently Suspended';
      const days = this.user.suspension.days_left;
      return `Suspended for ${days} day${days > 1 ? 's' : ''}`;
    },
    answerRatio() {
      const q = this.user.thread_count || 0;
      const a = this.user.reply_count || 0;
      if (q === 0) return a > 0 ? a + ':0' : '0:0';
      return (a / q).toFixed(1) + ':1';
    },
  },
  watch: {
    '$route.params.id'() {
      const tab = this.$route?.query?.tab;
      this.activeTab = (tab && this.tabs.some(t => t.key === tab)) ? tab : 'answers';
      this.loadProfile();
    },
    '$route.query.tab'(tab) {
      if (tab && this.tabs.some(t => t.key === tab)) {
        this.activeTab = tab;
      }
    },
    activeTab(tab) {
      if (tab === 'answers' && this.replies.length === 0) this.fetchReplies(1);
      if (tab === 'questions' && this.threads.length === 0) this.fetchThreads(1);
      if (tab === 'saved' && this.savedThreads.length === 0) this.fetchSaved(1);
      if (tab === 'activity' && this.activities.length === 0) this.fetchActivity(1);
      if (tab === 'warnings' && this.userWarnings.length === 0) this.fetchWarnings(1);
      if (tab === 'settings' && !this.preferences) this.loadPreferences();
    },
  },
  created() {
    this.loadProfile();
    const tab = this.$route?.query?.tab;
    if (tab && this.tabs.some(t => t.key === tab)) {
      this.activeTab = tab;
    }
  },
  methods: {
    async loadProfile() {
      this.loading = true;
      this.error = '';
      this.threads = [];
      this.replies = [];
      try {
        this.user = await getUser(this.userId);
        this.buildTabs();
        if (!this.user.profile_restricted) {
          this.fetchReplies();
        }
        if (this.user.is_own && this.user.preferences) {
          this.preferences = { ...this.user.preferences };
        }
        if (!this.user.is_own) {
          this.loadConnectionStatus();
        }
      } catch {
        this.error = 'Failed to load user profile.';
      } finally {
        this.loading = false;
      }
    },
    async loadConnectionStatus() {
      try {
        const data = await getConnectionStatus(this.userId);
        this.connectionStatus = data.status || 'none';
      } catch { this.connectionStatus = 'none'; }
    },
    async handleSendConnection() {
      this.connectionLoading = true;
      try {
        await sendConnectionRequest(this.userId);
        this.connectionStatus = 'pending_sent';
      } catch { /* silent */ }
      this.connectionLoading = false;
    },
    async handleAcceptConnection() {
      this.connectionLoading = true;
      try {
        await acceptConnection(this.userId);
        this.connectionStatus = 'connected';
      } catch { /* silent */ }
      this.connectionLoading = false;
    },
    async handleDeclineConnection() {
      this.connectionLoading = true;
      try {
        await declineConnection(this.userId);
        this.connectionStatus = 'none';
      } catch { /* silent */ }
      this.connectionLoading = false;
    },
    goToMessage() {
      const user = {
        id: this.userId,
        name: this.fullName,
        avatar: this.user.avatar || this.defaultAvatar,
        verified_label: this.user.verified_label || '',
      };
      window._cnwPendingChat = user;
      this.$router.push('/messages');
    },
    async fetchThreads(page) {
      if (page) this.threadsPage = page;
      this.loadingThreads = true;
      try {
        const data = await getUserThreads(this.userId, { page: this.threadsPage });
        this.threads = data.threads || [];
        this.threadsPages = data.pages || 1;
      } catch { /* silent */ }
      finally { this.loadingThreads = false; }
    },
    async fetchReplies(page) {
      if (page) this.repliesPage = page;
      this.loadingReplies = true;
      try {
        const data = await getUserReplies(this.userId, { page: this.repliesPage });
        this.replies = data.replies || [];
        this.repliesPages = data.pages || 1;
      } catch { /* silent */ }
      finally { this.loadingReplies = false; }
    },
    async fetchSaved(page) {
      if (page) this.savedPage = page;
      this.loadingSaved = true;
      try {
        const data = await getSavedThreads({ page: this.savedPage });
        this.savedThreads = data.threads || data || [];
        this.savedPages = data.pages || 1;
      } catch { /* silent */ }
      finally { this.loadingSaved = false; }
    },
    async fetchActivity(page) {
      if (page) this.activityPage = page;
      this.loadingActivity = true;
      try {
        const data = await getUserActivity({ page: this.activityPage });
        this.activities = data.activities || [];
        this.activityPages = data.pages || 1;
      } catch { /* silent */ }
      finally { this.loadingActivity = false; }
    },
    async fetchWarnings(page) {
      if (page) this.warningsPage = page;
      this.loadingWarnings = true;
      try {
        const data = await getUserWarnings(this.userId, { page: this.warningsPage });
        this.userWarnings = data.warnings || [];
        this.warningsPages = data.pages || 1;
      } catch { /* silent */ }
      finally { this.loadingWarnings = false; }
    },
    formatWarningDate(d) {
      if (!d) return '';
      const date = new Date(d);
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
        + ' ' + date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    },
    async deleteUserWarning(w) {
      const label = w.type === 'suspension' ? 'suspension' : 'warning';
      if (!confirm(`Are you sure you want to delete this ${label}?`)) return;
      w._deleting = true;
      try {
        await deleteWarning(w.id);
        this.userWarnings = this.userWarnings.filter(x => x.id !== w.id);
        // Refresh profile to update suspension status
        this.user = await getUser(this.userId);
      } catch {} finally {
        w._deleting = false;
      }
    },
    startEdit() {
      this.editForm.first_name = this.user.first_name || '';
      this.editForm.last_name = this.user.last_name || '';
      this.editForm.email = this.user.email || '';
      this.editForm.phone = this.user.phone || '';
      this.editForm.verified_label = this.user.verified_label || 'Verified Social Worker';
      this.editForm.professional_title = this.user.professional_title || 'Licensed Clinical Social Worker';
      this.editing = true;
    },
    cancelEdit() {
      this.editing = false;
    },
    async saveProfile() {
      this.saving = true;
      try {
        await updateUserProfile(this.editForm);
        this.user.first_name = this.editForm.first_name;
        this.user.last_name = this.editForm.last_name;
        this.user.email = this.editForm.email;
        this.user.phone = this.editForm.phone;
        this.user.verified_label = this.editForm.verified_label;
        this.user.professional_title = this.editForm.professional_title;
        this.editing = false;
      } catch { /* silent */ }
      finally { this.saving = false; }
    },
    async handleToggleAnonymous() {
      try {
        const res = await toggleAnonymous();
        this.user.anonymous = res.anonymous;
        if (window.cnwData?.currentUser) {
          window.cnwData.currentUser.anonymous = res.anonymous;
        }
        window.dispatchEvent(new CustomEvent('cnw-anonymous-updated', { detail: res.anonymous }));
      } catch { /* silent */ }
    },
    triggerUpload() {
      this.$refs.fileInput.click();
    },
    async handleAvatarUpload() {
      const file = this.$refs.fileInput.files[0];
      if (!file) return;
      try {
        const res = await uploadAvatar(file);
        if (res.success && res.avatar) {
          this.user.avatar = res.avatar;
          if (window.cnwData?.currentUser) {
            window.cnwData.currentUser.avatar = res.avatar;
          }
          window.dispatchEvent(new CustomEvent('cnw-avatar-updated', { detail: res.avatar }));
        }
      } catch { /* silent */ }
      this.$refs.fileInput.value = '';
    },
    buildTabs() {
      const base = [
        { key: 'answers', label: 'Answers' },
        { key: 'questions', label: 'Questions' },
        { key: 'saved', label: 'Saved' },
      ];
      // Only show Activity tab if user allows it (or it's own profile)
      if (this.user.show_activity !== false) {
        base.push({ key: 'activity', label: 'Activity' });
      }
      base.push(
        { key: 'warnings', label: 'Warnings & Suspensions' },
        { key: 'stats', label: 'Stats' },
      );
      if (this.user.is_own) {
        base.push({ key: 'settings', label: 'Settings' });
      }
      this.tabs = base;
    },
    async loadPreferences() {
      this.loadingPrefs = true;
      try {
        this.preferences = await getPreferences();
      } catch {}
      finally { this.loadingPrefs = false; }
    },
    async savePreferences() {
      this.savingPrefs = true;
      this.prefsSaved = false;
      try {
        this.preferences = await updatePreferences(this.preferences);
        this.prefsSaved = true;
        setTimeout(() => { this.prefsSaved = false; }, 2500);
      } catch {}
      finally { this.savingPrefs = false; }
    },
    truncate(str, len) {
      if (!str) return '';
      return str.length > len ? str.slice(0, len) + '...' : str;
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
.cnw-profile-view {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* ── Profile Header ─────────────────────────────────────────── */
.profile-header {
  display: flex;
  gap: var(--space-m, 28px);
  align-items: center;
}
.profile-header-left {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--space-xs, 14px);
  flex-shrink: 0;
}
.profile-avatar {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
}
.profile-upload-btn {
  padding: var(--space-3xs, 7px) var(--space-s, 19.8px);
  border: 1px solid #414141;
  background: #fff;
  border-radius: var(--radius-s, 2px);
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 1;
  color: var(--text-body, #414141);
  cursor: pointer;
  white-space: nowrap;
  text-align: center;
}

.profile-header-info {
  display: flex;
  flex-direction: column;
  gap: var(--space-2xs, 9.9px);
  justify-content: center;
  flex: 1;
  min-height: 1px;
  min-width: 1px;
}
.profile-joined {
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: #999;
}
.profile-name-row {
  display: flex;
  align-items: center;
  gap: var(--space-2xs, 9.9px);
  flex-wrap: wrap;
}
.profile-name {
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  font-size: var(--text-m, 18px);
  line-height: 24.5px;
  color: #000;
}
.profile-name-row .cnw-social-worker-verified {
  width: 14px;
  height: 14px;
  font-size: 9px;
}
.profile-suspended-icon {
  display: flex;
  align-items: center;
}
.profile-suspended-label {
  font-family: 'Poppins', sans-serif;
  font-weight: 500;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: #e74c3c;
}
.profile-verified-label {
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: #999;
}
.profile-title {
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: var(--text-body, #414141);
}
.profile-helpful {
  display: flex;
  align-items: center;
  gap: var(--space-4xs, 4.95px);
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: var(--text-body, #414141);
}
.profile-helpful svg {
  flex-shrink: 0;
}
.profile-stats-row {
  display: flex;
  align-items: center;
  gap: var(--space-2xs, 9.9px);
  flex-wrap: wrap;
}
.profile-stats-divider {
  width: 1px;
  height: 14px;
  background: #ccc;
  flex-shrink: 0;
}
.profile-reputation {
  display: flex;
  align-items: center;
  gap: var(--space-4xs, 4.95px);
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: var(--text-body, #414141);
}
.profile-reputation svg {
  flex-shrink: 0;
}

/* ── Profile Action Buttons ─────────────────────────────────── */
.profile-action-buttons {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 10px;
  flex-wrap: wrap;
}
.profile-action-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 18px;
  border: none;
  border-radius: 4px;
  font-family: 'Poppins', sans-serif;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.15s, opacity 0.15s;
  white-space: nowrap;
}
.profile-action-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.profile-action-message {
  background: var(--primary, #3aa9da);
  color: #fff;
}
.profile-action-message:hover { opacity: 0.85; }
.profile-action-connect {
  background: var(--secondary, #22a55b);
  color: #fff;
}
.profile-action-connect:hover { opacity: 0.85; }
.profile-action-accept {
  background: var(--secondary, #22a55b);
  color: #fff;
}
.profile-action-accept:hover { opacity: 0.85; }
.profile-action-decline {
  background: #f0f0f0;
  color: #666;
}
.profile-action-decline:hover { background: #e0e0e0; }
.profile-action-pending {
  background: #f0f0f0;
  color: #999;
}

/* ── Anonymous Toggle ───────────────────────────────────────── */
.profile-anon-row {
  display: flex;
  justify-content: flex-end;
}

/* ── Personal Info Card ─────────────────────────────────────── */
.profile-info-card {
  border: var(--radius-xs, 1px) solid var(--border);
  border-radius: var(--radius);
  padding: var(--space-s, 19.8px);
}
.profile-info-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: var(--space-xs, 14px);
}
.profile-info-header h3 {
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  font-size: var(--text-m, 16px);
  line-height: 24.5px;
  color: #000;
}
.profile-edit-btn {
  display: flex;
  align-items: center;
  gap: var(--space-4xs, 4.95px);
  background: none;
  border: none;
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: var(--primary);
  cursor: pointer;
}
.profile-edit-btn:hover {
  color: var(--teal-dark);
}
.profile-edit-actions {
  display: flex;
  gap: var(--space-2xs, 9.9px);
}
.profile-save-btn {
  padding: var(--space-3xs, 7px) var(--space-xs, 14px);
  background: var(--primary);
  color: #fff;
  border: none;
  border-radius: var(--radius-s, 2px);
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  cursor: pointer;
}
.profile-save-btn:hover { background: var(--secondary); }
.profile-save-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.profile-cancel-btn {
  padding: var(--space-3xs, 7px) var(--space-xs, 14px);
  background: none;
  border: var(--radius-xs, 1px) solid var(--border);
  border-radius: var(--radius-s, 2px);
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  cursor: pointer;
  color: var(--text-body, #414141);
}
.profile-info-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: var(--space-xs, 14px);
}
.profile-info-field {
  display: flex;
  flex-direction: column;
  gap: var(--space-4xs, 4.95px);
}
.profile-info-field label {
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: #999;
}
.profile-info-field span {
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: var(--text-body, #414141);
}
.profile-input {
  padding: var(--space-3xs, 7px) var(--space-2xs, 9.9px);
  border: var(--radius-xs, 1px) solid var(--border);
  border-radius: var(--radius-s, 2px);
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: var(--text-body, #414141);
  width: 100%;
}
.profile-input:focus {
  outline: none;
  border-color: var(--primary);
}
/* ── Edit Profile Modal ────────────────────────────────────── */
.profile-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}
.profile-modal {
  background: #fff;
  border-radius: var(--radius, 8px);
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}
.profile-modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--space-s, 19.8px) var(--space-s, 19.8px) 0;
}
.profile-modal-header h3 {
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  font-size: var(--text-m, 16px);
  color: #000;
  margin: 0;
}
.profile-modal-close {
  background: none;
  border: none;
  font-size: 24px;
  color: #999;
  cursor: pointer;
  line-height: 1;
  padding: 0;
}
.profile-modal-close:hover {
  color: #000;
}
.profile-modal-body {
  padding: var(--space-s, 19.8px);
  display: flex;
  flex-direction: column;
  gap: var(--space-xs, 14px);
}
.profile-modal-field {
  display: flex;
  flex-direction: column;
  gap: var(--space-4xs, 4.95px);
}
.profile-modal-field label {
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  color: #999;
}
.profile-modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: var(--space-2xs, 9.9px);
  padding: 0 var(--space-s, 19.8px) var(--space-s, 19.8px);
}

/* ── Tabs ───────────────────────────────────────────────────── */
.profile-tabs {
  display: flex;
  justify-content: center;
  gap: 0;
  background: var(--bg);
  border-radius: var(--radius-m);
  padding: var(--space-3xs);
  margin-top: var(--space-s);
}
.profile-tab {
  display: flex;
  align-items: center;
  gap: var(--space-4xs, 4.95px);
  padding: var(--space-2xs, 9.9px) var(--space-s, 19.8px);
  background: none;
  border: none;
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: var(--text-body, #414141);
  cursor: pointer;
  transition: color 0.15s, background 0.15s;
}
.profile-tab:hover {
  color: #000;
}
.profile-tab.is-active {
  color: #fff;
  background: var(--primary);
  border-radius: var(--radius-m);
}

/* ── Tab Content ────────────────────────────────────────────── */
.profile-tab-content {
  min-height: 200px;
}
.profile-cards-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--space-xs, 14px);
}
.profile-content-card {
  border: var(--radius-xs, 1px) solid var(--primary);
  border-radius: var(--radius);
  padding: var(--space-xs, 14px);
  cursor: pointer;
  transition: box-shadow 0.15s;
  display: flex;
  flex-direction: column;
  gap: var(--space-2xs, 9.9px);
}
.profile-content-card:hover {
  box-shadow: var(--shadow-md);
}
.profile-card-title {
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: #000;
}
.profile-card-excerpt {
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: var(--text-body, #414141);
}
.profile-card-tags {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-4xs, 4.95px);
}
.profile-card-meta {
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: #999;
}

/* ── Activity List ─────────────────────────────────────────── */
/* Warnings & Suspensions tab */
.profile-warnings-list {
  display: flex;
  flex-direction: column;
  gap: var(--space-xs, 14px);
}
.profile-warning-card {
  background: var(--white, #fff);
  border-radius: var(--radius-m, 12px);
  padding: var(--space-m, 16px);
  display: flex;
  flex-direction: column;
  gap: var(--space-2xs, 8px);
  border: 1px solid var(--border, #e0e0e0);
}
.profile-warning-card--warning {
  border-left: 4px solid #f39c12;
}
.profile-warning-card--suspension {
  border-left: 4px solid #e74c3c;
}
.profile-warning-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.profile-warning-type {
  font-size: 11px;
  font-weight: 600;
  padding: 2px 10px;
  border-radius: 10px;
  text-transform: uppercase;
}
.pw-type-warning {
  background: #fff8e1;
  color: #f57f17;
}
.pw-type-suspension {
  background: #ffebee;
  color: #c62828;
}
.profile-warning-date {
  font-size: var(--text-xs, 12px);
  color: #999;
}
.profile-warning-delete {
  background: none;
  border: none;
  color: #999;
  cursor: pointer;
  padding: 4px;
  margin-left: auto;
  border-radius: 4px;
  display: flex;
  align-items: center;
  transition: color 0.2s, background 0.2s;
}
.profile-warning-delete:hover { color: #e74c3c; background: #ffebee; }
.profile-warning-delete:disabled { opacity: 0.4; cursor: not-allowed; }
.profile-warning-reason {
  font-size: var(--text-xs, 13px);
  color: var(--text-body, #444);
  line-height: 1.5;
}
.profile-warning-expires {
  font-size: var(--text-xs, 12px);
  color: #e65100;
  font-weight: 500;
}
.profile-warning-permanent {
  color: #c62828;
}
.profile-warning-by {
  font-size: var(--text-xs, 12px);
  color: #999;
}

.profile-activity-list {
  display: flex;
  flex-direction: column;
  gap: 0;
}
.profile-activity-row {
  display: flex;
  align-items: flex-start;
  gap: var(--space-xs, 14px);
  padding: var(--space-xs, 14px) 0;
  border-bottom: 1px solid var(--border);
}
.profile-activity-row:last-child {
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
.activity-icon--received_vote { background: #fff3e0; color: #f57c00; }
.activity-icon--best_answer,
.activity-icon--marked_solution { background: #e8f5e9; color: #22a55b; }
.activity-icon--login { background: #f3e5f5; color: #7b1fa2; }
.activity-icon--thread_saved,
.activity-icon--thread_unsaved { background: #fce4ec; color: #c62828; }
.activity-icon--registered { background: #e0f7fa; color: #00838f; }
.activity-icon--vote_removed,
.activity-icon--vote_changed { background: #fff3e0; color: #f57c00; }
.activity-icon--thread_updated,
.activity-icon--reply_updated { background: #e3f2fd; color: #1976d2; }
.activity-icon--thread_deleted,
.activity-icon--reply_deleted { background: #ffebee; color: #c62828; }
.activity-icon--logout { background: #f3e5f5; color: #7b1fa2; }
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

/* ── Pagination ────────────────────────────────────────────── */
.profile-pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: var(--space-4xs, 4.95px);
  margin-top: var(--space-s, 19.8px);
}
.profile-pagination button {
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
.profile-pagination button:hover:not(:disabled) {
  border-color: var(--primary);
  color: var(--primary);
}
.profile-pagination button:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
.profile-page-btn.is-active {
  background: var(--primary);
  color: #fff;
  border-color: var(--primary);
}

/* ── Empty State ────────────────────────────────────────────── */
.profile-restricted-notice {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 40px 20px;
  text-align: center;
  background: #fff;
  border: 1px solid var(--border);
  border-radius: var(--radius);
  color: var(--text-med);
  font-size: 15px;
}
.profile-restricted-notice svg {
  color: var(--text-light);
}
.profile-restricted-pending {
  font-size: 13px;
  color: var(--text-light);
  font-style: italic;
}

.profile-empty-state {
  border: var(--radius-xs, 1px) solid var(--primary);
  border-radius: var(--radius);
  padding: var(--space-m, 28px) var(--space-s, 19.8px);
  text-align: center;
}
.profile-empty-title {
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  font-size: var(--text-m, 16px);
  line-height: 24.5px;
  color: #000;
  margin-bottom: var(--space-4xs, 4.95px);
}
.profile-empty-desc {
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  color: var(--text-body, #414141);
}
.profile-empty-link {
  display: inline-block;
  margin-top: var(--space-2xs, 9.9px);
  padding: var(--space-3xs, 7px) var(--space-s, 19.8px);
  background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
  color: #fff;
  border-radius: var(--radius-s, 2px);
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  line-height: 16px;
  text-decoration: none;
}

/* ── Stats Grid ─────────────────────────────────────────────── */
.profile-stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: var(--space-xs, 14px);
}
.stats-card {
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: var(--space-s, 19.8px);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--space-3xs, 7px);
  text-align: center;
  transition: box-shadow 0.15s;
}
.stats-card:hover {
  box-shadow: var(--shadow-md);
}
.stats-card-icon {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}
.stats-card-icon--reputation { background: #fff8e1; color: #f5a623; }
.stats-card-icon--questions { background: #e3f2fd; color: #1976d2; }
.stats-card-icon--answers { background: #e8f5e9; color: #388e3c; }
.stats-card-icon--helpful { background: #fce4ec; color: #e53935; }
.stats-card-icon--joined { background: #f3e5f5; color: #7b1fa2; }
.stats-card-icon--ratio { background: #e0f7fa; color: #00838f; }
.stats-card-value {
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  font-size: 22px;
  color: #000;
  line-height: 1.2;
}
.stats-card-label {
  font-family: 'Poppins', sans-serif;
  font-weight: 300;
  font-size: var(--text-xs, 14px);
  color: #999;
  line-height: 1.2;
}

/* ── Responsive ─────────────────────────────────────────────── */
@media (max-width: 760px) {
  .profile-header {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }
  .profile-header-info {
    align-items: center;
  }
  .profile-name-row {
    justify-content: center;
  }
  .profile-helpful {
    justify-content: center;
  }
  .profile-anon-row {
    justify-content: center;
  }
  .profile-info-grid {
    grid-template-columns: 1fr;
  }
  .profile-cards-grid {
    grid-template-columns: 1fr;
  }
  .profile-tabs {
    flex-direction: column;
    gap: var(--space-3xs, 7px);
    align-items: stretch;
  }
  .profile-tab {
    justify-content: center;
    padding: 10px 14px;
    font-size: 13px;
  }
  .profile-activity-row .activity-body {
    text-align: left;
  }
  .profile-activity-row .activity-meta {
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
  }
  .profile-activity-row .activity-link {
    display: block;
    margin-left: 0;
    margin-top: 4px;
  }
  .profile-stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .settings-select {
    max-width: 50%;
    min-width: 120px;
  }
}

/* ── Settings Tab ─────────────────────────────────────────── */
.profile-settings {
  display: flex;
  flex-direction: column;
  gap: 24px;
}
.settings-section {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: 10px;
  overflow: hidden;
}
.settings-section-header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 16px 20px;
  border-bottom: 1px solid var(--border);
  background: var(--bg);
}
.settings-section-header h3 {
  margin: 0;
  font-size: 15px;
  font-weight: 600;
  color: var(--text-dark);
}
.settings-section-header svg {
  color: var(--primary);
}
.settings-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 14px 20px;
  border-bottom: 1px solid var(--border);
}
.settings-row:last-child {
  border-bottom: none;
}
.settings-row-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
  flex: 1;
  min-width: 0;
}
.settings-label {
  font-size: 13.5px;
  font-weight: 500;
  color: var(--text-dark);
}
.settings-desc {
  font-size: 12px;
  color: var(--text-light);
}
.settings-select {
  padding: 7px 12px;
  border: 1px solid var(--border);
  border-radius: 6px;
  font-size: 13px;
  font-family: inherit;
  color: var(--text-body);
  background: var(--white);
  cursor: pointer;
  min-width: 160px;
  max-width: 40%;
  flex-shrink: 0;
}
.settings-select:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(58, 169, 218, 0.1);
}

/* Toggle switch */
.settings-toggle {
  background: none;
  border: none;
  padding: 0;
  cursor: pointer;
  flex-shrink: 0;
}
.settings-toggle-track {
  display: block;
  width: 40px;
  height: 22px;
  background: #ccc;
  border-radius: 11px;
  position: relative;
  transition: background 0.2s;
}
.settings-toggle.on .settings-toggle-track {
  background: var(--primary);
}
.settings-toggle-thumb {
  display: block;
  width: 18px;
  height: 18px;
  background: #fff;
  border-radius: 50%;
  position: absolute;
  top: 2px;
  left: 2px;
  transition: transform 0.2s;
  box-shadow: 0 1px 3px rgba(0,0,0,0.15);
}
.settings-toggle.on .settings-toggle-thumb {
  transform: translateX(18px);
}

/* Save footer */
.settings-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 12px;
}
.settings-saved-msg {
  font-size: 13px;
  color: var(--green);
  font-weight: 500;
}
.settings-save-btn {
  padding: 10px 28px;
  background: var(--primary);
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  transition: all 0.2s;
}
.settings-save-btn:hover {
  background: var(--teal-dark);
  box-shadow: 0 2px 8px rgba(58, 169, 218, 0.3);
}
.settings-save-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
