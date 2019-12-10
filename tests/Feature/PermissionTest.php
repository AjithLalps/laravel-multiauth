<?php

namespace Bitfumes\Multiauth\Tests\Feature;

use Bitfumes\Multiauth\Tests\TestCase;
use Bitfumes\Multiauth\Model\Permission;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PermissionTest extends TestCase
{
    use DatabaseMigrations;

    public function create_permission($args = [], $num = null)
    {
        return factory(Permission::class, $num)->create($args);
    }

    /** @test */
    public function api_can_give_all_permission()
    {
        $this->create_permission();
        $this->getJson(route('permission.index'))->assertOk()->assertJsonStructure(['data']);
    }

    /** @test */
    public function api_can_give_single_permission()
    {
        $permission = $this->create_permission();
        $this->getJson(route('permission.show', $permission->id))->assertJsonStructure(['data']);
    }

    /** @test */
    public function api_can_store_new_permission()
    {
        $permission = factory(Permission::class)->make(['name'=>'Laravel']);
        $this->postJson(route('permission.store'), $permission->toArray())
        ->assertStatus(201);
        $this->assertDatabaseHas('Permissions', ['name'=>'Laravel']);
    }

    /** @test */
    public function api_can_update_permission()
    {
        $permission = $this->create_permission();
        $this->putJson(route('permission.update', $permission->id), ['name'=>'UpdatedValue'])
        ->assertStatus(202);
        $this->assertDatabaseHas('Permissions', ['name'=>'UpdatedValue']);
    }

    /** @test */
    public function api_can_delete_permission()
    {
        $permission = $this->create_permission();
        $this->deleteJson(route('permission.destroy', $permission->id))->assertStatus(204);
        $this->assertDatabaseMissing('Permissions', ['name'=>$permission->name]);
    }

    /** @test */
    public function api_can_give_admin_with_all_its_permissions()
    {
        $admin      = $this->loginSuperAdmin();
        $permission = $this->create_permission();
        $admin->addDirectPermission($permission->id);
        $res        = $this->getJson(route('admin.all'))->json();
        $this->assertEquals($permission->name, $res['data'][0]['permissions'][0]['name']);
    }
}
