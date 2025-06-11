<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:super-admin {email?} {name?} {password?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a super admin user with all permissions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email') ?? $this->ask('Email del superadmin');
        $name = $this->argument('name') ?? $this->ask('Nombre del superadmin');
        $password = $this->argument('password') ?? $this->secret('Contraseña del superadmin');

        // Verificar si el usuario ya existe
        $user = User::where('email', $email)->first();

        if ($user) {
            $this->info("El usuario {$email} ya existe.");
            if (!$this->confirm('¿Quieres convertirlo en superadmin?', true)) {
                return Command::FAILURE;
            }
        } else {
            // Crear usuario
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);
            $this->info("Usuario {$email} creado exitosamente.");
        }

        // Crear el rol super-admin si no existe
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        
        // Asignar todos los permisos al rol
        $permissions = Permission::all();
        $superAdminRole->syncPermissions($permissions);
        $this->info("Se han asignado " . $permissions->count() . " permisos al rol super-admin.");

        // Asignar rol al usuario
        $user->assignRole('super-admin');
        
        $this->info("¡{$name} ({$email}) ahora es un superadmin con todos los permisos!");
        
        return Command::SUCCESS;
    }
}