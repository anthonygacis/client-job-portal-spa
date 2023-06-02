<script setup>
import {computed, onMounted, reactive, watch} from "vue";
import BaseLoading from "@/js/shared/components/base/BaseLoading.vue";
import {requestGet, requestPost} from "@/js/helpers/requests";
import {toast} from "@/js/helpers/toasts";
import {XIcon} from "vue-tabler-icons";
import {useAuthStore} from "@/js/shared/stores/AuthStore";
import TrashIcon from "vue-tabler-icons/icons/TrashIcon";

const props = defineProps(['id'])
const emits = defineEmits(['on-save'])
const state = reactive({
    userData: {
        personalInfo: {
            email: '',
            gender: '',
            civil_status: '',
        },
        educationalBackground: [],
        license: [],
        workExperience: [],
        voluntaryWork: [],
        trainingPrograms: [],
        otherInfo: {},
    },
    _doneRequired: {
        showError: false,
        personalInfo: {
            lastName: false,
            firstName: false,
            sex: false,
            agencyEmployeeNo: false,
        },
        familyBackground: {
            fatherLastName: false,
            fatherFirstName: false,
            motherLastName: false,
            motherFirstName: false,
        }
    },
    _isResAndPermEqual: false,
    _isLoading: false,
    _isProcessing: false,
})

const auth = useAuthStore()
const totalPersonalInfoError = computed(() => {
    return Object.keys(state._doneRequired.personalInfo).filter(i => state._doneRequired.personalInfo[i]).length
})
const totalFamilyBackgroundError = computed(() => {
    return Object.keys(state._doneRequired.familyBackground).filter(i => state._doneRequired.familyBackground[i]).length
})

function onAddItem(section) {
    if (section == 'educational-background') {
        state.userData.educationalBackground.push({
            educ_level: '',
            school_name: '',
            degree: '',
            period_from: '',
            period_to: '',
            units_earned: '',
            year_graduated: '',
            awards: ''
        })
    } else if (section == 'license') {
        state.userData.license.push({
            license_name: '',
            rating: '',
            exam_date: '',
            place: '',
            license_no: '',
            validity_date: ''
        })
    } else if (section == 'work-experience') {
        state.userData.workExperience.push({
            inclusive_date_from: '',
            inclusive_date_to: '',
            position_title: '',
            agency_name: '',
            monthly_salary: '',
            salary_grade: '',
            status_of_appointment: '',
            is_gov_service: false,
            is_present: false,
        })
    } else if (section == 'voluntary-work') {
        state.userData.voluntaryWork.push({
            org_name: '',
            org_address: '',
            inclusive_date_from: '',
            inclusive_date_to: '',
            no_of_hours: '',
            position_nature: ''
        })
    } else if (section == 'training-programs') {
        state.userData.trainingPrograms.push({
            training_title: '',
            inclusive_date_from: '',
            inclusive_date_to: '',
            no_of_hours: '',
            type_of_ld: '',
            sponsored_by: ''
        })
    }
}

function onItemRemove(index, section) {
    if (section == 'educational-background') {
        state.userData.educationalBackground.splice(index, 1)
    } else if (section == 'license') {
        state.userData.license.splice(index, 1)
    } else if (section == 'work-experience') {
        state.userData.workExperience.splice(index, 1)
    } else if (section == 'voluntary-work') {
        state.userData.voluntaryWork.splice(index, 1)
    } else if (section == 'training-programs') {
        state.userData.trainingPrograms.splice(index, 1)
    }
}

function validateRequired() {
    // personal info
    state._doneRequired.personalInfo.lastName = !state.userData.personalInfo.last_name
    state._doneRequired.personalInfo.firstName = !state.userData.personalInfo.first_name
    state._doneRequired.personalInfo.sex = !state.userData.personalInfo.gender
    state._doneRequired.personalInfo.agencyEmployeeNo = !state.userData.personalInfo.agency_emp_no

    // family background
    state._doneRequired.familyBackground.fatherLastName = !state.userData.personalInfo.father_last_name
    state._doneRequired.familyBackground.fatherFirstName = !state.userData.personalInfo.father_first_name
    state._doneRequired.familyBackground.motherLastName = !state.userData.personalInfo.mother_last_name
    state._doneRequired.familyBackground.motherFirstName = !state.userData.personalInfo.mother_first_name

    return Object.keys(state._doneRequired.personalInfo).filter(i => state._doneRequired.personalInfo[i]).length +
        Object.keys(state._doneRequired.familyBackground).filter(i => state._doneRequired.familyBackground[i]).length
}

