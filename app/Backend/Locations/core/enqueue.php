<?php

/**
 * Enqueue scripts and styles for Booknetic Locations - BUFFER VERSION
 */

// Start buffer at the beginning of everything
add_action('admin_init', function () {
    ob_start('booknetic_osm_buffer_callback');
}, 1);

function booknetic_osm_buffer_callback($buffer)
{
    // Only process if it's admin
    if (!is_admin()) return $buffer;

    // Inject CSS in the head
    $css_injection = '
    <!-- Booknetic OSM Assets -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <link rel="stylesheet" href="' . esc_url(BOOKNETIC_EXTRAS_URL . 'app/Backend/Locations/assets/css/add_new.css') . '" />
    ';

    // Inject JS before </body>
    $js_injection = '
    <!-- Booknetic OSM Scripts -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    ';

    // Inject CSS
    if (strpos($buffer, '</head>') !== false) {
        $buffer = str_replace('</head>', $css_injection . '</head>', $buffer);
    }

    // Inject JS
    if (strpos($buffer, '</body>') !== false) {
        $buffer = str_replace('</body>', $js_injection . '</body>', $buffer);
    }

    return $buffer;
}

// Ensure the buffer is processed at the end
add_action('shutdown', function () {
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
}, 99999);
