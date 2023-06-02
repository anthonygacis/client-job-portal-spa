import {useAuthStore} from "@/js/shared/stores/AuthStore";

export default async function onlyLoggedOut(to, from, index) {
    let auth = useAuthStore()
    const authenticated = await auth.isAuth();
    if (authenticated) {
        return {
            path: "/"
        }
    }

    if (to.meta.middleware && to.meta.middleware[index]) {
        return to.meta.middleware[index](to, from, index + 1)
    }
}
