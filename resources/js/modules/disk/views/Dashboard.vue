<script setup>
import AuthenticatedLayout from '@/shared/layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import { useFileStore } from '../store/fileStore';
import { useFileSystem } from '../composables/useFileSystem';

const props = defineProps({
    initialFiles: Array,
    initialFolders: Array,
    storageUsed: Number,
    storageLimit: Number,
});

const store = useFileStore();
const { navigateToFolder, uploadFile, createFolder } = useFileSystem();

onMounted(() => {
    store.setInitialData({
        files: props.initialFiles || [
            { id: 1, name: 'presupuesto_2026.pdf', size: 2516582, type: 'PDF', updated: 'hace 2 horas' },
            { id: 2, name: 'foto_servidor.jpg', size: 4300000, type: 'Imagen', updated: 'ayer' },
            { id: 3, name: 'configuracion_docker.txt', size: 12288, type: 'Texto', updated: 'hace 3 días' },
        ],
        folders: props.initialFolders || [
            { id: 1, name: 'Documentos' },
            { id: 2, name: 'Imágenes' },
            { id: 3, name: 'Proyectos' },
        ],
        storageUsed: props.storageUsed,
        storageLimit: props.storageLimit,
    });
});
</script>

<template>
    <Head title="Mi Unidad" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <nav class="flex text-sm text-gray-500 mb-2">
                    <ol class="list-none p-0 inline-flex">
                        <li class="flex items-center">
                            <span class="hover:text-gray-700 dark:hover:text-gray-300">Mi Unidad</span>
                        </li>
                    </ol>
                </nav>
                
                <div class="flex gap-2">
                    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-all flex items-center gap-2 shadow-sm shadow-indigo-200 dark:shadow-none">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nuevo
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-8">
            <!-- Folders Section -->
            <section>
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Carpetas</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div v-for="folder in store.folders" :key="folder.id" 
                             @click="navigateToFolder(folder.id)"
                             class="flex items-center p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 hover:shadow-md hover:border-indigo-200 dark:hover:border-indigo-500/50 transition-all cursor-pointer group">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg text-indigo-600 dark:text-indigo-400">
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
                                </svg>
                            </div>
                            <span class="font-medium truncate">{{ folder.name }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Files Section -->
            <section>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Archivos Recientes</h3>
                    <div class="flex border border-gray-200 dark:border-gray-800 rounded-lg overflow-hidden">
                        <button class="p-1.5 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor font-bold">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <button class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor font-bold">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </button>
                    </div>
                </div>
                
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
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="file in store.files" :key="file.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group cursor-pointer">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="text-gray-400 group-hover:text-indigo-500 transition-colors">
                                            <svg v-if="file.type === 'Imagen'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2-2v12a2 2 0 002 2z" /></svg>
                                            <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                        </div>
                                        <span class="text-sm font-medium">{{ file.name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell">{{ file.size }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden lg:table-cell">{{ file.updated }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <button class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
