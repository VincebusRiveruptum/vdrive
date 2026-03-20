import { useFileStore } from '../store/fileStore';
import { router } from '@inertiajs/vue3';

export function useFileSystem() {
    const store = useFileStore();

    const navigateToFolder = (folderId) => {
        router.get('/dashboard', { folder: folderId }, {
            preserveState: true,
            replace: true,
            only: ['initialFolders', 'initialFiles'],
        });
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
