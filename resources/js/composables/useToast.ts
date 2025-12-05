import { ref } from 'vue';

export interface Toast {
    id: string;
    title: string;
    description?: string;
    variant?: 'success' | 'error' | 'info' | 'warning';
    duration?: number;
}

const toasts = ref<Toast[]>([]);

export function useToast() {
    const showToast = (toast: Omit<Toast, 'id'>) => {
        const id = Math.random().toString(36).substring(7);
        const newToast: Toast = {
            id,
            duration: 5000,
            ...toast,
        };
        
        toasts.value.push(newToast);
        
        if (newToast.duration && newToast.duration > 0) {
            setTimeout(() => {
                removeToast(id);
            }, newToast.duration);
        }
        
        return id;
    };
    
    const removeToast = (id: string) => {
        const index = toasts.value.findIndex(t => t.id === id);
        if (index > -1) {
            toasts.value.splice(index, 1);
        }
    };
    
    const success = (title: string, description?: string) => {
        return showToast({ title, description, variant: 'success' });
    };
    
    const error = (title: string, description?: string) => {
        return showToast({ title, description, variant: 'error' });
    };
    
    const info = (title: string, description?: string) => {
        return showToast({ title, description, variant: 'info' });
    };
    
    const warning = (title: string, description?: string) => {
        return showToast({ title, description, variant: 'warning' });
    };
    
    return {
        toasts,
        showToast,
        removeToast,
        success,
        error,
        info,
        warning,
    };
}

