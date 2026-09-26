# Cook App Web Importer

Adds webpage-oriented recipe parsers to [Cook App](https://github.com/akirk/cook-app).

Cook App itself registers a minimal schema.org JSON-LD parser. This add-on registers the broader HTML parser, restoring support for schema.org microdata, WP Recipe Maker markup, heading-based ingredient groups, and explicit ingredient/instruction sections in webpage text.

## Requirements

- WordPress 6.0 or later
- PHP 7.4 or later
- Cook App

## Development

The parser is registered on `cook_app_load_recipe_parsers`, using the same parser registry used by Cook App's bundled parser.

Run the add-on's non-JSON-LD parser tests with:

```sh
../cook-app/vendor/bin/phpunit
```

The test suite includes the parser behavior itself and the integration point that registers it with Cook App's `ImportService`.
