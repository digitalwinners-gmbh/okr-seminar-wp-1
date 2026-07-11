#!/usr/bin/env python3
"""
Baut page-content.txt (WPBakery/Bridge-Shortcodes) aus den HTML-Sektionen
in src/sections/.

Die HTML-Sektionen werden als [vc_raw_html] eingebettet (base64 + rawurlencode,
so wie WPBakery es speichert). Dynamische Shortcodes ([seminar5],
[sp_easyaccordion], [wpseo_breadcrumb]) bleiben als vc_column_text erhalten.

Aufruf:  python3 build.py
Ausgabe: page-content.txt  →  Inhalt komplett in den WordPress-Editor
         (Classic/Text-Modus bzw. WPBakery-Backend) kopieren.
"""
import base64
import urllib.parse
from pathlib import Path

ROOT = Path(__file__).parent
SECTIONS = ROOT / "src" / "sections"

# Basis-URL, unter der der Ordner upload/okr-seminar/ auf dem Server liegt
ASSET_BASE = "https://okrexperten.de/wp-content/uploads/okr-seminar"

# Bei Änderungen an CSS/JS hochzählen — umgeht Cloudflare-/Browser-Cache
ASSET_VER = "18"


def raw_html(name: str) -> str:
    """HTML-Sektion als [vc_raw_html]-Shortcode (rawurlencode + base64)."""
    html = (SECTIONS / name).read_text(encoding="utf-8").strip()
    encoded = base64.b64encode(
        urllib.parse.quote(html, safe="").encode("ascii")
    ).decode("ascii")
    return f"[vc_raw_html]{encoded}[/vc_raw_html]"


def row(content: str, bg: str = "#f7f4ed", el_id: str = "", el_class: str = "") -> str:
    """Bridge-Row (full width) mit Hintergrundfarbe."""
    id_attr = f' el_id="{el_id}"' if el_id else ""
    cls = f"okrs-row {el_class}".strip()
    return (
        f'[vc_row css_animation="" row_type="row" use_row_as_full_screen_section="no" '
        f'type="full_width" angled_section="no" text_align="left" '
        f'background_image_as_pattern="without_pattern" z_index="" '
        f'background_color="{bg}"{id_attr} el_class="{cls}"]'
        f"[vc_column]{content}[/vc_column][/vc_row]"
    )


def raw_snippet(html: str) -> str:
    """Kleines Inline-HTML als [vc_raw_html]-Shortcode."""
    encoded = base64.b64encode(
        urllib.parse.quote(html, safe="").encode("ascii")
    ).decode("ascii")
    return f"[vc_raw_html]{encoded}[/vc_raw_html]"


parts = [
    # CSS einbinden (ganz oben)
    raw_snippet(f'<link rel="stylesheet" href="{ASSET_BASE}/okr-seminar.css?v={ASSET_VER}">'),

    # 1) Breadcrumb + Hero/Body-Grid (Überblick, Outcomes, Agenda, Sidebar)
    row(
        '[vc_empty_space height="32px"]'
        '[vc_empty_space height="24px"]'
        '[vc_column_text el_class="yoast_bremadcrumb okrs-wrap"][wpseo_breadcrumb][/vc_column_text]'
        + raw_html("01-hero.html"),
        bg="#f7f4ed",
    ),

    # 2) Vorteile / Benefits-Slider
    row(raw_html("02-benefits.html"), bg="#fbf9f3", el_id="vorteile"),

    # 3) Trainer (dunkel)
    row(raw_html("03-trainer.html"), bg="#1c1813", el_id="trainer"),

    # 4) Zertifikat & Badge
    row(raw_html("04-zertifikat.html"), bg="#f7f4ed"),

    # 5) Zielgruppe
    row(raw_html("05-zielgruppe.html"), bg="#fbf9f3"),

    # 6) Termine (dunkel): Intro + [seminar5] + Inhouse/Rabatt
    row(
        raw_html("06-termine-intro.html")
        + '[vc_column_text el_class="okrs-seminar-list"]'
        '[seminar6 type="3-D-OKR"]'
        '[seminar6_data type="3-D-OKR"]'
        "[/vc_column_text]"
        + raw_html("07-termine-outro.html"),
        bg="#1c1813",
        el_id="termine",
    ),

    # 7) Testimonials + Referenzen
    row(raw_html("08-testimonials.html"), bg="#f7f4ed"),

    # 8) Über uns
    row(raw_html("09-ueber-uns.html"), bg="#fbf9f3"),

    # 9) Kontakt (Jotform-Platzhalter + Ansprechpartnerin)
    row(raw_html("10-kontakt.html"), bg="#f7f4ed", el_id="kontakt"),

    # 10) FAQ: Kopf + Accordion (dynamisch) + „weitere Fragen“-Accordion
    row(
        raw_html("11-faq-head.html")
        + '[vc_column_text el_class="okrs-faq-acc"][sp_easyaccordion id="13796"][/vc_column_text]'
        + raw_html("12-faq-more.html")
        + '[vc_column_text el_class="okrs-faq-acc okrs-faq2"][sp_easyaccordion id="16052"][/vc_column_text]'
        + '[vc_empty_space height="48px"]',
        bg="#f7f4ed",
        el_id="faq",
    ),

    # JS einbinden (ganz unten)
    raw_snippet(f'<script src="{ASSET_BASE}/okr-seminar.js?v={ASSET_VER}" defer></script>'),
]

# FAQ: der Toggle-Button muss VOR dem zweiten Accordion stehen, das Accordion
# selbst wird über die Klasse .okrs-faq2 ein-/ausgeblendet (siehe okr-seminar.js).

out = ROOT / "page-content.txt"
out.write_text("".join(parts), encoding="utf-8")
print(f"OK: {out} geschrieben ({out.stat().st_size} Bytes)")
