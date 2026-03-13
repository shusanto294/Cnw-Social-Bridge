<template>
  <div class="moderation-view">
    <!-- Page header -->
    <div class="mod-page-header">
      <div class="mod-page-header-text">
        <h2 class="mod-title">Moderation Dashboard</h2>
        <p class="mod-subtitle">Manage reports, warnings and user suspensions</p>
      </div>
    </div>

    <!-- Stats cards -->
    <div class="mod-stats">
      <div class="mod-stat-card mod-stat-open" role="button" tabindex="0" @click="showFiltered('reports', 'open')" @keydown.enter="showFiltered('reports', 'open')" aria-label="View open reports">
        <div class="mod-stat-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <span class="mod-stat-num">{{ stats.open_reports || 0 }}</span>
        <span class="mod-stat-label">Open Reports</span>
      </div>
      <div class="mod-stat-card mod-stat-progress" role="button" tabindex="0" @click="showFiltered('reports', 'in_progress')" @keydown.enter="showFiltered('reports', 'in_progress')" aria-label="View in progress reports">
        <div class="mod-stat-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <span class="mod-stat-num">{{ stats.in_progress_reports || 0 }}</span>
        <span class="mod-stat-label">In Progress</span>
      </div>
      <div class="mod-stat-card mod-stat-warn" role="button" tabindex="0" @click="showFiltered('warnings', 'warning')" @keydown.enter="showFiltered('warnings', 'warning')" aria-label="View warnings">
        <div class="mod-stat-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <span class="mod-stat-num">{{ stats.total_warnings || 0 }}</span>
        <span class="mod-stat-label">Warnings</span>
      </div>
      <div class="mod-stat-card mod-stat-suspend" role="button" tabindex="0" @click="showFiltered('warnings', 'suspension')" @keydown.enter="showFiltered('warnings', 'suspension')" aria-label="View suspensions">
        <div class="mod-stat-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
        </div>
        <span class="mod-stat-num">{{ stats.active_suspensions || 0 }}</span>
        <span class="mod-stat-label">Suspensions</span>
      </div>
      <div class="mod-stat-card mod-stat-closed" role="button" tabindex="0" @click="showFiltered('reports', 'closed')" @keydown.enter="showFiltered('reports', 'closed')" aria-label="View closed reports">
        <div class="mod-stat-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <span class="mod-stat-num">{{ stats.closed_reports || 0 }}</span>
        <span class="mod-stat-label">Closed</span>
      </div>
    </div>

    <!-- Tabs -->
    <div class="mod-tabs" role="tablist">
      <button class="mod-tab" :class="{ active: tab === 'reports' }" @click="tab = 'reports'" role="tab" :aria-selected="tab === 'reports'">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        Reports
      </button>
      <button class="mod-tab" :class="{ active: tab === 'warnings' }" @click="tab = 'warnings'" role="tab" :aria-selected="tab === 'warnings'">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        Warnings &amp; Suspensions
      </button>
      <button class="mod-tab" :class="{ active: tab === 'actions' }" @click="tab = 'actions'" role="tab" :aria-selected="tab === 'actions'">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Take Action
      </button>
    </div>

    <!-- Reports Tab -->
    <div v-if="tab === 'reports'" class="mod-panel">
      <div class="mod-toolbar">
        <select v-model="reportFilter" class="mod-select" @change="reportPage = 1; loadReports()">
          <option value="">All Reports</option>
          <option value="open">Open</option>
          <option value="in_progress">In Progress</option>
          <option value="resolved">Resolved</option>
          <option value="closed">Closed</option>
        </select>
      </div>
      <div v-if="loadingReports" class="mod-report-list">
        <div v-for="n in 3" :key="n" class="cnw-skeleton-card" style="padding:14px;gap:10px">
          <div class="cnw-skeleton-row" style="gap:6px">
            <div class="cnw-skeleton cnw-skeleton-line" style="width:60px;height:22px;border-radius:var(--radius-pill)"></div>
            <div class="cnw-skeleton cnw-skeleton-line" style="width:50px;height:22px;border-radius:var(--radius-pill)"></div>
          </div>
          <div class="cnw-skeleton cnw-skeleton-line-lg" style="width:55%"></div>
          <div class="cnw-skeleton cnw-skeleton-line" style="width:90%"></div>
          <div class="cnw-skeleton cnw-skeleton-line-sm" style="width:25%"></div>
        </div>
      </div>
      <div v-else-if="reports.length === 0" class="mod-empty">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        <span>No reports found.</span>
      </div>
      <div v-else class="mod-report-list">
        <div v-for="report in reports" :key="report.id" class="mod-report-card">
          <div class="mod-report-top">
            <div class="mod-report-badges">
              <span class="mod-badge mod-badge-type">{{ formatReportType(report.type) }}</span>
              <span class="mod-badge" :class="'mod-badge-' + report.status">{{ report.status.replace('_', ' ') }}</span>
            </div>
            <span class="mod-report-date">{{ formatDate(report.created_at) }}</span>
          </div>
          <div class="mod-report-body">
            <h4 class="mod-report-subject">{{ report.subject }}</h4>
            <p class="mod-report-desc">{{ report.description }}</p>
          </div>
          <div class="mod-report-meta">
            <span class="mod-report-reporter">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              {{ report.reporter_name }}
            </span>
            <a v-if="report.content_type === 'reply' && report.thread_id" href="#" class="mod-report-view-link" @click.prevent="viewReportedContent(report)">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
              View Content
            </a>
            <a v-else-if="report.content_type === 'thread' && report.content_id" href="#" class="mod-report-view-link" @click.prevent="viewReportedContent(report)">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
              View Content
            </a>
            <a v-else-if="report.link" :href="report.link" target="_blank" class="mod-report-view-link">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
              View Content
            </a>
          </div>
          <div class="mod-report-actions">
            <select v-model="report._newStatus" class="mod-select">
              <option value="open">Open</option>
              <option value="in_progress">In Progress</option>
              <option value="resolved">Resolved</option>
              <option value="closed">Closed</option>
            </select>
            <input v-model="report._notes" class="mod-input-sm" placeholder="Admin notes..." aria-label="Admin notes" />
            <button class="mod-btn mod-btn-primary" @click="handleUpdateReport(report)" :disabled="report._saving">
              {{ report._saving ? 'Saving...' : 'Update' }}
            </button>
          </div>
        </div>
      </div>
      <!-- Pagination -->
      <nav v-if="!loadingReports && reports.length > 0 && reportTotalPages > 1" class="mod-pagination" aria-label="Reports pagination">
        <button class="mod-page-btn" :disabled="reportPage <= 1" @click="goReportPage(reportPage - 1)" aria-label="Previous page">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
        <template v-for="p in reportTotalPages" :key="p">
          <button class="mod-page-btn" :class="{ 'mod-page-active': p === reportPage }" @click="goReportPage(p)" :aria-label="'Page ' + p" :aria-current="p === reportPage ? 'page' : undefined">{{ p }}</button>
        </template>
        <button class="mod-page-btn" :disabled="reportPage >= reportTotalPages" @click="goReportPage(reportPage + 1)" aria-label="Next page">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
      </nav>
    </div>

    <!-- Warnings Tab -->
    <div v-if="tab === 'warnings'" class="mod-panel">
      <div class="mod-toolbar">
        <select v-model="warningTypeFilter" class="mod-select">
          <option value="">All</option>
          <option value="warning">Warnings Only</option>
          <option value="suspension">Suspensions Only</option>
        </select>
        <div class="mod-user-search mod-user-search--inline">
          <div class="mod-search-input-wrap">
            <input
              v-model="warningUserSearch"
              class="mod-input mod-input--with-icon"
              placeholder="Filter by user name..."
              aria-label="Filter warnings by user name"
              @input="searchWarningUsers"
              @focus="warningUserDropdownOpen = true"
            />
            <svg class="mod-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          </div>
          <div v-if="warningUserSelected" class="mod-selected-user">
            <img :src="warningUserSelected.avatar" class="mod-selected-avatar" />
            <span>{{ warningUserSelected.name }}</span>
            <button class="mod-clear-user" @click="clearWarningUserFilter">&times;</button>
          </div>
          <div v-if="warningUserDropdownOpen && warningUserResults.length > 0 && !warningUserSelected" class="mod-user-dropdown">
            <div
              v-for="u in warningUserResults"
              :key="u.id"
              class="mod-user-option"
              @mousedown.prevent="selectWarningUser(u)"
            >
              <img :src="u.avatar" class="mod-option-avatar" />
              <div class="mod-option-info">
                <span class="mod-option-name">{{ u.name }}</span>
                <span class="mod-option-detail">{{ u.email }}</span>
              </div>
            </div>
          </div>
          <div v-if="warningUserDropdownOpen && warningUserSearch.length >= 2 && warningUserResults.length === 0 && !warningUserSearching && !warningUserSelected" class="mod-user-dropdown">
            <div class="mod-user-no-results">No users found</div>
          </div>
        </div>
      </div>
      <div v-if="loadingWarnings" style="display:flex;flex-direction:column;gap:10px">
        <div v-for="n in 3" :key="n" class="cnw-skeleton-card" style="padding:12px;gap:8px">
          <div class="cnw-skeleton-row" style="gap:6px">
            <div class="cnw-skeleton cnw-skeleton-line" style="width:55px;height:20px;border-radius:var(--radius-pill)"></div>
            <div class="cnw-skeleton cnw-skeleton-line-sm" style="width:30%"></div>
          </div>
          <div class="cnw-skeleton cnw-skeleton-line" style="width:75%"></div>
          <div class="cnw-skeleton cnw-skeleton-line-sm" style="width:20%"></div>
        </div>
      </div>
      <div v-else-if="filteredWarnings.length === 0" class="mod-empty">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5" aria-hidden="true"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <span>No {{ warningTypeFilter || 'warnings or suspensions' }} found.</span>
      </div>
      <div v-else class="mod-warning-list">
        <div v-for="w in filteredWarnings" :key="w.id" class="mod-warning-card" :class="w.type === 'suspension' ? 'mod-warning-card--suspend' : ''">
          <div class="mod-warning-left">
            <div class="mod-warning-icon-wrap" :class="w.type === 'suspension' ? 'icon-suspend' : 'icon-warn'">
              <svg v-if="w.type === 'suspension'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
              <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
          </div>
          <div class="mod-warning-content">
            <div class="mod-warning-top-row">
              <span class="mod-badge" :class="w.type === 'suspension' ? 'mod-badge-suspension' : 'mod-badge-warning'">{{ w.type }}</span>
              <span class="mod-warning-user">{{ w.user_name }}</span>
              <span class="mod-warning-date">{{ formatDate(w.created_at) }}</span>
              <button class="mod-btn-icon mod-btn-icon-delete" @click="confirmDeleteWarning(w)" :disabled="w._deleting" title="Delete" aria-label="Delete warning">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
              </button>
            </div>
            <p class="mod-warning-reason">{{ w.reason }}</p>
            <div class="mod-warning-footer">
              <span v-if="w.expires_at" class="mod-warning-expires">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Expires: {{ formatDate(w.expires_at) }}
              </span>
              <span class="mod-warning-by">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                By: {{ w.moderator_name }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Take Action Tab -->
    <div v-if="tab === 'actions'" class="mod-panel">
      <div class="mod-actions-row">
        <div class="mod-action-section mod-action-section--suspend">
          <div class="mod-action-header">
            <div class="mod-action-icon-wrap icon-suspend">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
            </div>
            <h3 class="mod-section-title">Suspend a User</h3>
          </div>
          <div class="mod-action-form">
            <label class="mod-form-label">User</label>
            <div class="mod-user-search">
              <div class="mod-search-input-wrap">
                <input
                  v-model="suspendSearch"
                  class="mod-input mod-input--with-icon"
                  placeholder="Search by name, email or phone..."
                  aria-label="Search user to suspend"
                  @input="searchSuspendUsers"
                  @focus="suspendDropdownOpen = true"
                />
                <svg class="mod-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
              </div>
              <div v-if="suspendSelectedUser" class="mod-selected-user">
                <img :src="suspendSelectedUser.avatar" class="mod-selected-avatar" />
                <span>{{ suspendSelectedUser.name }}</span>
                <span class="mod-selected-email">{{ suspendSelectedUser.email }}</span>
                <button class="mod-clear-user" @click="clearSuspendUser">&times;</button>
              </div>
              <div v-if="suspendDropdownOpen && suspendSearchResults.length > 0 && !suspendSelectedUser" class="mod-user-dropdown">
                <div
                  v-for="u in suspendSearchResults"
                  :key="u.id"
                  class="mod-user-option"
                  @mousedown.prevent="selectSuspendUser(u)"
                >
                  <img :src="u.avatar" class="mod-option-avatar" />
                  <div class="mod-option-info">
                    <span class="mod-option-name">{{ u.name }}</span>
                    <span class="mod-option-detail">{{ u.email }}{{ u.phone ? ' · ' + u.phone : '' }}</span>
                  </div>
                </div>
              </div>
              <div v-if="suspendDropdownOpen && suspendSearch.length >= 2 && suspendSearchResults.length === 0 && !suspendSearching && !suspendSelectedUser" class="mod-user-dropdown">
                <div class="mod-user-no-results">No users found</div>
              </div>
            </div>
            <label class="mod-form-label">Reason</label>
            <textarea v-model="suspendReason" class="mod-textarea" placeholder="Reason for suspension..." rows="3"></textarea>
            <label class="mod-form-label">Duration</label>
            <select v-model="suspendDuration" class="mod-select">
              <option :value="1">1 day</option>
              <option :value="3">3 days</option>
              <option :value="7">7 days</option>
              <option :value="14">14 days</option>
              <option :value="30">30 days</option>
              <option :value="0">Permanent</option>
            </select>
            <button class="mod-btn mod-btn-danger" @click="handleSuspend" :disabled="!suspendUserId || !suspendReason.trim() || suspendSending">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
              {{ suspendSending ? 'Suspending...' : 'Suspend User' }}
            </button>
            <div v-if="suspendSuccess" class="mod-success" aria-live="polite">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
              User suspended successfully.
            </div>
          </div>
        </div>

        <div class="mod-action-section mod-action-section--warn">
          <div class="mod-action-header">
            <div class="mod-action-icon-wrap icon-warn">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <h3 class="mod-section-title">Warn a User</h3>
          </div>
          <div class="mod-action-form">
            <label class="mod-form-label">User</label>
            <div class="mod-user-search">
              <div class="mod-search-input-wrap">
                <input
                  v-model="warnSearch"
                  class="mod-input mod-input--with-icon"
                  placeholder="Search by name, email or phone..."
                  aria-label="Search user to warn"
                  @input="searchWarnUsers"
                  @focus="warnDropdownOpen = true"
                />
                <svg class="mod-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
              </div>
              <div v-if="warnSelectedUser" class="mod-selected-user">
                <img :src="warnSelectedUser.avatar" class="mod-selected-avatar" />
                <span>{{ warnSelectedUser.name }}</span>
                <span class="mod-selected-email">{{ warnSelectedUser.email }}</span>
                <button class="mod-clear-user" @click="clearWarnUser">&times;</button>
              </div>
              <div v-if="warnDropdownOpen && warnSearchResults.length > 0 && !warnSelectedUser" class="mod-user-dropdown">
                <div
                  v-for="u in warnSearchResults"
                  :key="u.id"
                  class="mod-user-option"
                  @mousedown.prevent="selectWarnUser(u)"
                >
                  <img :src="u.avatar" class="mod-option-avatar" />
                  <div class="mod-option-info">
                    <span class="mod-option-name">{{ u.name }}</span>
                    <span class="mod-option-detail">{{ u.email }}{{ u.phone ? ' · ' + u.phone : '' }}</span>
                  </div>
                </div>
              </div>
              <div v-if="warnDropdownOpen && warnSearch.length >= 2 && warnSearchResults.length === 0 && !warnSearching && !warnSelectedUser" class="mod-user-dropdown">
                <div class="mod-user-no-results">No users found</div>
              </div>
            </div>
            <label class="mod-form-label">Reason</label>
            <textarea v-model="warnReason" class="mod-textarea" placeholder="Reason for warning..." rows="3"></textarea>
            <button class="mod-btn mod-btn-warning" @click="handleWarn" :disabled="!warnUserId || !warnReason.trim() || warnSending">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              {{ warnSending ? 'Sending...' : 'Send Warning' }}
            </button>
            <div v-if="warnSuccess" class="mod-success" aria-live="polite">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
              Warning sent successfully.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { getModerationStats, getReports, updateReport, getWarnings, warnUser, suspendUser, getUsers, deleteWarning } from '@/api/index.js';

export default {
  name: 'ModerationView',
  data() {
    return {
      tab: 'reports',
      stats: {},
      reports: [],
      loadingReports: false,
      reportFilter: '',
      reportPage: 1,
      reportTotalPages: 1,
      warnings: [],
      loadingWarnings: false,
      warningTypeFilter: '',
      warningUserSearch: '',
      warningUserResults: [],
      warningUserSelected: null,
      warningUserDropdownOpen: false,
      warningUserSearching: false,
      warningUserTimer: null,
      warnUserId: '',
      warnReason: '',
      warnSending: false,
      warnSuccess: false,
      warnSearch: '',
      warnSearchResults: [],
      warnSelectedUser: null,
      warnDropdownOpen: false,
      warnSearching: false,
      warnSearchTimer: null,
      suspendUserId: '',
      suspendReason: '',
      suspendDuration: 7,
      suspendSending: false,
      suspendSuccess: false,
      suspendSearch: '',
      suspendSearchResults: [],
      suspendSelectedUser: null,
      suspendDropdownOpen: false,
      suspendSearching: false,
      suspendSearchTimer: null,
    };
  },
  watch: {
    tab(val) {
      if (val === 'warnings' && this.warnings.length === 0) this.loadWarnings();
    },
  },
  async mounted() {
    this.loadStats();
    this.loadReports();
    document.addEventListener('click', this.closeDropdowns);
  },
  beforeUnmount() {
    document.removeEventListener('click', this.closeDropdowns);
  },
  computed: {
    filteredWarnings() {
      let list = this.warnings;
      if (this.warningTypeFilter) {
        list = list.filter(w => w.type === this.warningTypeFilter);
      }
      if (this.warningUserSelected) {
        const uid = String(this.warningUserSelected.id);
        list = list.filter(w => String(w.user_id) === uid);
      }
      return list;
    },
  },
  methods: {
    async loadStats() {
      try {
        this.stats = await getModerationStats();
      } catch {}
    },
    async loadReports() {
      this.loadingReports = true;
      try {
        const data = await getReports({ page: this.reportPage, status: this.reportFilter });
        this.reports = (data.reports || []).map(r => ({
          ...r,
          _newStatus: r.status,
          _notes: r.admin_notes || '',
          _saving: false,
        }));
        this.reportTotalPages = data.pages || 1;
      } catch {} finally {
        this.loadingReports = false;
      }
    },
    goReportPage(page) {
      if (page < 1 || page > this.reportTotalPages) return;
      this.reportPage = page;
      this.loadReports();
    },
    async loadWarnings() {
      this.loadingWarnings = true;
      try {
        const data = await getWarnings();
        this.warnings = data.warnings || [];
      } catch {} finally {
        this.loadingWarnings = false;
      }
    },
    async handleUpdateReport(report) {
      report._saving = true;
      try {
        await updateReport(report.id, { status: report._newStatus, admin_notes: report._notes });
        report.status = report._newStatus;
        this.loadStats();
        window.dispatchEvent(new CustomEvent('cnw-moderation-updated'));
      } catch {} finally {
        report._saving = false;
      }
    },
    async handleWarn() {
      this.warnSending = true;
      this.warnSuccess = false;
      try {
        await warnUser(this.warnUserId, { reason: this.warnReason });
        this.warnSuccess = true;
        this.warnUserId = '';
        this.warnReason = '';
        this.warnSelectedUser = null;
        this.warnSearch = '';
        this.loadStats();
        setTimeout(() => { this.warnSuccess = false; }, 3000);
      } catch {} finally {
        this.warnSending = false;
      }
    },
    async handleSuspend() {
      this.suspendSending = true;
      this.suspendSuccess = false;
      try {
        await suspendUser(this.suspendUserId, { reason: this.suspendReason, duration: this.suspendDuration || null });
        this.suspendSuccess = true;
        this.suspendUserId = '';
        this.suspendReason = '';
        this.suspendSelectedUser = null;
        this.suspendSearch = '';
        this.loadStats();
        setTimeout(() => { this.suspendSuccess = false; }, 3000);
      } catch {} finally {
        this.suspendSending = false;
      }
    },
    searchWarningUsers() {
      clearTimeout(this.warningUserTimer);
      if (this.warningUserSearch.length < 2) { this.warningUserResults = []; return; }
      this.warningUserTimer = setTimeout(async () => {
        this.warningUserSearching = true;
        try {
          const data = await getUsers({ search: this.warningUserSearch, per_page: 10 });
          this.warningUserResults = data.users || [];
        } catch {} finally { this.warningUserSearching = false; }
      }, 300);
    },
    selectWarningUser(user) {
      this.warningUserSelected = user;
      this.warningUserSearch = '';
      this.warningUserResults = [];
      this.warningUserDropdownOpen = false;
    },
    clearWarningUserFilter() {
      this.warningUserSelected = null;
      this.warningUserSearch = '';
      this.warningUserResults = [];
    },
    async confirmDeleteWarning(w) {
      const label = w.type === 'suspension' ? 'suspension' : 'warning';
      if (!confirm(`Are you sure you want to delete this ${label} for ${w.user_name}?`)) return;
      w._deleting = true;
      try {
        await deleteWarning(w.id);
        this.warnings = this.warnings.filter(x => x.id !== w.id);
        this.loadStats();
      } catch {} finally {
        w._deleting = false;
      }
    },
    showFiltered(tabName, filter) {
      if (tabName === 'reports') {
        this.tab = 'reports';
        this.reportFilter = filter;
        this.reportPage = 1;
        this.loadReports();
      } else if (tabName === 'warnings') {
        this.tab = 'warnings';
        this.warningTypeFilter = filter;
        if (this.warnings.length === 0) this.loadWarnings();
      }
    },
    viewReportedContent(report) {
      if (report.content_type === 'reply' && report.thread_id) {
        this.$router.push('/thread/' + report.thread_id + '?highlight_reply=' + report.content_id);
      } else if (report.content_type === 'thread' && report.content_id) {
        this.$router.push('/thread/' + report.content_id);
      }
    },
    closeDropdowns(e) {
      if (!e.target.closest('.mod-user-search')) {
        this.warnDropdownOpen = false;
        this.suspendDropdownOpen = false;
      }
    },
    searchWarnUsers() {
      clearTimeout(this.warnSearchTimer);
      if (this.warnSearch.length < 2) { this.warnSearchResults = []; return; }
      this.warnSearchTimer = setTimeout(async () => {
        this.warnSearching = true;
        try {
          const data = await getUsers({ search: this.warnSearch, per_page: 10 });
          this.warnSearchResults = data.users || [];
        } catch {} finally { this.warnSearching = false; }
      }, 300);
    },
    selectWarnUser(user) {
      this.warnSelectedUser = user;
      this.warnUserId = user.id;
      this.warnSearch = '';
      this.warnSearchResults = [];
      this.warnDropdownOpen = false;
    },
    clearWarnUser() {
      this.warnSelectedUser = null;
      this.warnUserId = '';
      this.warnSearch = '';
      this.warnSearchResults = [];
    },
    searchSuspendUsers() {
      clearTimeout(this.suspendSearchTimer);
      if (this.suspendSearch.length < 2) { this.suspendSearchResults = []; return; }
      this.suspendSearchTimer = setTimeout(async () => {
        this.suspendSearching = true;
        try {
          const data = await getUsers({ search: this.suspendSearch, per_page: 10 });
          this.suspendSearchResults = data.users || [];
        } catch {} finally { this.suspendSearching = false; }
      }, 300);
    },
    selectSuspendUser(user) {
      this.suspendSelectedUser = user;
      this.suspendUserId = user.id;
      this.suspendSearch = '';
      this.suspendSearchResults = [];
      this.suspendDropdownOpen = false;
    },
    clearSuspendUser() {
      this.suspendSelectedUser = null;
      this.suspendUserId = '';
      this.suspendSearch = '';
      this.suspendSearchResults = [];
    },
    formatDate(d) {
      if (!d) return '';
      const date = new Date(d);
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
        + ' ' + date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    },
    formatReportType(t) {
      const map = {
        inappropriate_content: 'Inappropriate Content',
        harassment: 'Harassment',
        spam: 'Spam',
        confidentiality: 'Confidentiality',
        misinformation: 'Misinformation',
        technical: 'Technical Issue',
        other: 'Other',
      };
      return map[t] || t;
    },
  },
};
</script>

<style>
/* ── Layout ─────────────────────────────────────────────────── */
.moderation-view {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* ── Page header ────────────────────────────────────────────── */
.mod-page-header {
  display: flex;
  align-items: center;
  gap: 12px;
}
.mod-title {
  font-size: 22px;
  font-weight: 700;
  color: var(--text-dark);
  margin: 0;
  line-height: 1.2;
}
.mod-subtitle {
  font-size: 13px;
  color: var(--text-light);
  margin: 2px 0 0;
  font-weight: 400;
}

/* ── Stats cards ────────────────────────────────────────────── */
.mod-stats {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 12px;
}
.mod-stat-card {
  background: var(--white);
  border-radius: 10px;
  padding: 16px 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  border: 1px solid var(--border);
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
  overflow: hidden;
}
.mod-stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  border-radius: 10px 10px 0 0;
  transition: opacity 0.2s;
}
.mod-stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0,0,0,0.08);
}
.mod-stat-open::before { background: #e65100; }
.mod-stat-progress::before { background: #1565c0; }
.mod-stat-warn::before { background: #f57f17; }
.mod-stat-suspend::before { background: #c62828; }
.mod-stat-closed::before { background: var(--green); }
.mod-stat-open:hover { border-color: #e65100; }
.mod-stat-progress:hover { border-color: #1565c0; }
.mod-stat-warn:hover { border-color: #f57f17; }
.mod-stat-suspend:hover { border-color: #c62828; }
.mod-stat-closed:hover { border-color: var(--green); }
.mod-stat-icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg);
}
.mod-stat-open .mod-stat-icon { color: #e65100; background: #fff3e0; }
.mod-stat-progress .mod-stat-icon { color: #1565c0; background: #e3f2fd; }
.mod-stat-warn .mod-stat-icon { color: #f57f17; background: #fff8e1; }
.mod-stat-suspend .mod-stat-icon { color: #c62828; background: #ffebee; }
.mod-stat-closed .mod-stat-icon { color: var(--green); background: #e8f5e9; }
.mod-stat-num {
  font-size: 26px;
  font-weight: 700;
  color: var(--text-dark);
  line-height: 1;
}
.mod-stat-label {
  font-size: 12px;
  font-weight: 500;
  color: var(--text-light);
  text-align: center;
}

/* ── Tabs ───────────────────────────────────────────────────── */
.mod-tabs {
  display: flex;
  gap: 0;
  border-bottom: 2px solid var(--border);
  background: var(--white);
  border-radius: 10px 10px 0 0;
  padding: 0 4px;
}
.mod-tab {
  background: none;
  border: none;
  padding: 12px 20px;
  font-size: 13px;
  font-weight: 500;
  color: var(--text-light);
  cursor: pointer;
  border-bottom: 2px solid transparent;
  margin-bottom: -2px;
  font-family: inherit;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: color 0.2s;
}
.mod-tab svg { opacity: 0.5; transition: opacity 0.2s; }
.mod-tab:hover { color: var(--text-dark); }
.mod-tab:hover svg { opacity: 0.8; }
.mod-tab.active {
  color: var(--primary);
  border-bottom-color: var(--primary);
  font-weight: 600;
}
.mod-tab.active svg { opacity: 1; stroke: var(--primary); }

/* ── Panel ──────────────────────────────────────────────────── */
.mod-panel {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

/* ── Toolbar (filter row) ───────────────────────────────────── */
.mod-toolbar {
  display: flex;
  gap: 10px;
  align-items: flex-start;
  flex-wrap: wrap;
  background: var(--white);
  padding: 12px 16px;
  border-radius: 10px;
  border: 1px solid var(--border);
}
.mod-user-search--inline {
  min-width: 220px;
  flex: 1;
}

/* ── Form elements ──────────────────────────────────────────── */
.mod-select {
  padding: 9px 32px 9px 12px;
  border: 1px solid var(--border);
  border-radius: 8px;
  font-size: 13px;
  font-family: inherit;
  color: var(--text-body);
  background: var(--white) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23666' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 12px center;
  transition: border-color 0.2s;
  appearance: none;
  box-sizing: border-box;
}
.mod-input, .mod-textarea {
  padding: 9px 12px;
  border: 1px solid var(--border);
  border-radius: 8px;
  font-size: 13px;
  font-family: inherit;
  color: var(--text-body);
  width: 100%;
  box-sizing: border-box;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.mod-input:focus, .mod-textarea:focus, .mod-select:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(58, 169, 218, 0.1);
}
.mod-textarea { resize: vertical; }
.mod-input-sm {
  padding: 7px 10px;
  border: 1px solid var(--border);
  border-radius: 8px;
  font-size: 13px;
  font-family: inherit;
  color: var(--text-body);
  flex: 1;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.mod-input-sm:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(58, 169, 218, 0.1); }
.mod-form-label {
  font-size: 12px;
  font-weight: 600;
  color: var(--text-med);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-top: 4px;
}

/* ── Buttons ────────────────────────────────────────────────── */
.mod-btn {
  padding: 8px 18px;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  font-family: inherit;
  cursor: pointer;
  white-space: nowrap;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s;
}
.mod-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.mod-btn-primary { background: var(--primary); color: #fff; }
.mod-btn-primary:hover:not(:disabled) { background: var(--teal-dark); box-shadow: 0 2px 8px rgba(58, 169, 218, 0.3); }
.mod-btn-warning { background: #f59e0b; color: #fff; }
.mod-btn-warning:hover:not(:disabled) { background: #d97706; box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3); }
.mod-btn-danger { background: #dc2626; color: #fff; }
.mod-btn-danger:hover:not(:disabled) { background: #b91c1c; box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3); }
.mod-btn-icon {
  background: none;
  border: none;
  cursor: pointer;
  padding: 6px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  transition: all 0.2s;
}
.mod-btn-icon-delete { color: var(--text-light); margin-left: auto; }
.mod-btn-icon-delete:hover { color: #dc2626; background: #fef2f2; }
.mod-btn-icon-delete:disabled { opacity: 0.4; cursor: not-allowed; }

/* ── Empty state ────────────────────────────────────────────── */
.mod-empty {
  text-align: center;
  padding: 48px 20px;
  color: var(--text-light);
  font-size: 14px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  background: var(--white);
  border-radius: 10px;
  border: 1px dashed var(--border);
}
.mod-success {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: var(--green);
  font-size: 13px;
  font-weight: 500;
  background: #f0fdf4;
  padding: 8px 14px;
  border-radius: 8px;
  border: 1px solid #bbf7d0;
}

/* ── Report card ────────────────────────────────────────────── */
.mod-report-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.mod-report-card {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 16px 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  transition: box-shadow 0.2s;
}
.mod-report-card:hover {
  box-shadow: 0 2px 12px rgba(0,0,0,0.05);
}
.mod-report-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}
.mod-report-badges {
  display: flex;
  align-items: center;
  gap: 8px;
}
.mod-badge {
  font-size: 11px;
  font-weight: 600;
  padding: 3px 10px;
  border-radius: 20px;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  line-height: 1.4;
}
.mod-badge-type {
  background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
  color: #0369a1;
}
.mod-badge-open { background: #fff7ed; color: #c2410c; }
.mod-badge-in_progress { background: #eff6ff; color: #1d4ed8; }
.mod-badge-resolved { background: #f0fdf4; color: #15803d; }
.mod-badge-closed { background: #f5f5f5; color: #525252; }
.mod-badge-warning { background: #fffbeb; color: #b45309; }
.mod-badge-suspension { background: #fef2f2; color: #b91c1c; }
.mod-report-date {
  font-size: 12px;
  color: var(--text-light);
  white-space: nowrap;
}
.mod-report-body {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.mod-report-subject {
  font-size: 15px;
  font-weight: 600;
  color: var(--text-dark);
  margin: 0;
}
.mod-report-desc {
  font-size: 13px;
  font-weight: 400;
  color: var(--text-med);
  line-height: 1.5;
  margin: 0;
}
.mod-report-meta {
  display: flex;
  align-items: center;
  gap: 16px;
}
.mod-report-reporter {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  color: var(--text-light);
}
.mod-report-reporter svg { stroke: var(--text-light); }
.mod-report-view-link {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  color: var(--primary);
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s;
}
.mod-report-view-link:hover { color: var(--teal-dark); }
.mod-report-view-link svg { stroke: var(--primary); }
.mod-report-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding-top: 10px;
  border-top: 1px solid var(--border);
}
.mod-report-actions .mod-select,
.mod-report-actions .mod-input-sm {
  width: 100%;
}
.mod-report-actions .mod-btn {
  align-self: flex-start;
}

/* ── Warning / Suspension cards ─────────────────────────────── */
.mod-warning-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.mod-warning-card {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 16px 20px;
  display: flex;
  gap: 14px;
  align-items: flex-start;
  transition: box-shadow 0.2s;
}
.mod-warning-card:hover {
  box-shadow: 0 2px 12px rgba(0,0,0,0.05);
}
.mod-warning-card--suspend {
  border-left: 3px solid #dc2626;
}
.mod-warning-card:not(.mod-warning-card--suspend) {
  border-left: 3px solid #f59e0b;
}
.mod-warning-left {
  flex-shrink: 0;
  padding-top: 2px;
}
.mod-warning-icon-wrap,
.mod-action-icon-wrap {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}
.icon-warn { background: #fffbeb; color: #b45309; }
.icon-suspend { background: #fef2f2; color: #b91c1c; }
.mod-warning-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 6px;
  min-width: 0;
}
.mod-warning-top-row {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}
.mod-warning-user {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-dark);
}
.mod-warning-date {
  font-size: 12px;
  color: var(--text-light);
  margin-left: auto;
}
.mod-warning-reason {
  font-size: 13px;
  font-weight: 400;
  color: var(--text-med);
  line-height: 1.5;
  margin: 0;
}
.mod-warning-footer {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}
.mod-warning-expires {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  color: #c2410c;
  font-weight: 500;
}
.mod-warning-expires svg { stroke: #c2410c; }
.mod-warning-by {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  color: var(--text-light);
}
.mod-warning-by svg { stroke: var(--text-light); }

/* ── Take Action cards ──────────────────────────────────────── */
.mod-actions-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
.mod-action-section {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 0;
}
.mod-action-section--suspend {
  border-top: 3px solid #dc2626;
}
.mod-action-section--warn {
  border-top: 3px solid #f59e0b;
}
.mod-action-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 16px;
}
.mod-section-title {
  font-size: 16px;
  font-weight: 600;
  color: var(--text-dark);
  margin: 0;
}
.mod-action-form {
  display: flex;
  flex-direction: column;
  gap: 8px;
  align-items: flex-start;
}
.mod-action-form .mod-user-search,
.mod-action-form .mod-textarea,
.mod-action-form .mod-select,
.mod-action-form .mod-success {
  width: 100%;
}
.mod-action-form .mod-btn {
  width: auto;
  margin-top: 4px;
}

/* ── User search dropdown ───────────────────────────────────── */
.mod-user-search {
  position: relative;
}
.mod-search-input-wrap {
  position: relative;
  width: 100%;
}
.mod-search-input-wrap .mod-input--with-icon {
  padding-right: 36px;
  width: 100%;
  box-sizing: border-box;
}
.mod-search-icon {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: #999;
  pointer-events: none;
}
.mod-selected-user {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #f0fdf4;
  border: 1px solid #86efac;
  border-radius: 8px;
  padding: 6px 12px;
  margin-top: 6px;
  font-size: 13px;
  font-weight: 500;
  color: var(--text-dark);
}
.mod-selected-avatar {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  object-fit: cover;
}
.mod-selected-email {
  color: var(--text-light);
  font-size: 12px;
  font-weight: 400;
  margin-left: auto;
}
.mod-clear-user {
  background: none;
  border: none;
  font-size: 18px;
  color: var(--text-light);
  cursor: pointer;
  padding: 0 2px;
  line-height: 1;
  transition: color 0.2s;
}
.mod-clear-user:hover { color: #dc2626; }
.mod-user-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: 10px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.12);
  z-index: 100;
  max-height: 240px;
  overflow-y: auto;
  margin-top: 4px;
}
.mod-user-option {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  cursor: pointer;
  transition: background 0.15s;
}
.mod-user-option:first-child { border-radius: 10px 10px 0 0; }
.mod-user-option:last-child { border-radius: 0 0 10px 10px; }
.mod-user-option:hover { background: #f8fafc; }
.mod-option-avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
  border: 2px solid var(--border);
}
.mod-option-info {
  display: flex;
  flex-direction: column;
  min-width: 0;
  gap: 1px;
}
.mod-option-name {
  font-size: 13px;
  font-weight: 500;
  color: var(--text-dark);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.mod-option-detail {
  font-size: 12px;
  color: var(--text-light);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.mod-user-no-results {
  padding: 16px;
  text-align: center;
  color: var(--text-light);
  font-size: 13px;
}

/* ── Pagination ─────────────────────────────────────────────── */
.mod-pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  padding: 8px 0;
}
.mod-page-btn {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: 8px;
  width: 36px;
  height: 36px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 500;
  font-family: inherit;
  color: var(--text-med);
  cursor: pointer;
  transition: all 0.2s;
}
.mod-page-btn:hover:not(:disabled):not(.mod-page-active) {
  border-color: var(--primary);
  color: var(--primary);
  background: #f0f9ff;
}
.mod-page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
.mod-page-active {
  background: var(--primary);
  border-color: var(--primary);
  color: #fff;
}

/* ── Responsive ─────────────────────────────────────────────── */
@media (max-width: 760px) {
  .mod-stats { grid-template-columns: repeat(3, 1fr); }
  .mod-report-actions { flex-wrap: wrap; }
  .mod-actions-row { grid-template-columns: 1fr; }
  .mod-warning-card { flex-direction: column; gap: 10px; }
  .mod-warning-left { display: none; }
}
@media (max-width: 480px) {
  .mod-stats { grid-template-columns: 1fr 1fr; }
  .mod-tabs {
    flex-direction: column;
    gap: 0;
    border-bottom: none;
    border-radius: 0;
    overflow: hidden;
    padding: 0;
  }
  .mod-tab {
    padding: 12px 16px;
    font-size: 13px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 0;
    border-left: 3px solid transparent;
    justify-content: flex-start;
  }
  .mod-tab:last-child { border-bottom: none; }
  .mod-tab.active {
    border-bottom-color: var(--border);
    border-left-color: var(--primary);
    background: #f0f9ff;
  }
  .mod-report-card { padding: 12px 14px; }
  .mod-warning-card { padding: 12px 14px; }
  .mod-action-section { padding: 14px; }
  .mod-toolbar { padding: 10px 12px; }
}
</style>
