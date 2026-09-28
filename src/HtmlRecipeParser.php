<?php

namespace CookAppWebImporter;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Adds recipe-card HTML and visible-text heuristics to Cook App.
 */
class HtmlRecipeParser extends \CookApp\RecipeParser {
    public const SLUG = 'html-recipe';
    public const NAME = 'Recipe webpage HTML';

    public function support_confidence( string $url, string $content_type, string $content ): int {
        if ( stripos( $content, 'wprm-recipe-' ) !== false ) {
            return 20;
        }
        if ( preg_match( '/^\s*(ingredients?|zutaten)\s*:?\s*$/im', wp_strip_all_tags( $content ) ) ) {
            return 5;
        }
        return 0;
    }

    public function parse( string $url, string $content_type, string $content ): ?array {
        return \CookApp\Importer::from_html( $content );
    }
}
