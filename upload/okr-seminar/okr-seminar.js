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

    /* ---------- Sidebar-Tabs (Präsenz / Live-Online / Inhouse) ---------- */
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
