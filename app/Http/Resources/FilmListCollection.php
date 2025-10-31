<?php

namespace App\Http\Resources;

use App\Models\Film;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
class FilmListCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => FilmListResource::collection($this->collection),
            'current_page' => $this->currentPage(),
            'first_page_url' => $this->url(1),
            'next_page_url' => $this->nextPageUrl(),
            'prev_page_url' => $this->previousPageUrl(),
            'per_page' => $this->perPage(),
            'total' => $this->total(),
        ];
    }
}
