<script setup>
import {computed, onMounted, reactive} from "vue";
import {useAuthStore} from "@/js/shared/stores/AuthStore";
import {useRouter} from "vue-router";
import EyeOffIcon from "vue-tabler-icons/icons/EyeOffIcon";
import EyeIcon from "vue-tabler-icons/icons/EyeIcon";
import {toast} from "@/js/helpers/toasts";

const state = reactive({
    username: '',
    email: '',
    password: '',
    passwordConfirm: '',
    userType: 'job-seeker',
    _errors: null,
    _agree: false,
    _isPassShow: false,
    _isPassShowConfirm: false,
    _isProcessing: false,
});
const isDone = computed(() => {
    return state.username && state.email && state.password && state.passwordConfirm && state._agree && state.password === state.passwordConfirm
})
const router = useRouter();

function togglePassShow(type) {
    if (type === 'main') state._isPassShow = !state._isPassShow;
    else if (type === 'confirm') state._isPassShowConfirm = !state._isPassShowConfirm;
}

async function onSignUp() {
    state._isProcessing = true
    let auth = useAuthStore();
    await auth.create({
        username: state.username,
        email: state.email,
        password: state.password,
        password_confirmation: state.passwordConfirm,
        user_type: state.userType
    })
        .then(async () => {
            await router.push("/app/home");
        })
        .catch((e) => {
            if (e.response?.data) {
                state._errors = e.response.data.errors
                console.log(state._errors)
            }
        })
    state._isProcessing = false
}

onMounted(() => {
    document.body.classList.add("d-flex", "flex-column");
    document.querySelector(".page").classList.add("page-center");
});
</script>
<template>
    <div class="container container-tight py-4">
        <div class="text-center mb-4">
            <a class="navbar-brand navbar-brand-autodark" href="/"><img alt="" height="36" src="/logo.svg"/></a>
        </div>
        <div class="card card-md">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Create new account</h2>
                <div class="mb-3">
                    <label class="form-label">Username: <span class="text-red">*</span></label>
                    <input v-model="state.username" :class="{ 'is-invalid': state._errors?.username?.length }" class="form-control" placeholder="Your username" type="text"/>
                    <div v-if="state._errors?.username?.length" class="invalid-feedback">{{ state._errors?.username[0] }}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email address: <span class="text-red">*</span></label>
                    <input v-model="state.email" :class="{ 'is-invalid': state._errors?.email?.length }" class="form-control" placeholder="Your email" type="email"/>
                    <div v-if="state._errors?.email?.length" class="invalid-feedback">{{ state._errors?.email[0] }}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password: <span class="text-red">*</span></label>
                    <div class="input-group input-group-flat">
                        <input
                            v-model="state.password"
                            :type="state._isPassShow ? 'text' : 'password'"
                            autocomplete="off"
                            class="form-control"
                            placeholder="Password (At least 8 characters)"
                        />
                        <span class="input-group-text">
                            <span
                                class="link-secondary"
                                data-bs-toggle="tooltip"
                                title="Show password"
                                @click="togglePassShow('main')"
                            >
                                <eye-off-icon v-if="!state._isPassShow" class="icon"/>
                                <eye-icon v-else class="icon"/>
                            </span>
                        </span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm Password: <span class="text-red">*</span></label>
                    <div class="input-group input-group-flat">
                        <input
                            v-model="state.passwordConfirm"
                            :type="state._isPassShowConfirm ? 'text' : 'password'"
                            autocomplete="off"
                            class="form-control"
                            placeholder="Retype password"
                        />
                        <span class="input-group-text">
                            <span
                                class="link-secondary"
                                data-bs-toggle="tooltip"
                                title="Show password"
                                @click="togglePassShow('confirm')"
                            >
                                <eye-off-icon v-if="!state._isPassShowConfirm" class="icon"/>
                                <eye-icon v-else class="icon"/>
                            </span>
                        </span>
                    </div>
                </div>
                <div class="mb-3 d-flex justify-content-center">
                    <div class="form-selectgroup">
                        <label class="form-selectgroup-item">
                            <input v-model="state.userType" class="form-selectgroup-input" name="icons" type="radio" value="job-seeker"/>
                            <span class="form-selectgroup-label">Job Seeker</span>
                        </label>
                        <label class="form-selectgroup-item">
                            <input v-model="state.userType" class="form-selectgroup-input" name="icons" type="radio" value="employer"/>
                            <span class="form-selectgroup-label">Employer</span>
                        </label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-check">
                        <input v-model="state._agree" class="form-check-input" type="checkbox"/>
                        <span class="form-check-label">
                            Agree the
                            <a
                                data-bs-target="#terms-policy"
                                data-bs-toggle="modal"
                                href="javascript:void(0)"
                                tabindex="-1"
                            >terms and policy</a>.
                        </span>
                    </label>
                </div>
                <div class="form-footer">
                    <button :disabled="!isDone || state._isProcessing" class="btn btn-primary w-100" type="button" @click="onSignUp">Create new account</button>
                </div>
            </div>
        </div>
        <div class="text-center text-muted mt-3">
            Already have account?
            <router-link tabindex="-1" to="/app/login">Sign In</router-link>
        </div>
    </div>
    <teleport to="#external">
        <div id="terms-policy" aria-hidden="true" class="modal modal-blur fade" role="dialog" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Terms and Policy</h5>
                        <button aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"></button>
                    </div>
                    <div class="modal-body">Terms and policy here...</div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" data-bs-dismiss="modal" type="button">Got it!</button>
                    </div>
                </div>
            </div>
        </div>
    </teleport>
</template>
