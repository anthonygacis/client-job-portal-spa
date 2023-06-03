import guest from "@/js/router/middlewares/guest";
import onlyLoggedOut from "@/js/router/middlewares/only-logged-out";

const Login = () => import("@/js/modules/auth/AuthLogin.vue");
const SignUp = () => import("@/js/modules/auth/AuthSignup.vue");

const routes = [
    {
        path: "/app/login",
        name: "login",
        component: Login,
        meta: {
            title: "Login"
        }
    },
    {
        path: "/app/signup",
        name: "signup",
        component: SignUp,
        meta: {
            title: "Create new account"
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
