#!/usr/bin/env python3
"""
Baut page-jotform-resevierung.txt aus src/jotform/jotform-reservierung.html.

Der Jotform-Quellcode bleibt unverändert bis auf EINE Stelle: die beiden
Options-Zeilen des Seminar-Dropdowns

    <option value="">Please Select</option>
    <option value="TERMINE">TERMINE</option>

werden durch den Plugin-Shortcode [seminar6_options type="3-D-OKR"]
ersetzt (liefert "Bitte auswählen ..." + alle Termine als "Termin, Ort",
nach Datum sortiert, ausgebuchte übersprungen).

Der Inhalt kommt in [vc_column_text], damit WordPress den Shortcode
ausführt. Damit wpautop nichts zerlegt, wird alles auf EINE Zeile
minifiziert — dafür muss der einzige //-Zeilenkommentar im JotForm-JS
entfernt werden (Blockkommentare /* */ sind unkritisch).

Aufruf:  python3 build_jotform.py
"""
import re
from pathlib import Path

ROOT = Path(__file__).parent
SRC = ROOT / "src" / "jotform" / "jotform-reservierung.html"
OUT = ROOT / "page-jotform-resevierung.txt"

html = SRC.read_text(encoding="utf-8")

# 1) Dropdown-Optionen durch den Plugin-Shortcode ersetzen
options_block = re.compile(
    r'<option value="">Please Select</option>\s*'
    r'<option value="TERMINE">TERMINE</option>'
)
assert options_block.search(html), "TERMINE-Optionsblock nicht gefunden!"
html = options_block.sub('[seminar6_options type="3-D-OKR"]', html, count=1)

# 2) //-Zeilenkommentare entfernen (einzeilig-Minifizierung würde sonst
#    den restlichen Code der Zeile auskommentieren). Nur Zeilen, die mit
#    // beginnen — URLs (https://...) bleiben unberührt.
html = re.sub(r"^\s*//[^\n]*$", "", html, flags=re.M)

# 3) Auf eine Zeile minifizieren
html = re.sub(r"\s*\n\s*", " ", html)
html = re.sub(r">\s+<", "><", html)
html = html.strip()

# 4) Zentrierender Wrapper + WPBakery-Row
html = f'<div style="max-width:782px;margin:0 auto;padding:32px 16px 56px">{html}</div>'
content = (
    '[vc_row css_animation="" row_type="row" use_row_as_full_screen_section="no" '
    'type="full_width" angled_section="no" text_align="left" '
    'background_image_as_pattern="without_pattern" z_index="" '
    'background_color="#ffffff" el_class="okrs-row"]'
    f"[vc_column][vc_column_text]{html}[/vc_column_text][/vc_column][/vc_row]"
)

OUT.write_text(content, encoding="utf-8")
print(f"OK: {OUT.name} ({OUT.stat().st_size} Bytes)")
