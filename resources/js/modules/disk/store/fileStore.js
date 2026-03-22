import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useFileStore = defineStore('file', () => {
    const files = ref([]);
    const folders = ref([]);
    const currentPath = ref([]);
    const storageLimit = ref(16 * 1024 * 1024 * 1024); // 16GB in bytes
    const storageUsed = ref(12.5 * 1024 * 1024 * 1024); // 12.5GB in bytes

    const clipboard = ref({ type: null, item: null, action: null });

    const storagePercentage = computed(() => {
        return (storageUsed.value / storageLimit.value) * 100;
    });

    const formatSize = (bytes) => {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    };

    const formattedStorageUsed = computed(() => formatSize(storageUsed.value));
    const formattedStorageLimit = computed(() => formatSize(storageLimit.value));

    function setInitialData(data) {
        files.value = data.files || [];
        folders.value = data.folders || [];
        storageUsed.value = data.storageUsed || storageUsed.value;
        storageLimit.value = data.storageLimit || storageLimit.value;
    }

    return {
        files,
        folders,
        currentPath,
        storageUsed,
        storageLimit,
        storagePercentage,
        formattedStorageUsed,
        formattedStorageLimit,
        clipboard,
        setInitialData,
    };
});
