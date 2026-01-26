<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Roles
        $superAdminRole = Role::create([
            'nombre' => 'Super Administrativo',
            'slug' => 'super-admin'
        ]);

        $adminRole = Role::create([
            'nombre' => 'Administrativo',
            'slug' => 'admin'
        ]);

        $comisionRole = Role::create([
            'nombre' => 'Comision Admision',
            'slug' => 'comision'
        ]);

        // Crear usuarios
        User::create([
            'email' => 'jaquinov@unprg.edu.pe',
        ])->roles()->attach($superAdminRole->id);

        User::create([
            'email' => 'arojasf@unprg.edu.pe',
        ])->roles()->attach($superAdminRole->id);

        // User::create([
        //     'email' => 'ivalle@unprg.edu.pe',
        // ])->roles()->attach($adminRole->id);

        User::create([
            'email' => 'lmija@unprg.edu.pe',
        ])->roles()->attach($superAdminRole->id);

        User::create([
            'email' => 'mcromero@unprg.edu.pe',
        ])->roles()->attach($adminRole->id);

        User::create([
            'email' => 'mleivac@unprg.edu.pe',
        ])->roles()->attach($adminRole->id);

        User::create([
            'email' => 'jarevaloc@unprg.edu.pe',
        ])->roles()->attach($adminRole->id);
        User::create([
            'email' => 'laznaran@unprg.edu.pe',
        ])->roles()->attach($comisionRole->id);
        User::create([
            'email' => 'gpuicon@unprg.edu.pe',
        ])->roles()->attach($comisionRole->id);
        User::create([
            'email' => 'rpa.uipath@unprg.edu.pe',
            'password' => '4dm1510nEPG2025-II'
        ]);
        User::create([
            'name' => 'Administrativo',
            'email' => '4dm1n1str4d0r@unprg.edu.pe',
            'password' => '4dm1510nEPG2025-II'
        ])->roles()->attach($adminRole->id);;

        User::create([
            'name' => 'Comision Admision',
            'email' => 'c0m1s10n@unprg.edu.pe',
            'password' => '4dm1510nEPG2025-II'
        ])->roles()->attach($comisionRole->id);
        User::create([
            'name' => 'Sender',
            'email' => 'sotoya@unprg.edu.pe',
        ])->roles()->attach($adminRole->id);
    }
}
