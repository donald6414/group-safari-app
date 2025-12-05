<script setup lang="ts">
import { computed } from 'vue';
import { useToast, type Toast } from '@/composables/useToast';
import { cn } from '@/lib/utils';
import { X, CheckCircle2, XCircle, Info, AlertTriangle } from 'lucide-vue-next';

const { toasts, removeToast } = useToast();

const getVariantStyles = (variant: Toast['variant']) => {
    switch (variant) {
        case 'success':
            return 'bg-green-50 border-green-200 text-green-800';
        case 'error':
            return 'bg-red-50 border-red-200 text-red-800';
        case 'warning':
            return 'bg-yellow-50 border-yellow-200 text-yellow-800';
        case 'info':
        default:
            return 'bg-blue-50 border-blue-200 text-blue-800';
    }
};

const getIcon = (variant: Toast['variant']) => {
    switch (variant) {
        case 'success':
            return CheckCircle2;
        case 'error':
            return XCircle;
        case 'warning':
            return AlertTriangle;
        case 'info':
        default:
            return Info;
    }
};
</script>

<template>
    <Teleport to="body">
        <div class="fixed top-4 right-4 z-50 flex flex-col gap-2 w-full max-w-sm">
            <TransitionGroup
                name="toast"
                tag="div"
                class="flex flex-col gap-2"
            >
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    :class="cn(
                        'flex items-start gap-3 rounded-lg border p-4 shadow-lg backdrop-blur-sm',
                        getVariantStyles(toast.variant)
                    )"
                >
                    <component
                        :is="getIcon(toast.variant)"
                        class="h-5 w-5 shrink-0 mt-0.5"
                    />
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm">{{ toast.title }}</p>
                        <p v-if="toast.description" class="text-sm mt-1 opacity-90">
                            {{ toast.description }}
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="removeToast(toast.id)"
                        class="shrink-0 rounded-md p-1 opacity-70 hover:opacity-100 transition-opacity"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from {
    opacity: 0;
    transform: translateX(100%);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(100%);
}

.toast-move {
    transition: transform 0.3s ease;
}
</style>

