<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;
use Spatie\QueryBuilder\QueryBuilder;

final class MediaController extends Controller
{
    public function __invoke()
    {
        $query = QueryBuilder::for(app(request()->query('model')));

        $perPage = request()->query('per_page', 10);
        $paginatedMedia = $query->paginate($perPage)->appends(request()->except('page'));

        return MediaResource::collection($paginatedMedia);
    }
}
