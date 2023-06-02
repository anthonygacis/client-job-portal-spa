<script setup>
import {useAuthStore} from "@/js/shared/stores/AuthStore.js";
import {CalendarPlusIcon, HomeIcon, PencilPlusIcon, CurrencyPesoIcon, UserPlusIcon} from "vue-tabler-icons";
import {computed, onMounted} from "vue";

const user = useAuthStore()

</script>
<template>
    <div class="navbar-expand-md">
        <div id="navbar-menu" class="collapse navbar-collapse">
            <div class="navbar navbar-light">
                <div class="container-xl">
                    <ul class="navbar-nav">
                        <router-link :to="{ name: 'home' }" class="nav-item text-decoration-none" exact>
                            <li class="nav-link">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <home-icon class="icon"/>
                                </span>
                                <span class="nav-link-title">Home</span>
                            </li>
                        </router-link>
                        <router-link v-if="user.hasValidPermissions('user-management_read')" :to="{ name: 'user-management' }" class="nav-item text-decoration-none" exact>
                            <li class="nav-link">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <user-plus-icon class="icon"/>
                                </span>
                                <span class="nav-link-title">User Management</span>
                            </li>
                        </router-link>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>
