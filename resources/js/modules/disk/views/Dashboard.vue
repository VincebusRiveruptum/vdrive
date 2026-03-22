<script setup>
import AuthenticatedLayout from '@/shared/layouts/AuthenticatedLayout.vue';
import Dropdown from '@/shared/Dropdown.vue';
import DropdownLink from '@/shared/DropdownLink.vue';
import Modal from '@/shared/Modal.vue';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted, watch, computed, ref } from 'vue';
import { useFileStore } from '../store/fileStore';
import { useFileSystem } from '../composables/useFileSystem';

const props = defineProps({
    initialFiles: Array,
    initialFolders: Array,
    storageUsed: Number,
    storageLimit: Number,
    breadcrumbs: Array,
    currentFolderId: Number,
});

const store = useFileStore();
const fileInput = ref(null);
const { 
    navigateToFolder, uploadFile, downloadFile, createFolder, 
    deleteFile, deleteFolder, renameFile, renameFolder, pasteItem 
} = useFileSystem();

const updateStore = () => {
    store.setInitialData({
        files: props.initialFiles || [],
        folders: props.initialFolders || [],
        storageUsed: props.storageUsed || 0,
        storageLimit: props.storageLimit || (16 * 1024 * 1024 * 1024),
    });
};

const items = computed(() => {
    const folders = store.folders
        .map(f => ({ ...f, isFolder: true, type: 'Carpeta', size: '-', updated: '-' }))
        .sort((a, b) => a.name.localeCompare(b.name));
        
    const files = store.files
        .map(f => ({ ...f, isFolder: false }))
        .sort((a, b) => a.name.localeCompare(b.name));
        
    return [...folders, ...files];
});

const triggerFileUpload = () => {
    fileInput.value.click();
};

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        uploadFile(file, props.currentFolderId);
    }
};

const handleCreateFolder = () => {
    const name = prompt('Nombre de la nueva carpeta:');
    if (name) {
        createFolder(name, props.currentFolderId);
    }
};

const contextMenu = ref({ show: false, x: 0, y: 0, item: null });
const detailsModal = ref({ show: false, item: null });

const openContextMenu = (e, item) => {
    contextMenu.value = { show: true, x: e.clientX, y: e.clientY, item: item };
};

const closeContextMenu = () => {
    if (contextMenu.value.show) {
        contextMenu.value.show = false;
    }
};

const handleRename = (item) => {
    const newName = prompt('Ingrese el nuevo nombre:', item.name);
    if (newName && newName !== item.name) {
        if (item.isFolder) renameFolder(item.id, newName);
        else renameFile(item.id, newName);
    }
};

const handleCopy = (item) => {
    store.clipboard = { type: item.isFolder ? 'folder' : 'file', item: item, action: 'copy' };
};

const handlePaste = () => {
    pasteItem(props.currentFolderId);
};

const handleDetails = (item) => {
    detailsModal.value = { show: true, item: item };
};

onMounted(() => {
    updateStore();
    window.addEventListener('click', closeContextMenu);
    window.addEventListener('scroll', closeContextMenu);
});

onUnmounted(() => {
    window.removeEventListener('click', closeContextMenu);
    window.removeEventListener('scroll', closeContextMenu);
});

watch(
    () => [props.initialFiles, props.initialFolders],
    updateStore,
    { deep: true }
);
</script>

