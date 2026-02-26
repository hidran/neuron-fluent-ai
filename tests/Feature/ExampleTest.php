<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_welcome_page_renders_with_the_configured_app_name(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee(config('app.name'));
    }
}
