/*!
 * Tschudnow Profi Service – Entrümpelung, Haushaltsauflösung & Bodenleger
 * Minimales Vanilla-JavaScript ohne Abhängigkeiten:
 *   1. Aktuelles Jahr im Footer
 *   2. Header-Schatten beim Scrollen
 *   3. Mobile Navigation (Hamburger)
 *   4. Aktiven Abschnitt in der Navigation hervorheben
 *   5. Anliegen im Formular vorauswählen
 *   6. Clientseitige Formular-Validierung + vorausgefüllte E-Mail (mailto:)
 *      – es gibt kein Backend, über die Website wird nichts übertragen
 * Alle Funktionen prüfen, ob die benötigten Elemente existieren,
 * und laufen daher auf jeder Unterseite fehlerfrei.
 */
(function () {
  'use strict';

  var doc = document;
  var win = window;

  doc.documentElement.classList.add('js');

  var forEach = function (list, fn) {
    Array.prototype.forEach.call(list, fn);
  };

  var mq = function (query) {
    return win.matchMedia ? win.matchMedia(query) : null;
  };

  var onMqChange = function (mql, fn) {
    if (!mql) return;
    if (mql.addEventListener) mql.addEventListener('change', fn);
    else if (mql.addListener) mql.addListener(fn);
  };

  /* 1. Aktuelles Jahr ------------------------------------------------------ */
  var year = String(new Date().getFullYear());
  forEach(doc.querySelectorAll('[data-year]'), function (el) {
    el.textContent = year;
  });

  /* 2. Header-Schatten ----------------------------------------------------- */
  var header = doc.querySelector('.site-header');
  if (header) {
    var ticking = false;
    var updateHeader = function () {
      header.classList.toggle('is-scrolled', (win.scrollY || win.pageYOffset) > 8);
      ticking = false;
    };
    win.addEventListener('scroll', function () {
      if (!ticking) {
        ticking = true;
        win.requestAnimationFrame(updateHeader);
      }
    }, { passive: true });
    updateHeader();
  }

  /* 3. Mobile Navigation --------------------------------------------------- */
  var toggle = doc.querySelector('.nav-toggle');
  var menu = toggle ? doc.getElementById(toggle.getAttribute('aria-controls')) : null;

  if (toggle && menu) {
    var isOpen = function () {
      return toggle.getAttribute('aria-expanded') === 'true';
    };

    var setOpen = function (open, returnFocus) {
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      menu.classList.toggle('is-open', open);
      doc.body.classList.toggle('nav-open', open);
      if (!open && returnFocus) toggle.focus();
    };

    toggle.addEventListener('click', function () {
      setOpen(!isOpen());
    });

    // Klick auf einen Menüpunkt schließt das Menü
    menu.addEventListener('click', function (event) {
      if (event.target.closest && event.target.closest('a')) setOpen(false);
    });

    // Escape schließt das Menü und gibt den Fokus an den Button zurück
    doc.addEventListener('keydown', function (event) {
      if ((event.key === 'Escape' || event.key === 'Esc') && isOpen()) {
        setOpen(false, true);
      }
    });

    // Klick außerhalb schließt das Menü
    doc.addEventListener('click', function (event) {
      if (isOpen() && !menu.contains(event.target) && !toggle.contains(event.target)) {
        setOpen(false);
      }
    });

    // Fokus verlässt das Menü (z. B. per Tab) → Menü schließen
    menu.addEventListener('focusout', function (event) {
      var next = event.relatedTarget;
      if (isOpen() && next && !menu.contains(next) && next !== toggle) setOpen(false);
    });

    // Beim Wechsel auf die Desktop-Ansicht immer schließen
    onMqChange(mq('(min-width: 64rem)'), function (event) {
      if (event.matches) setOpen(false);
    });
  }

  /* 4. Aktiver Abschnitt (Scroll-Spy) -------------------------------------- */
  var navLinks = doc.querySelectorAll('.nav-list a[href^="#"]');

  if (navLinks.length && 'IntersectionObserver' in win) {
    var linkById = {};
    var sections = [];

    forEach(navLinks, function (link) {
      var id = decodeURIComponent(link.getAttribute('href').slice(1));
      var section = id ? doc.getElementById(id) : null;
      if (section) {
        linkById[id] = link;
        sections.push(section);
      }
    });

    var setActive = function (id) {
      forEach(navLinks, function (link) {
        var active = link === linkById[id];
        link.classList.toggle('is-active', active);
        if (active) link.setAttribute('aria-current', 'true');
        else link.removeAttribute('aria-current');
      });
    };

    var visible = {};
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        visible[entry.target.id] = entry.isIntersecting;
      });
      for (var i = 0; i < sections.length; i++) {
        if (visible[sections[i].id]) {
          setActive(sections[i].id);
          return;
        }
      }
    }, { rootMargin: '-40% 0px -55% 0px', threshold: 0 });

    sections.forEach(function (section) {
      observer.observe(section);
    });
  }

  /* 5. Anliegen vorauswählen ---------------------------------------------- */
  var topicSelect = doc.getElementById('f-anliegen');
  if (topicSelect) {
    doc.addEventListener('click', function (event) {
      var trigger = event.target.closest ? event.target.closest('[data-anliegen]') : null;
      if (!trigger) return;
      var value = trigger.getAttribute('data-anliegen');
      var exists = Array.prototype.some.call(topicSelect.options, function (option) {
        return option.value === value;
      });
      if (exists) topicSelect.value = value;
    });
  }

  /* 6. Formular-Validierung + mailto ------------------------------------- */
  var form = doc.getElementById('kontaktformular');

  if (form) {
    form.setAttribute('novalidate', '');

    var statusEl = doc.getElementById('formular-status');
    var recipient = form.getAttribute('data-mailto') || '';
    var fields = Array.prototype.filter.call(
      form.querySelectorAll('input, select, textarea'),
      function (field) {
        return field.id && field.type !== 'hidden' && field.type !== 'submit';
      }
    );

    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
    var phonePattern = /^[0-9+()\/\s-]{6,}$/;

    var getMessage = function (field) {
      if (field.type === 'checkbox') {
        return field.required && !field.checked
          ? 'Bitte bestätigen Sie, dass Sie die Datenschutzerklärung gelesen haben.'
          : '';
      }

      var value = field.value.trim();

      if (field.required && !value) {
        return field.getAttribute('data-msg-required') || 'Bitte füllen Sie dieses Feld aus.';
      }
      if (!value) return '';

      if (field.type === 'email' && !emailPattern.test(value)) {
        return 'Bitte geben Sie eine gültige E-Mail-Adresse ein (z. B. name@beispiel.de).';
      }
      if (field.type === 'tel' && !phonePattern.test(value)) {
        return 'Bitte geben Sie eine gültige Telefonnummer ein (Ziffern, Leerzeichen sowie + ( ) / -).';
      }

      var min = parseInt(field.getAttribute('minlength'), 10);
      if (min && value.length < min) {
        return 'Bitte schreiben Sie mindestens ' + min + ' Zeichen (aktuell ' + value.length + ').';
      }
      return '';
    };

    var showError = function (field, message) {
      var errorEl = doc.getElementById(field.id + '-fehler');
      if (message) field.setAttribute('aria-invalid', 'true');
      else field.removeAttribute('aria-invalid');
      if (errorEl) errorEl.textContent = message;
    };

    var validateField = function (field) {
      var message = getMessage(field);
      showError(field, message);
      return !message;
    };

    // Inhalt des Status-Elements: Titel, Text und optional ein Link
    var setStatus = function (type, title, text, link) {
      if (!statusEl) return;
      statusEl.className = 'form-status' + (type ? ' form-status--' + type : '');
      statusEl.textContent = '';
      if (!title) return;
      var strong = doc.createElement('strong');
      strong.textContent = title;
      statusEl.appendChild(strong);
      if (text) statusEl.appendChild(doc.createTextNode(text));
      if (link) {
        statusEl.appendChild(doc.createTextNode(' '));
        var a = doc.createElement('a');
        a.href = link.href;
        a.textContent = link.text;
        statusEl.appendChild(a);
      }
    };

    var valueOf = function (name) {
      var field = form.elements[name];
      return field ? field.value.trim() : '';
    };

    // Betreff und Text der E-Mail aus den Formularangaben zusammensetzen
    var buildMailto = function () {
      var topicField = form.elements.anliegen;
      var topic = topicField && topicField.selectedIndex > 0
        ? topicField.options[topicField.selectedIndex].text
        : 'Anfrage';
      var place = valueOf('ort');
      var name = valueOf('name');

      var subject = 'Anfrage über die Website: ' + topic + (place ? ' in ' + place : '');
      var lines = [
        'Guten Tag Herr Tschudnow,',
        '',
        'ich interessiere mich für: ' + topic,
        '',
        'Name: ' + name,
        'E-Mail: ' + valueOf('email'),
        'Telefon: ' + (valueOf('telefon') || '–'),
        'Ort / PLZ: ' + place,
        'Wunschtermin: ' + (valueOf('termin') || '–'),
        '',
        'Nachricht:',
        valueOf('nachricht'),
        '',
        'Viele Grüße',
        name
      ];

      return 'mailto:' + recipient +
        '?subject=' + encodeURIComponent(subject) +
        '&body=' + encodeURIComponent(lines.join('\r\n'));
    };

    fields.forEach(function (field) {
      var eventName = field.type === 'checkbox' || field.tagName === 'SELECT' ? 'change' : 'input';

      field.addEventListener(eventName, function () {
        field.setAttribute('data-dirty', '');
        // Fehler live korrigieren, sobald ein Feld als fehlerhaft markiert ist
        if (field.getAttribute('aria-invalid') === 'true') validateField(field);
      });

      field.addEventListener('blur', function () {
        if (field.hasAttribute('data-dirty')) validateField(field);
      });
    });

    form.addEventListener('submit', function (event) {
      event.preventDefault();

      var invalid = fields.filter(function (field) {
        return !validateField(field);
      });

      if (invalid.length) {
        setStatus(
          'error',
          'Bitte prüfen Sie Ihre Angaben.',
          invalid.length === 1
            ? 'Ein Feld ist noch nicht korrekt ausgefüllt.'
            : invalid.length + ' Felder sind noch nicht korrekt ausgefüllt.'
        );
        invalid[0].focus();
        return;
      }

      var mailto = buildMailto();

      setStatus(
        'success',
        'Vielen Dank! Ihr E-Mail-Programm wird geöffnet.',
        'Bitte prüfen Sie die vorbereitete Nachricht und senden Sie sie dort ab. ' +
          'Falls sich kein E-Mail-Programm öffnet, erreichen Sie uns telefonisch unter ' +
          '0176 312 245 60 oder per E-Mail an ' + recipient + '.',
        { href: mailto, text: 'E-Mail erneut öffnen' }
      );

      // Formulardaten bleiben erhalten, falls das E-Mail-Programm nicht startet
      win.location.href = mailto;
    });
  }
})();
