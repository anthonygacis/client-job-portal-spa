import {useAuthStore} from "@/js/shared/stores/AuthStore";

export default async function guest(to, from, index) {
    // custom logic for guest

    if (to.meta.middleware && to.meta.middleware[index]) {
        return to.meta.middleware[index](to, from, index + 1)
    }
}
