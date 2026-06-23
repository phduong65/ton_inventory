<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $accountant;
    protected User $supervisor;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->seed(RolesPermissionsSeeder::class);

        $this->admin      = User::factory()->create()->assignRole('admin');
        $this->manager    = User::factory()->create()->assignRole('manager');
        $this->accountant = User::factory()->create()->assignRole('accountant');
        $this->supervisor = User::factory()->create()->assignRole('supervisor');
    }
}
