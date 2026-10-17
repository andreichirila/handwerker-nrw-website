# Bilder für die Website

In diesem Ordner liegen die Fotos für die Website von **Tschudnow Profi Service**.
Solange hier keine Fotos liegen, zeigt die Website geschmackvolle Platzhalter
(„Fotos folgen“) bzw. eine gezeichnete Illustration – es entstehen **keine** kaputten
Bild-Symbole oder fehlerhaften Abrufe, weil die Bilder im HTML erst referenziert werden,
wenn Sie sie aktivieren.

## Erwartete Dateinamen

| Datei | Verwendung |
| ----- | ---------- |
| `images/hero.jpg` | Großes Titelbild oben auf der Startseite (optional) |
| `images/referenzen/entruempelung-1.jpg` | Galerie-Karte „Entrümpelung“ |
| `images/referenzen/haushaltsaufloesung-1.jpg` | Galerie-Karte „Haushaltsauflösung“ |
| `images/referenzen/bodenleger-1.jpg` | Galerie-Karte „Bodenverlegung“ |
| `images/referenzen/projekt-1-vorher.jpg` | Vorher/Nachher-Paar: Foto **vorher** |
| `images/referenzen/projekt-1-nachher.jpg` | Vorher/Nachher-Paar: Foto **nachher** |

Weitere Fotos einfach fortlaufend nummerieren (`entruempelung-2.jpg`, `bodenleger-2.jpg`,
`projekt-2-vorher.jpg` …) und im HTML eine zusätzliche Galerie-Karte anlegen
(eine vorhandene `<figure class="card gallery-item">` kopieren).

Dateinamen bitte **klein**, **ohne Leerzeichen und Umlaute** schreiben
(`ae`, `oe`, `ue`, `ss` statt `ä`, `ö`, `ü`, `ß`) – GitHub Pages unterscheidet Groß-/Kleinschreibung.

## Empfohlene Formate und Größen

- **Format:** JPG (Fotos) oder WebP. Bei WebP die Dateiendung im HTML/CSS entsprechend anpassen.
- **Hero-Bild:** ca. **1600–2000 px** breit, Querformat (z. B. 16:9 oder 3:2).
- **Galerie-Bilder:** ca. **1200 px** breit, Querformat **4:3** (z. B. 1200 × 900 px).
- **Vorher/Nachher:** beide Fotos im **gleichen Ausschnitt und Seitenverhältnis**,
  ideal **16:10** (z. B. 1200 × 750 px), möglichst vom selben Standpunkt aus fotografiert.
- **Dateigröße:** komprimieren – Ziel **unter 300 KB** pro Galeriebild, **unter 500 KB** für das Hero-Bild.
  Kostenlose Werkzeuge: z. B. [Squoosh](https://squoosh.app/) (läuft im Browser).
- Die Angaben `width` und `height` im `<img>`-Tag an die tatsächliche Pixelgröße anpassen
  (verhindert Layout-Sprünge beim Laden).

## So aktivieren Sie die Fotos

### Galerie (Abschnitt „Referenzen“ in `index.html`)

Jede Platzhalter-Karte enthält bereits ein auskommentiertes `<img>`-Tag, z. B.:

```html
<div class="gallery-item__media gallery-item__media--placeholder">
  <!-- <img src="images/referenzen/entruempelung-1.jpg" alt="…" loading="lazy" width="1200" height="900"> -->
  <span class="gallery-item__placeholder-icon">…</span>
  <span class="gallery-item__note">…Fotos folgen</span>
</div>
```

1. Foto unter dem passenden Namen in `images/referenzen/` ablegen.
2. Kommentarzeichen `<!--` und `-->` um das `<img>` entfernen.
3. Die beiden Platzhalter-Elemente (`gallery-item__placeholder-icon` und `gallery-item__note`) löschen.
4. Die Klasse `gallery-item__media--placeholder` entfernen.
5. `alt`-Text sachlich beschreiben (was ist zu sehen?) und den Text in der `figcaption` anpassen,
   z. B. „Kellerentrümpelung in Wuppertal“ statt „Fotos folgen in Kürze“.

Beim Vorher/Nachher-Paar bleiben die Beschriftungen `<span class="compare__label">` erhalten.

### Hero-Bild (optional)

1. Foto als `images/hero.jpg` ablegen.
2. In `index.html` beim Abschnitt `<section id="start" …>` die Klasse ergänzen:
   `class="hero on-dark"` → `class="hero hero--image on-dark"`.

Das Foto wird dann mit einer dunklen Überlagerung angezeigt (damit der Text gut lesbar bleibt);
die gezeichnete Illustration wird automatisch ausgeblendet. Ohne die Klasse wird das Bild gar nicht geladen.

## Bildrechte und Datenschutz – bitte beachten

- **Nur eigene Fotos** verwenden oder Fotos, für die Sie nachweislich die Nutzungsrechte haben.
  Keine Bilder aus Google-Suche, von Herstellern oder anderen Websites ohne Erlaubnis.
- **Keine Personen erkennbar** – weder Kundinnen und Kunden noch Mitarbeitende
  (ohne deren schriftliche Einwilligung).
- **Keine Kfz-Kennzeichen**, **Hausnummern, Straßenschilder, Klingelschilder** oder sonstige
  Hinweise, die auf eine Adresse oder einen Auftraggeber schließen lassen.
- **Keine persönlichen Gegenstände** mit Namen, Fotos, Briefe, Dokumente o. Ä. im Bild –
  gerade bei Haushaltsauflösungen besonders wichtig.
- Vor dem Fotografieren in Kundenobjekten die **Erlaubnis der Auftraggeber** einholen.
- Standortdaten (GPS) aus den Fotos entfernen – viele Smartphones speichern sie automatisch
  in den Bilddaten (EXIF). Beim Komprimieren z. B. mit Squoosh werden diese Daten entfernt.
