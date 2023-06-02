import {useAuthStore} from "@/js/shared/stores/AuthStore";

export default async function authorize(to, from, index) {
    let auth = useAuthStore()
    if (to.meta.pagePermissionName && !auth.hasValidPermissions(to.meta.pagePermissionName)) {
        return {
            name: '404-not-found'
        }
    }

    if (to.meta.middleware && to.meta.middleware[index]) {
        return to.meta.middleware[index](to, from, index + 1)
    }
}
