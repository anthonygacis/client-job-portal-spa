<script setup>

import {onMounted, reactive, watch} from "vue";
import {useAuthStore} from "@/js/shared/stores/AuthStore";

const state = reactive({
    firstName: '',
    middleName: '',
    lastName: '',
    _userType: '',
})
const emit = defineEmits(['onFieldsUpdated'])
const auth = useAuthStore()


watch(() => state, () => {
    emit('onFieldsUpdated', state)
}, {
    deep: true
})

onMounted(() => {
    state._userType = auth.getUser()?.user_type
})
</script>
<template>
    <div class="card-body text-center p-4 py-sm-5">
        <img alt="" class="mb-n2" height="120" src="/logo-circle.svg"/>
        <h1 class="mt-5">Welcome to SorSU Job Portal!</h1>
        <p v-if="state._userType === 'job-seeker'" class="text-muted">
            We are thrilled to have you on board and excited to help you embark on your professional journey.
            By creating an account with us, you've taken the first step towards finding your dream job
            and connecting with potential employers.
        </p>
        <p v-else-if="state._userType === 'employer'">
            We are thrilled to have you on board. Get ready to discover a world of talented candidates and find your perfect match for every job opening.
        </p>
    </div>
    <div class="hr-text hr-text-center hr-text-spaceless">Get Started</div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label">First Name: <span class="text-danger">*</span></label>
            <input v-model="state.firstName" class="form-control" name="firstname" placeholder="Your first name" type="text"/>
        </div>
        <div class="mb-3">
            <label class="form-label">Middle Name:</label>
            <input v-model="state.middleName" class="form-control" name="middlename" placeholder="Your middle name" type="text"/>
        </div>
        <div class="mb-3">
            <label class="form-label">Last Name: <span class="text-danger">*</span></label>
            <input v-model="state.lastName" class="form-control" name="lastname" placeholder="Your last name" type="text"/>
        </div>
    </div>
</template>
