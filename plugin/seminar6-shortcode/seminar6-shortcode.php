<?php
/*
Plugin Name: Seminar Shortcode 6
Description: Terminliste im neuen OKR-Seminar-Design (lokale Templates, schema.org JSON-LD) plus Kennzahlen-Shortcodes. Läuft parallel zum alten Plugin "Seminar Shortcode 5", das für die übrigen Seminarseiten unverändert bleibt. Shortcodes: [seminar6 type="3-D-OKR"], [seminar6_info type="3-D-OKR" info="min-price-praesenz"], [seminar6_data type="3-D-OKR"]. Datenquelle: private_html/seminarinfos3.ini (wie v5).
Version: 1.0
*/

if (!defined('ABSPATH')) { exit; }

require_once plugin_dir_path(__FILE__) . 'seminar6-core.php';

/** [seminar6 type="3-D-OKR"] → Terminliste im neuen Design */
function seminar6_shortcode($atts) {
    $atts = shortcode_atts(array(
        'type' => '3-D-OKR',
    ), $atts);
    return seminar6_render($atts['type']);
}
add_shortcode('seminar6', 'seminar6_shortcode');
