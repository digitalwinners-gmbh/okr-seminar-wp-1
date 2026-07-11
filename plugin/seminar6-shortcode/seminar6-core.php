<?php
/*
 * Seminar Shortcode 6 – Kernlogik: INI einlesen, Termine berechnen,
 * neues Layout aus templates/okrs-item.html rendern, Kennzahlen liefern.
 *
 * Shortcodes (Registrierung in seminar6-shortcode.php bzw. unten):
 *   [seminar6 type="3-D-OKR"]
 *       → Terminliste im neuen Design inkl. schema.org JSON-LD
 *   [seminar6_info type="3-D-OKR" info="min-price-praesenz"]
 *       → einzelner Wert als Text (siehe seminar6_stats für alle Keys)
 *   [seminar6_data type="3-D-OKR"]
 *       → <script> mit window.okrsSeminarData für JS-Platzhalter
 *         (data-okrs-info="…"-Elemente, z. B. in der Buchungs-Sidebar)
 *
 * Preis-/Rabattlogik entspricht Seminar Shortcode 5; Datenquelle bleibt
 * private_html/seminarinfos3.ini.
 */

if (!defined('ABSPATH') && !defined('SEMINAR6_TEST')) { exit; }

function seminar6_format_currency($amount, $currency) {
    return number_format($amount, 2, ',', '.') . ' ' . $currency;
}

function seminar6_ini_path() {
    if (defined('SEMINAR6_INI_PATH')) return SEMINAR6_INI_PATH;
    $candidates = array(
        plugin_dir_path(__FILE__) . '../../../../private_html/seminarinfos3.ini', // Produktiv (wie v1)
        plugin_dir_path(__FILE__) . 'seminarinfos3.ini',
        plugin_dir_path(__FILE__) . '../seminarinfos3.ini',
    );
    foreach ($candidates as $c) {
        if (is_readable($c)) return $c;
    }
    return $candidates[0];
}

/**
 * Liest die INI und berechnet pro zukünftigem Termin alle Felder.
 * Preis-/Rabattlogik entspricht v1 (EarlyBird, Summer Special, Zürich/Wien/Luzern).
 */
