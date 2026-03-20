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

    const uploadFile = (file, folderId = null) => {
        router.post(route('disk.upload'), {
            file: file,
            folder_id: folderId
        }, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                // El store se actualizará vía props en Dashboard.vue
            }
        });
    };

    const downloadFile = (fileId) => {
        window.location.href = route('disk.download', { file: fileId });
    };

    const createFolder = (name, parentId = null) => {
        router.post(route('disk.folder.store'), {
            name: name,
            parent_id: parentId
        }, {
            preserveScroll: true
        });
    };

    const deleteFile = (fileId) => {
        if (confirm('¿Estás seguro de que deseas eliminar este archivo?')) {
            router.delete(route('disk.file.destroy', { file: fileId }), {
                preserveScroll: true
            });
        }
    };

    const deleteFolder = (folderId) => {
        if (confirm('¿Estás seguro de que deseas eliminar esta carpeta y todo su contenido?')) {
            router.delete(route('disk.folder.destroy', { folder: folderId }), {
                preserveScroll: true
            });
        }
    };

    return {
        navigateToFolder,
        uploadFile,
        downloadFile,
        createFolder,
        deleteFile,
        deleteFolder,
    };
}
