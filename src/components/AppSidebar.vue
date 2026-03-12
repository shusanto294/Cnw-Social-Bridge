<template>
  <aside class="cnw-social-worker-sidebar" aria-label="Main navigation">
    <!-- User Card -->
    <div class="sidebar-user-card">
      <div class="sidebar-user-bg"></div>
      <div class="sidebar-user-identity">
        <span v-if="currentUser.anonymous" class="sidebar-anon-avatar">
          <svg width="28" height="28" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M7.556 5.91c.504-.334.887-.822 1.093-1.391a2.97 2.97 0 0 0-.653-3.16A2.97 2.97 0 0 0 6 .747a2.97 2.97 0 0 0-1.68.555 2.97 2.97 0 0 0-1.016 1.448 2.97 2.97 0 0 0 1.14 3.16 4.47 4.47 0 0 0-3.114 4.185v.78c0 .1.04.195.11.265a.375.375 0 0 0 .265.11h8.19a.375.375 0 0 0 .375-.375v-.78a4.47 4.47 0 0 0-2.734-4.185zM6.259 5.25a.376.376 0 0 1-.529 0 .376.376 0 0 1 0-.533.375.375 0 0 1 .529 0 .376.376 0 0 1 0 .533zm.112-1.305v.191a.375.375 0 0 1-.75 0v-.491a.375.375 0 0 1 .375-.375.296.296 0 0 0 .296-.296.296.296 0 0 0-.296-.297.296.296 0 0 0-.3.3.375.375 0 0 1-.75 0 1.046 1.046 0 1 1 1.425.968z" fill="#fff"/></svg>
        </span>
        <img
          v-else
          :src="currentUser.avatar || defaultAvatar"
          :alt="displayName"
          class="cnw-social-worker-avatar sidebar-avatar"
          width="72" height="72"
        />
        <router-link v-if="currentUser.id" to="/profile" class="sidebar-username sidebar-username-link">{{ displayName }}</router-link>
        <p v-else class="sidebar-username">{{ displayName }}</p>
        <p v-if="currentUser.id && currentUser.reputation != null" class="sidebar-reputation">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#f5a623" stroke-width="2" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          {{ currentUser.reputation || 0 }} Reputation
        </p>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
      <div class="sidebar-nav-group">
        <router-link to="/" class="sidebar-nav-item" active-class="is-active" exact>
          <span class="nav-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 13 13" fill="none" aria-hidden="true">
              <g clip-path="url(#clip0_20253_9)">
                <path d="M7.50302 10.5244C7.23895 10.2527 6.87841 10.1004 6.50009 10.1004H6.4747C5.67235 10.1004 5.02489 10.7504 5.02489 11.5502C5.02489 12.35 5.67235 13 6.4747 13H6.50009C7.30243 12.9873 7.93973 12.3271 7.92704 11.5248C7.91942 11.149 7.76708 10.791 7.50302 10.5244ZM9.20673 0.997852C8.56688 0.340234 7.65536 0.00507813 6.50009 0H6.4747C5.56063 0 4.78114 0.248828 4.15653 0.741406C3.85946 0.975 3.5954 1.24668 3.36942 1.54883C3.15106 1.84336 2.97333 2.16836 2.84638 2.51367C2.79052 2.66855 2.86161 2.83867 3.01141 2.90469L4.37997 3.50645C4.53739 3.575 4.7202 3.50645 4.78876 3.34902C4.7913 3.34648 4.7913 3.34141 4.79384 3.33887C4.92079 3.0291 5.12391 2.72441 5.39813 2.43496C5.64188 2.17598 5.99481 2.05156 6.4747 2.05156H6.50009C6.99266 2.05664 7.36083 2.18613 7.62489 2.4502C7.89149 2.71934 8.02098 3.04434 8.02098 3.45059C8.02098 3.79082 7.91942 4.10313 7.71122 4.40527C7.4827 4.73789 7.14501 5.10605 6.70829 5.49961C6.6372 5.56563 6.5661 5.6291 6.50262 5.69258C6.11415 6.07344 5.84501 6.42637 5.68505 6.76914C5.5911 6.96973 5.52001 7.22109 5.46923 7.52578C5.41591 7.88379 5.38798 8.24434 5.38798 8.60742C5.38544 8.78008 5.52255 8.91973 5.69266 8.9248H5.70028H7.33036C7.50048 8.9248 7.63759 8.79023 7.64012 8.62012C7.65282 7.92695 7.73153 7.58926 7.79755 7.42676C7.86102 7.26934 8.06669 6.95703 8.68368 6.36289C9.19403 5.87539 9.57489 5.39805 9.81102 4.94609C10.0548 4.47891 10.1792 3.95332 10.1792 3.38457C10.1741 2.46289 9.84911 1.65801 9.20673 0.997852Z" fill="white"/>
              </g>
              <defs>
                <clipPath id="clip0_20253_9">
                  <rect width="13" height="13" fill="white"/>
                </clipPath>
              </defs>
            </svg>
          </span>
          <span>Questions</span>
          <!-- <span class="nav-badge">1</span> -->
        </router-link>

        <router-link to="/tags" class="sidebar-nav-item" active-class="is-active">
          <span class="nav-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 13 13" fill="none" aria-hidden="true">
              <g clip-path="url(#clip0_20253_19)">
                <path d="M5.39378 0H0V5.39378L7.60459 13V7.60622H13L5.39378 0ZM2.9705 4.51791C2.11656 4.51791 1.42472 3.82606 1.42472 2.97213C1.42472 2.11819 2.11656 1.42634 2.9705 1.42634C3.82444 1.42634 4.51628 2.11819 4.51628 2.97213C4.51628 3.82606 3.82444 4.51791 2.9705 4.51791Z" fill="white"/>
              </g>
              <defs>
                <clipPath id="clip0_20253_19">
                  <rect width="13" height="13" fill="white"/>
                </clipPath>
              </defs>
            </svg>
          </span>
          <span>Tags</span>
          <!-- <span class="nav-badge">1</span> -->
        </router-link>

        <router-link to="/ask" class="sidebar-nav-item" active-class="is-active">
          <span class="nav-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 13 13" fill="none" aria-hidden="true">
              <g clip-path="url(#clip0_20253_29)">
                <path d="M8.9375 8.93743C8.82976 8.93743 8.72642 8.89463 8.65024 8.81844C8.57405 8.74226 8.53125 8.63892 8.53125 8.53118V8.12493C8.53125 8.01719 8.57405 7.91385 8.65024 7.83767C8.72642 7.76148 8.82976 7.71868 8.9375 7.71868C9.01785 7.71868 9.09639 7.69485 9.1632 7.65022C9.23001 7.60558 9.28208 7.54213 9.31283 7.4679C9.34357 7.39366 9.35162 7.31198 9.33594 7.23317C9.32027 7.15437 9.28158 7.08198 9.22476 7.02517C9.16795 6.96835 9.09556 6.92966 9.01676 6.91399C8.93795 6.89831 8.85627 6.90636 8.78204 6.9371C8.7078 6.96785 8.64435 7.01992 8.59972 7.08673C8.55508 7.15354 8.53125 7.23208 8.53125 7.31243C8.53125 7.42017 8.48845 7.52351 8.41226 7.59969C8.33608 7.67588 8.23274 7.71868 8.125 7.71868C8.01726 7.71868 7.91392 7.67588 7.83774 7.59969C7.76155 7.52351 7.71875 7.42017 7.71875 7.31243C7.71876 7.089 7.7802 6.86987 7.89634 6.67899C8.01248 6.48812 8.17886 6.33284 8.37729 6.23014C8.57572 6.12743 8.79856 6.08125 9.02146 6.09665C9.24436 6.11204 9.45875 6.18841 9.64117 6.31742C9.8236 6.44642 9.96706 6.62309 10.0559 6.82812C10.1447 7.03315 10.1754 7.25864 10.1447 7.47996C10.114 7.70127 10.023 7.90989 9.88179 8.083C9.74053 8.25612 9.5544 8.38707 9.34375 8.46155V8.53118C9.34375 8.63892 9.30095 8.74226 9.22476 8.81844C9.14858 8.89463 9.04524 8.93743 8.9375 8.93743Z" fill="white"/>
                <path d="M8.9375 10.1562C8.82976 10.1562 8.72642 10.1134 8.65024 10.0373C8.57405 9.96108 8.53125 9.85774 8.53125 9.75V9.54688C8.53125 9.43913 8.57405 9.3358 8.65024 9.25961C8.72642 9.18343 8.82976 9.14062 8.9375 9.14062C9.04524 9.14062 9.14858 9.18343 9.22476 9.25961C9.30095 9.3358 9.34375 9.43913 9.34375 9.54688V9.75C9.34375 9.85774 9.30095 9.96108 9.22476 10.0373C9.14858 10.1134 9.04524 10.1562 8.9375 10.1562Z" fill="white"/>
                <path d="M11.375 4.875H8.53125V2.03125C8.53068 1.60045 8.35929 1.18745 8.05467 0.882831C7.75005 0.578207 7.33705 0.40682 6.90625 0.40625H2.03125C1.60045 0.40682 1.18745 0.578207 0.882831 0.882831C0.578207 1.18745 0.40682 1.60045 0.40625 2.03125V4.875C0.40658 5.23527 0.526403 5.58525 0.746948 5.87012C0.967493 6.15499 1.2763 6.35866 1.625 6.44922V7.71875C1.6251 7.79901 1.64885 7.87746 1.69329 7.94429C1.73772 8.01113 1.80087 8.06339 1.87484 8.09453C1.92456 8.11457 1.97765 8.12492 2.03125 8.125C2.08458 8.12524 2.13742 8.11475 2.18662 8.09416C2.23582 8.07358 2.28038 8.04331 2.31766 8.00516L3.82484 6.5H5.28125V10.1562C5.2816 10.4794 5.41012 10.7892 5.63861 11.0176C5.86709 11.2461 6.17688 11.3746 6.5 11.375H9.61462L11.1312 12.5125C11.1916 12.5578 11.2634 12.5853 11.3385 12.5921C11.4137 12.5989 11.4892 12.5846 11.5567 12.5509C11.6242 12.5171 11.6809 12.4653 11.7206 12.4011C11.7602 12.3369 11.7812 12.2629 11.7812 12.1875V11.3054C12.0187 11.2211 12.2243 11.0654 12.3698 10.8597C12.5152 10.6539 12.5935 10.4082 12.5938 10.1562V6.09375C12.5934 5.77063 12.4649 5.46084 12.2364 5.23236C12.0079 5.00387 11.6981 4.87535 11.375 4.875ZM4.875 4.67188V4.26562H4.0625V4.67188C4.0625 4.77962 4.0197 4.88295 3.94351 4.95914C3.86733 5.03532 3.76399 5.07812 3.65625 5.07812C3.54851 5.07812 3.44517 5.03532 3.36899 4.95914C3.2928 4.88295 3.25 4.77962 3.25 4.67188V3.04688C3.25 2.72364 3.3784 2.41365 3.60696 2.18509C3.83552 1.95653 4.14552 1.82812 4.46875 1.82812C4.79198 1.82812 5.10198 1.95653 5.33054 2.18509C5.5591 2.41365 5.6875 2.72364 5.6875 3.04688V4.67188C5.6875 4.77962 5.6447 4.88295 5.56851 4.95914C5.49233 5.03532 5.38899 5.07812 5.28125 5.07812C5.17351 5.07812 5.07017 5.03532 4.99399 4.95914C4.9178 4.88295 4.875 4.77962 4.875 4.67188ZM11.7812 10.1562C11.7811 10.264 11.7383 10.3672 11.6621 10.4434C11.586 10.5196 11.4827 10.5624 11.375 10.5625C11.2673 10.5625 11.1639 10.6053 11.0877 10.6815C11.0116 10.7577 10.9688 10.861 10.9688 10.9688V11.375L9.99375 10.6438C9.92343 10.591 9.8379 10.5625 9.75 10.5625H6.5C6.39229 10.5624 6.28902 10.5196 6.21286 10.4434C6.13669 10.3672 6.09386 10.264 6.09375 10.1562V6.5H6.90625C7.19111 6.4996 7.47085 6.42432 7.71745 6.28172C7.96404 6.13911 8.16882 5.93419 8.31125 5.6875H11.375C11.4827 5.68761 11.586 5.73044 11.6621 5.80661C11.7383 5.88277 11.7811 5.98604 11.7812 6.09375V10.1562Z" fill="white"/>
                <path d="M4.46875 2.64062C4.36104 2.64073 4.25777 2.68357 4.18161 2.75973C4.10544 2.8359 4.06261 2.93916 4.0625 3.04688V3.45312H4.875V3.04688C4.87489 2.93916 4.83206 2.8359 4.75589 2.75973C4.67973 2.68357 4.57646 2.64073 4.46875 2.64062Z" fill="white"/>
              </g>
              <defs>
                <clipPath id="clip0_20253_29">
                  <rect width="13" height="13" fill="white"/>
                </clipPath>
              </defs>
            </svg>
          </span>
          <span>Ask a Question</span>
        </router-link>

        <router-link to="/activity" class="sidebar-nav-item" active-class="is-active">
          <span class="nav-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 13 13" fill="none" aria-hidden="true">
              <g clip-path="url(#clip0_20253_38)">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M11.7051 13H1.29492C0.837891 13 0.482422 12.6445 0.482422 12.1875V2.89453H12.5176V12.1875C12.5176 12.6445 12.1621 13 11.7051 13ZM0.482422 2.51367V1.75195C0.482422 1.32031 0.837891 0.939453 1.29492 0.939453H5.61133V0.482422C5.61133 0.228516 5.81445 0 6.09375 0H6.62695C6.90625 0 7.10938 0.228516 7.10938 0.482422V0.939453H7.8457V0.482422C7.8457 0.203125 8.04883 0 8.32812 0H8.83594C9.11523 0 9.31836 0.228516 9.31836 0.482422V0.939453H10.0547V0.482422C10.0547 0.203125 10.2578 0 10.5371 0H11.0703C11.3496 0 11.5527 0.228516 11.5527 0.482422V0.939453H11.7051C12.1621 0.939453 12.5176 1.32031 12.5176 1.75195V2.51367H0.482422ZM4.84961 2.1582H3.73242C3.63086 2.1582 3.55469 2.08203 3.55469 1.98047C3.55469 1.87891 3.63086 1.77734 3.73242 1.77734H4.84961C4.95117 1.77734 5.02734 1.87891 5.02734 1.98047C5.02734 2.08203 4.95117 2.1582 4.84961 2.1582ZM2.7168 2.1582H1.59961C1.49805 2.1582 1.42188 2.08203 1.42188 1.98047C1.42188 1.87891 1.49805 1.77734 1.59961 1.77734H2.7168C2.81836 1.77734 2.89453 1.87891 2.89453 1.98047C2.89453 2.08203 2.81836 2.1582 2.7168 2.1582ZM9.87695 8.02344H8.70898V9.16602C9.31836 9.08984 9.77539 8.60742 9.87695 8.02344ZM8.32812 8.02344H7.16016C7.26172 8.60742 7.71875 9.08984 8.32812 9.16602V8.02344ZM7.16016 7.64258H8.32812V6.47461C7.71875 6.55078 7.23633 7.0332 7.16016 7.64258ZM8.70898 7.64258H9.87695C9.80078 7.0332 9.31836 6.55078 8.70898 6.47461V7.64258ZM8.53125 9.57227C7.56641 9.57227 6.7793 8.78516 6.7793 7.82031C6.7793 6.88086 7.56641 6.09375 8.53125 6.09375C9.49609 6.09375 10.2578 6.88086 10.2578 7.82031V7.8457C10.2578 8.78516 9.49609 9.57227 8.53125 9.57227ZM5.78906 9.41992H2.76758C2.66602 9.41992 2.58984 9.34375 2.58984 9.2168V8.125C2.58984 7.66797 2.86914 7.23633 3.30078 7.00781C3.40234 6.95703 3.50391 6.98242 3.55469 7.05859C3.70703 7.26172 3.96094 7.38867 4.26562 7.38867C4.57031 7.38867 4.84961 7.26172 5.00195 7.05859C5.05273 6.98242 5.1543 6.95703 5.23047 7.00781C5.6875 7.23633 5.9668 7.64258 5.9668 8.125V9.2168C5.9668 9.34375 5.89062 9.41992 5.78906 9.41992ZM2.9707 9.03906H5.61133V8.125C5.61133 7.8457 5.45898 7.56641 5.20508 7.41406C4.97656 7.64258 4.64648 7.76953 4.29102 7.76953C3.93555 7.76953 3.58008 7.64258 3.37695 7.41406C3.12305 7.56641 2.9707 7.8457 2.9707 8.125V9.03906ZM4.29102 7.05859C3.68164 7.05859 3.17383 6.55078 3.17383 5.9668C3.17383 5.35742 3.68164 4.875 4.29102 4.875C4.875 4.875 5.38281 5.35742 5.38281 5.9668C5.38281 6.55078 4.875 7.05859 4.29102 7.05859ZM4.29102 5.23047C3.88477 5.23047 3.55469 5.56055 3.55469 5.9668C3.55469 6.37305 3.88477 6.67773 4.29102 6.67773C4.69727 6.67773 5.00195 6.37305 5.00195 5.9668C5.00195 5.56055 4.69727 5.23047 4.29102 5.23047ZM7.49023 11.8574C7.46484 11.8574 7.43945 11.8574 7.41406 11.832C7.33789 11.8066 7.28711 11.7559 7.28711 11.6797V10.7656H2.33594C1.92969 10.7656 1.59961 10.4355 1.59961 10.0293V4.39258C1.59961 4.01172 1.92969 3.68164 2.33594 3.68164H10.5117C10.8926 3.68164 11.2227 3.98633 11.2227 4.39258V10.0293C11.2227 10.4102 10.8926 10.7402 10.5117 10.7402H8.6582L7.61719 11.8066C7.5918 11.832 7.54102 11.8574 7.49023 11.8574ZM2.33594 4.03711C2.1582 4.03711 1.98047 4.21484 1.98047 4.39258V10.0293C1.98047 10.2324 2.1582 10.3848 2.33594 10.3848H7.49023C7.5918 10.3848 7.66797 10.4609 7.66797 10.5625V11.2227L8.45508 10.4355C8.48047 10.4102 8.55664 10.3848 8.58203 10.3848H10.5117C10.7148 10.3848 10.8672 10.2324 10.8672 10.0293V4.39258C10.8672 4.21484 10.7148 4.03711 10.5117 4.03711H2.33594Z" fill="white"/>
              </g>
              <defs>
                <clipPath id="clip0_20253_38">
                  <rect width="13" height="13" fill="white"/>
                </clipPath>
              </defs>
            </svg>
          </span>
          <span>My Activity</span>
        </router-link>

        <router-link to="/messages" class="sidebar-nav-item" active-class="is-active">
          <span class="nav-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 13 13" fill="none" aria-hidden="true">
              <g clip-path="url(#clip0_20253_44)">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M1.625 0C0.72754 0 0 0.730096 0 1.63071V9.24072C0 10.1414 0.72754 10.8714 1.625 10.8714H3.13412C3.25917 10.8714 3.38035 10.9149 3.47713 10.9943L5.471 12.6314C6.06959 13.1229 6.93041 13.1229 7.529 12.6314L9.52288 10.9943C9.61968 10.9149 9.74085 10.8714 9.86586 10.8714H11.375C12.2725 10.8714 13 10.1414 13 9.24072V1.63071C13 0.730096 12.2725 0 11.375 0L1.625 0ZM2.70833 4.34858C2.70833 4.04837 2.95085 3.805 3.25 3.805H5.41667C5.71583 3.805 5.95833 4.04837 5.95833 4.34858C5.95833 4.64878 5.71583 4.89215 5.41667 4.89215H3.25C2.95085 4.89215 2.70833 4.64878 2.70833 4.34858ZM3.25 5.9793C2.95085 5.9793 2.70833 6.22267 2.70833 6.52286C2.70833 6.82305 2.95085 7.06642 3.25 7.06642H9.75C10.0492 7.06642 10.2917 6.82305 10.2917 6.52286C10.2917 6.22267 10.0492 5.9793 9.75 5.9793H3.25Z" fill="white"/>
              </g>
              <defs>
                <clipPath id="clip0_20253_44">
                  <rect width="13" height="13" fill="white"/>
                </clipPath>
              </defs>
            </svg>
          </span>
          <span>Message</span>
          <span v-if="unreadMessageCount > 0" class="nav-badge" :aria-label="unreadMessageCount + ' unread messages'">{{ unreadMessageCount }}</span>
        </router-link>

        <router-link to="/users" class="sidebar-nav-item" active-class="is-active">
          <span class="nav-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 13 13" fill="none" aria-hidden="true">
              <g clip-path="url(#clip0_20253_49)">
                <path d="M6.49998 7.41668C7.33787 7.41668 8.11228 7.18055 8.73182 6.77938C9.2117 6.47215 9.83631 6.52293 10.2578 6.90633C11.4283 7.96766 12.0935 9.47332 12.091 11.0552V11.7331C12.091 12.4339 11.5222 13.0001 10.8215 13.0001H2.1785C1.47771 13.0001 0.908964 12.4339 0.908964 11.7331V11.0552C0.903886 9.47586 1.56912 7.96766 2.73963 6.90887C3.16111 6.52547 3.78826 6.47469 4.26561 6.78192C4.88768 7.18055 5.65955 7.41668 6.49998 7.41668Z" fill="white"/>
                <path d="M6.50005 6.28672C8.23608 6.28672 9.64341 4.87939 9.64341 3.14336C9.64341 1.40733 8.23608 0 6.50005 0C4.76402 0 3.35669 1.40733 3.35669 3.14336C3.35669 4.87939 4.76402 6.28672 6.50005 6.28672Z" fill="white"/>
              </g>
              <defs>
                <clipPath id="clip0_20253_49">
                  <rect width="13" height="13" fill="white"/>
                </clipPath>
              </defs>
            </svg>
          </span>
          <span>Users</span>
          <span v-if="connectionRequestCount > 0" class="nav-badge" :aria-label="connectionRequestCount + ' connection requests'">{{ connectionRequestCount }}</span>
        </router-link>
      </div>

