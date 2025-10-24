<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin User
 *
 * Ресурс пользователя для API-ответов.
 */
final class UserResource extends JsonResource
{
    /**
     * Преобразует ресурс в массив для JSON-ответа.
     *
     * @param  Request $request
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'role' => $this->role,
        ];
    }
}
