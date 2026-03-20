<script setup>
import { ref } from 'vue';
import ApplicationLogo from '../ApplicationLogo.vue';
import Dropdown from '../Dropdown.vue';
import DropdownLink from '../DropdownLink.vue';
import { Link } from '@inertiajs/vue3';
import { useFileStore } from '../../modules/disk/store/fileStore';

const store = useFileStore();
const showingNavigationDropdown = ref(false);
const isSidebarOpen = ref(true);

const navItems = [
    { name: 'Mi Unidad', icon: 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z', route: 'dashboard', active: true },
    { name: 'Recientes', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', route: 'dashboard', active: false },
    { name: 'Papelera', icon: 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16', route: 'dashboard', active: false },
];
</script>

<template>
    <div class="flex h-screen bg-main-bg text-gray-900 dark:text-gray-100 font-sans overflow-hidden">
        <!-- Sidebar -->
        <aside 
            :class="isSidebarOpen ? 'w-64' : 'w-20'"
            class="hidden md:flex flex-col border-r border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 transition-all duration-300 ease-in-out"
        >
            <div class="p-6 flex items-center gap-3">
                <Link :href="route('dashboard')" class="flex items-center gap-2">
                    <ApplicationLogo class="h-8 w-8 fill-current text-indigo-600 dark:text-indigo-400" />
                    <span v-if="isSidebarOpen" class="text-xl font-bold tracking-tight">VDRIVE</span>
                </Link>
            </div>

            <nav class="flex-1 px-4 space-y-2 mt-4">
                <Link 
                    v-for="item in navItems" 
                    :key="item.name"
                    :href="route(item.route)"
                    :class="[
                        item.active 
                            ? 'bg-gray-100 dark:bg-gray-800 text-indigo-600 dark:text-indigo-400' 
                            : 'hover:bg-gray-50 dark:hover:bg-gray-800/50 text-gray-600 dark:text-gray-400'
                    ]"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors group"
                >
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                    </svg>
                    <span v-if="isSidebarOpen" class="font-medium">{{ item.name }}</span>
                </Link>
            </nav>

            <!-- Storage Info -->
            <div v-if="isSidebarOpen" class="p-4 m-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-700/50">
                <div class="flex justify-between text-xs mb-2">
                    <span class="text-gray-500">Almacenamiento</span>
                    <span class="font-semibold text-gray-700 dark:text-gray-300">{{ Math.round(store.storagePercentage) }}%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-indigo-500 h-1.5 rounded-full transition-all duration-300" :style="{ width: store.storagePercentage + '%' }"></div>
                </div>
                <p class="text-[10px] text-gray-400 mt-2 text-center">{{ store.formattedStorageUsed }} de {{ store.formattedStorageLimit }} usados</p>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Header -->
            <header class="h-16 flex items-center justify-between px-6 border-b border-gray-200 dark:border-gray-800 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md z-10">
                <div class="flex items-center gap-4 flex-1">
                    <button @click="isSidebarOpen = !isSidebarOpen" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg md:block hidden text-gray-500">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor font-bold">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    
                    <div class="max-w-md w-full relative hidden sm:block">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input 
                            type="text" 
                            placeholder="Buscar archivos..." 
                            class="block w-full pl-10 pr-3 py-2 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                        >
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button class="flex items-center gap-2 p-1.5 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full transition-all border border-transparent hover:border-gray-200 dark:hover:border-gray-700">
                                <div class="h-8 w-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-700 dark:text-indigo-300 font-bold text-xs">
                                    {{ $page.props.auth.user.name.charAt(0) }}
                                </div>
                                <span class="text-sm font-medium mr-1 hidden lg:block">{{ $page.props.auth.user.name }}</span>
                            </button>
                        </template>

                        <template #content>
                            <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-800">
                                <p class="text-xs text-gray-500">Conectado como</p>
                                <p class="text-sm font-semibold truncate">{{ $page.props.auth.user.email }}</p>
                            </div>
                            <DropdownLink :href="route('profile.edit')">Perfil</DropdownLink>
                            <div class="border-t border-gray-100 dark:border-gray-800"></div>
                            <DropdownLink :href="route('logout')" method="post" as="button" class="text-red-600 dark:text-red-400">
                                Cerrar Sesión
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-6 scroll-smooth">
                <div v-if="$slots.header" class="mb-6">
                    <slot name="header" />
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
</style>