<!-- Support section -->
      <div class="sidebar-nav-group">
        <p class="sidebar-section-label">Support</p>

        <router-link to="/saved" class="sidebar-nav-item" active-class="is-active">
          <span class="nav-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 13 13" fill="none" aria-hidden="true">
              <path d="M6.49984 1.08325C6.64939 1.08325 6.77067 1.20451 6.77067 1.35409V2.16659H6.229V1.35409C6.229 1.20451 6.35028 1.08325 6.49984 1.08325Z" fill="white"/>
              <path d="M3.9 2.1665H6.175V3.95015L5.42983 3.32916C5.30289 3.22339 5.09711 3.22339 4.97019 3.32916C4.84327 3.43493 4.84327 3.60641 4.97019 3.71218L6.0404 4.604C6.29422 4.81554 6.70578 4.81554 6.9596 4.604L8.02983 3.71218C8.15674 3.60641 8.15674 3.43493 8.02983 3.32916C7.90292 3.22339 7.69714 3.22339 7.57017 3.32916L6.825 3.95015V2.1665H9.75C10.6475 2.1665 11.375 2.77279 11.375 3.52067V6.49984H10.5108C9.95193 6.49984 9.40474 6.63325 8.93403 6.88431L7.80293 7.48757C7.02135 7.90438 6.01434 7.8839 5.25816 7.43578L4.43991 6.95088C3.9448 6.65746 3.35234 6.49984 2.74481 6.49984H1.625V4.06234C1.625 3.0153 2.64355 2.1665 3.9 2.1665Z" fill="white"/>
              <path fill-rule="evenodd" clip-rule="evenodd" d="M1.08325 8.27732C1.08325 7.74448 1.46617 7.3125 1.93851 7.3125H3.1341C3.58725 7.3125 4.0269 7.48648 4.38075 7.80585L4.96969 8.33733C5.86888 9.14886 7.13719 9.18515 8.07173 8.42611L8.92036 7.73679C9.26003 7.46092 9.66791 7.3125 10.0864 7.3125H11.0613C11.5337 7.3125 11.9166 7.74448 11.9166 8.27732V10.2071C11.9166 11.4504 11.0231 12.4583 9.92098 12.4583H3.07887C1.97671 12.4583 1.08325 11.4504 1.08325 10.2071V8.27732ZM4.5043 10.8502C4.5043 10.6726 4.63194 10.5286 4.78939 10.5286H8.21045C8.36791 10.5286 8.49553 10.6726 8.49553 10.8502C8.49553 11.0279 8.36791 11.1719 8.21045 11.1719H4.78939C4.63194 11.1719 4.5043 11.0279 4.5043 10.8502Z" fill="white"/>
            </svg>
          </span>
          <span>Saved Threads</span>
        </router-link>

        <router-link to="/guidelines" class="sidebar-nav-item" active-class="is-active">
          <span class="nav-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 13 13" fill="none" aria-hidden="true">
              <g clip-path="url(#clip0_20253_69)">
                <path d="M12.22 3.45312L12.5653 2.58781C12.6141 2.46187 12.6019 2.31969 12.5247 2.21C12.4475 2.10031 12.3216 2.03125 12.1875 2.03125H10.1562V4.875H12.1875C12.3216 4.875 12.4475 4.81 12.5247 4.69625C12.6019 4.5825 12.6141 4.44438 12.5653 4.31844L12.22 3.45312Z" fill="white"/>
                <path d="M5.07812 6.5H7.10938C7.22312 6.5 7.3125 6.41062 7.3125 6.29688V6.09375C7.3125 5.42344 6.76406 4.875 6.09375 4.875C5.42344 4.875 4.875 5.42344 4.875 6.09375V6.29688C4.875 6.41062 4.96437 6.5 5.07812 6.5Z" fill="white"/>
                <path d="M7.51562 8.9375H4.67188C4.55969 8.9375 4.46875 9.02844 4.46875 9.14062V9.95312C4.46875 10.0653 4.55969 10.1562 4.67188 10.1562H7.51562C7.62781 10.1562 7.71875 10.0653 7.71875 9.95312V9.14062C7.71875 9.02844 7.62781 8.9375 7.51562 8.9375Z" fill="white"/>
                <path d="M6.09375 4.0625C6.4303 4.0625 6.70312 3.78967 6.70312 3.45312C6.70312 3.11658 6.4303 2.84375 6.09375 2.84375C5.7572 2.84375 5.48438 3.11658 5.48438 3.45312C5.48438 3.78967 5.7572 4.0625 6.09375 4.0625Z" fill="white"/>
                <path d="M2.84375 12.5938H9.34375V0.40625H2.84375V12.5938ZM4.0625 6.09375C4.0625 5.36656 4.44844 4.73688 5.02125 4.37531C4.80594 4.1275 4.67188 3.80656 4.67188 3.45312C4.67188 2.66906 5.30969 2.03125 6.09375 2.03125C6.87781 2.03125 7.51562 2.66906 7.51562 3.45312C7.51562 3.80656 7.38156 4.1275 7.16625 4.37531C7.73906 4.73281 8.125 5.36656 8.125 6.09375V6.29688C8.125 6.8575 7.67 7.3125 7.10938 7.3125H5.07812C4.5175 7.3125 4.0625 6.8575 4.0625 6.29688V6.09375ZM3.65625 9.14062C3.65625 8.58 4.11125 8.125 4.67188 8.125H7.51562C8.07625 8.125 8.53125 8.58 8.53125 9.14062V9.95312C8.53125 10.5138 8.07625 10.9688 7.51562 10.9688H4.67188C4.11125 10.9688 3.65625 10.5138 3.65625 9.95312V9.14062Z" fill="white"/>
                <path d="M0.40625 1.42188V11.5781C0.40625 12.1388 0.86125 12.5938 1.42188 12.5938H2.03125V0.40625H1.42188C0.86125 0.40625 0.40625 0.86125 0.40625 1.42188Z" fill="white"/>
              </g>
              <defs>
                <clipPath id="clip0_20253_69">
                  <rect width="13" height="13" fill="white"/>
                </clipPath>
              </defs>
            </svg>
          </span>
          <span>Community Guidelines</span>
        </router-link>

        <router-link v-if="canModerate" to="/moderation" class="sidebar-nav-item" active-class="is-active">
          <span class="nav-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          </span>
          <span>Moderation</span>
          <span v-if="openReportCount > 0" class="nav-badge">{{ openReportCount }}</span>
        </router-link>

        <router-link to="/report" class="sidebar-nav-item" active-class="is-active">
          <span class="nav-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 13 13" fill="none" aria-hidden="true">
              <g clip-path="url(#clip0_20253_79)">
                <path d="M6.50008 10.3459C6.99775 10.3459 7.41201 9.96456 7.45393 9.46861L8.10095 1.74096C8.13862 1.29278 7.98666 0.849724 7.68244 0.518812C7.3786 0.188326 6.94951 0 6.50008 0C6.05065 0 5.62156 0.188326 5.31769 0.518812C5.01342 0.849724 4.86154 1.29278 4.89921 1.74096L5.54623 9.46861C5.58815 9.96456 6.00241 10.3459 6.50008 10.3459Z" fill="white"/>
                <path d="M6.5 10.9146C5.92403 10.9146 5.45728 11.3813 5.45728 11.9573C5.45728 12.5332 5.92403 12.9999 6.5 12.9999C7.07597 12.9999 7.54272 12.5332 7.54272 11.9573C7.54272 11.3813 7.07597 10.9146 6.5 10.9146Z" fill="white"/>
              </g>
              <defs>
                <clipPath id="clip0_20253_79">
                  <rect width="13" height="13" fill="white"/>
                </clipPath>
              </defs>
            </svg>
          </span>
          <span>Report an Issue</span>
        </router-link>

        <a v-if="isLoggedIn" href="#" class="sidebar-nav-item" @click.prevent="handleLogout">
          <span class="nav-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
              <g clip-path="url(#clip0_20253_86)">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M6.89958 11.7703V12.468C6.89958 13.0275 6.62072 13.5105 6.13617 13.7902C5.90303 13.9248 5.64283 13.9999 5.37306 14C5.10304 14.0002 4.84292 13.9249 4.60962 13.7902L1.50731 11.9991C1.02269 11.7193 0.743896 11.2364 0.743896 10.6768V1.52682C0.743896 0.684879 1.4288 0 2.27072 0H9.10313C9.9451 0 10.6301 0.684824 10.6301 1.52682V3.45562C10.6301 3.73231 10.4054 3.95697 10.1287 3.95697C9.85194 3.95697 9.62747 3.73234 9.62747 3.45562V1.52682C9.62747 1.23771 9.39223 1.00248 9.10313 1.00248H3.11616L6.13617 2.74638C6.62056 3.02611 6.89958 3.509 6.89958 4.06837V10.7677H9.10313C9.3922 10.7677 9.62747 10.5326 9.62747 10.2434V8.55261C9.62747 8.27572 9.8518 8.05126 10.1287 8.05126C10.4055 8.05126 10.6301 8.27578 10.6301 8.55261V10.2434C10.6301 11.0854 9.9451 11.7703 9.10313 11.7703H6.89958ZM11.5448 6.38635L10.9927 6.93845C10.797 7.1342 10.797 7.45155 10.9927 7.64734C11.0869 7.74162 11.2139 7.79401 11.3472 7.79401C11.4806 7.79401 11.6074 7.74175 11.7018 7.64734L13.1094 6.23946C13.3051 6.04373 13.3051 5.72655 13.1094 5.53082L11.7018 4.12316C11.506 3.92738 11.1886 3.92746 10.9927 4.12314C10.797 4.3187 10.7971 4.63619 10.9927 4.8318L11.5448 5.38377H7.8463C7.56933 5.38377 7.34506 5.6082 7.34506 5.88514C7.34506 6.16208 7.56936 6.38638 7.8463 6.38638H11.5448V6.38635Z" fill="white"/>
              </g>
              <defs>
                <clipPath id="clip0_20253_86">
                  <rect width="14" height="14" fill="white"/>
                </clipPath>
              </defs>
            </svg>
          </span>
          <span>Logout</span>
        </a>
      </div>
    </nav>
  </aside>
