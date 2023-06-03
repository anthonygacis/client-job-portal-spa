import {defineStore} from "pinia";
import {attachApiUrl, requestGet, requestPost} from "@/js/helpers/requests";
import {ref} from "vue";
import {useRouter} from "vue-router";

export let useAuthStore = defineStore("auth", () => {
    let userData = ref(null)
    let userPermissions = ref(null)
    let hasCheck = ref(false)

    async function login(username, password) {
        await requestPost('/api/login', {
            username: username,
            password: password
        }).then(({data}) => {
            localStorage.setItem('auth', 'true')
            localStorage.setItem('user', JSON.stringify(data.user))
            localStorage.setItem('permissions', JSON.stringify(data.permissions))
            userData.value = data.user
            userPermissions.value = data.permissions
        }).catch((e) => {
            throw new Error(e.response.data.message)
        })
    }

    async function create(payload) {
        await requestPost('/api/signup', payload)
            .then(async ({data}) => {
                // TODO make auth
                localStorage.setItem('auth', 'true')
                localStorage.setItem('user', JSON.stringify(data.user))
                localStorage.setItem('permissions', JSON.stringify(data.permissions))
                userData.value = data.user
                userPermissions.value = data.permissions
            })
    }

    async function logout() {
        localStorage.removeItem('user')
        return await requestPost('/api/logout')
    }

    async function isAuth() {
        if (!hasCheck.value) {
            // check if session still persist
            await requestGet('/api/check').catch((e) => {
                if (e.response.status == 401) {
                    localStorage.removeItem('auth')
                }
            })
            hasCheck.value = true
        }

        getUser()
        getPermissions()

        return !!localStorage.getItem('auth');
    }

    async function refreshUserData() {
        await requestGet('/api/user').then(({data}) => {
            localStorage.setItem('user', JSON.stringify(data.user))
            userData.value = data
        })
    }

    function getUser() {
        let user = localStorage.getItem('user')
        if (user) userData.value = JSON.parse(user)

        return userData.value
    }

    function getPermissions() {
        let permissions = localStorage.getItem('permissions')
        if (permissions) userPermissions.value = JSON.parse(permissions)

        return userPermissions.value
    }

    function getID() {
        let user = localStorage.getItem('user')
        if (user) return JSON.parse(user)?.id

        return ''
    }

    function hasValidPermissions(...targetPermission) {
        if (getRoles().includes('Super Admin')) return true

        if (userPermissions.value) {
            targetPermission.filter(i => {
                userPermissions.value.includes(i)
            })
            return targetPermission.filter(i => userPermissions.value.includes(i)).length > 0
        }

        return false
    }

    function getRoles() {
        return userData.value?.roles?.map(i => i.name)?.join(', ')
    }

    return {login, create, logout, isAuth, getUser, getID, hasValidPermissions, refreshUserData, getRoles, getPermissions}
})
