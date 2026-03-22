import { useFileStore } from '../store/fileStore';
import { router } from '@inertiajs/vue3';

export function useFileSystem() {
    const store = useFileStore();

    const navigateToFolder = (folderId) => {
        router.get('/dashboard', { folder: folderId }, {
            preserveState: true,
            replace: true,
            only: ['initialFolders', 'initialFiles', 'breadcrumbs', 'currentFolderId', 'storageUsed'],
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

    const renameFile = (fileId, newName) => {
        router.put(route('disk.file.rename', { file: fileId }), {
            name: newName
        }, {
            preserveScroll: true
        });
    };

    const renameFolder = (folderId, newName) => {
        router.put(route('disk.folder.rename', { folder: folderId }), {
            name: newName
        }, {
            preserveScroll: true
        });
    };

    const pasteItem = (targetFolderId = null) => {
        if (!store.clipboard.item) return;

        router.post(route('disk.copy'), {
            id: store.clipboard.item.id,
            type: store.clipboard.type,
            target_folder_id: targetFolderId
        }, {
            preserveScroll: true,
            onSuccess: () => {
                store.clipboard = { type: null, item: null, action: null };
            }
        });
    };

    return {
        navigateToFolder,
        uploadFile,
        downloadFile,
        createFolder,
        deleteFile,
        deleteFolder,
        renameFile,
        renameFolder,
        pasteItem,
    };
}
