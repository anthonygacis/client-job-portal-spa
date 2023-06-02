<script setup>

import {onMounted, reactive, watch} from "vue";
import {filterObjsByColumns} from "@/js/helpers/utility";
import {ChevronLeftIcon, ChevronRightIcon} from "vue-tabler-icons";

const props = defineProps({
    data: {
        type: Object,
        required: true
    },
    columns: {
        type: Array,
        required: true
    },
})

const emits = defineEmits(['onTrigger'])

const state = reactive({
    data: [],
    content: [],
    columns: [],
    orderColumn: 'id',
    orderType: 'asc',
    currentPage: 1,
    navPages: [],
})

function onPagination(page) {
    if (page > state.data.last_page) {
        state.currentPage = state.data.last_page
    } else {
        state.currentPage = page
    }
    emits('onTrigger', 'paginate', state.currentPage, state.orderColumn, state.orderType)
}

function onSidePaginate(side) {
    if (side == 'left') state.currentPage--
    else if (side == 'right') state.currentPage++
    onPagination(state.currentPage)
}

function calculateNavPages() {
    state.navPages = []
    if (props.data?.last_page && state.currentPage) {
        let isLeftDone = false;
        let isRightDone = false;
        for (let i = 1; i <= state.data?.last_page; i++) {
            if (state.data?.last_page < 9) {
                state.navPages.push(i)
            } else {
                if (state.currentPage < 4 && i <= 7) {
                    state.navPages.push(i)
                } else if (state.currentPage >= 4 && (i == 1 || i == state.currentPage - 1 || i == state.currentPage + 1 || i == state.data.last_page) && state.currentPage <= state.data.last_page - 6) {
                    if (i == 1 || i == state.data.last_page || (state.currentPage >= state.data.last_page - 6 && i >= 6)) {
                        state.navPages.push(i)
                    } else if (state.currentPage < state.data.last_page - 6 && (i == state.currentPage - 1 || i == state.currentPage + 1)) {
                        state.navPages.push(i)
                    }
                } else {
                    if (i == 1) {
                        state.navPages.push(i)
                    } else if (i < state.currentPage - 1 && !isLeftDone) {
                        isLeftDone = true
                        state.navPages.push('.')
                    } else if (i > state.currentPage + 1 && !isRightDone && state.currentPage < state.data.last_page - 6) {
                        isRightDone = true
                        state.navPages.push('.')
                    } else if (i == state.data.last_page || i == state.currentPage || (state.currentPage >= state.data.last_page - 6 && i >= state.data.last_page - 6)) {
                        state.navPages.push(i)
                    }
                }
            }
        }
    }
}

function initData() {
    state.data = props.data
    state.content = filterObjsByColumns(props.data, props.columns)
    state.currentPage = props.data?.current_page > props.data.last_page ? props.data.last_page : props.data?.current_page
    state.orderColumn = props.data.field ?? 'id'
    state.orderType = props.data.order ?? 'asc'
    state.columns = props.columns.map((i) => {
        if (i.data == props.data.field) i.order = props.data.order ?? ''
        else i.order = ''
        return i
    })
}

function onHeaderClick(index) {
    state.columns.forEach((item, i) => {
        if (i != index) item.order = ''
    })
    state.columns[index].order = state.columns[index].order == 'asc' ? 'desc' : 'asc'
    state.orderColumn = state.columns[index].data
    state.orderType = state.columns[index].order
    emits('onTrigger', 'header-click', state.currentPage, state.orderColumn, state.orderType)
}

watch(() => props.data, (data) => {
    initData()
    calculateNavPages()
})

onMounted(() => {
    initData()
    calculateNavPages()
})
</script>
<template>
    <div class="table-responsive">
        <table class="table table-vcenter">
            <thead>
            <tr>
                <template v-for="(column, index) in state.columns">
                    <th v-if="!column.hidden" :style="{ width: column.width }">
                        <span v-if="column.sortable === false">{{ column.title ?? column.data }}</span>
                        <button v-else :class="{ [column.order]: true }" class="table-sort" @click="onHeaderClick(index)">{{ column.title ?? column.data }}</button>
                    </th>
                </template>
            </tr>
            </thead>
            <tbody>
            <slot :data-content="state.content"></slot>
            </tbody>
        </table>
    </div>
    <div class="d-flex align-items-center mt-3 px-3">
        <p class="m-0 text-muted">Showing <span>{{ props.data.from }}</span> to <span>{{ props.data.to }}</span> of <span>{{ props.data.total }}</span> entries</p>
        <ul class="pagination m-0 ms-auto">
            <li :class="{ 'disabled': state.currentPage == 1 }" class="page-item">
                <button class="page-link" tabindex="-1" @click="onSidePaginate('left')">
                    <chevron-left-icon class="icon"/>
                    prev
                </button>
            </li>
            <template v-for="item in state.navPages" :key="item">
                <li v-if="item != '.'" :class="{ 'active': state.currentPage == item }" class="page-item">
                    <button class="page-link" @click="onPagination(item)">{{ item }}</button>
                </li>
                <li v-else class="page-item">
                    <span class="page-link">...</span>
                </li>
            </template>
            <li :class="{ 'disabled': state.currentPage >= state.data.last_page }" class="page-item">
                <button class="page-link" @click="onSidePaginate('right')">
                    next
                    <chevron-right-icon class="icon"/>
                </button>
            </li>
        </ul>
    </div>
</template>
