# Changelog

Elke regel is ook een test die je na installatie kunt aflopen om te
controleren of een functie daadwerkelijk is meegenomen.

## 0.9.3

- **Tabellen nemen de tabelopmaak van de template over, ook de bovenste rij.**
  Een `{table N}` ziet er zonder instellingen nu hetzelfde uit als een gewone
  tabel die je met JCE of TinyMCE invoegt.
  - Onder Opties heet het veld nu ‘Extra CSS-class voor tabellen’ en is het
    standaard leeg. Op de website staat op `<table>` alleen nog
    `compacttables__table`, niet meer `table`.
  - Bij een update vanaf 0.9.2 of ouder wordt het veld eenmalig leeggemaakt als
    er nog de oude standaard `table` in stond. Een zelf gekozen class blijft staan.
  - De uitleg bij het veld (en op de helppagina) zegt wat een class oplevert,
    met voorbeelden voor Bootstrap-templates: `table`, `table-striped`,
    `table-bordered`, `table-hover`.
  - Het voorbeeld op het tabblad Stijl volgt dezelfde instelling.
- **Nieuw op het tabblad Stijl, onder ‘Tekst in de cellen’: ‘Regelafstand’ en
  ‘Ruimte binnen de cel’.**
  - Regelafstand: Template-standaard, 1, 1,15 (zoals in een tekstdocument),
    1,25, 1,5, 1,75 of 2.
  - Ruimte binnen de cel: ruimte tussen de tekst en de rand, aan alle vier de
    kanten, in px, rem of em. Leeg = template, 0 = geen ruimte.
  - Beide gelden voor alle cellen, ook de bovenste rij en gestapelde rijen op
    kleine schermen. Standaard leeg, dus bestaande tabellen veranderen niet.
- **Nieuw op het tabblad Stijl, onder ‘Overige cellen’: ‘Rij oplichten onder de
  muis’ (Ja/Nee).**
  - Met ‘Kleur bij oplichten’ (leeg = de tekstkleur) en ‘Intensiteit’ in
    procenten (leeg = 8%). Die twee velden zijn alleen zichtbaar bij Ja.
  - De bovenste rij licht niet op; op touchscreens gebeurt er niets.
  - Werkt in een lichte en een donkere weergave, en is te zien in het voorbeeld.
  - Standaard Nee, ook voor bestaande tabellen.

## 0.9.1

- **Kolomtitels zijn nu ook op Cassiopeia vet.** Cassiopeia geeft kolomtitels
  zelf een gewicht van 500 (`.table th`), waardoor ze nauwelijks vet lijken;
  T4 maakt ze wel vet.
  - ‘Tekst vetgedrukt’ bij de bovenste rij staat bij een nieuwe tabel op Ja.
  - Ja en Nee winnen nu altijd van de template, zowel bij de bovenste rij als
    bij de overige cellen.
- **Nieuw op het tabblad Stijl: ‘Tekst in de cellen’**, met horizontale
  (links/midden/rechts) en verticale (boven/midden/onder) uitlijning.
  Standaard links en boven, ook voor bestaande tabellen. ‘Template-standaard’
  laat het aan de template over.
- **Nieuw: tabellen koppelen.** Op het tabblad Stijl staat onder ‘Tabellen
  koppelen’ een uitklaplijst met alle andere tabellen; zet een vinkje bij de
  tabellen die dezelfde kolombreedtes moeten krijgen.
  - Achter elke naam staat de code en het aantal kolommen.
  - De koppeling geldt over en weer: de gekoppelde tabellen tonen het vinkje ook.
  - Op de website lijnen gekoppelde tabellen in één artikel hun kolommen exact
    uit. Werkt bij tabellen met evenveel kolommen, niet bij ‘Zijwaarts scrollen’.
  - Het overzicht heeft een kolom ‘Gekoppeld’: ‘geen’, of de namen van de
    gekoppelde tabellen onder elkaar.
  - Een verwijderde tabel verdwijnt uit de koppeling; ‘Opslaan als kopie’ maakt
    een kopie zonder koppelingen.

- De uitleg onder ‘Bovenste rij’ op het tabblad Stijl (zichtbaar als de
  tabel geen kolomtitels heeft) heeft nu dezelfde opmaak als die onder
  ‘Overige cellen’.

## 0.9.0 (eerste release op GitHub)

- **Automatische updates via GitHub**: na installatie van deze versie
  verschijnen nieuwe versies vanzelf onder Systeem → Updates.

## 0.0.17

- **Eerste versie onder de naam Compact Tables / Compact Tabellen**
  (voorheen Table Maker / Tabel Maker).
  - Bij installatie over Tabel Maker heen worden de tabellen, opties, rechten
    en de aan/uit-stand van de plugins overgenomen, met dezelfde nummers:
    elke `{table N}` in een artikel blijft werken. Tabel Maker wordt daarna
    verwijderd.
