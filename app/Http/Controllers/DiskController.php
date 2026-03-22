<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        $folderId = $request->input('folder_id');
        
        // Determine physical path
        if ($folderId) {
            $folder = Folder::find($folderId);
            $basePath = $folder->physical_path;
        } else {
            $basePath = "users/{$user->id}/files";
        }

        // Handle safe filename and potential collisions
        $originalName = $upload->getClientOriginalName();
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $extension = $extension ? '.' . $extension : '';
        
        $safeName = \Illuminate\Support\Str::slug($filename, '-', 'es') . $extension;
        $counter = 1;
        
        while (Storage::disk('local')->exists($basePath . '/' . $safeName)) {
            $safeName = \Illuminate\Support\Str::slug($filename, '-', 'es') . " ({$counter})" . $extension;
            $counter++;
        }

        $path = $upload->storeAs($basePath, $safeName, 'local');

        File::create([
            'name' => $originalName,
            'path' => $path,
            'size' => $upload->getSize(),
            'mime_type' => $upload->getMimeType(),
            'user_id' => $user->id,
            'folder_id' => $folderId,
        ]);

        return back()->with('success', 'Archivo subido correctamente.');
    }

    public function download(File $file)
    {
        if ($file->user_id !== auth()->id()) {
            abort(403);
        }

        return Storage::disk('local')->download($file->path, $file->name);
    }

    public function storeFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:folders,id'
        ]);

        $folder = Folder::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'user_id' => auth()->id(),
        ]);

        Storage::disk('local')->makeDirectory($folder->physical_path);

        return back()->with('success', 'Carpeta creada correctamente.');
    }

    public function destroy(File $file)
    {
        if ($file->user_id !== auth()->id()) {
            abort(403);
        }

        // Eliminar físicamente
        Storage::delete($file->path);

        $file->delete();

        return back()->with('success', 'Archivo eliminado.');
    }

    public function destroyFolder(Folder $folder)
    {
        if ($folder->user_id !== auth()->id()) {
            abort(403);
        }

        // Eliminar físicamente recursivo
        Storage::disk('local')->deleteDirectory($folder->physical_path);

        $this->recursiveDelete($folder);

        return back()->with('success', 'Carpeta eliminada.');
    }

    private function recursiveDelete(Folder $folder)
    {
        // Solo base de datos
        foreach ($folder->files as $file) {
            $file->delete();
        }

        // Eliminar subcarpetas recursivamente de DB
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

    public function renameFile(Request $request, File $file)
    {
        $request->validate(['name' => 'required|string|max:255']);
        if ($file->user_id !== auth()->id()) abort(403);
        
        $oldPath = $file->path;
        $basePath = dirname($oldPath);
        
        $originalName = $request->name;
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $extension = $extension ? '.' . $extension : '';
        $safeName = \Illuminate\Support\Str::slug($filename, '-', 'es') . $extension;
        
        $counter = 1;
        $finalSafeName = $safeName;
        // Check collision excluding itself
        while (Storage::disk('local')->exists($basePath . '/' . $finalSafeName) && $basePath . '/' . $finalSafeName !== $oldPath) {
            $finalSafeName = \Illuminate\Support\Str::slug($filename, '-', 'es') . " ({$counter})" . $extension;
            $counter++;
        }
        
        $newPath = $basePath . '/' . $finalSafeName;
        if ($oldPath !== $newPath) {
            Storage::disk('local')->move($oldPath, $newPath);
            $file->path = $newPath;
        }
        
        $file->name = $originalName;
        $file->save();
        
        return back()->with('success', 'Archivo renombrado.');
    }

    public function renameFolder(Request $request, Folder $folder)
    {
        $request->validate(['name' => 'required|string|max:255']);
        if ($folder->user_id !== auth()->id()) abort(403);
        
        $oldPhysicalPath = $folder->physical_path;
        
        $folder->name = $request->name;
        $folder->save();
        
        // Refresh physical path
        $newPhysicalPath = $folder->physical_path;
        
        if ($oldPhysicalPath !== $newPhysicalPath) {
            Storage::disk('local')->move($oldPhysicalPath, $newPhysicalPath);
            $this->updateChildFilesPaths($folder, $oldPhysicalPath, $newPhysicalPath);
        }
        
        return back()->with('success', 'Carpeta renombrada.');
    }

    private function updateChildFilesPaths(Folder $folder, $oldBasePath, $newBasePath)
    {
        foreach ($folder->files as $file) {
            if (str_starts_with($file->path, $oldBasePath)) {
                $file->path = $newBasePath . substr($file->path, strlen($oldBasePath));
                $file->save();
            }
        }
        foreach ($folder->children as $child) {
            $this->updateChildFilesPaths($child, $oldBasePath, $newBasePath);
        }
    }

    public function copyItem(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'type' => 'required|in:file,folder',
            'target_folder_id' => 'nullable|exists:folders,id'
        ]);
        
        $user = $request->user();
        
        if ($request->type === 'file') {
            $file = File::where('id', $request->id)->where('user_id', $user->id)->firstOrFail();
            $this->duplicateFile($file, $request->target_folder_id, $user);
        } else {
            $folder = Folder::where('id', $request->id)->where('user_id', $user->id)->firstOrFail();
            $this->duplicateFolder($folder, $request->target_folder_id, $user);
        }
        
        return back()->with('success', 'Elemento copiado correctamente.');
    }

    private function duplicateFile(File $file, $targetFolderId, $user)
    {
        $basePath = $targetFolderId ? Folder::find($targetFolderId)->physical_path : "users/{$user->id}/files";
        
        $originalName = $file->name;
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $extension = $extension ? '.' . $extension : '';
        
        $safeName = \Illuminate\Support\Str::slug($filename, '-', 'es') . $extension;
        $counter = 1;
        while (Storage::disk('local')->exists($basePath . '/' . $safeName)) {
            $safeName = \Illuminate\Support\Str::slug($filename, '-', 'es') . " ({$counter})" . $extension;
            $counter++;
        }
        $newPath = $basePath . '/' . $safeName;
        
        Storage::disk('local')->copy($file->path, $newPath);
        
        File::create([
            'name' => $originalName,
            'path' => $newPath,
            'size' => $file->size,
            'mime_type' => $file->mime_type,
            'user_id' => $user->id,
            'folder_id' => $targetFolderId,
        ]);
    }

    private function duplicateFolder(Folder $folder, $targetFolderId, $user)
    {
        $newName = $folder->name;
        if ($folder->parent_id == $targetFolderId) {
            $newName .= ' - Copia';
        }

        $newFolder = Folder::create([
            'name' => $newName,
            'parent_id' => $targetFolderId,
            'user_id' => $user->id,
        ]);

        Storage::disk('local')->makeDirectory($newFolder->physical_path);

        foreach ($folder->files as $file) {
            $this->duplicateFile($file, $newFolder->id, $user);
        }

        foreach ($folder->children as $child) {
            $this->duplicateFolder($child, $newFolder->id, $user);
        }
    }
}
