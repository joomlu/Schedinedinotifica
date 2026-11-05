<?php

namespace Tests\Feature;

use Tests\TestCase;

class TopbarRenderingTest extends TestCase
{
    public function test_topbar_renders_without_authenticated_user(): void
    {
        $this->assertNull(auth()->user());

        $html = view('layouts.topbar', [
            'topbarStrutturaState' => 'none',
            'topbarStrutturaCanSwitch' => false,
            'topbarStrutturaCurrentName' => null,
            'topbarStrutturaAllowed' => collect(),
            'topbarStrutturaCurrentId' => null,
            'topbarStrutturaLegacyOutsideAllowed' => false,
        ])->render();

        $this->assertStringContainsString('Accedi', $html);
        $this->assertStringNotContainsString('Super Admin', $html);
    }
}
