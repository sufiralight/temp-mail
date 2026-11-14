<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_welcome_page_includes_the_appearance_engine_and_preview(): void
    {
        $this->withoutVite();

        $this->get('/')
            ->assertOk()
            ->assertSee('Temp Mail SaaS')
            ->assertSee('temp-mail.appearance')
            ->assertSee('id="appearance-preview"', false);
    }
}
