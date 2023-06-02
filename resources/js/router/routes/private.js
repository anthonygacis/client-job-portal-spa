import auth from "@/js/router/middlewares/auth";

const Setup = () => import("@/js/modules/_temporary/setup/AppSetup.vue");
const LayoutMain = () => import("@/js/layouts/LayoutMain.vue");
const AppSettings = () => import("@/js/modules/profile/AppSettings.vue");

const SampleReport = () => import('@/js/shared/components/templates/reports/SampleReport.vue')

import homeRoute from '@/js/modules/home/router'
import userManagementRoute from '@/js/modules/user-management/router'
import authorize from "@/js/router/middlewares/authorize";

const routes = [
    {
        path: "/app",
        name: "app",
        component: LayoutMain,
        children: [
            homeRoute,
            userManagementRoute
        ],
    },
    {
        path: "/setup",
        name: "setup",
        component: Setup,
    },
    {
        path: "/profile",
        name: "profile",
        component: LayoutMain,
        redirect: () => ({name: 'settings'}),
        children: [
            // TODO add profile section here
            {
                path: "settings",
                name: "settings",
                component: AppSettings,
            },
        ],
    },
    {
        path: "/reports",
        name: "reports",
        redirect: () => ({name: 'settings'}),
        children: [
            {
                path: "sample",
                name: "sample",
                component: SampleReport,
            },
        ],
    },
];

export default routes.map((route) => {
    const meta = {
        middleware: [
            auth,
            authorize
        ],
        public: false,
        exist: true,
        ...route.meta
    }
    return {...route, meta};
});
