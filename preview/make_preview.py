#!/usr/bin/env python3
"""
Erzeugt preview/index.html – eine lokale Vorschau der Seite (ohne WordPress).
Dynamische Shortcodes ([seminar5], [sp_easyaccordion], Breadcrumb, Jotform)
werden durch einfache Platzhalter ersetzt. Asset-URLs zeigen auf die lokalen
Dateien in upload/okr-seminar/.

Aufruf: python3 preview/make_preview.py
"""
from pathlib import Path

ROOT = Path(__file__).parent.parent
SECTIONS = ROOT / "src" / "sections"
REMOTE = "https://okrexperten.de/wp-content/uploads/okr-seminar"


def sec(name: str) -> str:
    return (SECTIONS / name).read_text(encoding="utf-8")


def wrap_row(content: str, bg: str, el_id: str = "") -> str:
    idattr = f' id="{el_id}"' if el_id else ""
    return f'<div class="okrs-row" style="background:{bg}"{idattr}>{content}</div>'


SEMINAR5_STUB = """
<div class="okrs"><div class="okrs-seminar-card" style="padding:26px 22px">
  <p style="margin:0;font-weight:700">[seminar5 type="3-D-OKR" display="TYPE-HTML-SCHEMA"]</p>
  <p style="margin:8px 0 0;color:#6a635a">→ Hier rendert WordPress die dynamische Terminliste (Platzhalter in der Vorschau).</p>
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
    wrap_row(sec("06-termine-intro.html") + SEMINAR5_STUB + sec("07-termine-outro.html"), "#1c1813", "termine"),
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

html = f"""<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Vorschau – OKR Coach/Master Seminar (WPBakery-Umsetzung)</title>
<link rel="stylesheet" href="../upload/okr-seminar/okr-seminar.css">
<style>body{{margin:0;background:#f7f4ed}}</style>
</head>
<body>
{body}
<script src="../upload/okr-seminar/okr-seminar.js"></script>
</body>
</html>"""

out = ROOT / "preview" / "index.html"
out.write_text(html, encoding="utf-8")
print(f"OK: {out}")
