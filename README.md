# OKR Seminar – WPBakery/Bridge-Umsetzung

Umsetzung des Claude-Designs (`OKR Coach Master Seminar.html`) als
WPBakery-Seite für das Bridge-Theme auf okrexperten.de — **ohne Menü und
ohne Footer** (kommen vom Theme).

## Dateien

| Pfad | Zweck |
|---|---|
| `page-content.txt` | Fertiger WPBakery-Shortcode-Inhalt → komplett in den WordPress-Editor kopieren |
| `upload/okr-seminar/` | CSS, JS, Fonts, Bilder → **1:1 per FTP nach `wp-content/uploads/okr-seminar/` hochladen** |
| `plugin/seminar6-shortcode/` | **Neues Plugin** → nach `wp-content/plugins/seminar6-shortcode/` (läuft parallel zu seminar5) |
| `plugin-orig/` | Original-Plugin v1 + seminarinfos3.ini (Referenz, unverändert) |
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

## Plugin seminar6-shortcode

Eigenständiges Plugin für die neue Terminliste — läuft **parallel** zum
alten "Seminar Shortcode 5", das für alle anderen Seminarseiten
unverändert weiterläuft. Kein HTTP-Self-Request auf die Snippet-Seite:

- `[seminar6 type="3-D-OKR"]` —
  neues Layout aus `templates/okrs-item.html`, inkl. schema.org **JSON-LD**
  pro Termin. IDs bleiben `3D_n` / `3D_REM|PRES_SCHEMA_n` → Filter-JS
  funktioniert unverändert.
- `[seminar6_info type="3-D-OKR" info="…"]` — einzelner Wert als Text.
  Keys: `min-price-praesenz`, `min-price-online`, `old-price-praesenz`,
  `old-price-online`, `count-all`, `count-praesenz`, `count-online`,
  `termine-praesenz` („4 Termine“), `termine-online`, `count-cities`,
  `orte-praesenz` („an 4 Orten“), `next-date`.
- `[seminar6_data type="3-D-OKR"]` — gibt `window.okrsSeminarData` aus;
  `okr-seminar.js` füllt damit alle Elemente mit `data-okrs-info="key"`
  (z. B. „ab“-Preise und Terminanzahl in der Buchungs-Sidebar). Ohne
  Plugin bleibt der statische Fallback-Text stehen.
- Preis-/Rabattlogik (EarlyBird 10/15 %, Summer Special, Zürich/Wien/
  Luzern-Sonderfälle) entspricht v1; Datenquelle bleibt
  `private_html/seminarinfos3.ini`.

Lokal testen (ohne WordPress): `php preview/render_seminar6.php`

**Reihenfolge beim Deployment:** erst Plugin seminar6-shortcode
installieren & aktivieren, dann `page-content.txt` einfügen (sonst
bleibt der [seminar6]-Shortcode als Text sichtbar).

## Dynamisch vs. statisch

- **Dynamisch (Shortcodes bleiben):**
  - Termine: `[seminar6 type="3-D-OKR"]` + `[seminar6_data]` –
    neues Plugin seminar6-shortcode (siehe oben).
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
- **Preise/Terminanzahl in der Sidebar** kommen live aus
  `[seminar6_data]` (data-okrs-info-Platzhalter); die statischen Texte in
  `src/sections/01-hero.html` sind nur Fallback, falls das Plugin fehlt.
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
