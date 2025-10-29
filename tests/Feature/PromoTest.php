<?php

namespace Tests\Feature;

use App\Models\Film;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoTest extends TestCase
{
    use RefreshDatabase;

    public function testShowPromo(): void
    {
        $promoFilm = Film::factory()->create(['promo' => true]);

        $response = $this->getJson(route('promo.show'));

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $promoFilm->id,
                    'promo' => true
                ]
            ]);
    }

    public function testShowPromoNotFound(): void
    {
        // Не создаем промо-фильм
        Film::factory()->create(['promo' => false]);

        $response = $this->getJson(route('promo.show'));

        $response->assertNotFound()
            ->assertJson([
                'message' => 'Promo film not found'
            ]);
    }

    public function testCreatePromo(): void
    {
        $moderator = User::factory()->create([
            'role' => User::ROLE_MODERATOR,
        ]);
        $film = Film::factory()->create(['promo' => false]);

        $response = $this->actingAs($moderator)
            ->postJson(route('promo.create', $film->id));

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $film->id,
                    'promo' => true
                ]
            ]);

        $this->assertDatabaseHas('films', [
            'id' => $film->id,
            'promo' => true
        ]);
    }

    public function testCreatePromoResetsOtherPromos(): void
    {
        $moderator = User::factory()->create([
            'role' => User::ROLE_MODERATOR,
        ]);

        // Создаем существующий промо-фильм
        $oldPromo = Film::factory()->create(['promo' => true]);
        $newPromo = Film::factory()->create(['promo' => false]);

        $response = $this->actingAs($moderator)
            ->postJson(route('promo.create', $newPromo->id));

        $response->assertOk();

        // Проверяем, что новый фильм стал промо
        $this->assertDatabaseHas('films', [
            'id' => $newPromo->id,
            'promo' => true
        ]);

        // Проверяем, что старый промо-фильм больше не промо
        $this->assertDatabaseHas('films', [
            'id' => $oldPromo->id,
            'promo' => false
        ]);
    }

    public function testCreatePromoAsUser(): void
    {
        $user = User::factory()->create();
        $film = Film::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('promo.create', $film->id));

        $response->assertForbidden();
    }

    public function testCreatePromoUnauthenticated(): void
    {
        $film = Film::factory()->create();

        $response = $this->postJson(route('promo.create', $film->id));

        $response->assertUnauthorized();
    }

    public function testCreatePromoFilmNotFound(): void
    {
        $moderator = User::factory()->create([
            'role' => User::ROLE_MODERATOR,
        ]);

        $response = $this->actingAs($moderator)
            ->postJson(route('promo.create', 999));

        $response->assertNotFound()
            ->assertJson([
                'message' => 'Film not found'
            ]);
    }
}