<template>
    <Head title="Mi Unidad" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <nav class="flex text-sm text-gray-500 mb-2">
                    <ol class="list-none p-0 inline-flex items-center gap-2">
                        <li class="flex items-center">
                            <button @click="navigateToFolder(null)" class="hover:text-indigo-600 transition-colors">Mi Unidad</button>
                        </li>
                        <li v-for="crumb in breadcrumbs" :key="crumb.id" class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            <button @click="navigateToFolder(crumb.id)" class="hover:text-indigo-600 transition-colors">{{ crumb.name }}</button>
                        </li>
                    </ol>
                </nav>
                
                <div class="flex gap-2">
                    <!-- Hidden File Input -->
                    <input 
                        type="file" 
                        ref="fileInput" 
                        class="hidden" 
                        @change="handleFileUpload"
                    />

                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-all flex items-center gap-2 shadow-sm">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Nuevo
                            </button>
                        </template>

                        <template #content>
                            <button 
                                @click="triggerFileUpload"
                                class="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none transition duration-150 ease-in-out"
                            >
                                Subir archivo
                            </button>
                            <button 
                                @click="handleCreateFolder"
                                class="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none transition duration-150 ease-in-out"
                            >
                                Nueva carpeta
                            </button>
                        </template>
                    </Dropdown>
                </div>
            </div>
        </template>

        <div class="space-y-4" @contextmenu.prevent="openContextMenu($event, null)">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Tamaño</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Última modificación</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                        <tr v-for="item in items" :key="item.isFolder ? 'fol-' + item.id : 'fil-' + item.id" 
                            @click="item.isFolder ? navigateToFolder(item.id) : null"
                            @contextmenu.prevent.stop="openContextMenu($event, item)"
                            class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group cursor-pointer">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="text-gray-400 group-hover:text-indigo-500 transition-colors">
                                        <!-- Folder Icon -->
                                        <svg v-if="item.isFolder" class="h-6 w-6 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
                                        </svg>
                                        <!-- File Icons -->
                                        <svg v-else-if="item.type === 'Imagen'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2-2v12a2 2 0 002 2z" /></svg>
                                        <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                    </div>
                                    <span class="text-sm font-medium">{{ item.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell">{{ item.size }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden lg:table-cell">{{ item.updated }}</td>
                        </tr>
                        <tr v-if="items.length === 0">
                            <td colspan="3" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    Esta carpeta está vacía
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Context Menu -->
        <div v-if="contextMenu.show" 
             class="fixed z-50 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-100 dark:border-gray-700 py-1 text-sm text-gray-700 dark:text-gray-300 transform transition-all"
             :style="{ top: contextMenu.y + 'px', left: contextMenu.x + 'px' }">
            
            <template v-if="contextMenu.item">
                <button v-if="!contextMenu.item.isFolder" @click="downloadFile(contextMenu.item.id)" class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Descargar
                </button>
                <button @click="handleCopy(contextMenu.item)" class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    Copiar
                </button>
                <button @click="handleRename(contextMenu.item)" class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    Renombrar
                </button>
                <button @click="handleDetails(contextMenu.item)" class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Detalles
                </button>
                <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>
                <button @click="contextMenu.item.isFolder ? deleteFolder(contextMenu.item.id) : deleteFile(contextMenu.item.id)" class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-red-600 dark:text-red-400 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Eliminar
                </button>
            </template>
            <template v-else>
                <button v-if="store.clipboard.item" @click="handlePaste" class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    Pegar
                </button>
                <div v-else class="px-4 py-2 text-gray-400 text-xs italic">
                    Sin acciones disponibles aquí
                </div>
            </template>
        </div>

        <!-- Details Modal -->
        <Modal :show="detailsModal.show" @close="detailsModal.show = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 items-center flex gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Detalles del Elemento
                </h2>
                <div v-if="detailsModal.item" class="space-y-4 text-sm text-gray-600 dark:text-gray-400">
                    <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg space-y-3">
                        <p class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-2">
                            <span class="font-semibold">Nombre:</span> 
                            <span>{{ detailsModal.item.name }}</span>
                        </p>
                        <p class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-2">
                            <span class="font-semibold">Tipo:</span> 
                            <span>{{ detailsModal.item.type }}</span>
                        </p>
                        <p class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-2">
                            <span class="font-semibold">Tamaño:</span> 
                            <span>{{ detailsModal.item.size }}</span>
                        </p>
                        <p class="flex justify-between items-center">
                            <span class="font-semibold">Modificado:</span> 
                            <span>{{ detailsModal.item.updated }}</span>
                        </p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button @click="detailsModal.show = false" class="bg-gray-200 dark:bg-gray-700 px-4 py-2 rounded-lg text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 font-medium transition cursor-pointer">
                        Cerrar
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
