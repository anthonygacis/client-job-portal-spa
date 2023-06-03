import auth from "@/js/router/middlewares/auth";
import authorize from "@/js/router/middlewares/authorize";
import setupOnly from "@/js/router/middlewares/setup-only";

const AppSetup = () => import('@/js/modules/auth/setup/AppSetup.vue')

const routes = [
    {
        path: "/app/setup",
        name: "setup",
        component: AppSetup,
    },
];

export default routes.map((route) => {
    const meta = {
        middleware: [
            auth,
            authorize,
            setupOnly
        ],
        public: false,
        exist: true,
        ...route.meta
    }
    return {...route, meta};
});
