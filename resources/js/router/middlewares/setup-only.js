import {useAuthStore} from "@/js/shared/stores/AuthStore";

export default async function setupOnly(to, from, index) {
    let auth = useAuthStore()
    let isDoneSetup = auth.getUser()?.done_setup
    if (isDoneSetup) {
        // redirect to set up page
        return {
            path: "/app"
        }
    }

    if (to.meta.middleware && to.meta.middleware[index]) {
        return to.meta.middleware[index](to, from, index + 1)
    }
}
