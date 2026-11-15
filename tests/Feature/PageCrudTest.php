<?php

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

test('admin can create a page', function () {
    $this->actingAs($this->admin)
        ->post('/admin/pages', [
            'title' => 'About Us',
            'slug' => '',
            'template' => 'default',
            'status' => 'published',
        ])
        ->assertRedirect('/admin/pages');

    $this->assertDatabaseHas('pages', [
        'title' => 'About Us',
        'slug' => 'about-us',
        'status' => 'published',
    ]);
});

test('page slug is unique across pages', function () {
    Page::factory()->create(['slug' => 'contact']);

    $this->actingAs($this->admin)
        ->post('/admin/pages', [
            'title' => 'Contact',
            'slug' => 'contact',
            'template' => 'default',
            'status' => 'draft',
        ]);

    $this->assertDatabaseHas('pages', ['slug' => 'contact-2']);
});

test('admin can update a page', function () {
    $page = Page::factory()->create();

    $this->actingAs($this->admin)
        ->put("/admin/pages/{$page->id}", [
            'title' => 'Updated Page',
            'slug' => '',
            'template' => 'full-width',
            'status' => 'published',
        ])
        ->assertRedirect("/admin/pages/{$page->id}/edit");

    expect($page->fresh())
        ->title->toBe('Updated Page')
        ->template->toBe('full-width')
        ->status->toBe('published');
});

test('page requires a title', function () {
    $this->actingAs($this->admin)
        ->post('/admin/pages', [
            'title' => '',
            'template' => 'default',
            'status' => 'draft',
        ])
        ->assertSessionHasErrors('title');
});

test('page cannot be its own parent', function () {
    $page = Page::factory()->create();

    $this->actingAs($this->admin)
        ->put("/admin/pages/{$page->id}", [
            'title' => $page->title,
            'slug' => $page->slug,
            'template' => 'default',
            'status' => 'draft',
            'parent_id' => $page->id,
        ])
        ->assertSessionHasErrors('parent_id');
});

test('author cannot delete a page', function () {
    $role = Role::where('name', 'author')->firstOrFail();
    $author = User::factory()->create(['email_verified_at' => now()]);
    $author->roles()->attach($role);

    $page = Page::factory()->create();

    $this->actingAs($author)
        ->delete("/admin/pages/{$page->id}")
        ->assertForbidden();
});
