<?php

/**
 * Mu-plugins management functions
 */

if (!defined('ABSPATH')) exit;

/**
 * Activate the mu-plugin
 */
function booknetic_extras_activate()
{
    if (!defined('WPMU_PLUGIN_DIR')) {
        define('WPMU_PLUGIN_DIR', WP_CONTENT_DIR . '/mu-plugins');
    }

    $mu_plugins_dir = WPMU_PLUGIN_DIR;
    $source_file = plugin_dir_path(dirname(dirname(dirname(__DIR__)))) . 'mu-plugins/booknetic-extras-intercept.php';
    $target_file = $mu_plugins_dir . '/booknetic-extras-intercept.php';

    // Create mu-plugins directory if it doesn't exist
    if (!file_exists($mu_plugins_dir)) {
        wp_mkdir_p($mu_plugins_dir);
    }

    // Copy the file to mu-plugins
    if (file_exists($source_file)) {
        if (!copy($source_file, $target_file)) {
            if (defined('WP_DEBUG') && WP_DEBUG === true) {
                error_log('Failed to copy mu-plugin file');
            }
        }
    } else {
        if (defined('WP_DEBUG') && WP_DEBUG === true) {
            error_log('Source mu-plugin file not found: ' . $source_file);
        }
    }
}

/**
 * Deactivate the mu-plugin
 */
function booknetic_extras_deactivate()
{
    if (!defined('WPMU_PLUGIN_DIR')) {
        define('WPMU_PLUGIN_DIR', WP_CONTENT_DIR . '/mu-plugins');
    }

    $target_file = WPMU_PLUGIN_DIR . '/booknetic-extras-intercept.php';

    // Remove the mu-plugin file if it exists
    if (file_exists($target_file)) {
        if (!unlink($target_file)) {
            if (defined('WP_DEBUG') && WP_DEBUG === true) {
                error_log('Failed to remove mu-plugin file: ' . $target_file);
            }
        }
    }
}
