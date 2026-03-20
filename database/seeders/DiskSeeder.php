<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Folder;
use App\Models\File;

class DiskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first();
        if (!$admin) return;

        // Crear carpeta raíz
        $docs = Folder::create([
            'name' => 'Documentos Personales',
            'user_id' => $admin->id
        ]);

        // Crear subcarpeta
        $proyectos = Folder::create([
            'name' => 'Proyectos Laravel',
            'parent_id' => $docs->id,
            'user_id' => $admin->id
        ]);

        // Crear archivos
        File::create([
            'name' => 'manual_usuario.pdf',
            'path' => 'files/manual_usuario.pdf',
            'size' => 1024 * 1024 * 2.5, // 2.5 MB
            'mime_type' => 'application/pdf',
            'user_id' => $admin->id,
            'folder_id' => $docs->id
        ]);

        File::create([
            'name' => 'logo_vincebus.png',
            'path' => 'files/logo_vincebus.png',
            'size' => 1024 * 500, // 500 KB
            'mime_type' => 'image/png',
            'user_id' => $admin->id,
            'folder_id' => null // Raíz
        ]);
        
        File::create([
            'name' => 'notas.txt',
            'path' => 'files/notas.txt',
            'size' => 1024 * 12, // 12 KB
            'mime_type' => 'text/plain',
            'user_id' => $admin->id,
            'folder_id' => null // Raíz
        ]);
    }
}
