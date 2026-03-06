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
import NotFoundView from '@/components/views/NotFoundView.vue';

const routes = [
  { path: '/', component: QuestionListView },
  { path: '/thread/:id', component: ThreadDetailView },
  { path: '/ask', component: AskQuestionView },
  { path: '/messages', component: MessagesView },
  { path: '/users', component: UsersView },
  { path: '/profile', component: UserProfileView },
  { path: '/tags', component: TagsView },
  { path: '/activity', component: ActivityView },
  { path: '/saved', component: SavedThreadsView },
  { path: '/guidelines', component: GuidelinesView },
  { path: '/report', component: ReportIssueView },
  { path: '/:pathMatch(.*)*', name: 'NotFound', component: NotFoundView },
];

export default createRouter({
  history: createWebHashHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 };
  },
});
