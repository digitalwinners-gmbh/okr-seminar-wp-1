<?php
/*
Plugin Name: Seminar Shortcode 5
Description: A plugin that renders different HTML based on a parameter.
Version: 1.0
*/


function formatCurrency5($amount, $currency) {
    return number_format($amount, 2, ',', '.') . ' ' .$currency;
}


// Define the shortcode function
function seminar5_shortcode($atts) {
    
    
    $output = "";
    
    // Extract the shortcode parameter
    $atts = shortcode_atts(array(
        'type' => 'default', // Default parameter value
        'display' => 'default' // Default parameter value
    ), $atts);

    $type=$atts['type'];
    $display=$atts['display'];
    
    /* type = 1-D-OKR / 3-D-OKR / 2-FK-OKR / 1-SAFE-OKR
     * display = TYPE-HTML-SCHEMA, TYPE-COUNTDOWN, ALL-SCHEMA, ALL-TEXT
     * 
     *  
    */
    

    $html_template = 'https://okrexperten.de/snippet-okr-full-service-agentur/snippet-seminare-3/';
    $html = file_get_contents($html_template);
    // Create a new DOMDocument
    $dom = new DOMDocument();
    // Load the HTML content into the DOMDocument
    $dom->loadHTML($html);
    // Create a new DOMXPath object
    $xpath = new DOMXPath($dom);
    
    $config = parse_ini_file(plugin_dir_path(__FILE__) .'../../../../private_html/seminarinfos3.ini', false, INI_SCANNER_RAW);
    
    $start_date_string_countdown = '';

	setlocale(LC_TIME, 'de_DE.UTF-8');
	$monate = [
  'January' => 'Januar',
  'February' => 'Februar',
  'March' => 'März',
  'April' => 'April',
  'May' => 'Mai',
  'June' => 'Juni',
  'July' => 'Juli',
  'August' => 'August',
  'September' => 'September',
  'October' => 'Oktober',
  'November' => 'November',
  'December' => 'Dezember'
];
	
    $countId=0;
    


// Loop through each entry in the array
foreach ($config as $key => $value) {

    $certification_remark = "";

	$status_ausgebucht = false;


    // Split the value using the "|" separator
    $items = explode('|', $value);

    $seminar_code = $items[0];
    $seats_available = $items[1];
	$execution = $items[2];
	$special_3for2 = $items[3];
    $date_start = $items[4];
    $start_time_string = $items[5];
    $date_end = $items[6];
    $end_time_string = $items[7];
    $date_certification = $items[8];
    $trainer = $items[9];
    $services = $items[10];
    $location_name = $items[11];
    $location_street  = $items[12] ?? '';
    $location_country = $items[13] ?? '';
    $location_plz     = $items[14] ?? '';
    $location_city    = $items[15] ?? '';

	if ($location_country == "DE") $location_country = "D"; 
	if ($location_country == "AT") $location_country = "A"; 
	
    $location = $location_name . ", " . $location_street . ", " . $location_country . "-" . $location_plz ." ". $location_city;
    
    $date_string = $date_start . '-'. $date_end;
    $date_string2 = "von ". $date_start . ' bis '. $date_end;
    
    $startDateTime = DateTime::createFromFormat('d.m.Y', $date_start);
    $endDateTime = DateTime::createFromFormat('d.m.Y', $date_end);
    

    if ($startDateTime !== false && $endDateTime !== false) {

      $start_date_string = $startDateTime->format('Y-m-d');
      $end_date_string = $endDateTime->format('Y-m-d');

        
        if ($startDateTime->format('Ym') == $endDateTime->format('Ym')) {
          // Shorten the date range
          if ($startDateTime == $endDateTime) {
            // Shorten to just one date
            $date_string = $startDateTime->format('d.m.Y');
            $date_string2 = $date_string;
  
          } else {
          $date_string = $startDateTime->format('d') . ".-" . $endDateTime->format('d.m.Y');
            }  
       } else {
          $date_string = $startDateTime->format('d.m') . ".-" . $endDateTime->format('d.m.Y');
       }
       if ($date_certification != '') { 
		   $date_string .= " & ".$date_certification;
		   $certification_remark = "- Zertifizierung am ".$date_certification." Remote";
		}
		
	   $date_day_string = $startDateTime->format('d');
	   $date_year_string = $startDateTime->format('Y');

	   $date_month_string = $startDateTime->format('F');
	   $date_month_string = $monate[$date_month_string];
	   $date_month_year_string = $date_month_string." ".$date_year_string;


    } else {
      $output .= "Invalid date format.";
    }
    
	
           if (strstr($seminar_code, "_REM")) { $location_seminar="Live-Online"; $location = "Live-Online"; }

           if (strstr($seminar_code, "_FRA")) $location_seminar="Frankfurt am Main";
           if (strstr($seminar_code, "_BER")) $location_seminar="Berlin";
           if (strstr($seminar_code, "_MUC")) $location_seminar="München";
           if (strstr($seminar_code, "_ZUR")) $location_seminar="Zürich";
           if (strstr($seminar_code, "_LUZ")) $location_seminar="Luzern";
           if (strstr($seminar_code, "_VIE")) $location_seminar="Wien";

           if (strstr($seminar_code, "3-D")) { $seminar_title="OKR Coach/Master Seminar";
											 $seminar_description = "In 3 Tagen OKR Coach/Master werden – mit Zertifizierung.";
											 $seminar_duration= "3 Tage: $date_string, je $start_time_string bis $end_time_string Uhr";
											 $seminar_duration_short = "3 Tage";
       										 $seminar_url = "https://okrexperten.de/okr-training/okr-seminar/okr-seminar-okr-coach-master/";
											  
											  
											$price_net_float=1990.00;
											if (strstr($seminar_code, "_REM")) $price_net_float=1290.00;
    										if (strstr($seminar_code, "_ZUR")) $price_net_float=1990.00;
    										if (strstr($seminar_code, "_LUZ")) $price_net_float=2500.00;
    										if (strstr($seminar_code, "_VIE")) $price_net_float=1990.00;
											  
			 }

	           if (strstr($seminar_code, "2X-FK-")) { $seminar_title="OKR Seminar für Führungskräfte";
											 $seminar_description = "In 2×4 Stunden: OKR verstehen, anwenden – und über die Einführung entscheiden";
											 $seminar_duration= "2×4 Stunden: $date_string, je $start_time_string bis $end_time_string Uhr";
											 $seminar_duration_short = "2×4 Stunden";
       										 $seminar_url = "https://okrexperten.de/okr-training/okr-seminar/okr-seminar-okr-coach-master/";

 											$price_net_float=1290.00;
											if (strstr($seminar_code, "_REM")) $price_net_float=990.00;

								
				}
	
		           if (strstr($seminar_code, "OV-OKR")) { $seminar_title="OKR Seminar: OKR in der öffentlichen Verwaltung";
											 $seminar_description = "1 Tag Intensiv-Seminar: Verstehen. Formulieren. Anwenden.";
											 $seminar_duration= "1 Tag: $date_string, $start_time_string bis $end_time_string Uhr";
											 $seminar_duration_short = "1 Tag";
       										 $seminar_url = "https://okrexperten.de/okr-training/okr-seminar/okr-oeffentliche-verwaltung/";

 											$price_net_float=890.00;
											if (strstr($seminar_code, "_REM")) $price_net_float=690.00;

								
				}

		           if (strstr($seminar_code, "1-KI-OKR")) { $seminar_title="KI für OKR Coaches, Master & Champions";
											 $seminar_description = "KI-Upskilling für OKR Coaches / Master / Champions – mit Zertifizierung.";
											 $seminar_duration= "4 Stunden: $date_string, $start_time_string bis $end_time_string Uhr";
											 $seminar_duration_short = "4 Stunden";
       										 $seminar_url = "https://okrexperten.de/okr-training/okr-seminar/ki-fuer-okr-coaches-master-champions-upskilling/#tab-160364";

 											$price_net_float=390.00;
											if (strstr($seminar_code, "_REM")) $price_net_float=390.00;

								
				}




	
    $ust_text = "";

	
	$early_bird="";
	$today = new DateTime(); // aktuelles Datum
	$interval = $today->diff($startDateTime);
	$days = (int) $interval->format('%r%a'); // %r = +/- , %a = absolute Tage
	$weeks = (int) floor($days / 7);
	$discount = 0;
	$price_net_float_no_discount_float=$price_net_float;
	//$early_bird=$weeks." weeks";
	if ($weeks >=8) $discount = 10;
	if ($weeks >=12) $discount = 15;

	// Summer Special
	if (strstr($key, "3DMUC202606") || strstr($key, "3DREM202606")) {
		$discount = 15;
	}

	if ($discount>0 && (strstr($seminar_code, "3-D") || strstr($seminar_code, "1-KI-OKR")) && !strstr($seminar_code, "_LUZ") && !strstr($seminar_code, "_VIE")) {

	 
		$price_net_float=$price_net_float*(1-$discount/100);
	}


	
	$price_net=formatCurrency5($price_net_float,'€');
	$price_gross_float=$price_net_float*1.19; // D
	$price_gross=formatCurrency5($price_gross_float,'€')." € inkl. 19% USt.";
	$ust_text = "zzgl. USt.";
	$price_currency="EUR";
	$remark_partner = "";
	//$booking_link = "https://elo-d.digitalwinners.academy/s/digitalwinners-academy/3-tages-seminar-okr-lernen-und-erleben-online-seminare2/payment";

	$booking_link_param_seminar = $seminar_title ." - ".$location_seminar." - ".$date_string;
	$booking_link_param_preis = $price_net." zzgl. USt - bzw. ".$price_gross;
	$booking_link_param_code = $key;
	if ($discount>0) $booking_link_param_preis .= " - inkl. EarlyBird-Rabatt von ".$discount." %";

    if (strstr($seminar_code, "_ZUR")){
		$price_net=formatCurrency5($price_net_float,'CHF');
		//OLD: $price_gross=$price_net." inkl. 8,1% MWST"; 

		$price_net_EUR=formatCurrency5($price_net_float*1.1,'EUR');
		$price_gross_EUR=formatCurrency5($price_net_float*1.19,'EUR');
		$price_gross=$price_net." netto, Rechnung ohne Deutsche USt., ggf. Reverse Charge.<br>Für Unternehmen mit Sitz in Deutschland: ".$price_net_EUR." zzgl. 19 % Ust. = ".$price_gross_EUR." EUR"; 
		$price_currency="CHF";
		//OLD: $ust_text = "inkl. MWST";
		$ust_text = "netto<sup>*</sup>";
		$offer_link = "";
		$discount_remark = "";
		$booking_link_param_preis = $price_gross;
	}

	
	$booking_link_params = "?code=".rawurlencode($booking_link_param_code);
	$booking_link_params .= "&seminar=".rawurlencode($booking_link_param_seminar);
	$booking_link_params .= "&preis=".rawurlencode($booking_link_param_preis);

	$discount_remark = "25% Rabatt für sebstzahlende Student:innen, Start-ups (Pre-Seed/Seed, <30 MA) &amp; NGOs. Nicht für Beratungen / Berater:innen. Rabatte nicht kombinierbar.";
	
	$offer_link_html="";
	$booking_link = "https://form.jotform.com/251410725597358".$booking_link_params;
	$offer_link = "https://form.jotform.com/251414712620345".$booking_link_params;
		

    if (strstr($seminar_code, "_LUZ")){
		$price_net=formatCurrency5($price_net_float,'CHF');
		$price_gross=$price_net." inkl. 7,7% MWST"; // CH: net=gross
		$price_currency="CHF";
		$ust_text = "inkl. MWST";
		$remark_partner = "Buchung über unseren Partner und Veranstalter: IKF Institut für Kommunikation und Führung, Luzern";
		$booking_link = "https://ikf.ch/de/kurzformate/okr-workshop";
		$offer_link = "";
		$discount_remark = "";
	}

    if (strstr($seminar_code, "_VIE")){
		$price_net=formatCurrency5($price_net_float,'€');
		$price_gross_float=$price_net_float*1.20;
		$price_gross=formatCurrency5($price_gross_float,'€')." € inkl. 20% USt.";
		$price_currency="EUR";
		$ust_text = "zzgl. USt.";
		$remark_partner = "Buchung über unseren Partner und Veranstalter: TechTalk, Wien";
		$booking_link = "https://training.techtalk.at/trainings/3-tages-okr-kurs/";
		$offer_link = "";
		$discount_remark = "25% Rabatt für Student:innen (Selbstzahler), Start Ups (Seed-Phase, <30 Mitarbeitende, keine Beratung/Berater) und NGO's";
	}
	
	if ($discount>0 && (strstr($seminar_code, "3-D") || strstr($seminar_code, "1-KI-OKR")) && !strstr($seminar_code, "_LUZ") && !strstr($seminar_code, "_VIE")) {
		$early_bird="<span class=\"badge-earlybird\">EarlyBird</span><span class=\"discount-earlybird\">- ".$discount."%</span>";
		$price_net="<span style=\"color: #00af3d;\">".$price_net."</span>";
		if (strstr($seminar_code, "_ZUR"))
			$price_net_float_no_discount=formatCurrency5($price_net_float_no_discount_float,'CHF');
			else $price_net_float_no_discount=formatCurrency5($price_net_float_no_discount_float,'€');
		$ust_text = "<span style=\"text-decoration:line-through;\">".$price_net_float_no_discount."</span>&nbsp;".$ust_text;

	}

	// Summer Special
	if ($discount>0 && (strstr($key, "3DMUC202606") || strstr($key, "3DREM202606"))) {
		$early_bird="<span class=\"badge-special\">Summer Special</span><span class=\"discount-earlybird\">- ".$discount."%</span>";
	}

	
	if (!strstr($seminar_code, "_LUZ") && !strstr($seminar_code, "_VIE")) {
		$offer_link_html="<div class=\"offer-link\"><a href=\"".$offer_link."\" target=\"_blank\" style=\"font-size:14px;\">Angebot anfragen</a></div>";
	}
	
	
    if (strstr($seminar_code, "2X-FK-")) { 
		$discount_remark = "";
	}

	    if (strstr($seminar_code, "OV-OKR")) { 
		$discount_remark = "";
	}

	
    if (strstr($seminar_code, "_REM")) { $location_seminar="Live-Online"; $location = "Live-Online"; }

	$button_booking="<a href=\"".$booking_link."\" target=\"_blank\" class=\"qbutton  default\" data-hover-background-color=\"#ab8d3f\" data-hover-border-color=\"#ab8d3f\" style=\"border-color: #d5b240; background-color:#d5b240;\" >Jetzt buchen<i class=\"qode_icon_font_awesome fa fa-angle-right qode_button_icon_element\"></i></a>".$offer_link_html;

	if ($execution == "A") {
		$button_booking="<a href=\"javascript:void(0);\" class=\"qbutton default disabled\" aria-disabled=\"true\" tabindex=\"-1\" style=\"border-color:#d9d9d9; background-color:#d9d9d9; color:#555555; cursor:not-allowed; pointer-events:none;\">Ausgebucht</a>";

	}

	$seats_available_int = is_numeric($seats_available) ? (int) $seats_available : 0;
	
	$status_conduct="";
	if ($execution == "X") {
		$status_conduct=" <span class=\"green-text\"><strong>Durchführung gesichert</strong></span>";
	}
	if ($execution == "A") {
		$status_conduct=" <span class=\"green-text\"><strong>Ausgebucht</strong></span>";
		$status_ausgebucht = true;
	}
	if ($seats_available_int > 0) {
		if ($status_conduct != "") $status_conduct.=" -";
		$status_conduct.=" <span class=\"dred-text\"><strong>Nur noch ".$seats_available_int." Plätze verfügbar</strong></span>";
	}
	if ($status_conduct != "") {
		$status_conduct="<span data-type=\"normal\" class=\"qode_icon_shortcode  q_font_awsome_icon fa-lg  \" style=\"margin: 2px 5px 0 0; \"><i class=\"qode_icon_font_awesome fa fa-info-circle qode_icon_element\" style=\"color: #707070;\"></i></span>".$status_conduct;
	} else {
		$status_conduct="Teilnehmerzahl begrenzt.";
	}
	if ($special_3for2 == "3") { // 3
		if ($status_conduct != "") $status_conduct.="<br>";
		$status_conduct.="<strong>3-für-2-Team Special - 2 bezahlen, 3 teilnehmen</strong>";
	}
	
    // REPLACE
    $data = array(
            'seminar-title' => $seminar_title, 
            'seminar-description' => $seminar_description,
            'seminar-duration' => $seminar_duration,
            'seminar-duration-short' => $seminar_duration_short,
		    'seminar-url' => $seminar_url,
		    'date' => $date_string, // for HTML
            'start_date' => $start_date_string, // for schema.org
            'end_date' => $end_date_string, // for schema.org
            'start_time' => $start_time_string, // for HTML and schema.org
            'end_time' => $end_time_string, // for HTML and schema.org
           // 'remark' => $remark, // not used by all
            'location' => $location, // not used by all
            'trainer' => $trainer, 
            'services' => $services, 
		'location-city' => $location_seminar,
		'date-dd' => $date_day_string,
		'date-mm-yyyy' => $date_month_year_string,
		'date-mm' => $date_month_string,
		'date-yyyy' => $date_year_string,
		'certification_remark' => $certification_remark,
		'price_net' => $price_net,
		'price_gross' => $price_gross,
		'ust_text' => $ust_text,
		'remark_partner'=> $remark_partner,
		'booking_link'=> $booking_link,
		'discount_remark' => $discount_remark,
		'status_conduct' => $status_conduct,
		'button_booking' => $button_booking,
		'early_bird' => $early_bird,
		'location_name' =>     $location_name,
   		'location_street' =>  $location_street,
  		'location_country' =>   $location_country,
  		'location_plz' =>   $location_plz,
  		'location_city' =>   $location_city,
		'price_net_float' =>  $price_net_float,
		'price_currency' =>  $price_currency
        );
    
    
    // Get the current date as a DateTime object
    $currentDate = new DateTime();
    // TEST: $currentDate = new DateTime('2024-05-22');

    // Calculate the date that is 1 day after the current date
    $cutoffDate = clone $currentDate;
    $cutoffDate->modify('+1 day');


  if ($startDateTime > $cutoffDate) {

        $countId++;

      if (str_starts_with($seminar_code, $type)) {
    
           if ($start_date_string_countdown == '') {
                $start_date_string_countdown = $start_date_string;
                $start_time_string_countdown = $start_time_string;
           }
      }
        
      if ($display=='TYPE-HTML-SCHEMA' && str_starts_with($seminar_code, $type)) { // HTML Snippet
        
        $html="";
        // Define the ID you want to find
        $idToFind = "3D"; // $seminar_code;
        // Use XPath to find the div element with the specified ID
        $query = "//div[@id='$idToFind']";
        // Execute the XPath query
        $elements = $xpath->query($query);
        // Check if an element with the specified ID was found
        if ($elements->length > 0) {
           // Get the first matching element (there should be only one)
           $element = $elements->item(0);
           // Clone the element
           $clonedElement = $element->cloneNode(true);
           // Generate a new unique ID (e.g., by appending a counter)
           $newId = $idToFind . '_' . $countId;
           // Set the new unique ID to the cloned element
           $clonedElement->setAttribute('id', $newId);
           // Extract and output the modified HTML content of the cloned element
           $html = $dom->saveHTML($clonedElement);
        } else {
           $html = "<p>ERROR-SEMINAR_PLUGIN: HTML</p>";
        }   

        $schema_html="";
        // Define the ID you want to find
        $idToFind = "3D_PRES_SCHEMA";
        if (strstr($seminar_code, "_REM")) $idToFind = "3D_REM_SCHEMA";
         // Use XPath to find the div element with the specified ID
        $query = "//div[@id='$idToFind']";
        // Execute the XPath query
        $elements = $xpath->query($query);
        // Check if an element with the specified ID was found
        if ($elements->length > 0) {
           // Get the first matching element (there should be only one)
           $element = $elements->item(0);
           // Clone the element
           $clonedElement = $element->cloneNode(true);
           // Generate a new unique ID (e.g., by appending a counter)
           $newId = $idToFind . '_' . $countId;
           // Set the new unique ID to the cloned element
           $clonedElement->setAttribute('id', $newId);
           // Extract and output the modified HTML content of the cloned element
           $schema_html = $dom->saveHTML($clonedElement);
        } else {
           $schema_html = "<p>ERROR-SEMINAR_PLUGIN: SCHEMA.ORG</p>";
        }

        $html.=$schema_html;
          
        // Replace placeholders with data
        foreach ($data as $placeholder => $value) {
               $html = str_replace("{" . $placeholder . "}", $value, $html);
        }           
        $output .= $html;
        
       } // HTML Snippet

       if ($display=='ALL-SCHEMA') { // Schema.org List
        $schema_html="";
        // Define the ID you want to find
        
        $idToFind = "3D_PRES_SCHEMA";
        if (strstr($seminar_code, "_REM")) $idToFind = "3D_REM_SCHEMA";
    
        $idToFind = $seminar_code."_SCHEMA";
        // Use XPath to find the div element with the specified ID
        $query = "//div[@id='$idToFind']";
        // Execute the XPath query
        $elements = $xpath->query($query);
        // Check if an element with the specified ID was found
        $html="";
        if ($elements->length > 0) {
           // Get the first matching element (there should be only one)
           $element = $elements->item(0);
           // Extract and output the HTML content of the matching div
           $schema_html = $dom->saveHTML($element);
        }

        // Replace placeholders with data
        foreach ($data as $placeholder => $value) {
               $schema_html = str_replace("{" . $placeholder . "}", $value, $schema_html);
        }           
        $output .= $schema_html;                   
           
       } // Schema.org List

        
       if ($display=='ALL-TEXT2') { // Text List
                   
           if (strstr($seminar_code, "_REM")) $location_seminar="Live-Online";
           if (strstr($seminar_code, "_FRA")) $location_seminar="Frankfurt am Main";
           if (strstr($seminar_code, "_BER")) $location_seminar="Berlin";
           if (strstr($seminar_code, "_MUC")) $location_seminar="München";
           if (strstr($seminar_code, "_ZUR")) $location_seminar="Zürich";
           if (strstr($seminar_code, "_LUZ")) $location_seminar="Luzern";
           if (strstr($seminar_code, "_VIE")) $location_seminar="Wien";
           $location_seminar.="</br>";

        
           if (str_starts_with($seminar_code, "1-D-OKR") )
                $output .= "1-Tages OKR Seminar - ".$date_string." - ".$location_seminar;
           if (str_starts_with($seminar_code, "3-D-OKR") )  
                $output .= "3-Tages OKR Seminar - ".$date_string." - ".$location_seminar;
           if (str_starts_with($seminar_code, "2X-FK-OKR") )  
                $output .= "OKR Führungskräfte-Seminar - ".$date_string." - ".$location_seminar;
           if (str_starts_with($seminar_code, "OV-OKR") )  
                $output .= "OKR Seminar: OKR in der öffentlichen Verwaltung - ".$date_string." - ".$location_seminar;
           if (str_starts_with($seminar_code, "1-SAFE-OKR") ) 
                $output .= "SAFe OKR Seminar - ".$date_string." - ".$location_seminar;
           if (str_starts_with($seminar_code, "1-KI-OKR") )  
                $output .= "KI für OKR Coaches, Master & Champions - ".$date_string." - ".$location_seminar;
           

       } 
        
       if ($display=='ALL-TEXT') { // Text List
                   
           if (strstr($seminar_code, "_REM")) $location_seminar="Live-Online";
           if (strstr($seminar_code, "_FRA")) $location_seminar="Frankfurt am Main";
           if (strstr($seminar_code, "_BER")) $location_seminar="Berlin";
           if (strstr($seminar_code, "_MUC")) $location_seminar="München";
           if (strstr($seminar_code, "_ZUR")) $location_seminar="Zürich";
           if (strstr($seminar_code, "_LUZ")) $location_seminar="Luzern";
           if (strstr($seminar_code, "_VIE")) $location_seminar="Wien";
           $location_seminar.="</br>";

        
           if (str_starts_with($seminar_code, "1-D-OKR") )
               $seminar_label = "1-Tages OKR Seminar für AnwenderInnen";
           if (str_starts_with($seminar_code, "3-D-OKR") )  
                $seminar_label = "3-Tages OKR Seminar - Ausbildung zum OKR Coach/Master";
           if (str_starts_with($seminar_code, "2X-FK-OKR") )  
                $seminar_label = "OKR Führungskräfte-Seminar";
           if (str_starts_with($seminar_code, "OV-OKR") )  
                $seminar_label = "OKR Seminar: OKR in der öffentlichen Verwaltung";
           if (str_starts_with($seminar_code, "1-SAFE-OKR") ) 
                $seminar_label = "SAFe OKR Seminar - OKR Seminar für SAFe Profis";
           if (str_starts_with($seminar_code, "1-KI-OKR") )  
                $seminar_label = "KI für OKR Coaches, Master & Champions";
           
            $output .= "Seminar: ".$seminar_label." | Datum: ".$date_string2." | Ort: ".$location_seminar;

       }      
      
      
        
  } // if ($startDateTime > $cutoffDate)
        
}   // foreach ($config as $key => $value)
    

/*    
    if ($display=='TYPE-COUNTDOWN') { 
        
        $shortcode_string ="[wpcdt-countdown id=\"8436\" expiry_date_extra=\"$start_date_string_countdown $start_time_string_countdown:00\"]";
        $shortcode_result = do_shortcode($shortcode_string);

        $output .= $shortcode_result;
        //$output .= "<p>$start_date_string_countdown $start_time_string_countdown:00</p>";
    }   
*/
    if ($display=='ALL-TEXT')
               $output .= "anderes (Datum bitte in Mitteilung eintragen)";

        
    return $output;
    
    
}

// Register the shortcode
add_shortcode('seminar5', 'seminar5_shortcode');

?>