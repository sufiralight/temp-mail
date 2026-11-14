<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class UiLayoutsTest extends TestCase
{
    public function test_layout_variants_merge_attributes_and_use_responsive_classes(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.container size="sm" class="custom" data-test="container">
                <x-ui.stack direction="responsive" gap="sm" wrap>
                    <x-ui.grid columns="4" gap="lg">Content</x-ui.grid>
                </x-ui.stack>
            </x-ui.container>
            BLADE);

        foreach (['max-w-md', 'custom', 'data-test="container"', 'sm:flex-row', 'sm:flex-wrap', 'gap-2', 'grid-cols-1', 'md:grid-cols-2', 'lg:grid-cols-4', 'gap-6'] as $expected) {
            $this->assertStringContainsString($expected, $html);
        }

        $html = Blade::render('<x-ui.container size="unsupported"><x-ui.grid columns="unsupported" /></x-ui.container>');
        $this->assertStringContainsString('max-w-5xl', $html);
        $this->assertStringContainsString('md:grid-cols-2', $html);
        $this->assertStringNotContainsString('unsupported', $html);
    }

    public function test_page_and_state_components_preserve_semantics(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.page-header title="Search" description="Find records." />
            <x-ui.section aria-labelledby="results">
                <x-slot:heading><h2 id="results">Results</h2></x-slot:heading>
                <x-ui.empty-state title="No results" heading-level="3">
                    <x-slot:primary><x-ui.button>Try again</x-ui.button></x-slot:primary>
                </x-ui.empty-state>
                <x-ui.divider orientation="vertical" />
                <x-ui.divider decorative />
                <x-ui.skeleton shape="circle" size="sm" />
                <x-ui.sr-only>Loading results</x-ui.sr-only>
            </x-ui.section>
            BLADE);

        foreach (['<h1 class="text-heading">Search</h1>', '<h2 id="results">Results</h2>', '<h3 class="text-lg font-semibold">No results</h3>', 'Try again', 'aria-orientation="vertical"', 'aria-hidden="true"', 'size-8', 'class="sr-only"'] as $expected) {
            $this->assertStringContainsString($expected, $html);
        }
    }
}