</template>

<script>
import { logout, getUnreadMessageCount, getConnectionRequests, getModerationStats } from '@/api/index.js';
import { getUserChannel } from '@/pusher';

export default {
  name: 'AppSidebar',
  data() {
    return {
      currentUser: window.cnwData?.currentUser || { name: '', first_name: '', last_name: '', avatar: '' },
      defaultAvatar: window.cnwData?.defaultAvatar || '',
      isLoggedIn: !!(window.cnwData?.currentUser?.id > 0),
      canModerate: !!(window.cnwData?.currentUser?.canModerate),
      unreadMessageCount: 0,
      connectionRequestCount: 0,
      openReportCount: 0,
    };
  },
  mounted() {
    this._onAvatarUpdated = (e) => { this.currentUser = Object.assign({}, this.currentUser, { avatar: e.detail }); };
    this._onAnonUpdated = (e) => { this.currentUser = Object.assign({}, this.currentUser, { anonymous: e.detail }); };
    window.addEventListener('cnw-avatar-updated', this._onAvatarUpdated);
    window.addEventListener('cnw-anonymous-updated', this._onAnonUpdated);

    // Shared helper to refresh the unread count from the server
    this._refreshUnread = () => {
      getUnreadMessageCount().then((res) => { this.unreadMessageCount = res.count || 0; }).catch(() => {});
    };
    this._refreshConnectionRequests = () => {
      getConnectionRequests().then((res) => { this.connectionRequestCount = (res.requests || []).length; }).catch(() => {});
    };

    // Fetch initial counts
    if (this.isLoggedIn) {
      this._refreshUnread();
      this._refreshConnectionRequests();
    }
    if (this.canModerate) {
      this._refreshOpenReports = () => {
        getModerationStats().then((res) => { this.openReportCount = res.open_reports || 0; }).catch(() => {});
      };
      this._refreshOpenReports();
    }

    // Listen for Pusher events to update badge in real-time
    const channel = getUserChannel();
    if (channel) {
      channel.bind('new-message', () => {
        setTimeout(this._refreshUnread, 300);
      });
      channel.bind('messages-read', this._refreshUnread);
      channel.bind('new-notification', (data) => {
        if (data.type === 'connection_request' || data.type === 'connection_accepted') {
          setTimeout(this._refreshConnectionRequests, 300);
        }
      });
      // Connection changes from the other side
      channel.bind('connection-removed', () => { setTimeout(this._refreshConnectionRequests, 300); });
      channel.bind('connection-blocked', () => { setTimeout(this._refreshConnectionRequests, 300); });

      // Report count updates for moderators
      if (this.canModerate && this._refreshOpenReports) {
        channel.bind('new-report', () => { setTimeout(this._refreshOpenReports, 300); });
      }
    }

    // Listen for local event when user reads messages in MessagesView
    window.addEventListener('cnw-messages-read', this._refreshUnread);
    // Listen for local event when user accepts/declines a connection request
    window.addEventListener('cnw-connections-updated', this._refreshConnectionRequests);
    if (this.canModerate) {
      window.addEventListener('cnw-moderation-updated', this._refreshOpenReports);
    }
  },
  beforeUnmount() {
    window.removeEventListener('cnw-avatar-updated', this._onAvatarUpdated);
    window.removeEventListener('cnw-anonymous-updated', this._onAnonUpdated);
    window.removeEventListener('cnw-messages-read', this._refreshUnread);
    window.removeEventListener('cnw-connections-updated', this._refreshConnectionRequests);
    if (this._refreshOpenReports) {
      window.removeEventListener('cnw-moderation-updated', this._refreshOpenReports);
    }
  },
  computed: {
    displayName() {
      if (this.currentUser.anonymous) return 'Anonymous';
      const fn = this.currentUser.first_name || '';
      const ln = this.currentUser.last_name || '';
      const full = (fn + ' ' + ln).trim();
      return full || this.currentUser.name || 'Guest';
    },
  },
  methods: {
    async handleLogout() {
      try { await logout(); } catch { /* silent */ }
      window.cnwData.currentUser = { id: 0, name: '', first_name: '', last_name: '', avatar: '' };
      window.location.hash = '#/';
      window.location.reload();
    },
  },
};
</script>

