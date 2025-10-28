<?php

namespace App\DTOs;

use Illuminate\Foundation\Http\FormRequest;

class FilmListQueryParams
{
    public function __construct(
        public ?int    $page = 1,
        public ?int    $perPage = 8,
        public ?string $genre = null,
        public ?string $status = null,
        public ?string $orderBy = 'released',
        public ?string $orderTo = 'desc',
        public ?string $search = null,
        public ?bool   $isModerator = false
    )
    {
    }

    public static function fromRequest(FormRequest $request): self
    {
        return new self(
            page: $request->get('page', 1),
            perPage: $request->get('per_page', 8),
            genre: $request->get('genre'),
            status: $request->get('status'),
            orderBy: $request->get('order_by', 'released'),
            orderTo: $request->get('order_to', 'desc'),
            search: $request->get('search'),
            isModerator: $request->user()?->isModerator() ?? false
        );
    }
}
