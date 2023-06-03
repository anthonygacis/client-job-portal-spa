import {createRouter, createWebHistory} from "vue-router";
import NProgress from "nprogress";
import publicRoutes from "./routes/public.js";
import onlyLoggedOutRoutes from "./routes/only-logged-out.js";
import privateRoutes from "./routes/private.js";
import privateSetupRoutes from "./routes/private-setup.js";
import "nprogress/nprogress.css";

const NotFound = () => import("../shared/components/errors/NotFound.vue");
const LayoutMain = () => import("@/js/layouts/LayoutMain.vue");

const routes = [
    ...publicRoutes,
    ...onlyLoggedOutRoutes,
    ...privateSetupRoutes,
    {
        path: "/app",
        name: "app",
        component: LayoutMain,
        children: [
            ...privateRoutes,
        ],
    },
    {
        path: "/:catchAll(.*)*",
        alias: '/404',
        name: '404-not-found',
        component: NotFound,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    linkActiveClass: "active",
    linkExactActiveClass: "exact-active",
});

router.beforeEach(async (to, from) => {
    NProgress.start();

    if (to.meta.middleware) {
        const middleware = to.meta.middleware
        return middleware[0](to, from, 1)
    }
});

router.afterEach((to, from) => {
    NProgress.done();
    if (to.meta.title) {
        document.title = to.meta.title
    }
});

export {router, routes};
