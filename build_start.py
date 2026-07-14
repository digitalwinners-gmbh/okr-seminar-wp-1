#!/usr/bin/env python3
"""
Baut page-start.txt (WPBakery/Bridge) aus src/start-hero.html.

Der Hero ist self-contained (CSS + HTML + JS inline) und wird als
[vc_raw_html] (base64 + rawurlencode) in eine full-width-Row gepackt.
So bleibt der Code beim Einfuegen in WordPress unveraendert (kein wpautop,
Scripts bleiben erhalten). KEINE externen CSS/JS-Dateien noetig, keine
Versionsnummer — einfach page-start.txt komplett in die WP-Seite kopieren.

Aufruf:  python3 build_start.py
Ausgabe: page-start.txt
"""
import base64
import urllib.parse
from pathlib import Path

ROOT = Path(__file__).parent
FRAGMENT = ROOT / "src" / "start-hero.html"


def raw_html(html: str) -> str:
    encoded = base64.b64encode(
        urllib.parse.quote(html, safe="").encode("ascii")
    ).decode("ascii")
    return f"[vc_raw_html]{encoded}[/vc_raw_html]"


html = FRAGMENT.read_text(encoding="utf-8").strip()

content = (
    '[vc_row css_animation="" row_type="row" use_row_as_full_screen_section="no" '
    'type="full_width" angled_section="no" text_align="left" '
    'background_image_as_pattern="without_pattern" z_index="" '
    'background_color="#FAF8F3" el_class="okrst-row"]'
    f"[vc_column]{raw_html(html)}[/vc_column][/vc_row]"
)

out = ROOT / "page-start.txt"
out.write_text(content, encoding="utf-8")
print(f"OK: {out} geschrieben ({out.stat().st_size} Bytes)")
