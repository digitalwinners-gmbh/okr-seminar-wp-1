#!/usr/bin/env python3
"""
Baut die drei Danke-Seiten als WPBakery/Bridge-Shortcode-Dateien:

  src/danke/danke-buchung.html  → page-danke-buchung.txt
  src/danke/danke-anfrage.html  → page-danke-anfrage.txt
  src/danke/danke-inhouse.html  → page-danke-inhouse.txt
  src/danke/danke-kontakt.html  → page-danke-kontakt.txt

WICHTIG: Der Inhalt kommt in [vc_column_text] (NICHT vc_raw_html), damit
WordPress die [jotform-display]-Shortcodes ausführt. Damit wpautop das
HTML nicht zerlegt, wird alles auf EINE Zeile minifiziert.

Aufruf:  python3 build_danke.py
Danach:  Inhalt der page-danke-*.txt in die jeweilige WordPress-Seite
         kopieren (Classic-Editor Text-Modus bzw. WPBakery-Backend).
"""
import base64
import re
import urllib.parse
from pathlib import Path

ROOT = Path(__file__).parent
ASSET_BASE = "https://okrexperten.de/wp-content/uploads/okr-seminar"

# ASSET_VER aus build.py übernehmen (eine Quelle für die Versionsnummer)
ASSET_VER = re.search(r'ASSET_VER = "(\d+)"', (ROOT / "build.py").read_text()).group(1)


def raw_snippet(html: str) -> str:
    encoded = base64.b64encode(urllib.parse.quote(html, safe="").encode("ascii")).decode("ascii")
    return f"[vc_raw_html]{encoded}[/vc_raw_html]"


def minify(html: str) -> str:
    """HTML auf eine Zeile bringen: Kommentare raus, Whitespace kollabieren."""
    html = re.sub(r"<!--.*?-->", "", html, flags=re.S)
    html = re.sub(r"\s+", " ", html)
    html = re.sub(r">\s+<", "><", html)
    return html.strip()


def row(content: str, bg: str = "#f7f4ed") -> str:
    return (
        f'[vc_row css_animation="" row_type="row" use_row_as_full_screen_section="no" '
        f'type="full_width" angled_section="no" text_align="left" '
        f'background_image_as_pattern="without_pattern" z_index="" '
        f'background_color="{bg}" el_class="okrs-row"]'
        f"[vc_column]{content}[/vc_column][/vc_row]"
    )


for src_name, out_name in [
    ("danke-buchung.html", "page-danke-buchung.txt"),
    ("danke-anfrage.html", "page-danke-anfrage.txt"),
    ("danke-inhouse.html", "page-danke-inhouse.txt"),
    ("danke-kontakt.html", "page-danke-kontakt.txt"),
]:
    html = (ROOT / "src" / "danke" / src_name).read_text(encoding="utf-8")
    content = (
        raw_snippet(f'<link rel="stylesheet" href="{ASSET_BASE}/okr-seminar.css?v={ASSET_VER}">')
        + row(f"[vc_column_text]{minify(html)}[/vc_column_text]")
    )
    out = ROOT / out_name
    out.write_text(content, encoding="utf-8")
    print(f"OK: {out_name} ({out.stat().st_size} Bytes)")
