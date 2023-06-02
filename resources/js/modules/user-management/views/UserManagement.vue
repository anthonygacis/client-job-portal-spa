<script setup>
import BaseContainer from "@/js/shared/components/base/BaseContainer.vue";
import {computed, onMounted, reactive} from "vue";
import {requestGet, requestPut} from "@/js/helpers/requests";
import BaseFormModal from "@/js/shared/components/base/BaseFormModal.vue";
import {toast} from "@/js/helpers/toasts";
import BaseLoading from "@/js/shared/components/base/BaseLoading.vue";
import BaseDataTable from "@/js/shared/components/base/BaseDataTable.vue";
import 'filepond/dist/filepond.min.css'
import {PencilIcon, PlusIcon} from "vue-tabler-icons";
import {useAuthStore} from "@/js/shared/stores/AuthStore";
import UserIcon from "vue-tabler-icons/icons/UserIcon";
import UserPlusIcon from "vue-tabler-icons/icons/UserPlusIcon";
import {closeModal} from "@/js/helpers/utility";

const state = reactive({
    contentList: [],
    currentTotalDisplayItems: 0,
    selectedItem: {},
    payload: {
        permissions: [],
        existingRole: [],
        newRole: '',
    },
    urlParams: '',
    staticData: null,
    _modeRoleUpdate: 'existing',
    _isLoading: false,
    _isProcessing: false,
})

const columns = [
    {
        data: 'id',
        title: 'ID',
        sortable: false,
        hidden: true
    },
    {
        data: 'name',
        title: 'Name',
    },
    {
        data: 'roles_text',
        title: 'Roles',
        sortable: false
    },
    {
        data: 'username',
        title: 'Username',
    },
    {
        title: 'Actions',
        sortable: false,
        width: '150px'
    }
]

const auth = useAuthStore()

const totalVisibleColumns = computed(() => {
    return columns.reduce((total, item) => {
        if (!item.hidden) return total + 1
        return total
    }, 0)
})

async function onModalSave() {
    state._isProcessing = true
    await requestPut('/api/user/update-role/' + state.selectedItem.id, {
        role_type: state._modeRoleUpdate,
        existing_roles: state.payload.existingRole,
        new_role: state.payload.newRole,
        permissions: state.payload.permissions
    }).then(async ({data}) => {
        state._modeRoleUpdate = 'existing'
        toast('success', data.message)
        await loadList()
        closeModal('update-permissions')
    }).catch((e) => {
        toast('error', e.response.data.message)
    })
    state._isProcessing = false
}

async function loadList(eventType = 'reload', currentPage = 1, column = 'id', order = 'asc') {
    state._isLoading = true
    let urlParams = new URLSearchParams({
        model: 'users',
        per_page: 10,
        order_field: column,
        order_type: order,
        current_page: currentPage,
        search: '',
    })
    if (eventType != 'reload' || !state.urlParams) {
        state.urlParams = urlParams.toString()
    }
    await requestGet('/api/datatable' + '?' + state.urlParams).then(({data}) => {
        state.contentList = data
        state.currentTotalDisplayItems = data.data.length
        state._isLoading = false
    })

    await requestGet('/api/general/load?items=permissions,features,roles').then(({data}) => {
        state.staticData = data
    })
}

function setSelectedItem(item) {
    state.selectedItem = item
}

