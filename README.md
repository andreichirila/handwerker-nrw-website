# Musterbau Haustechnik GmbH – Demo-Website (fiktiver SHK-Betrieb)

Statische, responsive Website für einen **fiktiven** Handwerksbetrieb aus Dortmund (NRW):
Meisterbetrieb für Sanitär, Heizung & Klima (SHK), Badsanierung und Wärmepumpen.

> ⚠️ **Demo / Platzhalter:** Firmenname, Inhaber, Adresse, Telefonnummer, E-Mail, Zahlen,
> Projekte, Kundenstimmen sowie Impressum und Datenschutzerklärung sind **erfunden**.
> Sie sind auf der Website mit Badges („Platzhalter“) und einem Hinweisbanner gekennzeichnet.

- Nur **HTML, CSS und etwas Vanilla-JavaScript** – kein Build-Schritt, keine Frameworks
- **Keine externen Abhängigkeiten** (kein CDN, keine Web-Fonts, keine Fremd-Karten, keine Fotos)
- Alle Grafiken (Logo, Icons, Illustrationen, Karte) sind selbst erstellte **Inline-SVGs / CSS**
- Nur **relative Links** → läuft direkt über GitHub Pages (Branch `main`, Ordner `/`)

## Funktionen

- Sticky Header mit Logo, Navigation und Telefon-Button, mobiles Hamburger-Menü
  (`aria-expanded`, `aria-controls`, schließt per Link-Klick, Klick außerhalb und <kbd>Esc</kbd>)
- Hervorhebung des aktiven Abschnitts beim Scrollen (IntersectionObserver)
- Smooth Scrolling mit `scroll-margin-top`, respektiert `prefers-reduced-motion`
- Kontaktformular als reine Oberfläche mit clientseitiger Validierung
  (es wird **nichts versendet** – freundlicher Demo-Hinweis nach dem Absenden)
- Barrierearm: Skip-Link, sichtbare Fokus-Stile, Labels, Live-Region für Formularmeldungen,
  Kontraste nach WCAG AA, dekorative SVGs mit `aria-hidden`
- SEO: `title`, `meta description`, Open Graph/Twitter, `theme-color`, Favicon,
  JSON-LD (`Plumber` / `HVACBusiness`) mit `areaServed`; Impressum/Datenschutz/404 mit `noindex`
- Funktioniert auch ohne JavaScript (Menü wird dann als Zeile angezeigt)

## Struktur

```
.
├── index.html          # One-Pager: Start, Leistungen, Über uns, Referenzen, Einzugsgebiet, Kontakt
├── impressum.html      # Muster-Impressum (Platzhalter, noindex)
├── datenschutz.html    # Muster-Datenschutzerklärung (Platzhalter, noindex)
├── 404.html            # Fehlerseite (wird von GitHub Pages automatisch verwendet)
├── .nojekyll           # GitHub Pages: Dateien unverändert ausliefern (kein Jekyll)
├── README.md
└── assets/
    ├── css/style.css   # Gesamtes Styling (mobile-first, Design-Tokens als CSS-Variablen)
    ├── js/main.js      # Navigation, Scroll-Spy, Formular-Validierung, Jahreszahl
    └── img/favicon.svg # Logo / Favicon
```

## Lokal ansehen

**Variante 1:** `index.html` einfach im Browser öffnen (Doppelklick).

**Variante 2 (empfohlen, verhält sich wie ein echter Webserver):**

```bash
python3 -m http.server 8000
```

Danach <http://localhost:8000> im Browser öffnen. Beenden mit <kbd>Strg</kbd>+<kbd>C</kbd>.

## Platzhalter anpassen

1. **Firmendaten ersetzen** – am schnellsten per „Suchen & Ersetzen“ über alle Dateien:

   | Platzhalter                         | Vorkommen                              |
   | ----------------------------------- | -------------------------------------- |
   | `Musterbau Haustechnik GmbH`        | alle HTML-Dateien, README, JSON-LD     |
   | `Musterstraße 12`, `44135 Dortmund` | Kontakt, Footer, Impressum, Datenschutz, JSON-LD |
   | `0231 000 000 00` / `+4923100000000` / `+49 231 00000000` | Anzeige / `tel:`-Links / JSON-LD |
   | `info@example.com`                  | Kontakt, Footer, Impressum, Datenschutz, JSON-LD |
   | `Max Mustermann`                    | Über uns, Impressum, Datenschutz, JSON-LD |
   | `1987`, Zahlen, Team, Projekte, Kundenstimmen | `index.html`                 |
   | HRB, USt-IdNr., Betriebsnummer, Versicherung  | `impressum.html`             |

