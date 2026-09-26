<?php

use CookAppWebImporter\HtmlRecipeParser;
use CookApp\ImportService;
use PHPUnit\Framework\TestCase;

class HtmlRecipeParserTest extends TestCase {
    public function test_registers_with_cook_app_and_handles_document_dispatch(): void {
        $imports = ( new ReflectionClass( ImportService::class ) )->newInstanceWithoutConstructor();
        $html = '<article itemscope itemtype="https://schema.org/Recipe">'
            . '<h1 itemprop="name">Registered Microdata Recipe</h1>'
            . '<meta itemprop="recipeIngredient" content="1 cup flour">'
            . '<p itemprop="recipeInstructions">Mix well.</p>'
            . '</article>';

        $this->assertArrayHasKey( HtmlRecipeParser::SLUG, $imports->get_registered_parsers() );
        $recipe = $imports->parse_document( 'https://example.com/recipe', 'text/html', $html );
        $this->assertSame( 'Registered Microdata Recipe', $recipe['title'] );
        $this->assertSame( 'flour', $recipe['ingredients'][0]['name'] );
    }

    public function test_parses_schema_org_microdata(): void {
        $html = '<article itemscope itemtype="https://schema.org/Recipe">'
            . '<h1 itemprop="name">Microdata Soup</h1>'
            . '<meta itemprop="recipeYield" content="2">'
            . '<meta itemprop="prepTime" content="PT5M">'
            . '<meta itemprop="cookTime" content="PT20M">'
            . '<meta itemprop="recipeIngredient" content="2 cups stock">'
            . '<meta itemprop="recipeIngredient" content="1 carrot">'
            . '<p itemprop="recipeInstructions">Simmer until tender.</p>'
            . '</article>';
        $parser = new HtmlRecipeParser();

        $this->assertSame( 9, $parser->support_confidence( 'https://example.com/soup', 'text/html', $html ) );
        $recipe = $parser->parse( 'https://example.com/soup', 'text/html', $html );
        $this->assertSame( 'Microdata Soup', $recipe['title'] );
        $this->assertSame( 2, $recipe['servings'] );
        $this->assertCount( 2, $recipe['ingredients'] );
        $this->assertSame( 'Simmer until tender.', $recipe['instructions'][0] );
    }

    public function test_parses_wprm_ingredient_groups(): void {
        $html = '<script type="application/ld+json">' . json_encode( [
            '@context' => 'https://schema.org',
            '@type' => 'Recipe',
            'name' => 'Grouped Recipe',
            'recipeIngredient' => [ '1 red onion' ],
            'recipeInstructions' => [ 'Fry it.' ],
        ] ) . '</script>'
            . '<div class="wprm-recipe-ingredient-group">'
            . '<h4 class="wprm-recipe-group-name">Base</h4>'
            . '<ul><li class="wprm-recipe-ingredient">'
            . '<span class="wprm-recipe-ingredient-amount">1</span>'
            . '<span class="wprm-recipe-ingredient-name">red onion</span>'
            . '<span class="wprm-recipe-ingredient-notes">, diced</span>'
            . '</li></ul></div>';
        $recipe = ( new HtmlRecipeParser() )->parse( 'https://example.com', 'text/html', $html );

        $this->assertSame( 'Base', $recipe['parts'][0]['title'] );
        $this->assertSame( 'red onion', $recipe['parts'][0]['ingredients'][0]['name'] );
        $this->assertSame( 'diced', $recipe['parts'][0]['ingredients'][0]['notes'] );
    }

    public function test_parses_explicit_recipe_sections_from_html_text(): void {
        $html = "<h1>Pancakes</h1>\n<h2>Ingredients</h2>\n<p>200 g flour</p>\n"
            . "<h2>Method</h2>\n<p>Mix everything</p>\n<p>Fry in a pan</p>";
        $parser = new HtmlRecipeParser();

        $this->assertSame( 5, $parser->support_confidence( 'https://example.com', 'text/html', $html ) );
        $recipe = $parser->parse( 'https://example.com', 'text/html', $html );
        $this->assertSame( 'Pancakes', $recipe['title'] );
        $this->assertSame( 'flour', $recipe['ingredients'][0]['name'] );
        $this->assertCount( 2, $recipe['instructions'] );
    }
}
