<script setup>

const props = defineProps({
    yearItems: {
        type: Number,
        default: 5
    },
    contentType: {
        validator(value) {
            return ['year', 'month', null].includes(value)
        }
    },
    modelValue: null,
})

const emits = defineEmits(['update:modelValue'])

const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']

</script>
<template>
    <select :value="modelValue" class="form-select" @change="$emit('update:modelValue', $event.target.value)">
        <option value="">-- Please select --</option>
        <option v-for="(_, index) in props.yearItems" v-if="props.contentType === 'year'" :value="new Date().getFullYear() - index">{{ new Date().getFullYear() - index }}</option>
        <option v-for="(month, index) in months" v-else-if="props.contentType === 'month'" :value="index + 1">{{ month }}</option>
        <slot></slot>
    </select>
</template>
