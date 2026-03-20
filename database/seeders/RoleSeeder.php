<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles
        $adminRole = Role::create(['name' => 'Administrador']);
        $userRole = Role::create(['name' => 'Usuario']);

        // Definir permisos básicos
        $managePlanes = Permission::create(['name' => 'gestionar planes']);
        $uploadFiles = Permission::create(['name' => 'subir archivos']);
        $viewFiles = Permission::create(['name' => 'ver archivos']);

        // Asignar permisos a roles
        $adminRole->givePermissionTo([$managePlanes, $uploadFiles, $viewFiles]);
        $userRole->givePermissionTo([$uploadFiles, $viewFiles]);
    }
}
