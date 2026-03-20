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

        // Calcular almacenamiento (puedes mover esto a un servicio luego)
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
        ]);
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
