<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $perms = [
            'tracks.manage',
            'levels.manage',
            'subjects.manage',

            'courses.view',
            'courses.create',
            'courses.update',
            'courses.delete',
            'courses.publish',
            'courses.price',

            'enrollments.manage',

            'users.manage',
            'roles.manage',
        ];

        foreach ($perms as $p) {
            Permission::findOrCreate($p);
        }

        $admin   = Role::findOrCreate('admin');
        $manager = Role::findOrCreate('manager');
        $teacher = Role::findOrCreate('teacher');
        $student = Role::findOrCreate('student'); // ✅ nouveau

        $admin->syncPermissions($perms);

        $manager->syncPermissions(array_values(array_diff($perms, [
            'users.manage',
            'roles.manage',
        ])));

        $teacher->syncPermissions([
            'courses.view',
            'courses.update',
        ]);

        // ✅ student: aucune permission (juste un rôle “public”)
        $student->syncPermissions([]);
    }
}