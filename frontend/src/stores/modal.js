import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useModalStore = defineStore('modal', () => {
    const isOpen = ref(false);
    const currentModal = ref(null); // 'login', 'register', 'forgot-password'
    const props = ref({});

    function openModal(modalName, modalProps = {}) {
        currentModal.value = modalName;
        props.value = modalProps;
        isOpen.value = true;
    }

    function closeModal() {
        isOpen.value = false;
        currentModal.value = null;
        props.value = {};
    }

    function switchModal(modalName, modalProps = {}) {
        currentModal.value = modalName;
        props.value = modalProps;
    }

    return {
        isOpen,
        currentModal,
        props,
        openModal,
        closeModal,
        switchModal
    };
});

