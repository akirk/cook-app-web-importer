<?php
/**
 * Plugin Name: Cook App Web Importer
 * Description: Adds HTML, microdata, and heuristic webpage parsing to Cook App's recipe importer.
 * Version: 0.1.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: cook-app
 * Author: Alex Kirk
 * Author URI: https://alex.kirk.at/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cook-app-web-importer
 */

namespace CookAppWebImporter;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action(
    'cook_app_load_recipe_parsers',
    function ( \CookApp\ImportService $imports ): void {
        $imports->register_parser( HtmlRecipeParser::SLUG, new HtmlRecipeParser() );
    }
);

add_action(
    'plugins_loaded',
    function (): void {
        if ( class_exists( '\CookApp\RecipeParser' ) ) {
            require_once __DIR__ . '/src/HtmlRecipeParser.php';
        }
    },
    5
);
