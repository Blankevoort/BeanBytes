const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: () => import('pages/IndexPage.vue') }
    ]
  },
  {
    path: '/requests',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: () => import('pages/JobRequestsPage.vue') }
    ]
  },
  {
    path: '/tag/:tag',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: () => import('pages/TagPostsPage.vue') }
    ]
  },
  {
    path: '/account',
    component: () => import('layouts/BlankLayout.vue'),
    children: [
      { path: '', component: () => import('pages/AccountPage.vue') }
    ],

    meta: {
      auth: true,
    },
  },
  {
    path: '/bookmarks',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: () => import('pages/BookmarksPage.vue') }
    ],

    meta: {
      requireAuth: true,
    },
  },
  {
    path: '/notifications',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: () => import('pages/NotificationsPage.vue') }
    ],

    meta: {
      requireAuth: true,
    },
  },
  {
    path: '/user/:name',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: () => import('pages/UserInfoPage.vue') }
    ],

    meta: {
      requireAuth: true,
    },
  },
  {
    path: '/settings/account',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: () => import('pages/AccountSettingPage.vue') }
    ],

    meta: {
      requireAuth: true,
    },
  },

  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes
