import guest from "@/js/router/middlewares/guest";
import onlyLoggedOut from "@/js/router/middlewares/only-logged-out";

const Login = () => import("../../modules/auth/AuthLogin.vue");

const routes = [
    {
        path: "/login",
        name: "login",
        component: Login,
        meta: {
            title: "Login"
        }
    },
];

export default routes.map((route) => {
    const meta = {
        middleware: [
            onlyLoggedOut
        ],
        public: true,
        onlyLoggedOut: true,
        exist: true,
        ...route.meta
    };

    return {...route, meta};
});