<style>
.cnw-social-worker-sidebar {
  width: 278px;
  flex-shrink: 0;
  background: #fff;
  border-radius: var(--radius);
  position: sticky;
  top: 20px;
  height: fit-content;
}

.sidebar-user-card {
  margin-bottom: 12px;
}

.sidebar-user-bg {
  height: 78px;
  background: linear-gradient(180deg, var(--primary) 0%, var(--secondary) 100%);
  border-radius: var(--radius) var(--radius) 0 0;
}

.sidebar-user-identity {
  margin-top: -36px;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding-bottom: 16px;
}

.sidebar-avatar {
  width: 56px;
  height: 56px;
  border: 2px solid #fff;
  margin-bottom: 8px;
}
.sidebar-anon-avatar {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: var(--primary, #3aa9da);
  border: 2px solid #fff;
  margin-bottom: 8px;
}

.sidebar-username {
  color: var(--text-dark);
  font-size: 14px;
  font-weight: 600;
  text-align: center;
  padding: 0 8px;
}
.sidebar-username-link {
  text-decoration: none;
  cursor: pointer;
}
.sidebar-username-link:hover {
  color: var(--primary);
}
.sidebar-reputation {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  font-size: 12px;
  font-weight: 300;
  color: #999;
  margin-top: 2px;
}
.sidebar-reputation svg {
  flex-shrink: 0;
}

.sidebar-nav {
  border-radius: var(--radius);
  overflow: hidden;
  margin: 0 20px 20px;
}

.sidebar-nav-group {
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding: 10px 0px;
  border-bottom: 1px solid rgba(255,255,255,0.08);
}
.sidebar-nav-group:last-child {
  border-bottom: none;
}

.sidebar-section-label {
  font-size: 14px;
  font-weight: 600;
  padding: 2px 6px;
  text-align: center;
}

.sidebar-nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 28px;
  color: #fff;
  font-size: 13.5px;
  font-weight: 300;
  text-decoration: none;
  background: var(--bd-body-5);
  border-radius: var(--radius);
  transition: background 0.15s;
}
.sidebar-nav-item:hover,
.sidebar-nav-item.is-active {
  background: linear-gradient(88deg, var(--bg-body) 22.95%, var(--secondary) 80.13%);
  color: #fff;
}
.sidebar-nav-item:focus-visible {
  background: linear-gradient(88deg, var(--bg-body) 22.95%, var(--secondary) 80.13%);
  color: #fff;
  outline: 2px solid #fff;
  outline-offset: -2px;
}

.nav-icon {
  width: 13px;
  height: 13px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: inherit;
}

.nav-badge {
  margin-left: auto;
  background: var(--primary);
  color: #fff;
  font-size: 11px;
  font-weight: 700;
  min-width: 20px;
  height: 20px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 5px;
}
.sidebar-nav-item.is-active .nav-badge {
  background: var(--tertiary);
}
</style>
