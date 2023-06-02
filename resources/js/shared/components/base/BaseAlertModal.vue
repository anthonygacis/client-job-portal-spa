<script setup>
const props = defineProps({
    modalTitle: String,
    modalId: {
        type: String,
        required: true
    },
})
</script>
<template>
    <Teleport to="#external">
        <div :id="props.modalId" aria-hidden="true" class="modal modal-blur fade" data-bs-backdrop="static" role="dialog" tabindex="-1">
            <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="modal-title">{{ props.modalTitle ?? 'Are you sure?' }}</div>
                        <slot>Modal Content</slot>
                    </div>
                    <div class="modal-footer">
                        <slot name="footer"></slot>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
