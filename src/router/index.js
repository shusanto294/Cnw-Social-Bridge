import { createRouter, createWebHashHistory } from 'vue-router';
import QuestionListView from '@/components/views/QuestionListView.vue';
import ThreadDetailView from '@/components/views/ThreadDetailView.vue';
import AskQuestionView from '@/components/views/AskQuestionView.vue';
import MessagesView from '@/components/views/MessagesView.vue';
import UsersView from '@/components/views/UsersView.vue';
import TagsView from '@/components/views/TagsView.vue';
import ActivityView from '@/components/views/ActivityView.vue';
import SavedThreadsView from '@/components/views/SavedThreadsView.vue';
import GuidelinesView from '@/components/views/GuidelinesView.vue';
import ReportIssueView from '@/components/views/ReportIssueView.vue';
import UserProfileView from '@/components/views/UserProfileView.vue';
import LoginView from '@/components/views/LoginView.vue';
import NotFoundView from '@/components/views/NotFoundView.vue';
import ModerationView from '@/components/views/ModerationView.vue';

const routes = [
  { path: '/', component: QuestionListView },
  { path: '/thread/:id', component: ThreadDetailView },
  { path: '/ask', component: AskQuestionView, meta: { requiresAuth: true } },
  { path: '/messages', component: MessagesView, meta: { requiresAuth: true } },
  { path: '/users', component: UsersView, meta: { requiresAuth: true } },
  { path: '/users/:id', component: UserProfileView, props: true },
  { path: '/profile', component: UserProfileView },
  { path: '/tags', component: TagsView },
  { path: '/activity', component: ActivityView, meta: { requiresAuth: true } },
  { path: '/saved', component: SavedThreadsView, meta: { requiresAuth: true } },
  { path: '/guidelines', component: GuidelinesView },
  { path: '/report', component: ReportIssueView, meta: { requiresAuth: true } },
  { path: '/login', name: 'Login', component: LoginView, meta: { guestOnly: true } },
  { path: '/moderation', component: ModerationView, meta: { requiresAuth: true } },
  { path: '/:pathMatch(.*)*', name: 'NotFound', component: NotFoundView },
];

const router = createRouter({
  history: createWebHashHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 };
  },
});

router.beforeEach((to, from, next) => {
  const userId = window.cnwData && window.cnwData.currentUser && window.cnwData.currentUser.id;
  if (to.meta.requiresAuth && !userId) {
    return next({ path: '/login', query: { redirect: to.fullPath } });
  }
  if (to.meta.guestOnly && userId) {
    return next('/');
  }
  next();
});

export default router;
