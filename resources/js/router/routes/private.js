import homeRoute from '@/js/modules/home/router'
import userManagementRoute from '@/js/modules/user-management/router'
import auth from "@/js/router/middlewares/auth";
import authorize from "@/js/router/middlewares/authorize";
import doneSetup from "@/js/router/middlewares/done-setup";

const AppSettings = () => import("@/js/modules/profile/AppSettings.vue");
const LayoutMain = () => import("@/js/layouts/LayoutMain.vue");
const SampleReport = () => import('@/js/shared/components/templates/reports/SampleReport.vue')


const routes = [
    homeRoute,
    userManagementRoute,
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
            authorize,
            doneSetup
        ],
        public: false,
        exist: true,
        ...route.meta
    }
    return {...route, meta};
});
