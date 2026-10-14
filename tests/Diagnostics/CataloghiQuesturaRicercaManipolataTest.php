<?php

namespace Tests\Diagnostics;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CataloghiQuesturaRicercaManipolataTest extends TestCase
{
    use RefreshDatabase, \Tests\Support\StrutturaFixtures;

    public static function cataloghi(): array
    {
        return [['tipo_alloggiato'], ['tipo_documento']];
    }

    #[DataProvider('cataloghi')]
    public function test_ricerca_non_scalare_non_genera_errore_server(string $catalogo): void
    {
        $this->actingAs($this->actor('super_admin'));
        $this->getJson('/'.$catalogo.'?q[]=forgiato')->assertStatus(400);
    }
}
