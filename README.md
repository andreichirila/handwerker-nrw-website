# Tschudnow Profi Service – Website

Statische, responsive Website für **Tschudnow Profi Service** aus Wuppertal (NRW):
Entrümpelung, Haushaltsauflösung (Hausauflösung) und Bodenlegerarbeiten in Wuppertal
und Umgebung (Bergisches Land, z. B. Solingen, Remscheid, Velbert, Hagen, Schwelm, Haan,
Sprockhövel, Mettmann, Essen, Düsseldorf).

**Live:** <https://andreichirila.github.io/handwerker-nrw-website/>

- Nur **HTML, CSS und etwas Vanilla-JavaScript** – kein Build-Schritt, keine Frameworks
- **Keine externen Abhängigkeiten** (kein CDN, keine Web-Fonts, keine Fremd-Karten, kein Tracking)
- Logo, Icons, Illustrationen und Karte sind selbst erstellte **Inline-SVGs / CSS**
- Nur **relative Links** → läuft direkt über GitHub Pages (Branch `main`, Ordner `/`)

## Inhalt der Startseite

| Abschnitt | Inhalt |
| --------- | ------ |
| Start (Hero) | Kernbotschaft, Buttons „Kostenlose Besichtigung anfragen“ und Telefon, optionales Titelfoto |
| Leistungen | Entrümpelung · Haushaltsauflösung / Hausauflösung · Bodenlegerarbeiten, Hinweis „aus einer Hand“ |
| Ablauf | Anfrage → Besichtigung → Festes Angebot → Durchführung → Besenreine Übergabe |
| Über uns | Regionaler Dienstleister aus Wuppertal, persönlicher Ansprechpartner |
| Referenzen | Vorbereitete Galerie mit „Fotos folgen“-Platzhaltern inkl. Vorher/Nachher-Paar |
| Einzugsgebiet | Selbst gezeichnete SVG-Karte rund um Wuppertal + Städte-Liste |
| Kontakt | Formular (Versand über `kontakt.php`), Telefon, E-Mail, WhatsApp, Ansprechpartner |

Bewusst **nicht** enthalten (weil nicht belegt): Jahreszahlen/Erfahrung, Teamgrößen,
Kundenstimmen, Bewertungen, Preise, Zertifikate, Versicherungsangaben, Öffnungszeiten,
Straßenadresse.

## Funktionen

- Sticky Header mit Logo, Navigation und Telefon-Button, mobiles Hamburger-Menü
  (`aria-expanded`, `aria-controls`, schließt per Link-Klick, Klick außerhalb und <kbd>Esc</kbd>)
- Hervorhebung des aktiven Abschnitts beim Scrollen (IntersectionObserver)
- Smooth Scrolling mit `scroll-margin-top`, respektiert `prefers-reduced-motion`
- Klick auf „Angebot anfragen“ in einer Leistungskarte wählt das passende Anliegen im Formular vor
- **Kontaktformular mit `kontakt.php`:** clientseitige und serverseitige Validierung,
  Honeypot, zeitgebundenes Token, Rate-Limit; die Anfrage wird per E-Mail an
  `eugen.tschudnow@outlook.de` weitergeleitet (Reply-To = Absender). Konfiguration
  (`../kontakt-config.php`) und Laufzeitdaten (`../kontakt-data/`) liegen außerhalb des
  Webroots. Ersatzweg bei Fehlern: vorausgefüllte E-Mail (`mailto:`).
- Barrierearm: Skip-Link, sichtbare Fokus-Stile, Labels, Live-Region für Formularmeldungen,
  Kontraste nach WCAG AA, dekorative SVGs mit `aria-hidden`, Karte mit `title`/`desc`
- SEO: `title`, `meta description`, Canonical, Open Graph/Twitter, `theme-color`, Favicon,
  JSON-LD (`HomeAndConstructionBusiness`) mit Ort, Telefon, E-Mail, `areaServed`,
  `knowsAbout`, `makesOffer`; Impressum/Datenschutz/404 mit `noindex`
- Funktioniert auch ohne JavaScript (Menü wird dann als Zeile angezeigt, Formular-Hinweis auf direkte Kontaktwege)

## Struktur

```
.
├── index.html            # One-Pager: Start, Leistungen, Ablauf, Über uns, Referenzen, Einzugsgebiet, Kontakt
├── impressum.html        # Impressum (noindex) – markierte Angaben noch ergänzen
├── datenschutz.html      # Datenschutzerklärung (noindex)
├── 404.html              # Fehlerseite (wird von GitHub Pages automatisch verwendet)
├── .nojekyll             # GitHub Pages: Dateien unverändert ausliefern (kein Jekyll)
├── README.md
├── assets/
│   ├── css/style.css     # Gesamtes Styling (mobile-first, Design-Tokens als CSS-Variablen)
│   ├── js/main.js        # Navigation, Scroll-Spy, Formular-Validierung + mailto, Jahreszahl
│   └── img/favicon.svg   # Logo / Favicon
└── images/
    ├── README.md         # Anleitung: Dateinamen, Größen, Bildrechte
    ├── hero.jpg          # (optional, noch nicht vorhanden)
    └── referenzen/       # Galerie-Fotos (noch nicht vorhanden)
```

## Bilder einfügen

Kurzfassung (ausführlich in [`images/README.md`](images/README.md)):

1. Fotos komprimiert ablegen, z. B. `images/referenzen/entruempelung-1.jpg`,
   `haushaltsaufloesung-1.jpg`, `bodenleger-1.jpg`, `projekt-1-vorher.jpg`, `projekt-1-nachher.jpg`.
