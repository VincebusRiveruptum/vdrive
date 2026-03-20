<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DiskController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $folderId = $request->input('folder');

        $folders = Folder::where('user_id', $user->id)
            ->where('parent_id', $folderId)
            ->get();

        $files = File::where('user_id', $user->id)
            ->where('folder_id', $folderId)
            ->get();

        // Calcular breadcrumbs
        $breadcrumbs = [];
        if ($folderId) {
            $current = Folder::find($folderId);
            while ($current) {
                array_unshift($breadcrumbs, [
                    'id' => $current->id,
                    'name' => $current->name
                ]);
                $current = $current->parent;
            }
        }

        // Calcular almacenamiento
        $storageUsed = File::where('user_id', $user->id)->sum('size');
        $storageLimit = 16 * 1024 * 1024 * 1024; // 16GB estático por ahora

        return Inertia::render('disk/views/Dashboard', [
            'initialFolders' => $folders,
            'initialFiles' => $files->map(function ($file) {
                return [
                    'id' => $file->id,
                    'name' => $file->name,
                    'size' => $this->formatSize($file->size),
                    'type' => $this->getFileType($file->mime_type),
                    'updated' => $file->updated_at->diffForHumans(),
                ];
            }),
            'storageUsed' => (int) $storageUsed,
            'storageLimit' => $storageLimit,
            'breadcrumbs' => $breadcrumbs,
            'currentFolderId' => $folderId,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:102400', // Máximo 100MB por ahora
            'folder_id' => 'nullable|exists:folders,id'
        ]);

        $user = $request->user();
        $upload = $request->file('file');
        
        // Almacenar físicamente (usando el disco 'local' que es storage/app)
        // Guardamos en una carpeta privada por usuario
        $path = $upload->store("users/{$user->id}/files");

        File::create([
            'name' => $upload->getClientOriginalName(),
            'path' => $path,
            'size' => $upload->getSize(),
            'mime_type' => $upload->getMimeType(),
            'user_id' => $user->id,
            'folder_id' => $request->input('folder_id'),
        ]);

        return back()->with('success', 'Archivo subido correctamente.');
    }

    public function download(File $file)
    {
        if ($file->user_id !== auth()->id()) {
            abort(403);
        }

        return \Illuminate\Support\Facades\Storage::disk('local')->download($file->path, $file->name);
    }

    public function storeFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:folders,id'
        ]);

        Folder::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Carpeta creada correctamente.');
    }

    public function destroy(File $file)
    {
        if ($file->user_id !== auth()->id()) {
            abort(403);
        }

        // Eliminar físicamente
        \Illuminate\Support\Facades\Storage::delete($file->path);

        $file->delete();

        return back()->with('success', 'Archivo eliminado.');
    }

    public function destroyFolder(Folder $folder)
    {
        if ($folder->user_id !== auth()->id()) {
            abort(403);
        }

        $this->recursiveDelete($folder);

        return back()->with('success', 'Carpeta eliminada.');
    }

    private function recursiveDelete(Folder $folder)
    {
        // Eliminar archivos físicamente y de la BD
        foreach ($folder->files as $file) {
            \Illuminate\Support\Facades\Storage::delete($file->path);
            $file->delete();
        }

        // Eliminar subcarpetas recursivamente
        foreach ($folder->children as $subfolder) {
            $this->recursiveDelete($subfolder);
        }

        $folder->delete();
    }

    private function formatSize($bytes)
    {
        if ($bytes === 0) return '0 Bytes';
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log($k));
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    private function getFileType($mime)
    {
        if (str_contains($mime, 'image')) return 'Imagen';
        if (str_contains($mime, 'pdf')) return 'PDF';
        if (str_contains($mime, 'text')) return 'Texto';
        return 'Archivo';
    }
}
