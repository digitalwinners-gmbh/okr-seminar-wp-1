#!/usr/bin/env python3
"""
Baut die beiden neuen Danke-Seiten fuer die Kontakt-Strecken:

  src/danke/danke-kontaktformular.html -> page-danke-kontaktformular.txt
  src/danke/danke-kontakttermin.html   -> page-danke-kontakttermin.txt

- Kontaktformular: Daten kommen per JotForm-POST -> [jotform-display]-Shortcodes,
  daher [vc_column_text] (WordPress fuehrt die Shortcodes aus) + minify.
- Kontakttermin: Daten kommen per Calendly-URL-Query (?invitee_full_name=...&
  invitee_email=...). Kein WP-Shortcode -> JavaScript liest die Query, daher
  [vc_raw_html] (Script bleibt erhalten, kein wpautop). Nicht minifizieren.

Aufruf:  python3 build_danke_kontakt.py
"""
import base64
import re
import urllib.parse
from pathlib import Path

ROOT = Path(__file__).parent
ASSET_BASE = "https://okrexperten.de/wp-content/uploads/okr-seminar"
ASSET_VER = re.search(r'ASSET_VER = "(\d+)"', (ROOT / "build.py").read_text()).group(1)


def raw_snippet(html: str) -> str:
    enc = base64.b64encode(urllib.parse.quote(html, safe="").encode("ascii")).decode("ascii")
    return f"[vc_raw_html]{enc}[/vc_raw_html]"


def minify(html: str) -> str:
    html = re.sub(r"<!--.*?-->", "", html, flags=re.S)
    html = re.sub(r"\s+", " ", html)
    html = re.sub(r">\s+<", "><", html)
    return html.strip()


def row(content: str, bg: str = "#f9f8f4") -> str:
    return (
        '[vc_row css_animation="" row_type="row" use_row_as_full_screen_section="no" '
        'type="full_width" angled_section="no" text_align="left" '
        'background_image_as_pattern="without_pattern" z_index="" '
        f'background_color="{bg}" el_class="okrs-row"]'
        f"[vc_column]{content}[/vc_column][/vc_row]"
    )


css_link = raw_snippet(f'<link rel="stylesheet" href="{ASSET_BASE}/okr-seminar.css?v={ASSET_VER}">')

# 1) Kontaktformular (JotForm-POST -> vc_column_text, minify)
html_kf = (ROOT / "src" / "danke" / "danke-kontaktformular.html").read_text(encoding="utf-8")
out_kf = css_link + row(f"[vc_column_text]{minify(html_kf)}[/vc_column_text]")
(ROOT / "page-danke-kontaktformular.txt").write_text(out_kf, encoding="utf-8")
print(f"OK: page-danke-kontaktformular.txt ({len(out_kf)} Bytes)")

# 2) Kontakttermin (Calendly-GET -> vc_raw_html, Script bleibt erhalten)
html_kt = (ROOT / "src" / "danke" / "danke-kontakttermin.html").read_text(encoding="utf-8").strip()
out_kt = css_link + row(raw_snippet(html_kt))
(ROOT / "page-danke-kontakttermin.txt").write_text(out_kt, encoding="utf-8")
print(f"OK: page-danke-kontakttermin.txt ({len(out_kt)} Bytes)")
