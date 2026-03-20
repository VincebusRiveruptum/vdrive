<script setup>
import AuthenticatedLayout from '@/shared/layouts/AuthenticatedLayout.vue';
import Dropdown from '@/shared/Dropdown.vue';
import DropdownLink from '@/shared/DropdownLink.vue';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, watch, computed, ref } from 'vue';
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
const { navigateToFolder, uploadFile, downloadFile, createFolder, deleteFile, deleteFolder } = useFileSystem();

const updateStore = () => {
    store.setInitialData({
        files: props.initialFiles || [],
        folders: props.initialFolders || [],
        storageUsed: props.storageUsed || 0,
        storageLimit: props.storageLimit || (16 * 1024 * 1024 * 1024),
    });
};

onMounted(updateStore);

watch(
    () => [props.initialFiles, props.initialFolders],
    updateStore,
    { deep: true }
);

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
...

        <div class="space-y-4">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Tamaño</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Última modificación</th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Acciones</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                        <tr v-for="item in items" :key="item.isFolder ? 'fol-' + item.id : 'fil-' + item.id" 
                            @click="item.isFolder ? navigateToFolder(item.id) : null"
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
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <div class="flex justify-end gap-2">
                                    <button 
                                        v-if="!item.isFolder"
                                        @click.stop="downloadFile(item.id)"
                                        class="text-gray-400 hover:text-indigo-600 transition-colors p-1"
                                        title="Descargar"
                                    >
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </button>
                                    <button 
                                        @click.stop="item.isFolder ? deleteFolder(item.id) : deleteFile(item.id)"
                                        class="text-gray-400 hover:text-red-500 transition-colors p-1"
                                        title="Eliminar"
                                    >
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                    <button @click.stop class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1">
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="items.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
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
    </AuthenticatedLayout>
</template>
