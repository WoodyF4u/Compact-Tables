# Changelog

Elke regel is ook een test die je na installatie kunt aflopen om te
controleren of een functie daadwerkelijk is meegenomen.

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
- **Nieuw: tabellen koppelen.** Tabellen met dezelfde ‘Koppelnaam’ op één
  pagina krijgen dezelfde kolombreedtes, zodat ze onder elkaar uitlijnen.
  Werkt bij tabellen met evenveel kolommen, niet bij ‘Zijwaarts scrollen’.

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
