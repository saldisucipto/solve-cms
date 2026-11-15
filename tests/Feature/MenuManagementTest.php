<?php

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $role = Role::where('name', 'super-admin')->firstOrFail();
    $this->admin = User::factory()->create(['email_verified_at' => now()]);
    $this->admin->roles()->attach($role);
});

test('admin can create a menu', function () {
    $this->actingAs($this->admin)
        ->post('/admin/menus', ['name' => 'Primary Navigation', 'slug' => '', 'location' => 'primary'])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('menus', ['name' => 'Primary Navigation', 'location' => 'primary']);
});

test('two menus cannot share a location', function () {
    Menu::create(['name' => 'First', 'slug' => 'first', 'location' => 'primary']);

    $this->actingAs($this->admin)
        ->post('/admin/menus', ['name' => 'Second', 'slug' => 'second', 'location' => 'primary'])
        ->assertSessionHasErrors('location');
});

test('admin can add a custom item to a menu', function () {
    $menu = Menu::create(['name' => 'Main', 'slug' => 'main', 'location' => 'primary']);

    $this->actingAs($this->admin)
        ->post("/admin/menus/{$menu->id}/items", [
            'label' => 'Home',
            'type' => 'custom',
            'url' => '/',
            'target' => '_self',
            'order' => 0,
        ])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('menu_items', ['menu_id' => $menu->id, 'label' => 'Home', 'url' => '/']);
});

test('admin can add an item that links to a page', function () {
    $menu = Menu::create(['name' => 'Main', 'slug' => 'main', 'location' => 'primary']);
    $page = Page::factory()->published()->create();

    $this->actingAs($this->admin)
        ->post("/admin/menus/{$menu->id}/items", [
            'label' => $page->title,
            'type' => 'page',
            'linkable_id' => $page->id,
            'target' => '_self',
            'order' => 1,
        ])
        ->assertSessionHasNoErrors();

    $item = MenuItem::firstOrFail();

    expect($item->linkable_type)->toBe(Page::class)
        ->and($item->linkable_id)->toBe($page->id)
        ->and($item->url)->toBeNull();
});

test('linked item requires an existing target', function () {
    $menu = Menu::create(['name' => 'Main', 'slug' => 'main', 'location' => 'primary']);

    $this->actingAs($this->admin)
        ->post("/admin/menus/{$menu->id}/items", [
            'label' => 'Ghost',
            'type' => 'page',
            'linkable_id' => 99999,
            'target' => '_self',
        ])
        ->assertSessionHasErrors('linkable_id');
});

test('custom item requires a url', function () {
    $menu = Menu::create(['name' => 'Main', 'slug' => 'main', 'location' => 'primary']);

    $this->actingAs($this->admin)
        ->post("/admin/menus/{$menu->id}/items", [
            'label' => 'Nowhere',
            'type' => 'custom',
            'target' => '_self',
        ])
        ->assertSessionHasErrors('url');
});

test('admin can delete a menu item', function () {
    $menu = Menu::create(['name' => 'Main', 'slug' => 'main', 'location' => 'primary']);
    $item = $menu->items()->create([
        'label' => 'Home',
        'type' => 'custom',
        'url' => '/',
        'target' => '_self',
        'order' => 0,
    ]);

    $this->actingAs($this->admin)
        ->delete("/admin/menus/{$menu->id}/items/{$item->id}")
        ->assertSessionHasNoErrors();

    $this->assertDatabaseMissing('menu_items', ['id' => $item->id]);
});

test('an item from another menu cannot be edited through this menu', function () {
    $menuA = Menu::create(['name' => 'A', 'slug' => 'a', 'location' => 'primary']);
    $menuB = Menu::create(['name' => 'B', 'slug' => 'b', 'location' => 'footer']);

    $item = $menuB->items()->create([
        'label' => 'Home',
        'type' => 'custom',
        'url' => '/',
        'target' => '_self',
        'order' => 0,
    ]);

    $this->actingAs($this->admin)
        ->delete("/admin/menus/{$menuA->id}/items/{$item->id}")
        ->assertNotFound();
});

test('user without permission cannot manage menus', function () {
    $role = Role::where('name', 'author')->firstOrFail();
    $author = User::factory()->create(['email_verified_at' => now()]);
    $author->roles()->attach($role);

    $this->actingAs($author)
        ->get('/admin/menus')
        ->assertForbidden();
});
