<?php

namespace App\Services\Media;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class MediaService
{
    protected const THUMBNAIL_WIDTH = 640;

    protected const DISK = 'public';

    /**
     * @param  array<string, mixed>  $attributes
     */
    /**
     * Extensions we are willing to write to disk. Anything else is stored as
     * `.bin`, so a filename can never decide the stored extension on its own.
     *
     * @var list<string>
     */
    private const ALLOWED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'ico',
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'txt', 'csv', 'mp4', 'webm', 'mp3', 'wav',
    ];

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function store(UploadedFile $file, int $userId, array $attributes = []): Media
    {
        $extension = $this->storeExtension($file);
        $filename = Str::uuid()->toString().'.'.$extension;
        $directory = 'media/'.now()->format('Y/m');

        $path = $file->storeAs($directory, $filename, self::DISK);

        [$width, $height] = $this->dimensions($file);

        $media = Media::create([
            'user_id' => $userId,
            'disk' => self::DISK,
            'path' => $path,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType() ?: 'application/octet-stream',
            'extension' => $extension,
            'size' => $file->getSize() ?: 0,
            'width' => $width,
            'height' => $height,
            'title' => $attributes['title'] ?? $this->titleFromFilename($file->getClientOriginalName()),
            'alt_text' => $attributes['alt_text'] ?? null,
            'caption' => $attributes['caption'] ?? null,
        ]);

        $this->generateThumbnail($media);

        return $media;
    }

    /**
     * Prefer the extension the file content actually looks like, and never trust
     * an extension outside the allow-list.
     */
    private function storeExtension(UploadedFile $file): string
    {
        $guessed = strtolower((string) $file->guessExtension());
        $client = strtolower((string) ($file->getClientOriginalExtension() ?: $file->extension()));

        foreach ([$guessed, $client] as $candidate) {
            if ($candidate !== '' && in_array($candidate, self::ALLOWED_EXTENSIONS, true)) {
                return $candidate;
            }
        }

        return 'bin';
    }

    public function delete(Media $media): void
    {
        Storage::disk($media->disk)->delete($media->path);
        Storage::disk($media->disk)->delete($this->thumbnailPath($media));

        $media->delete();
    }

    public function thumbnailUrl(Media $media): ?string
    {
        if (! $media->isImage()) {
            return null;
        }

        $thumbnail = $this->thumbnailPath($media);

        if (! Storage::disk($media->disk)->exists($thumbnail)) {
            return null;
        }

        return Storage::disk($media->disk)->url($thumbnail);
    }

    /**
     * @return array{0: int|null, 1: int|null}
     */
    protected function dimensions(UploadedFile $file): array
    {
        if (! str_starts_with((string) $file->getClientMimeType(), 'image/')) {
            return [null, null];
        }

        $size = @getimagesize($file->getRealPath());

        if ($size === false) {
            return [null, null];
        }

        return [$size[0], $size[1]];
    }

    protected function generateThumbnail(Media $media): void
    {
        if (! $media->isImage() || $media->extension === 'svg' || ! function_exists('imagecreatetruecolor')) {
            return;
        }

        try {
            $sourcePath = Storage::disk($media->disk)->path($media->path);
            $contents = @file_get_contents($sourcePath);

            if ($contents === false) {
                return;
            }

            $source = @imagecreatefromstring($contents);

            if ($source === false) {
                return;
            }

            $width = imagesx($source);
            $height = imagesy($source);
            $targetWidth = min(self::THUMBNAIL_WIDTH, $width);
            $targetHeight = max(1, (int) round($height * ($targetWidth / $width)));

            $thumbnailImage = imagecreatetruecolor($targetWidth, $targetHeight);
            imagealphablending($thumbnailImage, false);
            imagesavealpha($thumbnailImage, true);
            imagecopyresampled($thumbnailImage, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

            $thumbnail = $this->thumbnailPath($media);
            $destination = Storage::disk($media->disk)->path($thumbnail);

            Storage::disk($media->disk)->makeDirectory(dirname($thumbnail));

            imagewebp($thumbnailImage, $destination, 82);

            imagedestroy($source);
            imagedestroy($thumbnailImage);
        } catch (Throwable) {
            // Thumbnail generation is best-effort; the original file stays usable.
        }
    }

    protected function thumbnailPath(Media $media): string
    {
        $directory = dirname($media->path);
        $basename = pathinfo($media->path, PATHINFO_FILENAME);

        return "{$directory}/thumbs/{$basename}.webp";
    }

    protected function titleFromFilename(string $name): string
    {
        return Str::of($name)
            ->beforeLast('.')
            ->replace(['-', '_'], ' ')
            ->title()
            ->limit(120)
            ->value();
    }
}