2. In `index.html` im Abschnitt „Referenzen“ das jeweils **auskommentierte `<img>`** aktivieren,
   die Platzhalter-Elemente löschen und die Klasse `gallery-item__media--placeholder` entfernen.
3. Optionales Titelbild: `images/hero.jpg` ablegen und in `index.html` bei
   `<section id="start" class="hero on-dark">` die Klasse `hero--image` ergänzen.

Nur eigene Fotos verwenden; keine Personen, Kennzeichen oder Adressen erkennbar.

## Offene Punkte vor dem Livegang

Auf der Website sind nur noch tatsächlich **unbekannte** Angaben markiert
(gelbe Markierung bzw. Badge „Bitte ergänzen“ / „Bitte prüfen“):

- [ ] **Impressum:** ladungsfähige Anschrift (`[Straße Hausnummer]`, `[PLZ] Wuppertal`) ergänzen –
      auch im Abschnitt „Verantwortlich nach § 18 Abs. 2 MStV“
- [ ] **Datenschutzerklärung:** dieselbe Anschrift beim „Verantwortlichen“ ergänzen
- [ ] **Inhaber-Angabe** „Eugen Tschudnow“ im Impressum bestätigen
- [ ] **USt-IdNr.** ergänzen, falls vorhanden (sonst Abschnitt entfernen); Steuernummer ist optional
- [ ] **Gewerbe-/Handwerkseintragung:** falls zutreffend Kammer, Gewerk und Betriebsnummer eintragen,
      sonst Abschnitt entfernen. Bitte insbesondere klären, ob für die angebotenen Bodenarbeiten
      (z. B. **Parkett**) eine Eintragung in die Handwerksrolle erforderlich ist.
- [ ] Hinweise zur **Verbraucherstreitbeilegung** und zur eingestellten EU-OS-Plattform prüfen
- [ ] Angaben zu **GitHub, Outlook.com (Microsoft) und WhatsApp** in der Datenschutzerklärung prüfen
- [ ] Impressum und Datenschutzerklärung **rechtlich prüfen lassen**
- [ ] Danach die Hinweis-Kästen oben auf `impressum.html` / `datenschutz.html` und alle
      `<span class="badge-ph">…</span>` bzw. `<span class="ph-text">…</span>` entfernen
- [ ] Eigene Fotos einsetzen (siehe oben), optional OG-Vorschaubild `assets/img/og-image.jpg`
      (1200 × 630 px) anlegen und die auskommentierte `og:image`-Zeile in `index.html` aktivieren

## Lokal ansehen

**Variante 1:** `index.html` einfach im Browser öffnen (Doppelklick).

**Variante 2 (empfohlen, verhält sich wie ein echter Webserver):**

```bash
python3 -m http.server 8000
```

Danach <http://localhost:8000> im Browser öffnen. Beenden mit <kbd>Strg</kbd>+<kbd>C</kbd>.

## Veröffentlichen mit GitHub Pages

Die Seite ist bereits unter <https://andreichirila.github.io/handwerker-nrw-website/> erreichbar.
Änderungen werden nach einem Push auf `main` nach ein bis zwei Minuten automatisch veröffentlicht.

Einrichtung (falls neu nötig):

1. Im Repository **Settings → Pages** öffnen.
2. Unter **Build and deployment → Source** die Option **Deploy from a branch** wählen.
3. Bei **Branch** `main` und Ordner **`/ (root)`** auswählen und **Save** klicken.
4. **Enforce HTTPS** aktivieren.

> Hinweis zur `404.html`: GitHub Pages liefert sie automatisch bei unbekannten Adressen aus.
> Damit Styles und Links auch bei tief verschachtelten Pfaden funktionieren, setzt ein kleines
> Skript im `<head>` der 404-Seite auf `*.github.io` ein `<base href="/handwerker-nrw-website/">`.
> Wird das Repository umbenannt, diesen Pfad in `404.html` anpassen.

## Eigene Domain (optional)

1. Domain bei einem Anbieter registrieren (z. B. `tschudnow-profi-service.de` – Verfügbarkeit prüfen).
2. Im Repository unter **Settings → Pages → Custom domain** die Domain eintragen
   (GitHub legt dabei eine Datei `CNAME` im Repository an).
3. Beim Domain-Anbieter die DNS-Einträge laut
   [GitHub-Dokumentation](https://docs.github.com/de/pages/configuring-a-custom-domain-for-your-github-pages-site)
   setzen (A-/AAAA-Einträge für die Hauptdomain bzw. CNAME für `www`).
4. Nach erfolgreicher Prüfung **Enforce HTTPS** aktivieren.
5. Anschließend anpassen:
   - `index.html`, `impressum.html`, `datenschutz.html`: `<link rel="canonical">`, `og:url`
     und die URLs im JSON-LD (`@id`, `url`, `logo`) auf die neue Domain umstellen
   - `404.html`: im Skript `<base href="/handwerker-nrw-website/">` durch `<base href="/">` ersetzen
     bzw. die Bedingung auf die eigene Domain anpassen

## Farben anpassen

Zentrale Design-Tokens stehen in `assets/css/style.css` unter „1. Design-Tokens“
(`--c-primary` Nachtblau, `--c-accent` Holz-/Amberton …). Bei Farbänderungen den Kontrast
(mind. 4,5 : 1 für Text) prüfen.

## Lizenz / Grafiken

Code, SVG-Grafiken und Texte wurden eigens für diese Website erstellt; es werden keine
lizenzpflichtigen Bilder oder Schriften Dritter verwendet.
