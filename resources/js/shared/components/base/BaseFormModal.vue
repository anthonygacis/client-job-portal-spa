<script setup>
const props = defineProps({
    modalTitle: String,
    modalId: {
        type: String,
        required: true
    },
    modalSize: {
        type: String,
        default: 'normal',
    }
})
</script>
<template>
    <Teleport to="#external">
        <div :id="props.modalId" aria-hidden="true" class="modal modal-blur fade" data-bs-backdrop="static" role="dialog" tabindex="-1">
            <div :class="{ 'modal-lg': props.modalSize === 'large', 'modal-full-width': props.modalSize === 'full' }" class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ props.modalTitle ?? 'Modal Title' }}</h5>
                        <button aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"></button>
                    </div>
                    <div class="modal-body">
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
