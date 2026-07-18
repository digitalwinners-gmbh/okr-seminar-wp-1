#!/usr/bin/env python3
"""
Baut page-kontaktformular.txt und page-kontakt.txt (WPBakery/Bridge)
aus src/kontaktformular.html bzw. src/kontakt.html.
Self-contained (CSS+HTML+JS inline) als [vc_raw_html] in full-width-Row.
Einfach die jeweilige .txt komplett in die WP-Seite kopieren.

Aufruf:  python3 build_kontakt.py
"""
import base64
import urllib.parse
from pathlib import Path

ROOT = Path(__file__).parent

PAGES = [
    ("kontaktformular.html", "page-kontaktformular.txt", "okrk-row"),
    ("kontakt.html", "page-kontakt.txt", "okrk-row"),
]


def raw_html(html: str) -> str:
    enc = base64.b64encode(urllib.parse.quote(html, safe="").encode("ascii")).decode("ascii")
    return f"[vc_raw_html]{enc}[/vc_raw_html]"


for src_name, out_name, el_class in PAGES:
    html = (ROOT / "src" / src_name).read_text(encoding="utf-8").strip()
    content = (
        '[vc_row css_animation="" row_type="row" use_row_as_full_screen_section="no" '
        'type="full_width" angled_section="no" text_align="left" '
        'background_image_as_pattern="without_pattern" z_index="" '
        f'background_color="#f9f8f4" el_class="{el_class}"]'
        f"[vc_column]{raw_html(html)}[/vc_column][/vc_row]"
    )
    out = ROOT / out_name
    out.write_text(content, encoding="utf-8")
    print(f"OK: {out_name} ({out.stat().st_size} Bytes)")
