/* OKR Seminar – Interaktionen (Agenda-Tabs, Sidebar-Tabs, Awards-Karussell,
   Benefits-Slider, Teilen-Button, Toggles). Vanilla JS, keine Abhängigkeiten. */
(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState !== 'loading') { fn(); }
    else { document.addEventListener('DOMContentLoaded', fn); }
  }

  ready(function () {

    /* ---------- Agenda-Tabs (Tag 1–3) ---------- */
    var agendaTabs = document.querySelectorAll('.okrs-agenda-tab');
    agendaTabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        var target = tab.getAttribute('data-day');
        agendaTabs.forEach(function (t) { t.classList.toggle('is-active', t === tab); });
        document.querySelectorAll('.okrs-agenda-day').forEach(function (d) {
          d.classList.toggle('is-active', d.getAttribute('data-day') === target);
        });
      });
    });

    /* ---------- Sidebar-Tabs (Präsenz / Live-Online / Inhouse) ----------
       Nur Umschalten der Panes — der Termin-Filter wird bewusst NICHT
       mitgeschaltet; das machen ausschließlich die CTA-Buttons darunter. */
    var selectFilter; // wird im Termin-Filter-Block zugewiesen
    var sideTabs = document.querySelectorAll('.okrs-side-tab');
    sideTabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        var target = tab.getAttribute('data-pane');
        sideTabs.forEach(function (t) { t.classList.toggle('is-active', t === tab); });
        document.querySelectorAll('.okrs-side-pane').forEach(function (p) {
          p.classList.toggle('is-active', p.getAttribute('data-pane') === target);
        });
      });
    });

    /* CTA-Buttons in den Sidebar-Tabs („Präsenz-Termine" / „Online-Termine"):
       setzen beim Klick den passenden Termin-Filter, bevor der Anker
       zu #termine scrollt — wichtig für den ersten Klick ohne Tab-Wechsel. */
    document.querySelectorAll('.okrs-side-pane .okrs-side-cta[href="#termine"]').forEach(function (cta) {
      cta.addEventListener('click', function () {
        var pane = cta.closest('.okrs-side-pane');
        if (pane && typeof selectFilter === 'function') {
          selectFilter(pane.getAttribute('data-pane'));
        }
      });
    });

    /* ---------- Awards-Karussell (Auto-Advance + Pfeile + Dots) ---------- */
    var awardTrack = document.querySelector('.okrs-awards-track');
    if (awardTrack) {
      var slides = awardTrack.children.length;
      var idx = 0;
      var timer = null;

      var apply = function () {
        awardTrack.style.transform = 'translateX(-' + (idx * 100) + '%)';
        document.querySelectorAll('.okrs-awards-dot').forEach(function (d, i) {
          d.classList.toggle('is-active', i === idx);
        });
      };
      var start = function () {
        clearInterval(timer);
        timer = setInterval(function () { idx = (idx + 1) % slides; apply(); }, 5000);
      };
      var go = function (i) { idx = ((i % slides) + slides) % slides; apply(); start(); };

      var prev = document.querySelector('.okrs-awards-btn--prev');
      var next = document.querySelector('.okrs-awards-btn--next');
      if (prev) prev.addEventListener('click', function () { go(idx - 1); });
      if (next) next.addEventListener('click', function () { go(idx + 1); });
      document.querySelectorAll('.okrs-awards-dot').forEach(function (d, i) {
        d.addEventListener('click', function () { go(i); });
      });

      apply();
      start();
    }

    /* ---------- Horizontale Slider (Benefits + Bewertungen) ----------
       scrollBy({behavior:'smooth'}) wird von scroll-snap teils ignoriert,
       daher eigene rAF-Animation. */
    var animateScroll = function (el, delta) {
      var start = el.scrollLeft;
      var target = Math.max(0, Math.min(el.scrollWidth - el.clientWidth, start + delta));
      // scroll-snap schnappt Zwischenpositionen sofort zurück → währenddessen aus
      el.style.scrollSnapType = 'none';
      var done = false;
      var finish = function () {
        if (done) return;
        done = true;
        el.scrollLeft = target;
        el.style.scrollSnapType = '';
      };
      var t0 = performance.now();
      var step = function (now) {
        if (done) return;
        var p = Math.min(1, (now - t0) / 320);
        var e = 1 - Math.pow(1 - p, 3);
        el.scrollLeft = start + (target - start) * e;
        if (p < 1) { requestAnimationFrame(step); }
        else { finish(); }
      };
      requestAnimationFrame(step);
      // Fallback: falls rAF gedrosselt ist (Hintergrund-Tab), direkt springen
      setTimeout(finish, 400);
    };
    var bindSlider = function (trackId, prevSel, nextSel, cardSel, gap) {
      var track = document.getElementById(trackId);
      if (!track) return;
      var stepSize = function () {
        var card = track.querySelector(cardSel);
        return card ? card.getBoundingClientRect().width + gap : 320;
      };
      var prev = document.querySelector(prevSel);
      var next = document.querySelector(nextSel);
      if (prev) prev.addEventListener('click', function () { animateScroll(track, -stepSize()); });
      if (next) next.addEventListener('click', function () { animateScroll(track, stepSize()); });
    };
    bindSlider('okrs-bn-track', '.okrs-bn-btn--prev', '.okrs-bn-btn--next', '.okrs-bn-card', 16);
    bindSlider('okrs-t-track', '.okrs-t-btn--prev', '.okrs-t-btn--next', '.okrs-t-card', 18);

    /* ---------- Teilen-Button ---------- */
    var shareBtn = document.getElementById('okrs-share');
    if (shareBtn) {
      shareBtn.addEventListener('click', function () {
        var url = window.location.href.split('#')[0];
        var data = {
          title: 'OKR Coach & Master Seminar',
          text: 'Werde in 3 Tagen zertifizierter OKR Coach, Master & Champion.',
          url: url
        };
        if (navigator.share) { navigator.share(data).catch(function () {}); return; }
        if (navigator.clipboard) {
          navigator.clipboard.writeText(url).then(function () {
            var label = shareBtn.querySelector('span');
            if (!label) return;
            var old = label.textContent;
            label.textContent = 'Link kopiert!';
            setTimeout(function () { label.textContent = old; }, 2200);
          }).catch(function () {});
        }
      });
    }

    /* ---------- Termin-Filter (Alle / Präsenz / Live-Online / Inhouse) ----------
       [seminar5] rendert pro Termin eine Row id="3D_n" plus eine Detail-Row
       id="3D_REM_SCHEMA_n" (Live-Online) bzw. "3D_PRES_SCHEMA_n" (Präsenz) —
       das Format steckt also in der ID. Zahlen in den Buttons werden zur
       Laufzeit aus der tatsächlichen Terminliste befüllt. */
    var filterBar = document.querySelector('.okrs-filterbar');
    var seminarCard = document.querySelector('.okrs-seminar-list, .okrs-seminar-card');
    if (filterBar && seminarCard) {
      var entries = []; // { format: 'praesenz'|'online', rows: [mainRow, schemaRow] }
      seminarCard.querySelectorAll('[id^="3D_"]').forEach(function (el) {
        var m = el.id.match(/^3D_(REM|PRES)_SCHEMA_(\d+)$/);
        if (!m) return;
        // '#3D_1' wäre ungültig (ID beginnt mit Ziffer) → Attribut-Selektor
        var main = seminarCard.querySelector('[id="3D_' + m[2] + '"]');
        if (!main) return;
        entries.push({
          format: m[1] === 'REM' ? 'online' : 'praesenz',
          rows: [main, el]
        });
      });

      var counts = {
        alle: entries.length,
        praesenz: entries.filter(function (e) { return e.format === 'praesenz'; }).length,
        online: entries.filter(function (e) { return e.format === 'online'; }).length
      };
      filterBar.querySelectorAll('[data-count]').forEach(function (span) {
        var n = counts[span.getAttribute('data-count')];
        span.textContent = n > 0 || counts.alle > 0 ? '(' + n + ')' : '';
      });

      var label = document.querySelector('[data-filter-label]');
      var banners = document.querySelectorAll('.okrs-special-banner, .okrs-reserve-banner');
      var applyFilter = function (key) {
        var inhouse = key === 'inhouse';
        seminarCard.classList.toggle('okrs-filter-hidden', inhouse);
        banners.forEach(function (b) { b.classList.toggle('okrs-filter-hidden', inhouse); });
        entries.forEach(function (e) {
          var hide = !inhouse && key !== 'alle' && e.format !== key;
          e.rows.forEach(function (r) { r.classList.toggle('okrs-filter-hidden', hide); });
        });
        if (label) {
          if (inhouse) {
            label.textContent = 'Inhouse-Training – flexibel für dein Team, vor Ort oder online, Deutsch oder Englisch.';
          } else {
            var n = key === 'alle' ? counts.alle : counts[key];
            label.textContent = entries.length ? n + (n === 1 ? ' Termin' : ' Termine') : '';
          }
        }
      };

      // Zentrale Auswahl: Button aktivieren + Filter anwenden.
      // Wird auch von den Sidebar-Tabs aufgerufen (Präsenz/Online/Inhouse).
      selectFilter = function (key) {
        var target = filterBar.querySelector('[data-filter="' + key + '"]');
        if (!target) return;
        filterBar.querySelectorAll('.okrs-filter-btn').forEach(function (b) {
          b.classList.toggle('is-active', b === target);
        });
        applyFilter(key);
      };

      filterBar.querySelectorAll('.okrs-filter-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
          selectFilter(btn.getAttribute('data-filter'));
        });
      });
      applyFilter('alle');
    }

    /* ---------- Termin-Details auf-/zuklappen (neue Liste, layout="okrs") ---------- */
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('.okrs-date-toggle');
      if (!btn) return;
      var row = btn.closest('.okrs-date-row');
      if (!row) return;
      var open = row.classList.toggle('is-open');
      var chev = btn.querySelector('.okrs-date-chev');
      if (chev) chev.textContent = open ? '▴' : '▾';
    });

    /* ---------- Dynamische Kennzahlen ([seminar5_data] → Sidebar) ----------
       Das Plugin gibt window.okrsSeminarData aus; alle Elemente mit
       data-okrs-info="key" bekommen den Live-Wert. Ohne Plugin-Daten bleibt
       der statische Fallback-Text im HTML stehen. */
    if (window.okrsSeminarData) {
      document.querySelectorAll('[data-okrs-info]').forEach(function (el) {
        var v = window.okrsSeminarData[el.getAttribute('data-okrs-info')];
        if (typeof v === 'string' && v !== '') el.textContent = v;
      });
    }

    /* ---------- Generische Toggles (Zielgruppe im Detail, weitere FAQs) ---------- */
    document.querySelectorAll('[data-okrs-toggle]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var sel = btn.getAttribute('data-okrs-toggle');
        var el = document.querySelector(sel);
        if (!el) return;
        var open = el.classList.toggle('is-open');
        var openLabel = btn.getAttribute('data-label-open');
        var closeLabel = btn.getAttribute('data-label-close');
        if (openLabel && closeLabel) {
          btn.textContent = open ? closeLabel : openLabel;
        }
      });
    });

  });
})();
