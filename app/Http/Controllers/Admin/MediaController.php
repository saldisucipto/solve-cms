<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMediaRequest;
use App\Http\Requests\Admin\UpdateMediaRequest;
use App\Models\Media;
use App\Services\ActivityLogger;
use App\Services\Media\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MediaController extends Controller
{
    public function __construct(
        private readonly MediaService $media,
        private readonly ActivityLogger $activity,
    ) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Media::class);

        $type = $request->string('type')->value();
        $search = $request->string('search')->value();

        $media = Media::query()
            ->when($search, fn ($query, string $term) => $query->where(fn ($inner) => $inner
                ->where('title', 'like', "%{$term}%")
                ->orWhere('original_name', 'like', "%{$term}%")
                ->orWhere('alt_text', 'like', "%{$term}%")))
            ->when($type === 'image', fn ($query) => $query->where('mime_type', 'like', 'image/%'))
            ->when($type === 'video', fn ($query) => $query->where('mime_type', 'like', 'video/%'))
            ->when($type === 'audio', fn ($query) => $query->where('mime_type', 'like', 'audio/%'))
            ->when($type === 'document', fn ($query) => $query->whereNot(fn ($inner) => $inner
                ->where('mime_type', 'like', 'image/%')
                ->orWhere('mime_type', 'like', 'video/%')
                ->orWhere('mime_type', 'like', 'audio/%')))
            ->latest()
            ->paginate(24)
            ->withQueryString()
            ->through(fn (Media $item): array => [
                'id' => $item->id,
                'title' => $item->title,
                'original_name' => $item->original_name,
                'url' => $item->url(),
                'thumbnail' => $this->media->thumbnailUrl($item),
                'mime_type' => $item->mime_type,
                'extension' => $item->extension,
                'size' => $item->humanSize(),
                'width' => $item->width,
                'height' => $item->height,
                'alt_text' => $item->alt_text,
                'caption' => $item->caption,
                'created_at' => $item->created_at?->toIso8601String(),
            ]);

        return Inertia::render('Admin/Media/Index', [
            'media' => $media,
            'filters' => [
                'type' => $type,
                'search' => $search,
            ],
            'can' => [
                'upload' => $request->user()->can('create', Media::class),
                'delete' => $request->user()->hasPermission('media.delete'),
            ],
        ]);
    }

    /**
     * JSON list used by the media picker inside the editor and the featured
     * image field. Images only — those are the assets an editor places.
     */
    public function library(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Media::class);

        $search = $request->string('search')->value();

        $items = Media::query()
            ->where('mime_type', 'like', 'image/%')
            ->when($search, fn ($query, string $term) => $query->where(fn ($inner) => $inner
                ->where('title', 'like', "%{$term}%")
                ->orWhere('original_name', 'like', "%{$term}%")
                ->orWhere('alt_text', 'like', "%{$term}%")))
            ->latest()
            ->limit(60)
            ->get()
            ->map(fn (Media $media): array => $this->presentForPicker($media))
            ->all();

        return response()->json(['items' => $items]);
    }

    public function store(StoreMediaRequest $request): RedirectResponse|JsonResponse
    {
        $media = $this->media->store(
            $request->file('file'),
            $request->user()->id,
            $request->safe()->only(['title', 'alt_text', 'caption']),
        );

        $this->activity->log('media.uploaded', "Uploaded \"{$media->original_name}\"", $media);

        if ($request->wantsJson()) {
            return response()->json(['media' => $this->presentForPicker($media)], 201);
        }

        return back()->with('success', 'File uploaded.');
    }

    /**
     * @return array<string, mixed>
     */
    private function presentForPicker(Media $media): array
    {
        return [
            'id' => $media->id,
            'url' => $media->url(),
            'thumbnail' => $this->media->thumbnailUrl($media),
            'title' => $media->title ?? $media->original_name,
            'alt_text' => $media->alt_text,
            'mime_type' => $media->mime_type,
            'width' => $media->width,
            'height' => $media->height,
        ];
    }

    public function update(UpdateMediaRequest $request, Media $media): RedirectResponse
    {
        $media->update($request->validated());

        $this->activity->log('media.updated', "Updated media \"{$media->title}\"", $media);

        return back()->with('success', 'Media details updated.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        Gate::authorize('delete', $media);

        $name = $media->original_name;
        $this->media->delete($media);

        $this->activity->log('media.deleted', "Deleted media \"{$name}\"");

        return back()->with('success', 'Media deleted.');
    }
}
