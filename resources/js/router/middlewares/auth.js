import {useAuthStore} from "@/js/shared/stores/AuthStore";

export default async function auth(to, from, index) {
    let auth = useAuthStore()
    const authenticated = await auth.isAuth();
    if (!authenticated) {
        return {
            path: "/app/login",
            query: {
                redirect: to.fullPath
            },
        }
    }

    if (to.meta.middleware && to.meta.middleware[index]) {
        return to.meta.middleware[index](to, from, index + 1)
    }
}