async function saveDetails() {
    if (validateRequired()) {
        toast('error', 'Please double check your inputs')
        state._doneRequired.showError = true
        return
    }

    state._isProcessing = true
    await requestPost('/api/user/details', {
        user_id: props.id,
        personal_info: state.userData.personalInfo,
        educ_background: state.userData.educationalBackground,
        license: state.userData.license,
        work_experience: state.userData.workExperience,
        organization: state.userData.voluntaryWork,
        training: state.userData.trainingPrograms,
        other_info: state.userData.otherInfo
    }).then(async ({data}) => {
        toast('success', 'Success')
        await auth.refreshUserData()
        await loadData()
        emits('on-save')
    }).catch((e) => {
        if (e.response.status == 422) {
            toast('error', e.response.data)
        }
    })
    state._isProcessing = false
}

function addChild() {
    state.userData.personalInfo.children.push({
        id: new Date().toISOString(),
        name: '',
        birth_date: '',
    })
}

function removeItem(index) {
    if (state.userData.personalInfo.children) {
        state.userData.personalInfo.children.splice(index, 1)
    }
}

watch(() => state._isResAndPermEqual, (value) => {
    if (value) {
        state.userData.personalInfo.permanent_addr_1 = state.userData.personalInfo?.residential_addr_1
        state.userData.personalInfo.permanent_addr_2 = state.userData.personalInfo?.residential_addr_2
        state.userData.personalInfo.permanent_addr_3 = state.userData.personalInfo?.residential_addr_3
        state.userData.personalInfo.permanent_zip_code = state.userData.personalInfo?.residential_zip_code
    } else {
        state.userData.personalInfo.permanent_addr_1 = ''
        state.userData.personalInfo.permanent_addr_2 = ''
        state.userData.personalInfo.permanent_addr_3 = ''
        state.userData.personalInfo.permanent_zip_code = ''
    }
})

async function loadData() {
    if (props.id) {
        await requestGet('/api/user/details/' + props.id).then(({data}) => {
            state.userData.personalInfo = data.user_primary
            state.userData.educationalBackground = data.user_educ_background ?? []
            state.userData.license = data.user_license ?? []
            state.userData.workExperience = data.user_work_experience ?? []
            state.userData.voluntaryWork = data.user_organization ?? []
            state.userData.trainingPrograms = data.user_training ?? []
            state.userData.otherInfo = data.user_other_info?.civil_service ?? {}
            if (state.userData.personalInfo) {
                state.userData.personalInfo.email = data.email ?? ''
                state.userData.personalInfo.civil_status = data.user_primary.civil_status ?? ''
                state.userData.personalInfo.gender = data.user_primary.gender ?? ''
            }
        })
    }
}

onMounted(async () => {
    if (props.id) {
        state._isLoading = true
        await loadData()
        state._isLoading = false
    }
})

