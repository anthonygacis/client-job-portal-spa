<script setup>
import {onMounted, reactive} from "vue";
import {useRoute, useRouter} from "vue-router";
import {useAuthStore} from "@/js/shared/stores/AuthStore.js";
import {EyeOffIcon, EyeIcon} from "vue-tabler-icons";

const state = reactive({
    username: "",
    password: "",
    is_invalid: false,
    _is_disabled: false,
    _is_pass_show: false,
    _is_processing: false,
});

const router = useRouter();
const route = useRoute();
const auth = useAuthStore();

function togglePassShow() {
    state._is_pass_show = !state._is_pass_show;
}

async function handleSignIn() {
    state._is_processing = true;
    try {
        await auth.login(state.username, state.password);
        if (route.query.redirect) {
            await router.push(route.query.redirect.toString());
        } else {
            await router.push("/app/home");
        }
    } catch (error) {
        console.log(error)
        state.is_invalid = true;
    }
    state._is_processing = false;
}

function clearError() {
    state.is_invalid = false;
}

onMounted(async () => {
    document.body.classList.add("d-flex", "flex-column");
    document.querySelector(".page").classList.add("page-center");
});
</script>

<template>
    <div class="container container-normal py-4">
        <div class="row align-items-center g-4">
            <div class="col-lg">
                <div class="container-tight">
                    <div class="card card-md">
                        <div class="card-body">
                            <h2 class="h2 text-center mb-4">Login to your account</h2>
                            <form autocomplete="off" method="get" novalidate @submit.prevent="">
                                <div class="mb-3">
                                    <label class="form-label">Username:</label>
                                    <input
                                        v-model="state.username"
                                        :class="{ 'is-invalid': state.is_invalid }"
                                        :disabled="state._is_disabled"
                                        autocomplete="on"
                                        autofocus
                                        class="form-control"
                                        placeholder="Your username"
                                        tabindex="1"
                                        type="text"
                                        @input="clearError"
                                    />
                                    <div class="invalid-feedback">Invalid credentials</div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">
                                        Password
                                    </label>
                                    <div class="input-group input-group-flat">
                                        <input
                                            v-model="state.password"
                                            :class="{ 'is-invalid': state.is_invalid }"
                                            :disabled="state._is_disabled"
                                            :type="state._is_pass_show ? 'text' : 'password'"
                                            autocomplete="off"
                                            class="form-control"
                                            placeholder="Your password"
                                            tabindex="2"
                                            @input="clearError"
                                        />
                                        <span
                                            :class="{ 'border border-danger': state.is_invalid }"
                                            class="input-group-text"
                                        >
                                            <span
                                                class="link-secondary"
                                                data-bs-toggle="tooltip"
                                                title="Show password"
                                                @click="togglePassShow"
                                            >
                                                <eye-off-icon v-if="!state._is_pass_show" class="icon"/>
                                                <eye-icon v-else class="icon"/>
                                            </span>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-footer">
                                    <button
                                        :disabled="state._is_processing"
                                        class="btn btn-primary w-100 mb-3"
                                        type="submit"
                                        @click="handleSignIn"
                                    >
                                        <span v-if="state._is_processing">
                                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                            Loading ...
                                        </span>
                                        <span v-else>Sign in</span>
                                    </button>
                                    <div class="text-center text-muted mt-3">
                                        New here?
                                        <router-link tabindex="-1" to="/app/signup">Create now!</router-link>
                                    </div>
                                    <div class="text-center text-muted mt-3">
                                        <a href="/">Go back</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg d-none d-lg-block">
                <img alt="" class="d-block mx-auto" height="300" src="/logo-circle.svg"/>
            </div>
        </div>
    </div>
</template>
