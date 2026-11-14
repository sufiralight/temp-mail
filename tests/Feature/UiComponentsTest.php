<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\ViewException;
use Tests\TestCase;

class UiComponentsTest extends TestCase
{
    public function test_button_variants_render_native_types_and_loading_is_disabled(): void
    {
        foreach (['primary', 'secondary', 'ghost', 'danger'] as $variant) {
            $html = Blade::render('<x-ui.button :variant="$variant" class="custom">Save</x-ui.button>', compact('variant'));
            $this->assertStringContainsString('type="button"', $html);
            $this->assertStringContainsString('data-variant="'.$variant.'"', $html);
            $this->assertStringContainsString('custom', $html);
        }

        $html = Blade::render('<x-ui.button type="submit" loading>Save</x-ui.button>');
        $this->assertStringContainsString('type="submit"', $html);
        $this->assertStringContainsString('disabled', $html);
        $this->assertStringContainsString('aria-busy="true"', $html);
        $this->assertStringContainsString('Save', $html);
    }

    public function test_icon_button_forwards_native_and_variant_attributes(): void
    {
        $html = Blade::render('<x-ui.icon-button icon="eye" label="Show password" variant="ghost" size="sm" disabled />');
        $this->assertStringContainsString('data-variant="ghost"', $html);
        $this->assertStringContainsString('data-size="sm"', $html);
        $this->assertStringContainsString('aria-label="Show password"', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);
        $this->assertStringContainsString('disabled', $html);
    }

    public function test_icons_reject_names_outside_the_allowlist(): void
    {
        $this->expectException(ViewException::class);
        $this->expectExceptionMessage('Unknown UI icon');
        Blade::render('<x-ui.icon name="../../unknown" />');
    }

    public function test_icon_buttons_require_an_accessible_name(): void
    {
        $this->expectException(ViewException::class);
        $this->expectExceptionMessage('requires an accessible label');
        Blade::render('<x-ui.icon-button icon="eye" label="" />');
    }

    public function test_form_fields_connect_labels_descriptions_and_validation_errors(): void
    {
        $errors = new ViewErrorBag;
        $errors->put('default', new MessageBag(['email' => ['Enter a valid address.']]));
        view()->share('errors', $errors);

        $html = Blade::render(
            '<x-ui.input name="email" id="email" label="Email" description="Public address" aria-describedby="intro" required />',
        );

        foreach (['for="email"', 'aria-describedby="intro email-description email-error"', 'id="email-description"',
            'id="email-error"', 'aria-invalid="true"', 'Enter a valid address.'] as $expected) {
            $this->assertStringContainsString($expected, $html);
        }
    }

    public function test_password_field_preserves_autofill_attributes_and_never_renders_a_value(): void
    {
        $html = Blade::render('<x-ui.password-field name="password" id="secret" label="Password" autocomplete="new-password" value="hidden-value" readonly />');

        foreach (['type="password"', 'autocomplete="new-password"', 'aria-controls="secret"', 'aria-pressed="false"', 'readonly', 'disabled'] as $expected) {
            $this->assertStringContainsString($expected, $html);
        }

        $this->assertStringNotContainsString('hidden-value', $html);
    }

    public function test_preview_renders_without_duplicate_ids_and_is_local_loopback_only(): void
    {
        $this->withoutVite();
        $this->app->detectEnvironment(fn () => 'local');

        $response = $this->get('/_dev/components')->assertOk()->assertSee('Component preview');
        preg_match_all('/\sid="([^"]+)"/', $response->getContent(), $ids);
        $this->assertSame($ids[1], array_values(array_unique($ids[1])));

        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.10'])
            ->withHeaders(['Host' => 'localhost', 'X-Forwarded-For' => '127.0.0.1'])
            ->get('/_dev/components')->assertNotFound();

        $this->app->detectEnvironment(fn () => 'production');
        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->get('/_dev/components')->assertNotFound();
    }
}
