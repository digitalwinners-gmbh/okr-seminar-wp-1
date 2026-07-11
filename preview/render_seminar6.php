<?php
/*
 * Lokaler Test-Harness: rendert die neue Terminliste ([seminar6]) mit
 * PHP-CLI und der seminarinfos3.ini aus plugin-orig/ — ohne WordPress.
 * Wird von make_preview.py aufgerufen; Ausgabe landet in preview/index.html.
 */
define('SEMINAR6_TEST', true);
define('SEMINAR6_INI_PATH', __DIR__ . '/../plugin-orig/seminarinfos3.ini');

// Minimale WP-Stubs
function plugin_dir_path($file) { return dirname($file) . '/'; }
function esc_html($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_url($u) { return htmlspecialchars((string) $u, ENT_QUOTES, 'UTF-8'); }
function wp_json_encode($data, $flags = 0) { return json_encode($data, $flags); }
function shortcode_atts($defaults, $atts) { return array_merge($defaults, array_intersect_key((array) $atts, $defaults)); }
function add_shortcode($tag, $fn) {}

require __DIR__ . '/../plugin/seminar6-shortcode/seminar6-core.php';

echo seminar6_render('3-D-OKR');
echo "\n";
echo seminar6_data_shortcode(array('type' => '3-D-OKR'));
