<script setup>
import {onMounted} from "vue";
import AppNavBar from "./partials/AppNavBar.vue";
import {useAuthStore} from "@/js/shared/stores/AuthStore";
import AppNavigation from "./partials/AppNavigation.vue";

const auth = useAuthStore();

onMounted(() => {
    document.body.classList.remove("border-top-wide", "border-primary", "d-flex", "flex-column");
    document.querySelector(".page").classList.remove("page-center");
});
</script>
<template>
    <div class="sticky-top">
        <!-- Navbar -->
        <header class="navbar navbar-expand-md navbar-light d-print-none">
            <div class="container-xl">
                <button class="navbar-toggler" data-bs-target="#navbar-menu" data-bs-toggle="collapse" type="button">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
                    <router-link :to="{ name: 'home' }">
                        <img alt="SorSU Web Cloud" class="navbar-brand-image" height="32" src="/logo-icon.svg" width="110"/>
                    </router-link>
                </h1>
                <app-nav-bar/>
            </div>
        </header>
        <app-navigation/>
    </div>
    <div class="page-wrapper">
        <router-view/>
        <footer class="footer footer-transparent d-print-none">
            <div class="container-xl">
                <div class="row text-center align-items-center flex-row-reverse">
                    <div class="col-lg-auto ms-lg-auto">
                        <ul class="list-inline list-inline-dots mb-0">
                            <li class="list-inline-item">
                                <a class="link-secondary" href="/" rel="noopener" target="_blank">v0.0.1</a>
                            </li>
                        </ul>
                    </div>
                    <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                        <ul class="list-inline list-inline-dots mb-0">
                            <li class="list-inline-item">
                                Copyright &copy; {{ new Date().getFullYear() }}.
                                <a class="link-secondary" href="/">Payroll Management System.</a> All rights reserved.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