2. **Platzhalter-Kennzeichnung entfernen:** das Banner `<div class="demo-banner">` in allen Seiten
   löschen sowie alle `<span class="badge-ph">…</span>` entfernen. Den Demo-Hinweis im
   Kontaktformular (`<div class="notice">`) und den Button-Text „(Demo)“ anpassen.
3. **Strukturierte Daten (JSON-LD)** im `<head>` von `index.html` aktualisieren
   (Name, Adresse, Geo-Koordinaten, Öffnungszeiten, `areaServed`, `url`, `logo`).
4. **Domain eintragen:** die auskommentierten Zeilen `<link rel="canonical">`,
   `og:url` und `og:image` aktivieren und `https://www.example.com/` ersetzen
   (OG-Bild idealerweise als 1200 × 630 px PNG/JPG unter `assets/img/` ablegen).
5. **Farben & Schrift** zentral in `assets/css/style.css` unter „1. Design-Tokens“ ändern
   (`--c-primary`, `--c-accent`, `--font-sans` …). Bei Farbänderungen den Kontrast prüfen.
6. **Echtes Kontaktformular:** Das Formular sendet absichtlich nichts. Für den Echtbetrieb
   z. B. einen DSGVO-konformen Formular-Dienst oder ein eigenes Backend anbinden
   (`action`-Attribut setzen, Submit-Handler in `assets/js/main.js` anpassen) und den
   Dienst in der Datenschutzerklärung nennen.

## Veröffentlichen mit GitHub Pages

### Über die Weboberfläche

1. Repository auf GitHub anlegen und den Inhalt dieses Ordners auf den Branch `main` pushen.
2. Im Repository **Settings → Pages** öffnen.
3. Unter **Build and deployment → Source** die Option **Deploy from a branch** wählen.
4. Bei **Branch** `main` und Ordner **`/ (root)`** auswählen und **Save** klicken.
5. Nach ein bis zwei Minuten ist die Seite erreichbar unter
   `https://<benutzername>.github.io/<repository>/`.
6. Optional: **Enforce HTTPS** aktivieren und unter **Custom domain** eine eigene Domain eintragen.

### Über die Kommandozeile mit `gh api`

Voraussetzung: [GitHub CLI](https://cli.github.com/) ist installiert und mit `gh auth login` angemeldet.
`OWNER` und `REPO` entsprechend ersetzen:

```bash
# GitHub Pages aktivieren: Branch main, Ordner / (root)
gh api --method POST \
  -H "Accept: application/vnd.github+json" \
  /repos/OWNER/REPO/pages \
  -f "build_type=legacy" \
  -f "source[branch]=main" \
  -f "source[path]=/"

# Status und URL abfragen
gh api /repos/OWNER/REPO/pages --jq '.html_url, .status'

# Optional: HTTPS erzwingen (sobald das Zertifikat bereitsteht)
gh api --method PUT /repos/OWNER/REPO/pages -F "https_enforced=true"
```

> Hinweis zur `404.html`: GitHub Pages liefert sie automatisch bei unbekannten Adressen aus.
> Damit Styles und Links auch bei tief verschachtelten Pfaden (z. B. `/<repository>/a/b/c`)
> funktionieren, setzt ein kleines Skript im `<head>` der 404-Seite auf `*.github.io` ein
> `<base href="/handwerker-nrw-website/">`. Wird das Repository umbenannt oder eine eigene
> Domain genutzt, diesen Pfad in `404.html` entsprechend anpassen (bei eigener Domain: `/`).

## Wichtig: Impressum & Datenschutz

Geschäftsmäßige Websites in Deutschland benötigen ein vollständiges **Impressum**
(§ 5 Digitale-Dienste-Gesetz, DDG) und eine **Datenschutzerklärung** (Art. 13 DSGVO).

Die mitgelieferten Seiten `impressum.html` und `datenschutz.html` sind **reine Muster mit
Platzhalterdaten** und ausdrücklich **nicht rechtsverbindlich**. Vor einer echten
Veröffentlichung müssen sie mit den tatsächlichen Angaben befüllt und **rechtlich geprüft**
werden – z. B. über einen seriösen Generator (etwa von Anwaltskanzleien oder IT-Rechts-Portalen)
oder durch eine Rechtsanwältin bzw. einen Rechtsanwalt. Insbesondere Angaben zur
Handwerkskammer, Handwerksrolle, Berufshaftpflicht, zum Hosting (GitHub Pages, Datenübermittlung
in die USA) und zu eingesetzten Formular-Diensten sind individuell zu prüfen.

Solange die Seite eine Demo ist, empfiehlt es sich, sie gar nicht oder nur mit dem
Demo-Banner zu veröffentlichen.

## Lizenz / Grafiken

Alle Inhalte dieser Demo (Code, SVG-Grafiken, Texte) wurden eigens erstellt; es werden keine
lizenzpflichtigen Bilder oder Schriften Dritter verwendet.