onMounted(async () => {
    await loadList()
})
</script>
<template>
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="mb-1">
                        <ol aria-label="breadcrumbs" class="breadcrumb breadcrumb-arrows">
                            <li aria-current="page" class="breadcrumb-item">User Management</li>
                        </ol>
                    </div>
                    <h2 class="page-title">Records</h2>
                </div>
            </div>
        </div>
    </div>
    <base-container>
        <div class="row">
            <div class="col-md-12">
                <div v-if="state._isLoading" class="card">
                    <div class="card-body">
                        <base-loading/>
                    </div>
                </div>
                <div v-else class="card">
                    <div class="card-header">
                        <h3 class="card-title">List of Account</h3>
                        <div class="card-actions">
                            <button class="btn btn-primary me-2" data-bs-target="#add-new-item" data-bs-toggle="modal">
                                <plus-icon class="icon"/>
                                Add new
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <base-data-table v-slot="slotProps" :columns="columns" :data="state.contentList" @on-trigger="loadList">
                            <tr v-for="item in slotProps.dataContent" :key="item.id">
                                <template v-for="(data, key) in item" :key="key">
                                    <td v-if="!columns.find(i => i.data == key)?.hidden">{{ data }}</td>
                                </template>
                                <td>
                                    <button v-if="item.id !== auth.getID()" v-tooltip="'Update permissions'" class="btn btn-primary me-2 btn-icon"
                                            data-bs-target="#update-permissions"
                                            data-bs-toggle="modal"
                                            @click="setSelectedItem(item)">
                                        <pencil-icon class="icon"/>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="slotProps.dataContent.length == 0">
                                <td :colspan="totalVisibleColumns" class="text-center">No data</td>
                            </tr>
                        </base-data-table>
                    </div>
                </div>
            </div>
        </div>
    </base-container>
    <base-form-modal modal-id="update-permissions" modal-title="Update role">
        <div class="row">
            <div class="col-md-12">
                <div class="mb-3">
                    <div class="form-selectgroup d-flex justify-content-center">
                        <label class="form-selectgroup-item">
                            <input v-model="state._modeRoleUpdate" class="form-selectgroup-input" name="mode" type="radio" value="existing">
                            <span class="form-selectgroup-label">
                                  <user-icon class="icon me-1"/>
                                  From existing role
                            </span>
                        </label>
                        <label class="form-selectgroup-item">
                            <input v-model="state._modeRoleUpdate" class="form-selectgroup-input" name="mode" type="radio" value="new">
                            <span class="form-selectgroup-label">
                                  <user-plus-icon class="icon me-1"/>
                                  Create new role
                            </span>
                        </label>
                    </div>
                </div>
                <div v-if="state._modeRoleUpdate === 'existing'" class="mb-3">
                    <label class="form-label">Select role/s:</label>
                    <div>
                        <template v-for="role in state.staticData?.roles" :key="role.id">
                            <label
                                v-if="(state.payload.existingRole.includes('Super Admin') && role.name === 'Super Admin') || !state.payload.existingRole.includes('Super Admin')"
                                class="form-check">
                                <input v-model="state.payload.existingRole" :value="role.name" class="form-check-input" name="existing_role" type="checkbox">
                                <span class="form-check-label">{{ role.name }}</span>
                            </label>
                        </template>
                    </div>
                </div>
                <div v-else class="mb-3">
                    <label class="form-label">Create new role:</label>
                    <input v-model="state.payload.newRole" class="form-control" placeholder="Enter text" type="text">
                    <label class="form-label mt-3">Select permissions:</label>
                    <template v-for="(permissions, property) in state.staticData?.features" :key="property">
                        <div class="mb-3">
                            <label class="form-label">{{ property }}</label>
                            <div>
                                <template v-for="permission in permissions" :key="property + '_' + permission">
                                    <label
                                        v-if="(state.payload.permissions.includes(property + '_all') && (property + '_' + permission === property + '_all')) || !state.payload.permissions.includes(property + '_all')"
                                        class="form-check form-check-inline">
                                        <input v-model="state.payload.permissions"
                                               :disabled="state.payload.permissions.includes(property + '_all') && (property + '_' + permission !== property + '_all')"
                                               :name="property"
                                               :value="property + '_' + permission"
                                               class="form-check-input" type="checkbox">
                                        <span class="form-check-label">{{ permission }}</span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        <template v-slot:footer>
            <button :disabled="state._isProcessing" class="btn btn-primary" type="button" @click="onModalSave">Update Role</button>
        </template>
    </base-form-modal>
</template>

