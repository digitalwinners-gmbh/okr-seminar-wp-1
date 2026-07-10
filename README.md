# OKR Seminar – WPBakery/Bridge-Umsetzung

Umsetzung des Claude-Designs (`OKR Coach Master Seminar.html`) als
WPBakery-Seite für das Bridge-Theme auf okrexperten.de — **ohne Menü und
ohne Footer** (kommen vom Theme).

## Dateien

| Pfad | Zweck |
|---|---|
| `page-content.txt` | Fertiger WPBakery-Shortcode-Inhalt → komplett in den WordPress-Editor kopieren |
| `upload/okr-seminar/` | CSS, JS, Fonts, Bilder → **1:1 per FTP nach `wp-content/uploads/okr-seminar/` hochladen** |
| `src/sections/*.html` | Lesbare HTML-Quellen der Sektionen (zum Bearbeiten) |
| `build.py` | Baut `page-content.txt` neu aus den Sektionen (`python3 build.py`) |
| `preview/` | Lokale Vorschau ohne WordPress (`python3 preview/make_preview.py`, dann `preview/index.html` öffnen) |
| `extracted.html`, `asset-*.js`, `assets/` | Entpacktes Original-Design (Referenz) |

## Einbau in WordPress

1. **Assets hochladen:** Ordner `upload/okr-seminar/` per FTP nach
   `wp-content/uploads/okr-seminar/` (CSS, JS, `fonts/`, `images/`).
   Die Fonts (Archivo, Hanken Grotesk, Instrument Serif) sind selbst
   gehostet → DSGVO-konform, kein Google-Fonts-CDN.
2. **Seiteninhalt einfügen:** Inhalt von `page-content.txt` in den
   Seiten-Editor kopieren (Classic-Editor im Text-Modus bzw.
   WPBakery-Backend-Editor). CSS/JS werden über `vc_raw_html`-Blöcke am
   Anfang/Ende der Seite geladen — kein Eingriff ins Theme nötig.
3. **Jotform einbinden:** In `src/sections/10-kontakt.html` ist ein
   markierter Platzhalter (`JOTFORM-EMBED HIER EINFÜGEN`). Embed-Code
   einsetzen, dann `python3 build.py` und Inhalt neu einfügen — oder das
   Embed direkt im WPBakery-Editor in den Kontakt-Block setzen.
4. **Seiteneinstellungen (Bridge):** Seitentemplate ggf. „Full Width“;
   Titel-/Breadcrumb-Bereich des Themes für diese Seite deaktivieren,
   da der Breadcrumb (`[wpseo_breadcrumb]`) bereits im Inhalt steckt.

## Dynamisch vs. statisch

- **Dynamisch (Shortcodes bleiben):**
  - Termine: `[seminar5 type="3-D-OKR" display="TYPE-HTML-SCHEMA"]` –
    liegt in einer hellen Karte innerhalb der dunklen Termine-Sektion
    (Klasse `okrs-seminar-card`), damit das bestehende Markup lesbar bleibt.
  - FAQ: `[sp_easyaccordion id="13796"]` + `[sp_easyaccordion id="16052"]`
    (zweites Accordion per „Weitere Fragen anzeigen“ einblendbar).
  - Breadcrumb: `[wpseo_breadcrumb]`.
- **Statisch (HTML im neuen Design):** Hero, Stats, Überblick, Outcomes,
  Agenda-Tabs (ersetzt `[wptabs id="13792"]`), Buchungs-Sidebar,
  Awards-Karussell, Vorteile-Slider (ersetzt `[smartslider3 slider="3"]`),
  Trainer, Zertifikat, Zielgruppe (Chips + ausklappbare Detail-Liste für
  SEO), Testimonials (ersetzt `[rt-testimonial id="6369"]`),
  Referenzen-Logos (Bild statt `[insert page='snippet-okr-referenzen']`),
  Über uns, Kontakt.

## Bewusst weggelassen / zu prüfen

- **Vimeo-Video** (Borlabs-Embed der alten Seite): im Claude-Design nicht
  vorhanden. Bei Bedarf als `[borlabs-cookie …]`-Block z. B. nach der
  Trainer-Sektion einfügen.
- **Preise/Terminanzahl in der Sidebar** („ab 1.691,50 €“, „6 Termine“)
  sind statisch — bei Preisänderungen in `src/sections/01-hero.html`
  anpassen und neu bauen.
- Das Kontaktformular-Design aus dem Claude-Entwurf war eine Attrappe;
  stattdessen kommt das Jotform-Embed (siehe oben).

## Workflow für Änderungen

```bash
# 1. Sektion in src/sections/ bearbeiten
# 2. Shortcode-Datei neu bauen:
python3 build.py
# 3. Lokale Vorschau aktualisieren (optional):
python3 preview/make_preview.py
# 4. page-content.txt neu in WordPress einfügen
```
