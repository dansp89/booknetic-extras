<?php
/*
Plugin Name: Booknetic AJAX Handler
Plugin URI: https://github.com/DanSP/booknetic-extras
Description: Intercepts Booknetic AJAX responses to modify the HTML content.
Version: 1.0.0
Author: DanSP
Author URI: https://github.com/DanSP89
License: MIT
*/

add_action('init', 'booknetic_ajax_init', 1);

function booknetic_ajax_init() {
    if (!isset($_GET['page']) || $_GET['page'] !== 'booknetic' || !isset($_GET['ajax']) || $_GET['ajax'] != '1') {
        return;
    }
    
    // Use output buffering to capture and modify the response
    ob_start('modify_booknetic_output');
}

function modify_booknetic_output($output) {
    if (!current_user_can('manage_options')) {
        return $output;
    }
    // Verify if it is a Booknetic request
    if (!isset($_GET['page']) || $_GET['page'] !== 'booknetic' || !isset($_GET['ajax']) || $_GET['ajax'] != '1') {
        return $output;
    }
    
    // Try to decode as JSON
    $data = json_decode($output, true);
    
    if (json_last_error() === JSON_ERROR_NONE && isset($data['html'])) {
        // Patterns to find the Booknetic script
        $patterns = [
            '/\/wp-content\/plugins\/booknetic\/app\/Backend\/Locations\/assets\/js\/add_new\.js\?v=[^"&]*/',
            '/\/wp-content\/plugins\/booknetic\/app\/Backend\/Locations\/assets\/js\/add_new\.js/'
        ];
        
        $replacement = '/wp-content/plugins/booknetic-extras/app/Backend/Locations/assets/js/add_new.min.js';
        
        // Apply the replacements
        foreach ($patterns as $pattern) {
            $data['html'] = preg_replace($pattern, $replacement, $data['html']);
        }
        
        // Return the modified JSON
        return json_encode($data);
    }
    
    return $output;
}