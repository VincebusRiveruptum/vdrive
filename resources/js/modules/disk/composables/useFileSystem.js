import { useFileStore } from '../store/fileStore';
import { router } from '@inertiajs/vue3';

export function useFileSystem() {
    const store = useFileStore();

    const navigateToFolder = (folderId) => {
        // En una implementación real, esto haría una petición Inertia
        console.log('Navegando a carpeta:', folderId);
        // router.get(route('dashboard', { folder: folderId }));
    };

    const uploadFile = (file) => {
        console.log('Subiendo archivo:', file.name);
        // Lógica de subida con Axios/Inertia
    };

    const createFolder = (name) => {
        console.log('Creando carpeta:', name);
    };

    return {
        navigateToFolder,
        uploadFile,
        createFolder,
    };
}