// onUnmounted(() => {
//     console.log('unmount')
// })
</script>
<template>
    <div class="row">
        <div class="col-md-12">
            <div v-if="state._isLoading" class="card">
                <div class="card-body">
                    <base-loading/>
                </div>
            </div>
            <div v-else class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a aria-selected="true" class="nav-link active" data-bs-toggle="tab" href="#tabs-1" role="tab">
                                Personal Information
                                <span v-if="totalPersonalInfoError > 0" class="badge bg-red ms-2">{{ totalPersonalInfoError }}</span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a aria-selected="false" class="nav-link" data-bs-toggle="tab" href="#tabs-2" role="tab" tabindex="-1">
                                Family Background
                                <span v-if="totalFamilyBackgroundError > 0" class="badge bg-red ms-2">{{ totalFamilyBackgroundError }}</span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a aria-selected="false" class="nav-link" data-bs-toggle="tab" href="#tabs-3" role="tab" tabindex="-1">
                                Educational Background
                                <!--                                <span class="badge bg-red ms-2">error</span>-->
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a aria-selected="false" class="nav-link" data-bs-toggle="tab" href="#tabs-4" role="tab" tabindex="-1">
                                License
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a aria-selected="false" class="nav-link" data-bs-toggle="tab" href="#tabs-5" role="tab" tabindex="-1">
                                Work Experience
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a aria-selected="false" class="nav-link" data-bs-toggle="tab" href="#tabs-6" role="tab" tabindex="-1">
                                Voluntary Work
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a aria-selected="false" class="nav-link" data-bs-toggle="tab" href="#tabs-7" role="tab" tabindex="-1">
                                Training Programs
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a aria-selected="false" class="nav-link" data-bs-toggle="tab" href="#tabs-8" role="tab" tabindex="-1">
                                Other information
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div id="tabs-1" class="tab-pane active show" role="tabpanel">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Last Name: <span class="text-danger">*</span></label>
                                        <input v-model="state.userData.personalInfo.last_name"
                                               :class="{'is-invalid': state._doneRequired.showError && state._doneRequired.personalInfo.lastName}"
                                               class="form-control" name="last_name"
                                               type="text">
                                        <div v-if="state._doneRequired.showError && state._doneRequired.personalInfo.lastName"
                                             class="invalid-feedback">
                                            This field is required
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">First Name: <span class="text-danger">*</span></label>
                                        <input v-model="state.userData.personalInfo.first_name"
                                               :class="{'is-invalid': state._doneRequired.showError && state._doneRequired.personalInfo.firstName}"
                                               class="form-control" name="first_name"
                                               type="text">
                                        <div v-if="state._doneRequired.showError && state._doneRequired.personalInfo.firstName"
                                             class="invalid-feedback">
                                            This field is required
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Middle Name:</label>
                                        <input v-model="state.userData.personalInfo.middle_name" class="form-control" name="middle_name" type="text">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Name Extension (Jr., Sr.):</label>
                                        <input v-model="state.userData.personalInfo.name_ext" class="form-control" name="name_ext" type="text">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Date of Birth:</label>
                                        <input v-model="state.userData.personalInfo.birth_date" class="form-control" name="birth_date" type="date">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Place of Birth:</label>
                                        <input v-model="state.userData.personalInfo.birth_place" class="form-control" name="birth_place" type="text">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Sex: <span class="text-danger">*</span></label>
                                        <select v-model="state.userData.personalInfo.gender"
                                                :class="{'is-invalid': state._doneRequired.showError && state._doneRequired.personalInfo.sex}"
                                                class="form-select" name="gender">
                                            <option value="">-- Please select --</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                        </select>
                                        <div v-if="state._doneRequired.showError && state._doneRequired.personalInfo.sex"
                                             class="invalid-feedback">
                                            This field is required
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Civil Status:</label>
                                        <select v-model="state.userData.personalInfo.civil_status" class="form-select" name="civil_status">
                                            <option value="">-- Please select --</option>
                                            <option value="single">Single</option>
                                            <option value="widowed">Widowed</option>
                                            <option value="married">Married</option>
                                            <option value="others">Other/s</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Height (m): </label>
                                        <input v-model="state.userData.personalInfo.height" class="form-control" min="1" name="height" step="0.001" type="number">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Weight (kg): </label>
                                        <input v-model="state.userData.personalInfo.weight" class="form-control" min="1" name="weight" step="0.001" type="number">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Blood Type: </label>
                                        <input v-model="state.userData.personalInfo.blood_type" class="form-control" name="blood_type" type="text">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Citizenship: </label>
                                        <input v-model="state.userData.personalInfo.citizenship" class="form-control" name="citizenship" type="text">
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">GSIS ID No.: </label>
                                        <input v-model="state.userData.personalInfo.gsis_no" class="form-control" name="gsis_no" type="text">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">PAG-IBIG ID No.: </label>
                                        <input v-model="state.userData.personalInfo.pagibig_no" class="form-control" name="pagibig_no" type="text">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">PHILHEALTH No.: </label>
                                        <input v-model="state.userData.personalInfo.philhealth_no" class="form-control" name="philhealth_no" type="text">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">SSS No.: </label>
                                        <input v-model="state.userData.personalInfo.sss_no" class="form-control" name="sss_no" type="text">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">TIN No.: </label>
                                        <input v-model="state.userData.personalInfo.tin_no" class="form-control" name="tin_no" type="text">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Agency Employee No.: <span class="text-danger">*</span></label>
                                        <input v-model="state.userData.personalInfo.agency_emp_no"
                                               :class="{'is-invalid': state._doneRequired.showError && state._doneRequired.personalInfo.agencyEmployeeNo}"
                                               class="form-control" name="agency_emp_no" type="text">
                                        <div v-if="state._doneRequired.showError && state._doneRequired.personalInfo.agencyEmployeeNo"
                                             class="invalid-feedback">
                                            This field is required
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="hr-text hr-text-center">
                                        <span>Residential Address</span>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">House/Block/Lot No. and Street:</label>
                                        <input v-model="state.userData.personalInfo.residential_addr_1" class="form-control" name="residential_addr_1" type="text">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Subdivision/Village and Barangay:</label>
                                        <input v-model="state.userData.personalInfo.residential_addr_2" class="form-control" name="residential_addr_2" type="text">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">City/Municipality and Province:</label>
                                        <input v-model="state.userData.personalInfo.residential_addr_3" class="form-control" name="residential_addr_3" type="text">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">ZIP Code:</label>
                                        <input v-model="state.userData.personalInfo.residential_zip_code" class="form-control" name="residential_zip_code" type="text">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="hr-text hr-text-center">
                                        <span>Permanent Address</span>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-check">
                                            <input v-model="state._isResAndPermEqual" class="form-check-input" type="checkbox">
                                            <span class="form-check-label">Same as Residential Address</span>
                                        </label>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">House/Block/Lot No. and Street:</label>
                                        <input v-model="state.userData.personalInfo.permanent_addr_1" :disabled="state._isResAndPermEqual" class="form-control" name="permanent_addr_1" type="text">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Subdivision/Village and Barangay:</label>
                                        <input v-model="state.userData.personalInfo.permanent_addr_2" :disabled="state._isResAndPermEqual" class="form-control" name="permanent_addr_2" type="text">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">City/Municipality and Province:</label>
                                        <input v-model="state.userData.personalInfo.permanent_addr_3" :disabled="state._isResAndPermEqual" class="form-control" name="permanent_addr_3" type="text">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">ZIP Code:</label>
                                        <input v-model="state.userData.personalInfo.permanent_zip_code" :disabled="state._isResAndPermEqual" class="form-control" name="permanent_zip_code" type="text">
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Telephone No.:</label>
                                        <input v-model="state.userData.personalInfo.tel_no" class="form-control" name="tel_no" type="text">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Contact No.:</label>
                                        <input v-model="state.userData.personalInfo.mobile_no" class="form-control" name="mobile_no" type="text">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">E-mail Address (If any):</label>
                                        <input v-model="state.userData.personalInfo.email" class="form-control" name="email" type="email">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="tabs-2" class="tab-pane" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Father's Surname: <span class="text-danger">*</span></label>
                                        <input v-model="state.userData.personalInfo.father_last_name"
                                               :class="{'is-invalid': state._doneRequired.showError && state._doneRequired.familyBackground.fatherLastName}"
                                               class="form-control" name="father_last_name"
                                               type="text">
                                        <div v-if="state._doneRequired.showError && state._doneRequired.familyBackground.fatherLastName"
                                             class="invalid-feedback">
                                            This field is required
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Father's First Name: <span class="text-danger">*</span></label>
                                        <input v-model="state.userData.personalInfo.father_first_name"
                                               :class="{'is-invalid': state._doneRequired.showError && state._doneRequired.familyBackground.fatherFirstName}"
                                               class="form-control" name="father_first_name" type="text">
                                        <div v-if="state._doneRequired.showError && state._doneRequired.familyBackground.fatherFirstName"
                                             class="invalid-feedback">
                                            This field is required
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Father's Middle Name:</label>
                                        <input v-model="state.userData.personalInfo.father_middle_name" class="form-control" name="father_middle_name" type="text">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Mother's Surname: <span class="text-danger">*</span></label>
                                        <input v-model="state.userData.personalInfo.mother_last_name"
                                               :class="{'is-invalid': state._doneRequired.showError && state._doneRequired.familyBackground.motherLastName}"
                                               class="form-control" name="mother_last_name" type="text">
                                        <div v-if="state._doneRequired.showError && state._doneRequired.familyBackground.motherLastName"
                                             class="invalid-feedback">
                                            This field is required
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Mother's First Name: <span class="text-danger">*</span></label>
                                        <input v-model="state.userData.personalInfo.mother_first_name"
                                               :class="{'is-invalid': state._doneRequired.showError && state._doneRequired.familyBackground.motherFirstName}"
                                               class="form-control" name="mother_first_name" type="text">
                                        <div v-if="state._doneRequired.showError && state._doneRequired.familyBackground.motherFirstName"
                                             class="invalid-feedback">
                                            This field is required
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Mother's Middle Name:</label>
                                        <input v-model="state.userData.personalInfo.mother_middle_name" class="form-control" name="mother_middle_name" type="text">
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Spouse's Surname: </label>
                                        <input v-model="state.userData.personalInfo.spouse_last_name" class="form-control" name="spouse_last_name" type="text">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Spouse's First Name: </label>
                                        <input v-model="state.userData.personalInfo.spouse_first_name" class="form-control" name="spouse_first_name" type="text">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Spouse's Middle Name: </label>
                                        <input v-model="state.userData.personalInfo.spouse_middle_name" class="form-control" name="spouse_middle_name" type="text">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Occupation: </label>
                                        <input v-model="state.userData.personalInfo.spouse_occupation" class="form-control" name="spouse_occupation" type="text">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Employer / Business Name: </label>
                                        <input v-model="state.userData.personalInfo.spouse_employer" class="form-control" name="spouse_employer" type="text">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Business Address: </label>
                                        <input v-model="state.userData.personalInfo.spouse_employer_addr" class="form-control" name="spouse_employer_addr" type="text">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Telephone No.: </label>
                                        <input v-model="state.userData.personalInfo.spouse_employer_tel_no" class="form-control" name="spouse_employer_tel_no" type="text">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Name of children: </label>
                                    </div>
                                    <div v-for="(child, index) in state.userData.personalInfo.children" :key="child.id" class="row mb-2">
                                        <div class="col-md-6">
                                            <label class="form-label">Name: </label>
                                            <input v-model="child.name" class="form-control" name="children_name[]" type="text">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Birth date: </label>
                                            <div class="input-group input-group-flat">
                                                <input v-model="child.birth_date" class="form-control" name="children_birthdate[]" type="date">
                                                <div class="input-group-text">
                                                    <a class="link-danger" href="javascript: void(0)" @click="removeItem(index)">
                                                        <trash-icon class="icon"/>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <button class="btn btn-info mt-2" @click="addChild">Add</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="tabs-3" class="tab-pane" role="tabpanel">
                            <div v-for="(item, index) in state.userData.educationalBackground" :key="index" class="card mb-3">
                                <div class="card-header">
                                    <h3 class="card-title">Item #{{ index + 1 }}</h3>
                                    <div class="card-actions btn-actions">
                                        <button class="btn-action" @click="onItemRemove(index, 'educational-background')">
                                            <x-icon class="icon"/>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="mb-3">
                                                <label class="form-label">Education Level: <span class="text-danger">*</span></label>
                                                <select v-model="item.educ_level" class="form-select">
                                                    <option value="">-- Please select --</option>
                                                    <option value="elementary">Elementary</option>
                                                    <option value="secondary">Secondary</option>
                                                    <option value="vocational">Vocational / Trade Course</option>
                                                    <option value="college">College</option>
                                                    <option value="graduate-studies">Graduate Studies</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="mb-3">
                                                <label class="form-label">Name of School: <span class="text-danger">*</span></label>
                                                <input v-model="item.school_name" class="form-control" placeholder="Enter in full" type="text">
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="mb-3">
                                                <label class="form-label">Basic Education / Degree / Course: <span class="text-danger">*</span></label>
                                                <input v-model="item.degree" class="form-control" placeholder="Enter in full" type="text">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="mb-3 row">
                                                <label class="form-label">Period of Attendance: <span class="text-danger">*</span></label>
                                                <div class="col-md-6">
                                                    <input v-model="item.period_from" class="form-control" min="1" placeholder="from.." type="number">
                                                </div>
                                                <div class="col-md-6">
                                                    <input v-model="item.period_to" class="form-control" min="1" placeholder="to.." type="number">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Highest Level / Units Earned: <span class="text-danger">*</span></label>
                                                <input v-model="item.units_earned" class="form-control" type="text">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="mb-3">
                                                <label class="form-label">Year Graduated: <span class="text-danger">*</span></label>
                                                <input v-model="item.year_graduated" class="form-control" min="1" type="number">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Scholarship / Academic Honors Received: </label>
                                                <input v-model="item.awards" class="form-control" type="text">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <button class="btn btn-info" @click="onAddItem('educational-background')">Add Item</button>
                            </div>
                        </div>
                        <div id="tabs-4" class="tab-pane" role="tabpanel">
                            <div v-for="(item, index) in state.userData.license" :key="index" class="card mb-3">
                                <div class="card-header">
                                    <h3 class="card-title">Item #{{ index + 1 }}</h3>
                                    <div class="card-actions btn-actions">
                                        <button class="btn-action" @click="onItemRemove(index, 'license')">
                                            <x-icon class="icon"/>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-10">
                                            <div class="mb-3">
                                                <label class="form-label">Name: <span class="text-danger">*</span></label>
                                                <input v-model="item.license_name" class="form-control"
                                                       placeholder="Career Service/RA 1080 (Board/Bar) under special laws/CES/CSEE/Barangay Eligibility/Driver's License"
                                                       type="text">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="mb-3">
                                                <label class="form-label">Rating: </label>
                                                <input v-model="item.rating" class="form-control" type="text">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Examination / Conferment Date: </label>
                                                <input v-model="item.exam_date" class="form-control" type="date">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Place of Examination / Conferment: </label>
                                                <input v-model="item.place" class="form-control" type="text">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">License No.: </label>
                                                <input v-model="item.license_no" class="form-control" type="text">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Date of Validity: </label>
                                                <input v-model="item.validity_date" class="form-control" type="date">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <button class="btn btn-info" @click="onAddItem('license')">Add Item</button>
                            </div>
                        </div>
                        <div id="tabs-5" class="tab-pane" role="tabpanel">
                            <div v-for="(item, index) in state.userData.workExperience" :key="'work-' + index" class="card mb-3">
                                <div class="card-header">
                                    <h3 class="card-title">Item #{{ index + 1 }}</h3>
                                    <div class="card-actions btn-actions">
                                        <button class="btn-action" @click="onItemRemove(index, 'work-experience')">
                                            <x-icon class="icon"/>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="mb-3 row">
                                                <label class="form-label">Period of Attendance: <span class="text-danger">*</span></label>
                                                <div class="col-md-6">
                                                    <input v-model="item.inclusive_date_from" class="form-control" min="1" placeholder="from.." type="number">
                                                </div>
                                                <div class="col-md-6">
                                                    <input v-model="item.inclusive_date_to" class="form-control" min="1" placeholder="to.." type="number">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="mb-3">
                                                <label class="form-label">Position Title: <span class="text-danger">*</span></label>
                                                <input v-model="item.position_title" class="form-control" placeholder="Write in full / Do not abbreviate" type="text">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Department / Agency / Office / Company: <span class="text-danger">*</span></label>
                                                <input v-model="item.agency_name" class="form-control" placeholder="Write in full / Do not abbreviate" type="text">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Monthly Salary: <span class="text-danger">*</span></label>
                                                <input v-model="item.monthly_salary" class="form-control" type="text">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Salary / Job / Pay Grade: </label>
                                                <input v-model="item.salary_grade" class="form-control" placeholder="if applicable" type="text">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Status of Appointment: <span class="text-danger">*</span></label>
                                                <input v-model="item.status_of_appointment" class="form-control" type="text">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Government Service: <span class="text-danger">*</span></label>
                                                <div>
                                                    <label class="form-check form-check-inline">
                                                        <input v-model="item.is_gov_service" :name="'gov_service_' + index" :value="true" checked class="form-check-input" type="radio">
                                                        <span class="form-check-label">Yes</span>
                                                    </label>
                                                    <label class="form-check form-check-inline">
                                                        <input v-model="item.is_gov_service" :name="'gov_service_' + index" :value="false" class="form-check-input" type="radio">
                                                        <span class="form-check-label">No</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <button class="btn btn-info" @click="onAddItem('work-experience')">Add Item</button>
                            </div>
                        </div>
                        <div id="tabs-6" class="tab-pane" role="tabpanel">
                            <div v-for="(item, index) in state.userData.voluntaryWork" :key="index" class="card mb-3">
                                <div class="card-header">
                                    <h3 class="card-title">Item #{{ index + 1 }}</h3>
                                    <div class="card-actions btn-actions">
                                        <button class="btn-action" @click="onItemRemove(index, 'voluntary-work')">
                                            <x-icon class="icon"/>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="mb-3">
                                                <label class="form-label">Name of Organization: <span class="text-danger">*</span></label>
                                                <input v-model="item.org_name" class="form-control" placeholder="Write in full" type="text">
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="mb-3">
                                                <label class="form-label">Address of Organization: <span class="text-danger">*</span></label>
                                                <input v-model="item.org_address" class="form-control" placeholder="Write in full" type="text">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="mb-3">
                                                <label class="form-label">No. of Hours: <span class="text-danger">*</span></label>
                                                <input v-model="item.no_of_hours" class="form-control" step="0.01" type="number">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="mb-3 row">
                                                <label class="form-label">Inclusive Dates: <span class="text-danger">*</span></label>
                                                <div class="col-md-6">
                                                    <input v-model="item.inclusive_date_from" v-tooltip="'From'" class="form-control" type="date">
                                                </div>
                                                <div class="col-md-6">
                                                    <input v-model="item.inclusive_date_to" v-tooltip="'To'" class="form-control" type="date">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="mb-3">
                                                <label class="form-label">Position / Nature of Work: <span class="text-danger">*</span></label>
                                                <input v-model="item.position_nature" class="form-control" type="text">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <button class="btn btn-info" @click="onAddItem('voluntary-work')">Add Item</button>
                            </div>
                        </div>
                        <div id="tabs-7" class="tab-pane" role="tabpanel">
                            <div v-for="(item, index) in state.userData.trainingPrograms" :key="index" class="card mb-3">
                                <div class="card-header">
                                    <h3 class="card-title">Item #{{ index + 1 }}</h3>
                                    <div class="card-actions btn-actions">
                                        <button class="btn-action" @click="onItemRemove(index, 'training-programs')">
                                            <x-icon class="icon"/>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-7">
                                            <div class="mb-3">
                                                <label class="form-label">Title of Learning and Development Interventions / Training Programs: <span class="text-danger">*</span></label>
                                                <input v-model="item.training_title" class="form-control" placeholder="Write in full" type="text">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3 row">
                                                <label class="form-label">Inclusive Dates: <span class="text-danger">*</span></label>
                                                <div class="col-md-6">
                                                    <input v-model="item.inclusive_date_from" v-tooltip="'From'" class="form-control" type="date">
                                                </div>
                                                <div class="col-md-6">
                                                    <input v-model="item.inclusive_date_to" v-tooltip="'To'" class="form-control" type="date">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="mb-3">
                                                <label class="form-label">No. of Hours: <span class="text-danger">*</span></label>
                                                <input v-model="item.no_of_hours" class="form-control" step="0.01" type="number">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Type of LD (Managerial / Supervisory / Technical / etc.:</label>
                                                <input v-model="item.type_of_ld" class="form-control" type="text">
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label class="form-label">Conducted / Sponsored by:</label>
                                                <input v-model="item.sponsored_by" class="form-control" placeholder="Write in full" type="text">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <button class="btn btn-info" @click="onAddItem('training-programs')">Add Item</button>
                            </div>
                        </div>
                        <div id="tabs-8" class="tab-pane" role="tabpanel">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    Are you related by consanguinity or affinity to the appointing or recommending authority, or to the chief of bureau or
                                                    office or to the person who has immediate supervision over you in the Office, Bureau or Department where you will be appointed,
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">a. within the third degree?</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-check form-check-inline">
                                                <input v-model="state.userData.otherInfo.a1" :value="true" class="form-check-input" name="a-1" type="radio">
                                                <span class="form-check-label">Yes</span>
                                            </label>
                                            <label class="form-check form-check-inline">
                                                <input v-model="state.userData.otherInfo.a1" :value="false" class="form-check-input" name="a-1" type="radio">
                                                <span class="form-check-label">No</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">b. within the fourth degree (for Local Government Unit - Career Employees?</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-check form-check-inline">
                                                <input v-model="state.userData.otherInfo.a2" :value="true" class="form-check-input" name="a-2" type="radio">
                                                <span class="form-check-label">Yes</span>
                                            </label>
                                            <label class="form-check form-check-inline">
                                                <input v-model="state.userData.otherInfo.a2" :value="false" class="form-check-input" name="a-2" type="radio">
                                                <span class="form-check-label">No</span>
                                            </label>
                                            <div v-if="state.userData.otherInfo.a2">
                                                <label class="form-label">If YES, give details:</label>
                                                <input v-model="state.userData.otherInfo.a2_details" class="form-control" type="text">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">a. Have you ever been found guilty of any administrative offense?</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-check form-check-inline">
                                                <input v-model="state.userData.otherInfo.b1" :value="true" class="form-check-input" name="b-1" type="radio">
                                                <span class="form-check-label">Yes</span>
                                            </label>
                                            <label class="form-check form-check-inline">
                                                <input v-model="state.userData.otherInfo.b1" :value="false" class="form-check-input" name="b-1" type="radio">
                                                <span class="form-check-label">No</span>
                                            </label>
                                            <div v-if="state.userData.otherInfo.b1">
                                                <label class="form-label">If YES, give details:</label>
                                                <input v-model="state.userData.otherInfo.b1_details" class="form-control" type="text">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">b. Have you been criminally charged before any court?</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-check form-check-inline">
                                                <input v-model="state.userData.otherInfo.b2" :value="true" class="form-check-input" name="b-2" type="radio">
                                                <span class="form-check-label">Yes</span>
                                            </label>
                                            <label class="form-check form-check-inline">
                                                <input v-model="state.userData.otherInfo.b2" :value="false" class="form-check-input" name="b-2" type="radio">
                                                <span class="form-check-label">No</span>
                                            </label>
                                            <div v-if="state.userData.otherInfo.b2">
                                                <label class="form-label">If YES, give details:</label>
                                                <label class="form-label">Date Filed:</label>
                                                <input v-model="state.userData.otherInfo.b2_date_filed" class="form-control" type="text">
                                                <label class="form-label">Status of Case/s:</label>
                                                <input v-model="state.userData.otherInfo.b2_status" class="form-control" type="text">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Have you ever been convicted of any crime or violation of any law, decree, ordinance, or regulation by any court or
                                                    tribunal?</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-check form-check-inline">
                                                <input v-model="state.userData.otherInfo.c1" :value="true" class="form-check-input" name="c-1" type="radio">
                                                <span class="form-check-label">Yes</span>
                                            </label>
                                            <label class="form-check form-check-inline">
                                                <input v-model="state.userData.otherInfo.c1" :value="false" class="form-check-input" name="c-1" type="radio">
                                                <span class="form-check-label">No</span>
                                            </label>
                                            <div v-if="state.userData.otherInfo.c1">
                                                <label class="form-label">If YES, give details:</label>
                                                <input v-model="state.userData.otherInfo.c1_details" class="form-control" type="text">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    Have you ever been separated from the service in any of the following modes: resignation,
                                                    retirement, dropped from the rolls, dismissal, termination, end of term, finished contract or phased out
                                                    (abolition) in the public or private sector?
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-check form-check-inline">
                                                <input v-model="state.userData.otherInfo.d1" :value="true" class="form-check-input" name="d-1" type="radio">
                                                <span class="form-check-label">Yes</span>
                                            </label>
                                            <label class="form-check form-check-inline">
                                                <input v-model="state.userData.otherInfo.d1" :value="false" class="form-check-input" name="d-1" type="radio">
                                                <span class="form-check-label">No</span>
                                            </label>
                                            <div v-if="state.userData.otherInfo.d1">
                                                <label class="form-label">If YES, give details:</label>
                                                <input v-model="state.userData.otherInfo.d1_details" class="form-control" type="text">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">a. Have you ever been a candidate in a national or local election held within the last year (except
                                                    Barangay election)?</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-check form-check-inline">
                                                <input v-model="state.userData.otherInfo.e1" :value="true" class="form-check-input" name="e-1" type="radio">
                                                <span class="form-check-label">Yes</span>
                                            </label>
                                            <label class="form-check form-check-inline">
                                                <input v-model="state.userData.otherInfo.e1" :value="false" class="form-check-input" name="e-1" type="radio">
                                                <span class="form-check-label">No</span>
                                            </label>
                                            <div v-if="state.userData.otherInfo.e1">
                                                <label class="form-label">If YES, give details:</label>
                                                <input v-model="state.userData.otherInfo.e1_details" class="form-control" type="text">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">b. Have you resigned from the government service during the three (3)-month period before the last
                                                    election to promote/actively campaign for a national or local candidate?</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-check form-check-inline">
                                                <input v-model="state.userData.otherInfo.e2" :value="true" class="form-check-input" name="e-2" type="radio">
                                                <span class="form-check-label">Yes</span>
                                            </label>
                                            <label class="form-check form-check-inline">
                                                <input v-model="state.userData.otherInfo.e2" :value="false" class="form-check-input" name="e-2" type="radio">
                                                <span class="form-check-label">No</span>
                                            </label>
                                            <div v-if="state.userData.otherInfo.e2">
                                                <label class="form-label">If YES, give details:</label>
                                                <input v-model="state.userData.otherInfo.e2_details" class="form-control" type="text">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    Have you acquired the status of an immigrant or permanent resident of another country?
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-check form-check-inline">
                                                <input v-model="state.userData.otherInfo.f1" :value="true" class="form-check-input" name="f-1" type="radio">
                                                <span class="form-check-label">Yes</span>
                                            </label>
                                            <label class="form-check form-check-inline">
                                                <input v-model="state.userData.otherInfo.f1" :value="false" class="form-check-input" name="f-1" type="radio">
                                                <span class="form-check-label">No</span>
                                            </label>
                                            <div v-if="state.userData.otherInfo.f1">
                                                <label class="form-label">If YES, give details (country):</label>
                                                <input v-model="state.userData.otherInfo.f1_details" class="form-control" type="text">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    Pursuant to: (a) Indigenous People's Act (RA 8371); (b) Magna Carta for Disabled Persons (RA
                                                    7277); and (c) Solo Parents Welfare Act of 2000 (RA 8972), please answer the following items:
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">a. Are you a member of any indigenous group?</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-check form-check-inline">
                                                <input v-model="state.userData.otherInfo.g1" :value="true" class="form-check-input" name="g-1" type="radio">
                                                <span class="form-check-label">Yes</span>
                                            </label>
                                            <label class="form-check form-check-inline">
                                                <input v-model="state.userData.otherInfo.g1" :value="false" class="form-check-input" name="g-1" type="radio">
                                                <span class="form-check-label">No</span>
                                            </label>
                                            <div v-if="state.userData.otherInfo.g1">
                                                <label class="form-label">If YES, give details:</label>
                                                <input v-model="state.userData.otherInfo.g1_details" class="form-control" type="text">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">b. Are you a person with disability?</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-check form-check-inline">
                                                <input v-model="state.userData.otherInfo.g2" :value="true" class="form-check-input" name="g-2" type="radio">
                                                <span class="form-check-label">Yes</span>
                                            </label>
                                            <label class="form-check form-check-inline">
                                                <input v-model="state.userData.otherInfo.g2" :value="false" class="form-check-input" name="g-2" type="radio">
                                                <span class="form-check-label">No</span>
                                            </label>
                                            <div v-if="state.userData.otherInfo.g2">
                                                <label class="form-label">If YES, please specify ID No.:</label>
                                                <input v-model="state.userData.otherInfo.g2_details" class="form-control" type="text">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">b. Are you a solo parent?</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-check form-check-inline">
                                                <input v-model="state.userData.otherInfo.g3" :value="true" class="form-check-input" name="g-3" type="radio">
                                                <span class="form-check-label">Yes</span>
                                            </label>
                                            <label class="form-check form-check-inline">
                                                <input v-model="state.userData.otherInfo.g3" :value="false" class="form-check-input" name="g-3" type="radio">
                                                <span class="form-check-label">No</span>
                                            </label>
                                            <div v-if="state.userData.otherInfo.g3">
                                                <label class="form-label">If YES, please specify ID No.:</label>
                                                <input v-model="state.userData.otherInfo.g3_details" class="form-control" type="text">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button :disabled="state._isProcessing" class="btn btn-primary" @click="saveDetails">Save</button>
                </div>
            </div>
        </div>
    </div>
</template>
