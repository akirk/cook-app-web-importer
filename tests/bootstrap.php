<?php

$GLOBALS['cook_app_web_importer_test_actions'] = [];

if ( ! function_exists( 'add_action' ) ) {
    function add_action( $hook_name, $callback, $priority = 10, $accepted_args = 1 ) {
        $GLOBALS['cook_app_web_importer_test_actions'][ $hook_name ][ $priority ][] = [
            'callback'      => $callback,
            'accepted_args' => $accepted_args,
        ];
        return true;
    }
}

if ( ! function_exists( 'do_action' ) ) {
    function do_action( $hook_name, ...$args ) {
        $callbacks = $GLOBALS['cook_app_web_importer_test_actions'][ $hook_name ] ?? [];
        ksort( $callbacks );
        foreach ( $callbacks as $priority_callbacks ) {
            foreach ( $priority_callbacks as $registered ) {
                call_user_func_array(
                    $registered['callback'],
                    array_slice( $args, 0, $registered['accepted_args'] )
                );
            }
        }
    }
}

$cook_app_dir = getenv( 'COOK_APP_TEST_PLUGIN_DIR' );
if ( ! $cook_app_dir ) {
    $cook_app_dir = dirname( __DIR__, 2 ) . '/cook-app';
}

require_once $cook_app_dir . '/tests/bootstrap.php';
require_once dirname( __DIR__ ) . '/cook-app-web-importer.php';

do_action( 'plugins_loaded' );