function seminar6_collect($type) {
    $config = @parse_ini_file(seminar6_ini_path(), false, INI_SCANNER_RAW);
    if (!$config) return array();

    $monate = array(
        'January' => 'Januar', 'February' => 'Februar', 'March' => 'März',
        'April' => 'April', 'May' => 'Mai', 'June' => 'Juni', 'July' => 'Juli',
        'August' => 'August', 'September' => 'September', 'October' => 'Oktober',
        'November' => 'November', 'December' => 'Dezember',
    );

    $today = new DateTime();
    $cutoff = (clone $today)->modify('+1 day');
    $entries = array();

    foreach ($config as $key => $value) {
        $items = explode('|', $value);
        if (count($items) < 12) continue;

        $seminar_code   = $items[0];
        if (!str_starts_with($seminar_code, $type)) continue;

        $seats_available = $items[1];
        $execution       = $items[2];
        $special_3for2   = $items[3];
        $date_start      = $items[4];
        $start_time      = $items[5];
        $date_end        = $items[6];
        $end_time        = $items[7];
        $date_cert       = $items[8];
        $trainer         = $items[9];
        $services        = $items[10];
        $location_name   = $items[11];
        $location_street  = $items[12] ?? '';
        $location_country = $items[13] ?? '';
        $location_plz     = $items[14] ?? '';
        $location_city    = $items[15] ?? '';

        $startDateTime = DateTime::createFromFormat('d.m.Y', $date_start);
        $endDateTime   = DateTime::createFromFormat('d.m.Y', $date_end);
        if ($startDateTime === false || $endDateTime === false) continue;
        if ($startDateTime <= $cutoff) continue;

        // Ortskürzel → Stadt / Format
        $is_online = (bool) strstr($seminar_code, '_REM');
        $city = 'Präsenz';
        if ($is_online)                        $city = 'Live-Online';
        if (strstr($seminar_code, '_FRA'))     $city = 'Frankfurt am Main';
        if (strstr($seminar_code, '_BER'))     $city = 'Berlin';
        if (strstr($seminar_code, '_MUC'))     $city = 'München';
        if (strstr($seminar_code, '_ZUR'))     $city = 'Zürich';
        if (strstr($seminar_code, '_LUZ'))     $city = 'Luzern';
        if (strstr($seminar_code, '_VIE'))     $city = 'Wien';

        if ($location_country == 'DE') $location_country = 'D';
        if ($location_country == 'AT') $location_country = 'A';
        $location = $is_online
            ? 'Live-Online (Videocall)'
            : $location_name . ', ' . $location_street . ', ' . $location_country . '-' . $location_plz . ' ' . $location_city;

        // Datumsstrings
        $date_string = $date_start . '-' . $date_end;
        if ($startDateTime->format('Ym') == $endDateTime->format('Ym')) {
            $date_string = ($startDateTime == $endDateTime)
                ? $startDateTime->format('d.m.Y')
                : $startDateTime->format('d') . '.–' . $endDateTime->format('d.m.Y');
        } else {
            $date_string = $startDateTime->format('d.m') . '.–' . $endDateTime->format('d.m.Y');
        }
        $cert_note = '';
        if ($date_cert != '') {
            $date_string .= ' & ' . $date_cert;
            $cert_note = 'Zertifizierung am ' . $date_cert . ' Remote';
        }

        // Seminartyp → Titel, Dauer, Basispreis
        $seminar_title = 'OKR Seminar';
        $seminar_description = '';
        $duration = $date_string;
        $price_base = 0.0;
        if (strstr($seminar_code, '3-D')) {
            $seminar_title = 'OKR Coach/Master Seminar';
            $seminar_description = 'In 3 Tagen OKR Coach/Master werden – mit Zertifizierung.';
            $duration = "3 Tage, $date_string, je {$start_time}–{$end_time} Uhr";
            $price_base = 1990.00;
            if ($is_online)                    $price_base = 1290.00;
            if (strstr($seminar_code, '_ZUR')) $price_base = 1990.00;
            if (strstr($seminar_code, '_LUZ')) $price_base = 2500.00;
            if (strstr($seminar_code, '_VIE')) $price_base = 1990.00;
        }
        if (strstr($seminar_code, '2X-FK-')) {
            $seminar_title = 'OKR Seminar für Führungskräfte';
            $seminar_description = 'In 2×4 Stunden: OKR verstehen, anwenden – und über die Einführung entscheiden.';
            $duration = "2×4 Stunden, $date_string, je {$start_time}–{$end_time} Uhr";
            $price_base = $is_online ? 990.00 : 1290.00;
        }
        if (strstr($seminar_code, 'OV-OKR')) {
            $seminar_title = 'OKR Seminar: OKR in der öffentlichen Verwaltung';
            $seminar_description = '1 Tag Intensiv-Seminar: Verstehen. Formulieren. Anwenden.';
            $duration = "1 Tag, $date_string, {$start_time}–{$end_time} Uhr";
            $price_base = $is_online ? 690.00 : 890.00;
        }
        if (strstr($seminar_code, '1-KI-OKR')) {
            $seminar_title = 'KI für OKR Coaches, Master & Champions';
            $seminar_description = 'KI-Upskilling für OKR Coaches / Master / Champions – mit Zertifizierung.';
            $duration = "4 Stunden, $date_string, {$start_time}–{$end_time} Uhr";
            $price_base = 390.00;
        }

        // EarlyBird / Summer Special (wie v1)
        $days = (int) $today->diff($startDateTime)->format('%r%a');
        $weeks = (int) floor($days / 7);
        $discount = 0;
        if ($weeks >= 8)  $discount = 10;
        if ($weeks >= 12) $discount = 15;
        $special_label = 'EarlyBird';
        if (strstr($key, '3DMUC202606') || strstr($key, '3DREM202606')) {
            $discount = 15;
            $special_label = 'Summer Special';
        }
        $discountable = (strstr($seminar_code, '3-D') || strstr($seminar_code, '1-KI-OKR'))
            && !strstr($seminar_code, '_LUZ') && !strstr($seminar_code, '_VIE');
        if (!$discountable) $discount = 0;

        $price_net_float = $price_base;
        if ($discount > 0) $price_net_float = $price_base * (1 - $discount / 100);

        // Währung / USt.
        $currency = 'EUR';
        $currency_symbol = '€';
        $ust_text = 'zzgl. USt.';
        $partner_note = '';
        if (strstr($seminar_code, '_ZUR')) { $currency = 'CHF'; $currency_symbol = 'CHF'; $ust_text = 'netto*'; }
        if (strstr($seminar_code, '_LUZ')) {
            $currency = 'CHF'; $currency_symbol = 'CHF'; $ust_text = 'inkl. MWST';
            $partner_note = 'Buchung über unseren Partner und Veranstalter: IKF Institut für Kommunikation und Führung, Luzern';
        }
        if (strstr($seminar_code, '_VIE')) {
            $partner_note = 'Buchung über unseren Partner und Veranstalter: TechTalk, Wien';
        }

        // Buchungs-/Angebotslinks (wie v1)
        $price_net = seminar6_format_currency($price_net_float, $currency_symbol);
        $price_gross = seminar6_format_currency($price_net_float * 1.19, '€') . ' € inkl. 19% USt.';
        $param_preis = $price_net . ' zzgl. USt - bzw. ' . $price_gross;
        if ($discount > 0) $param_preis .= ' - inkl. EarlyBird-Rabatt von ' . $discount . ' %';
        if (strstr($seminar_code, '_ZUR')) $param_preis = $price_net . ' netto';
        $params = '?code=' . rawurlencode($key)
                . '&seminar=' . rawurlencode($seminar_title . ' - ' . $city . ' - ' . $date_string)
                . '&preis=' . rawurlencode($param_preis);
        $booking_link = 'https://okrexperten.de/okr-training/okr-seminar/okr-seminar-okr-coach-master/buchung/' . $params;
        $offer_link   = 'https://okrexperten.de/okr-training/okr-seminar/okr-seminar-okr-coach-master/inhouse/' . $params;
        if (strstr($seminar_code, '_LUZ')) { $booking_link = 'https://ikf.ch/de/kurzformate/okr-workshop'; $offer_link = ''; }
        if (strstr($seminar_code, '_VIE')) { $booking_link = 'https://training.techtalk.at/trainings/3-tages-okr-kurs/'; $offer_link = ''; }

        // Bruttopreis-Text fürs Details-Panel (Rechenregeln wie v1)
        $price_gross_text = seminar6_format_currency($price_net_float * 1.19, '€') . ' inkl. 19 % USt.';
        if (strstr($seminar_code, '_VIE')) {
            $price_gross_text = seminar6_format_currency($price_net_float * 1.20, '€') . ' inkl. 20 % USt.';
        }
        if (strstr($seminar_code, '_LUZ')) {
            $price_gross_text = seminar6_format_currency($price_net_float, 'CHF') . ' inkl. 7,7 % MWST';
        }
        if (strstr($seminar_code, '_ZUR')) {
            $price_gross_text = seminar6_format_currency($price_net_float, 'CHF')
                . ' netto, Rechnung ohne Deutsche USt., ggf. Reverse Charge.<br>'
                . 'Für Unternehmen mit Sitz in Deutschland: '
                . seminar6_format_currency($price_net_float * 1.1, 'EUR')
                . ' zzgl. 19 % USt. = '
                . seminar6_format_currency($price_net_float * 1.19, 'EUR');
        }

        // Verfügbarkeit
        $seats = is_numeric($seats_available) ? (int) $seats_available : 0;
        $avail = 'verfuegbar';
        if ($seats > 0)         $avail = 'wenige';
        if ($execution === 'A') $avail = 'ausgebucht';
        $avail_labels = array('verfuegbar' => 'Verfügbar', 'wenige' => 'Wenige Plätze', 'ausgebucht' => 'Ausgebucht');

        $notes = array();
        if ($execution === 'X')  $notes[] = 'Durchführung gesichert';
        if ($seats > 0 && $execution !== 'A') $notes[] = 'Nur noch ' . $seats . ' Plätze verfügbar';
        if ($special_3for2 === '3') $notes[] = '3-für-2-Team-Special – 2 zahlen, 3 nehmen teil';
        if ($partner_note !== '') $notes[] = $partner_note;

        $entries[] = array(
            'key'            => $key,
            'code'           => $seminar_code,
            'format'         => $is_online ? 'online' : 'praesenz',
            'title'          => $seminar_title,
            'description'    => $seminar_description,
            'city'           => $city,
            'date'           => $startDateTime->format('d.m.Y'),
            'date_string'    => $date_string,
            'start_iso'      => $startDateTime->format('Y-m-d') . 'T' . $start_time,
            'end_iso'        => $endDateTime->format('Y-m-d') . 'T' . $end_time,
            'duration'       => $duration,
            'location'       => $location,
            'location_name'  => $location_name,
            'location_street' => $location_street,
            'location_plz'   => $location_plz,
            'cert_note'      => $cert_note,
            'price_gross_text' => $price_gross_text,
            'location_city'  => $location_city,
            'trainer'        => $trainer,
            'services'       => ($services === '-' || $services === '') ? 'Interaktive Gruppenübungen' : $services,
            'avail'          => $avail,
            'avail_label'    => $avail_labels[$avail],
            'discount'       => $discount,
            'special_label'  => $discount > 0 ? $special_label : '',
            'price_net_float' => $price_net_float,
            'price_base_float' => $price_base,
            'currency'       => $currency,
            'currency_symbol' => $currency_symbol,
            'price'          => $price_net,
            'price_old'      => $discount > 0 ? seminar6_format_currency($price_base, $currency_symbol) : '',
            'ust_text'       => $ust_text,
            'booking_link'   => $booking_link,
            'offer_link'     => $offer_link,
            'notes'          => $notes,
            'sort'           => $startDateTime->getTimestamp(),
        );
    }

    usort($entries, function ($a, $b) { return $a['sort'] <=> $b['sort']; });
    return $entries;
}

