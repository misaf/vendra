<?php

declare(strict_types=1);

namespace Misaf\VendraMultimedia\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Misaf\VendraMultimedia\Models\Multimedia;
use Misaf\VendraMultimedia\Support\DefaultPathGenerator;

#[Signature('vendra-multimedia:relocate {--dry-run : Report the media to move without moving files}')]
#[Description('Move media stored before the tenant path prefix into its tenant directory')]
final class RelocateMediaCommand extends Command
{
    public function handle(DefaultPathGenerator $pathGenerator): int
    {
        $relocatedMedia = 0;
        $relocatedFiles = 0;

        Multimedia::query()
            ->withoutGlobalScopes()
            ->chunkById(100, function (Collection $media) use ($pathGenerator, &$relocatedMedia, &$relocatedFiles): void {
                foreach ($media as $medium) {
                    $movedFiles = $this->relocate($medium, $pathGenerator);

                    if ($movedFiles > 0) {
                        $relocatedMedia++;
                        $relocatedFiles += $movedFiles;
                    }
                }
            });

        $verb = $this->option('dry-run') ? 'Would move' : 'Moved';

        $this->components->info("{$verb} {$relocatedFiles} files across {$relocatedMedia} media.");

        return self::SUCCESS;
    }

    private function relocate(Multimedia $medium, DefaultPathGenerator $pathGenerator): int
    {
        $legacyBasePath = $pathGenerator->getLegacyBasePath($medium);
        $basePath = $pathGenerator->getBasePath($medium);

        if ($legacyBasePath === $basePath) {
            return 0;
        }

        $movedFiles = 0;

        foreach (array_unique(array_filter([$medium->disk, $medium->conversions_disk])) as $diskName) {
            $disk = Storage::disk($diskName);

            foreach ($disk->allFiles($legacyBasePath) as $legacyFile) {
                $movedFiles++;

                if (! $this->option('dry-run')) {
                    $disk->move($legacyFile, $basePath.mb_substr($legacyFile, mb_strlen($legacyBasePath)));
                }
            }

            if (! $this->option('dry-run')) {
                $disk->deleteDirectory($legacyBasePath);
            }
        }

        return $movedFiles;
    }
}
