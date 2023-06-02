const UserManagement = () => import('@/js/modules/user-management/views/UserManagement.vue')

export default {
    path: "user-management",
    name: "user-management",
    component: UserManagement,
    meta: {
        title: "User Management",
        pagePermissionName: 'user-management_read'
    },
}
