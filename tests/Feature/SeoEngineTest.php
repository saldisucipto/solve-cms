<?php

use App\Models\Redirect;
use App\Models\Role;
use App\Models\User;
use App\Services\Seo\SeoAnalyzer;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('redirect handling', function () {
    test('an active redirect sends the visitor to the destination', function () {
        Redirect::create([
            'source' => '/old-page',
            'destination' => '/new-page',
            'status_code' => 301,
            'is_active' => true,
        ]);

        $this->get('/old-page')->assertRedirect('/new-page');
    });

    test('302 redirects are honoured too', function () {
        Redirect::create([
            'source' => '/temp',
            'destination' => '/somewhere',
            'status_code' => 302,
            'is_active' => true,
        ]);

        $response = $this->get('/temp');

        $response->assertStatus(302);
        expect($response->headers->get('Location'))->toContain('/somewhere');
    });

    test('a redirect records a hit', function () {
        $redirect = Redirect::create([
            'source' => '/counted',
            'destination' => '/target',
            'status_code' => 301,
            'is_active' => true,
        ]);

        $this->get('/counted');

        expect($redirect->fresh()->hits)->toBe(1);
        expect($redirect->fresh()->last_hit_at)->not->toBeNull();
    });

    test('an inactive redirect is ignored', function () {
        Redirect::create([
            'source' => '/disabled',
            'destination' => '/target',
            'status_code' => 301,
            'is_active' => false,
        ]);

        $this->get('/disabled')->assertNotFound();
    });

    test('a redirect that loops back to itself is ignored', function () {
        Redirect::create([
            'source' => '/loop',
            'destination' => '/loop',
            'status_code' => 301,
            'is_active' => true,
        ]);

        $this->get('/loop')->assertNotFound();
    });

    test('admin routes are never intercepted by redirects', function () {
        Redirect::create([
            'source' => '/admin',
            'destination' => '/somewhere',
            'status_code' => 301,
            'is_active' => true,
        ]);

        $this->get('/admin')->assertRedirect('/login');
    });

    test('unknown paths still return 404', function () {
        $this->get('/no-such-page')->assertNotFound();
    });
});

describe('redirect management', function () {
    beforeEach(function () {
        $this->seed(RolePermissionSeeder::class);

        $role = Role::where('name', 'super-admin')->firstOrFail();
        $this->admin = User::factory()->create(['email_verified_at' => now()]);
        $this->admin->roles()->attach($role);
    });

    test('admin can create a redirect and the source is normalised', function () {
        $this->actingAs($this->admin)
            ->post('/admin/seo/redirects', [
                'source' => 'legacy-url/',
                'destination' => '/new',
                'status_code' => 301,
                'is_active' => true,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('redirects', ['source' => '/legacy-url']);
    });

    test('redirect source must be unique', function () {
        Redirect::create(['source' => '/taken', 'destination' => '/x', 'status_code' => 301]);

        $this->actingAs($this->admin)
            ->post('/admin/seo/redirects', [
                'source' => '/taken',
                'destination' => '/y',
                'status_code' => 301,
            ])
            ->assertSessionHasErrors('source');
    });

    test('an author cannot manage redirects', function () {
        $role = Role::where('name', 'author')->firstOrFail();
        $author = User::factory()->create(['email_verified_at' => now()]);
        $author->roles()->attach($role);

        $this->actingAs($author)
            ->get('/admin/seo/redirects')
            ->assertForbidden();
    });

    test('redirect can be toggled and deleted', function () {
        $redirect = Redirect::create([
            'source' => '/toggle-me',
            'destination' => '/x',
            'status_code' => 301,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)
            ->patch("/admin/seo/redirects/{$redirect->id}/toggle")
            ->assertSessionHasNoErrors();

        expect($redirect->fresh()->is_active)->toBeFalse();

        $this->actingAs($this->admin)
            ->delete("/admin/seo/redirects/{$redirect->id}")
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('redirects', ['id' => $redirect->id]);
    });
});

describe('seo analyzer', function () {
    beforeEach(function () {
        $this->analyzer = new SeoAnalyzer;
    });

    test('flags a missing title and description', function () {
        $result = $this->analyzer->analyze(['title' => null, 'content' => '<p>Hello</p>']);

        $ids = collect($result['checks'])->keyBy('id');

        expect($ids['title_present']['status'])->toBe(SeoAnalyzer::STATUS_BAD)
            ->and($ids['description_present']['status'])->toBe(SeoAnalyzer::STATUS_BAD);
    });

    test('passes a well-formed entry', function () {
        $result = $this->analyzer->analyze([
            'title' => 'A Complete Guide to Solve CMS Features',
            'seo_title' => 'A Complete Guide to Solve CMS Features',
            'meta_description' => str_repeat('A thorough description of solve cms features and capabilities. ', 3),
            'focus_keyword' => 'solve cms',
            'slug' => 'complete-guide-solve-cms-features',
            'content' => '<p>Solve cms is a content management system.</p><h2>Solve cms features</h2>'
                .'<p>'.str_repeat('word ', 320).'</p>',
        ]);

        $ids = collect($result['checks'])->keyBy('id');

        expect($ids['keyword_in_title']['status'])->toBe(SeoAnalyzer::STATUS_GOOD)
            ->and($ids['keyword_in_slug']['status'])->toBe(SeoAnalyzer::STATUS_GOOD)
            ->and($ids['content_length']['status'])->toBe(SeoAnalyzer::STATUS_GOOD);
    });

    test('reports word count and heading count', function () {
        $result = $this->analyzer->analyze([
            'title' => 'Counting Test Entry',
            'content' => '<h2>One</h2><h3>Two</h3><p>alpha beta gamma</p>',
        ]);

        expect($result['stats']['words'])->toBe(5)
            ->and($result['stats']['headings'])->toBe(2);
    });

    test('warns when a body heading is h1', function () {
        $result = $this->analyzer->analyze([
            'title' => 'Heading Test',
            'content' => '<h1>Big</h1><p>text</p>',
        ]);

        $ids = collect($result['checks'])->keyBy('id');

        expect($ids['heading_hierarchy']['status'])->toBe(SeoAnalyzer::STATUS_WARNING);
    });

    test('the analyze endpoint returns checks as json', function () {
        $this->seed(RolePermissionSeeder::class);

        $role = Role::where('name', 'super-admin')->firstOrFail();
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->roles()->attach($role);

        $response = $this->actingAs($admin)->postJson('/admin/seo/analyze', [
            'title' => 'Endpoint Test',
            'seo_title' => 'Endpoint Test Title',
            'meta_description' => 'Description for the endpoint test.',
            'focus_keyword' => 'endpoint',
            'slug' => 'endpoint-test',
            'content' => '<p>endpoint body</p>',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'summary' => ['good', 'warning', 'bad', 'total'],
                'checks',
                'stats',
            ]);
    });

    test('summary counts every check', function () {
        $result = $this->analyzer->analyze(['title' => 'Summary Test']);

        $summary = $result['summary'];

        expect($summary['good'] + $summary['warning'] + $summary['bad'])->toBe($summary['total'])
            ->and($summary['total'])->toBe(count($result['checks']));
    });
});