/** Terminliste im neuen Design rendern (aus templates/okrs-item.html). */
function seminar6_render($type) {
    $entries = seminar6_collect($type);
    if (!$entries) return '<p class="okrs-date-empty">Aktuell sind keine offenen Termine verfügbar – gerne als Inhouse-Seminar anfragen.</p>';

    $template = @file_get_contents(plugin_dir_path(__FILE__) . 'templates/okrs-item.html');
    if (!$template) return '<p>ERROR-SEMINAR_PLUGIN: TEMPLATE</p>';

    $output = '';
    $n = 0;
    foreach ($entries as $e) {
        $n++;

        $special_chip = '';
        if ($e['special_label'] !== '') {
            $special_chip = '<span class="okrs-date-special"><span class="okrs-date-special-dot"></span>'
                . esc_html($e['special_label']) . '&nbsp;&nbsp;−' . $e['discount'] . '&nbsp;%</span>';
        }

        $price_old_html = $e['price_old'] !== ''
            ? '<span class="okrs-date-price-old">' . esc_html($e['price_old']) . '</span> '
            : '';

        if ($e['avail'] === 'ausgebucht') {
            $booking_button = '<span class="okrs-date-book okrs-date-book--disabled" aria-disabled="true">Ausgebucht</span>';
        } else {
            $booking_button = '<a class="okrs-date-book" href="' . esc_url($e['booking_link']) . '" target="_blank" rel="noopener">Buchen</a>';
        }

        // Angebot-Button (weiß, Goldrand, zweizeilig) zwischen Details und Buchen.
        // Ohne Angebots-Link (Partner-Termine Wien/Luzern): unsichtbarer
        // Platzhalter gleicher Größe, damit Preis/Details spaltengenau
        // mit den anderen Zeilen ausgerichtet bleiben.
        $offer_button = ($e['offer_link'] !== '')
            ? '<a class="okrs-date-offer-btn" href="' . esc_url($e['offer_link']) . '" target="_blank" rel="noopener">Angebot<br>anfragen</a>'
            : '<span class="okrs-date-offer-btn okrs-date-offer-btn--ghost" aria-hidden="true">Angebot<br>anfragen</span>';

        // Zertifizierungs-Hinweis als unauffällige 2. Zeile beim Ort
        $location_cert_html = ($e['cert_note'] !== '')
            ? '<br>' . esc_html($e['cert_note'])
            : '';

        $note_html = $e['notes']
            ? '<div class="okrs-date-note">' . esc_html(implode(' · ', $e['notes'])) . '</div>'
            : '';

        // schema.org JSON-LD
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'EducationEvent',
            'name' => $e['title'] . ' – ' . $e['city'],
            'description' => $e['description'],
            'startDate' => $e['start_iso'],
            'endDate' => $e['end_iso'],
            'eventAttendanceMode' => $e['format'] === 'online'
                ? 'https://schema.org/OnlineEventAttendanceMode'
                : 'https://schema.org/OfflineEventAttendanceMode',
            'eventStatus' => 'https://schema.org/EventScheduled',
            'location' => $e['format'] === 'online'
                ? array('@type' => 'VirtualLocation', 'url' => 'https://okrexperten.de/okr-training/okr-seminar/okr-seminar-okr-coach-master/')
                : array('@type' => 'Place', 'name' => $e['location_name'], 'address' => array(
                    '@type' => 'PostalAddress',
                    'streetAddress' => $e['location_street'],
                    'postalCode' => $e['location_plz'],
                    'addressLocality' => $e['location_city'],
                  )),
            'offers' => array(
                '@type' => 'Offer',
                'price' => number_format($e['price_net_float'], 2, '.', ''),
                'priceCurrency' => $e['currency'],
                'url' => $e['booking_link'],
                'availability' => $e['avail'] === 'ausgebucht' ? 'https://schema.org/SoldOut' : 'https://schema.org/InStock',
            ),
            'organizer' => array('@type' => 'Organization', 'name' => 'DigitalWinners GmbH – OKR Experten', 'url' => 'https://okrexperten.de'),
            'performer' => array('@type' => 'Person', 'name' => $e['trainer']),
        );

        $data = array(
            'n'              => $n,
            'schema_fmt'     => $e['format'] === 'online' ? 'REM' : 'PRES',
            'format'         => $e['format'],
            'avail'          => $e['avail'],
            'avail_label'    => esc_html($e['avail_label']),
            'date'           => esc_html($e['date']),
            'city'           => esc_html($e['city']),
            'special_chip'   => $special_chip,
            'price'          => esc_html($e['price']),
            'price_class'    => $e['special_label'] !== '' ? ' okrs-date-price--special' : '',
            'price_old_html' => $price_old_html,
            'ust_text'       => $e['ust_text'],
            'booking_button' => $booking_button,
            'duration'       => esc_html($e['duration']),
            'location'       => esc_html($e['location']),
            'trainer'        => esc_html($e['trainer']),
            'services'       => esc_html($e['services']),
            'note_html'      => $note_html,
            'offer_button'   => $offer_button,
            'location_cert_html' => $location_cert_html,
            'gross_html'     => '<div class="okrs-date-gross"><strong>Preis</strong><br>' . $e['price_gross_text'] . '</div>',
            'json_ld'        => wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        );

        $html = $template;
        foreach ($data as $placeholder => $value) {
            $html = str_replace('{' . $placeholder . '}', $value, $html);
        }
        $output .= $html;
    }
    return $output;
}

