<?php
/**
 * Remove google maps from Booknetic - ONLY page=booknetic
 * @author DanSP ⚡
 */

if (!defined('ABSPATH')) exit;

add_action('admin_init', function() {
    // Check if it's the Booknetic page
    if (!isset($_GET['page']) || $_GET['page'] !== 'booknetic') {
        return;
    }
    
    // Start buffer to remove Google Maps
    ob_start(function($buffer) {
        return preg_replace('#<script[^>]*maps\.googleapis\.com/maps/api/js[^<]*</script>#is', '', $buffer);
    });
}, 1);

add_action('shutdown', function() {
    // Only finalize if it's the Booknetic page and there is a buffer
    if (isset($_GET['page']) && $_GET['page'] === 'booknetic' && ob_get_level() > 0) {
        @ob_end_flush();
    }
}, 9999);