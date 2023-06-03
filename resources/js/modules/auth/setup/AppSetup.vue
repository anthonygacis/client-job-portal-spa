<script setup>
import {onMounted, reactive} from "vue";
import StepOne from "./StepOne.vue";
import StepTwo from "./StepTwo.vue";
import {useRouter} from "vue-router";
import {toast} from "@/js/helpers/toasts";

const steps = [
    StepOne,
    StepTwo
];
const state = reactive({
    activeState: null,
    currentStep: 1,
    progress: 0,
});
const router = useRouter();

function onContinue() {
    // perform validation
    if (state.currentStep === 1) {
        if (!state.activeState.firstName && !state.activeState.lastName) {
            toast('error', 'Please double check the required fields')
            return
        }
    }

    if (state.currentStep < steps.length) {
        state.currentStep++;
        state.progress = (state.currentStep / steps.length) * 100;
    } else {
        router.push("/app");
    }
}

function onUpdated(localState) {
    state.activeState = localState
}

onMounted(() => {
    document.body.classList.add("d-flex", "flex-column");
    document.querySelector(".page").classList.add("page-center");
});
</script>
<template>
    <div class="container container-tight py-4">
        <div class="text-center mb-4">
            <router-link class="navbar-brand navbar-brand-autodark" to="/">
                <img alt="" height="36" src="/logo.svg"/>
            </router-link>
        </div>
        <div class="card card-md">
            <component :is="steps[state.currentStep - 1]" @onFieldsUpdated="onUpdated"></component>
        </div>
        <div class="row align-items-center mt-3">
            <div class="col-4">
                <div class="progress">
                    <div
                        :style="{ width: `${state.progress}%` }"
                        aria-label="25% Complete"
                        aria-valuemax="100"
                        aria-valuemin="0"
                        aria-valuenow="25"
                        class="progress-bar"
                        role="progressbar"
                    >
                        <span class="visually-hidden">{{ state.progress }}% Complete </span>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="btn-list justify-content-end">
                    <button class="btn btn-primary" @click="onContinue">Continue</button>
                </div>
            </div>
        </div>
    </div>
</template>
