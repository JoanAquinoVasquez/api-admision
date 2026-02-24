<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
        // // Crear roles
        // $superAdminRole = Role::create([
        //     'nombre' => 'Super Administrativo',
        //     'slug' => 'super-admin'
        // ]);

        // $adminRole = Role::create([
        //     'nombre' => 'Administrativo',
        //     'slug' => 'admin'
        // ]);

        // $comisionRole = Role::create([
        //     'nombre' => 'Comision Admision',
        //     'slug' => 'comision'
        // ]);

        // // Crear usuarios y asignar roles
        // $superAdmin = User::create([
        //     'name' => 'Super Admin',
        //     'email' => 'superadmin@example.com',
        //     'password' => Hash::make('password123'),
        // ]);
        // $superAdmin->roles()->attach($superAdminRole->id);

        // $admin = User::create([
        //     'name' => 'Administrador',
        //     'email' => 'admin@example.com',
        //     'password' => Hash::make('password123'),
        // ]);
        // $admin->roles()->attach($adminRole->id);

        // $comision = User::create([
        //     'name' => 'Comision',
        //     'email' => 'comision@example.com',
        //     'password' => Hash::make('password123'),
        // ]);
        // $comision->roles()->attach($comisionRole->id);
    }
}
