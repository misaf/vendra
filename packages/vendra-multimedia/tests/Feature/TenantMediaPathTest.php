<?php

declare(strict_types=1);

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Misaf\VendraMultimedia\Models\Multimedia;
use Misaf\VendraMultimedia\Support\DefaultPathGenerator;
use Misaf\VendraMultimedia\Tests\Fixtures\Gallery;
use Misaf\VendraSupport\Tenancy\TenantSchema;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

beforeEach(function (): void {
    Storage::fake('public');
    Gallery::createTable();
});

function addGalleryMedia(): Multimedia
{
    return Gallery::query()->create()
        ->addMediaFromString('image bytes')
        ->usingFileName('photo.jpg')
        ->toMediaCollection(diskName: 'public');
}

it('stores media under the tenant of the media row', function (): void {
    $tenant = makeCurrentTestTenant();
    $media = addGalleryMedia();

    expect($media->getPathRelativeToRoot())->toBe("{$tenant->getKey()}/{$media->uuid}/photo.jpg");

    Storage::disk('public')->assertExists($media->getPathRelativeToRoot());
});

it('keeps media of two tenants in separate directories', function (): void {
    makeCurrentTestTenant();
    $first = addGalleryMedia();

    makeCurrentTestTenant();
    $second = addGalleryMedia();

    expect(dirname($first->getPathRelativeToRoot(), 2))->not->toBe(dirname($second->getPathRelativeToRoot(), 2));

    Storage::disk('public')->assertExists([$first->getPathRelativeToRoot(), $second->getPathRelativeToRoot()]);
});

it('resolves the same path and url after the tenant is forgotten', function (): void {
    makeCurrentTestTenant();
    $media = addGalleryMedia();
    $path = $media->getPathRelativeToRoot();
    $url = $media->getUrl();

    forgetCurrentTestTenant();
    $reloaded = Multimedia::query()->withoutGlobalScopes()->findOrFail($media->getKey());

    expect($reloaded->getPathRelativeToRoot())->toBe($path)
        ->and($reloaded->getUrl())->toBe($url);
});

it('removes the tenant directory when the media is deleted outside tenant context', function (): void {
    $tenant = makeCurrentTestTenant();
    $media = addGalleryMedia();

    forgetCurrentTestTenant();
    Multimedia::query()->withoutGlobalScopes()->findOrFail($media->getKey())->delete();

    Storage::disk('public')->assertMissing("{$tenant->getKey()}/{$media->uuid}");
});

it('keeps the uuid layout for media without a tenant', function (): void {
    $media = new Multimedia(['uuid' => 'legacy-uuid']);

    expect(resolve(DefaultPathGenerator::class)->getPath($media))->toBe('legacy-uuid/');
});

it('preserves string tenant keys in media paths', function (string $prefix, string $expected): void {
    Config::set('media-library.prefix', $prefix);
    $media = new Media;
    $media->setAttribute('uuid', 'photo-uuid');
    $media->setAttribute(TenantSchema::column(), 'workspace-42');

    expect(resolve(DefaultPathGenerator::class)->getPath($media))->toBe($expected);
})->with([
    ['', 'workspace-42/photo-uuid/'],
    ['assets', 'assets/workspace-42/photo-uuid/'],
]);

it('rejects a media tenant key that cannot identify a tenant', function (): void {
    $media = new Media;
    $media->setAttribute(TenantSchema::column(), ['invalid']);

    expect(fn (): string => resolve(DefaultPathGenerator::class)->getPath($media))
        ->toThrow(UnexpectedValueException::class, 'The media tenant key must be an integer or string.');
});

describe('relocate command', function (): void {
    it('moves media stored before the tenant prefix into its tenant directory', function (): void {
        $tenant = makeCurrentTestTenant();
        $media = addGalleryMedia();
        Storage::disk('public')->move($media->getPathRelativeToRoot(), "{$media->uuid}/photo.jpg");
        Storage::disk('public')->put("{$media->uuid}/conversions/photo-thumb.webp", 'thumb');

        $this->artisan('vendra-multimedia:relocate')->assertSuccessful();

        Storage::disk('public')
            ->assertExists("{$tenant->getKey()}/{$media->uuid}/photo.jpg")
            ->assertExists("{$tenant->getKey()}/{$media->uuid}/conversions/photo-thumb.webp")
            ->assertMissing($media->uuid);

        $this->artisan('vendra-multimedia:relocate')
            ->expectsOutputToContain('Moved 0 files across 0 media.')
            ->assertSuccessful();
    });

    it('moves nothing on a dry run', function (): void {
        makeCurrentTestTenant();
        $media = addGalleryMedia();
        Storage::disk('public')->move($media->getPathRelativeToRoot(), "{$media->uuid}/photo.jpg");

        $this->artisan('vendra-multimedia:relocate', ['--dry-run' => true])
            ->expectsOutputToContain('Would move 1 files across 1 media.')
            ->assertSuccessful();

        Storage::disk('public')->assertExists("{$media->uuid}/photo.jpg");
    });

    it('keeps the legacy directory when moving a file fails', function (): void {
        makeCurrentTestTenant();
        $media = addGalleryMedia();
        $legacyFile = "{$media->uuid}/photo.jpg";
        $originalDisk = Storage::disk('public');
        $originalDisk->move($media->getPathRelativeToRoot(), $legacyFile);

        $disk = Mockery::mock(FilesystemAdapter::class);
        $disk->shouldReceive('allFiles')->andReturn([$legacyFile]);
        $disk->shouldReceive('exists')->andReturnFalse();
        $disk->shouldReceive('move')->andReturnFalse();
        $disk->shouldNotReceive('deleteDirectory');
        Storage::set('public', $disk);

        expect(fn () => $this->artisan('vendra-multimedia:relocate')->run())
            ->toThrow(RuntimeException::class);

        $originalDisk->assertExists($legacyFile);
    });
});