/** Kennzahlen für [seminar6_info] und [seminar6_data]. */
function seminar6_stats($type) {
    $entries = seminar6_collect($type);
    $praesenz = array_values(array_filter($entries, fn($e) => $e['format'] === 'praesenz'));
    $online   = array_values(array_filter($entries, fn($e) => $e['format'] === 'online'));

    $min_price = function ($list) {
        $eur = array_filter($list, fn($e) => $e['currency'] === 'EUR' && $e['avail'] !== 'ausgebucht');
        if (!$eur) $eur = $list;
        if (!$eur) return null;
        usort($eur, fn($a, $b) => $a['price_net_float'] <=> $b['price_net_float']);
        return $eur[0];
    };
    $cheapest_p = $min_price($praesenz);
    $cheapest_o = $min_price($online);

    $cities = array_values(array_unique(array_map(fn($e) => $e['city'], $praesenz)));

    $fmt = fn($f, $c) => seminar6_format_currency($f, $c);

    return array(
        'count-all'          => (string) count($entries),
        'count-praesenz'     => (string) count($praesenz),
        'count-online'       => (string) count($online),
        'termine-praesenz'   => count($praesenz) . (count($praesenz) === 1 ? ' Termin' : ' Termine'),
        'termine-online'     => count($online) . (count($online) === 1 ? ' Termin' : ' Termine'),
        'count-cities'       => (string) count($cities),
        'orte-praesenz'      => 'an ' . count($cities) . (count($cities) === 1 ? ' Ort' : ' Orten'),
        'min-price-praesenz' => $cheapest_p ? 'ab ' . $fmt($cheapest_p['price_net_float'], $cheapest_p['currency_symbol']) : '',
        'min-price-online'   => $cheapest_o ? 'ab ' . $fmt($cheapest_o['price_net_float'], $cheapest_o['currency_symbol']) : '',
        'old-price-praesenz' => $cheapest_p ? $fmt($cheapest_p['price_base_float'], $cheapest_p['currency_symbol']) : '',
        'old-price-online'   => $cheapest_o ? $fmt($cheapest_o['price_base_float'], $cheapest_o['currency_symbol']) : '',
        'next-date'          => $entries ? $entries[0]['date'] : '',
    );
}

/** [seminar6_info type="3-D-OKR" info="min-price-praesenz"] → einzelner Wert */
function seminar6_info_shortcode($atts) {
    $atts = shortcode_atts(array('type' => '3-D-OKR', 'info' => ''), $atts);
    $stats = seminar6_stats($atts['type']);
    return isset($stats[$atts['info']]) ? esc_html($stats[$atts['info']]) : '';
}

/** [seminar6_data type="3-D-OKR"] → window.okrsSeminarData für JS-Platzhalter */
function seminar6_data_shortcode($atts) {
    $atts = shortcode_atts(array('type' => '3-D-OKR'), $atts);
    $stats = seminar6_stats($atts['type']);
    return '<script>window.okrsSeminarData = ' . wp_json_encode($stats, JSON_UNESCAPED_UNICODE) . ';</script>';
}

add_shortcode('seminar6_info', 'seminar6_info_shortcode');
add_shortcode('seminar6_data', 'seminar6_data_shortcode');
