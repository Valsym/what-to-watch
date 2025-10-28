<?php

namespace App\Http\Resources;

use App\Models\Film;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
//use Override;

/**
 * Ресурс для краткого представления фильма в списке.
 *
 * @property int                             $id
 * @property string                          $name
 * @property string|null                     $poster_image
 * @property string|null                     $preview_image
 * @property string|null                     $preview_video_link
 * @property Collection  $genres
 * @property int|null                        $released
 *
 * @mixin Film
 */
final class FilmListResource extends JsonResource
{
    /**
     * Преобразует ресурс в массив.
     *
     * @param Request $request
     *
     * @return (int|mixed|null|string)[]
     *
     * @psalm-return array{id: int, name: string, poster_image: null|string, preview_image: null|string, preview_video_link: null|string, genre: mixed|null, released: int}
     */
//    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'poster_image' => $this->poster_image,
            'preview_image' => $this->preview_image,
            'preview_video_link' => $this->preview_video_link,
//            'genre' => $this->genres !== null ? $this->genres->pluck('name')->first() : null,
            'released' => (int)$this->released,
//            'genre' => $this->genres->pluck('name')->first(),
            'genre' => $this->genres->isNotEmpty() ? $this->genres->first()->name : null,
            // Добавляем rating для главной страницы если нужно
//            'rating' => $this->when($request->has('order_by') && $request->get('order_by') === 'rating',
//                function () {
//                    return $this->rating ?? 0;
//                }
//            ),
        ];
    }
}
