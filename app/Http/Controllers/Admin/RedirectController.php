<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Redirect;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RedirectController extends Controller
{
    /**
     * @var list<int>
     */
    private const STATUS_CODES = [301, 302, 307, 308];

    public function __construct(private readonly ActivityLogger $activity) {}

    public function index(Request $request): Response
    {
        Gate::authorize('seo.redirects');

        $search = $request->string('search')->value();

        $redirects = Redirect::query()
            ->when($search, fn ($query, string $term) => $query->where(fn ($inner) => $inner
                ->where('source', 'like', "%{$term}%")
                ->orWhere('destination', 'like', "%{$term}%")))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString()
            ->through(fn (Redirect $redirect): array => [
                'id' => $redirect->id,
                'source' => $redirect->source,
                'destination' => $redirect->destination,
                'status_code' => $redirect->status_code,
                'hits' => $redirect->hits,
                'is_active' => $redirect->is_active,
                'last_hit_at' => $redirect->last_hit_at?->diffForHumans(),
            ]);

        return Inertia::render('Admin/Seo/Redirects', [
            'redirects' => $redirects,
            'filters' => ['search' => $search],
            'statusCodes' => self::STATUS_CODES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('seo.redirects');

        $data = $this->validated($request);
        $data['source'] = $this->normaliseSource($data['source']);

        $redirect = Redirect::create($data);

        $this->activity->log('redirect.created', "Created redirect {$redirect->source} → {$redirect->destination}", $redirect);

        return back()->with('success', 'Redirect created.');
    }

    public function update(Request $request, Redirect $redirect): RedirectResponse
    {
        Gate::authorize('seo.redirects');

        $data = $this->validated($request, $redirect);
        $data['source'] = $this->normaliseSource($data['source']);

        $redirect->update($data);

        $this->activity->log('redirect.updated', "Updated redirect {$redirect->source}", $redirect);

        return back()->with('success', 'Redirect updated.');
    }

    public function destroy(Redirect $redirect): RedirectResponse
    {
        Gate::authorize('seo.redirects');

        $source = $redirect->source;
        $redirect->delete();

        $this->activity->log('redirect.deleted', "Deleted redirect {$source}");

        return back()->with('success', 'Redirect deleted.');
    }

    public function toggle(Redirect $redirect): RedirectResponse
    {
        Gate::authorize('seo.redirects');

        $redirect->update(['is_active' => ! $redirect->is_active]);

        return back()->with('success', $redirect->is_active ? 'Redirect enabled.' : 'Redirect disabled.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Redirect $redirect = null): array
    {
        $data = $request->validate([
            'source' => [
                'required',
                'string',
                'max:2048',
                Rule::unique('redirects', 'source')->ignore($redirect?->id),
            ],
            'destination' => ['required', 'string', 'max:2048'],
            'status_code' => ['required', 'integer', Rule::in(self::STATUS_CODES)],
            'is_active' => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }

    private function normaliseSource(string $source): string
    {
        $path = '/'.ltrim(trim($source), '/');

        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        return $path;
    }
}
