<?php

use CookAppWebImporter\HtmlRecipeParser;
use CookApp\ImportService;
use PHPUnit\Framework\TestCase;

class HtmlRecipeParserTest extends TestCase {
    private function fixture( string $name ): string {
        return file_get_contents( __DIR__ . '/fixtures/' . $name );
    }

    public function test_registers_with_cook_app_and_handles_document_dispatch(): void {
        $imports = ( new ReflectionClass( ImportService::class ) )->newInstanceWithoutConstructor();
        $html = $this->fixture( 'simplehomeedit-dijon-salmon-sections.html' );

        $this->assertArrayHasKey( HtmlRecipeParser::SLUG, $imports->get_registered_parsers() );
        $recipe = $imports->parse_document( 'https://simplehomeedit.com/recipe', 'text/html', $html );
        $this->assertSame( 'Dijon Salmon and Crispy Potatoes', $recipe['title'] );
        $this->assertSame( 'POTATOES', $recipe['parts'][0]['title'] );
        $this->assertSame( 'SALMON', $recipe['parts'][1]['title'] );
        $this->assertSame( 'CREAMY LEMON DILL SAUCE', $recipe['parts'][2]['title'] );
        $this->assertSame( 'TO SERVE', $recipe['parts'][3]['title'] );
    }

    public function test_parses_wprm_ingredient_groups(): void {
        $html = $this->fixture( 'wprm-grouped-recipe.html' );
        $parser = new HtmlRecipeParser();
        $recipe = $parser->parse( 'https://example.com', 'text/html', $html );

        $this->assertSame( 'Base', $recipe['parts'][0]['title'] );
        $this->assertSame( 'red onion', $recipe['parts'][0]['ingredients'][0]['name'] );
        $this->assertSame( 'diced', $recipe['parts'][0]['ingredients'][0]['notes'] );
    }

    public function test_parses_explicit_recipe_sections_from_html_text(): void {
        $html = $this->fixture( 'heading-recipe.html' );
        $parser = new HtmlRecipeParser();

        $this->assertSame( 5, $parser->support_confidence( 'https://example.com', 'text/html', $html ) );
        $recipe = $parser->parse( 'https://example.com', 'text/html', $html );
        $this->assertSame( 'Pancakes', $recipe['title'] );
        $this->assertSame( 'flour', $recipe['ingredients'][0]['name'] );
        $this->assertCount( 2, $recipe['instructions'] );
    }
}
