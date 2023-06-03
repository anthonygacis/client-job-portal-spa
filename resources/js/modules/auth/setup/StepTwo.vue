<script setup>
import {nextTick, onMounted, reactive} from "vue";
import {useAuthStore} from "@/js/shared/stores/AuthStore";
import PSGC from "@ageesea/psgc-js";

const state = reactive({
    _userType: '',
})
const auth = useAuthStore()

onMounted(async () => {
    state._userType = auth.getUser()?.user_type
    await nextTick()
    PSGC.init({
        bind: {
            region: "#region",
            province: "#province",
            municipality: "#municipality",
            barangay: "#barangay",
        }
    })
})
</script>
<template>
    <div v-if="state._userType === 'job-seeker'" class="card-body">
        <h3>Your other information</h3>
        <div class="mb-3">
            <label class="form-label">Gender: <span class="text-danger">*</span></label>
            <select class="form-select">
                <option value="">-- Please select --</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Others">Others</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Region: <span class="text-danger">*</span></label>
            <select id="region" class="form-select"></select>
        </div>
        <div class="mb-3">
            <label class="form-label">Province: <span class="text-danger">*</span></label>
            <select id="province" class="form-select"></select>
        </div>
        <div class="mb-3">
            <label class="form-label">Municipality: <span class="text-danger">*</span></label>
            <select id="municipality" class="form-select"></select>
        </div>
        <div class="mb-3">
            <label class="form-label">Barangay: <span class="text-danger">*</span></label>
            <select id="barangay" class="form-select"></select>
        </div>
    </div>
</template>
