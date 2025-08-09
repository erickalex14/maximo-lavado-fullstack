import { ref } from 'vue';

export interface ToastMessage { id: string; type: 'success' | 'error' | 'info' | 'warning'; text: string; timeout?: number }

const messages = ref<ToastMessage[]>([]);

function push(message: Omit<ToastMessage, 'id'>) {
  const id = Math.random().toString(36).slice(2);
  const full: ToastMessage = { timeout: 4000, ...message, id };
  messages.value.push(full);
  if (full.timeout) {
    setTimeout(() => remove(id), full.timeout);
  }
}

function remove(id: string) { messages.value = messages.value.filter(m => m.id !== id); }

export function useToast() {
  return { messages, push, remove };
}
