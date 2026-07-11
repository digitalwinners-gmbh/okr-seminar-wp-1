#!/usr/bin/env python3
"""
Erzeugt preview/index.html – eine lokale Vorschau der Seite (ohne WordPress).
Dynamische Shortcodes ([seminar5], [sp_easyaccordion], Breadcrumb, Jotform)
werden durch einfache Platzhalter ersetzt. Asset-URLs zeigen auf die lokalen
Dateien in upload/okr-seminar/.

Aufruf: python3 preview/make_preview.py
"""
import subprocess
import time
from pathlib import Path

ROOT = Path(__file__).parent.parent
SECTIONS = ROOT / "src" / "sections"
REMOTE = "https://okrexperten.de/wp-content/uploads/okr-seminar"


def sec(name: str) -> str:
    return (SECTIONS / name).read_text(encoding="utf-8")


def wrap_row(content: str, bg: str, el_id: str = "") -> str:
    idattr = f' id="{el_id}"' if el_id else ""
    return f'<div class="okrs-row" style="background:{bg}"{idattr}>{content}</div>'


def stub_row(n: int, fmt: str, city: str) -> str:
    """Bildet die echte seminar5-Struktur nach: Haupt-Row 3D_n + Schema-Row."""
    schema = "REM" if fmt == "online" else "PRES"
    return f"""
  <div class="vc_row wpb_row section termine" id="3D_{n}" style="border-bottom:1px solid #ece5d6;padding:14px 0">
    <strong>{city}</strong> – OKR Coach/Master Seminar ({'Live-Online' if fmt == 'online' else 'Präsenz'})
  </div>
  <div class="vc_row wpb_row section" id="3D_{schema}_SCHEMA_{n}" style="padding:6px 0;color:#8a8275;font-size:13px">
    Detail-Row zu Termin {n} (3D_{schema}_SCHEMA_{n})
  </div>"""


def seminar5_render() -> str:
    """Echte Plugin-Ausgabe via PHP-CLI (Harness), sonst Stub."""
    try:
        out = subprocess.run(
            ["php", "-d", "error_reporting=0", str(ROOT / "preview" / "render_seminar6.php")],
            capture_output=True, text=True, timeout=30,
        )
        if out.returncode == 0 and "okrs-date-row" in out.stdout:
            return f'<div class="okrs-seminar-list">{out.stdout}</div>'
    except Exception:
        pass
    return SEMINAR5_STUB


SEMINAR5_STUB = f"""
<div class="okrs"><div class="okrs-seminar-list">
  <p style="margin:0 0 8px;font-weight:700;color:#a89f8c">[seminar6] – Stub (PHP nicht verfügbar)</p>
  {stub_row(1, 'online', 'Live-Online 14.09.')}
  {stub_row(2, 'praesenz', 'Frankfurt 30.09.')}
  {stub_row(3, 'online', 'Live-Online 07.10.')}
  {stub_row(4, 'praesenz', 'Berlin 13.10.')}
  {stub_row(5, 'praesenz', 'Zürich 29.10.')}
</div></div>
"""

FAQ_STUB = """
<div class="okrs"><div class="okrs-faq-acc">
  <div style="background:#fff;border:1px solid #ece5d6;border-radius:14px;padding:20px 22px;margin-bottom:12px;font-weight:700">Brauche ich Vorkenntnisse? <span style="float:right;color:#a9863c">+</span></div>
  <div style="background:#fff;border:1px solid #ece5d6;border-radius:14px;padding:20px 22px;margin-bottom:12px;font-weight:700">Wie erhalte ich mein Zertifikat? <span style="float:right;color:#a9863c">+</span></div>
  <div style="background:#fff;border:1px solid #ece5d6;border-radius:14px;padding:20px 22px;font-weight:700">Gibt es Rabatte? <span style="float:right;color:#a9863c">+</span></div>
  <p style="color:#8a8275;font-size:13px;margin-top:10px">→ [sp_easyaccordion id="13796"] – Platzhalter in der Vorschau.</p>
</div></div>
"""

FAQ2_STUB = """
<div class="okrs"><div class="okrs-faq-acc okrs-faq2">
  <div style="background:#fff;border:1px solid #ece5d6;border-radius:14px;padding:20px 22px;font-weight:700">Weitere Frage (Accordion 2) <span style="float:right;color:#a9863c">+</span></div>
  <p style="color:#8a8275;font-size:13px;margin-top:10px">→ [sp_easyaccordion id="16052"] – Platzhalter in der Vorschau.</p>
</div></div>
"""

body = "".join([
    wrap_row('<div class="okrs"><div class="okrs-wrap" style="padding-top:24px;font-size:13px;color:#8a8275">Startseite » OKR Training » OKR Seminar » <strong>OKR Coach &amp; Master</strong> <span style="opacity:.6">([wpseo_breadcrumb]-Platzhalter)</span></div></div>' + sec("01-hero.html"), "#f7f4ed"),
    wrap_row(sec("02-benefits.html"), "#fbf9f3", "vorteile"),
    wrap_row(sec("03-trainer.html"), "#1c1813", "trainer"),
    wrap_row(sec("04-zertifikat.html"), "#f7f4ed"),
    wrap_row(sec("05-zielgruppe.html"), "#fbf9f3"),
    wrap_row(sec("06-termine-intro.html") + seminar5_render() + sec("07-termine-outro.html"), "#1c1813", "termine"),
    wrap_row(sec("08-testimonials.html"), "#f7f4ed"),
    wrap_row(sec("09-ueber-uns.html"), "#fbf9f3"),
    wrap_row(sec("10-kontakt.html"), "#f7f4ed", "kontakt"),
    wrap_row(sec("11-faq-head.html") + FAQ_STUB + sec("12-faq-more.html") + FAQ2_STUB + '<div style="height:48px"></div>', "#f7f4ed", "faq"),
])

# Lokale Assets statt Live-URLs
body = body.replace(REMOTE + "/images/", "../upload/okr-seminar/images/")
# Jotform-Platzhalter sichtbar machen
body = body.replace(
    "<!-- ============================================================",
    '<div style="border:2px dashed #d8cfbb;border-radius:14px;min-height:420px;display:flex;align-items:center;justify-content:center;color:#8a8275;font-weight:600">Jotform-Embed (Platzhalter)</div><!--',
)

ts = int(time.time())
html = f"""<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Vorschau – OKR Coach/Master Seminar (WPBakery-Umsetzung)</title>
<link rel="stylesheet" href="../upload/okr-seminar/okr-seminar.css?t={ts}">
<style>body{{margin:0;background:#f7f4ed}}</style>
</head>
<body>
{body}
<script src="../upload/okr-seminar/okr-seminar.js?t={ts}"></script>
</body>
</html>"""

out = ROOT / "preview" / "index.html"
out.write_text(html, encoding="utf-8")
print(f"OK: {out}")
