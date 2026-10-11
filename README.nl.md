# Compact Tabellen

Een Joomla 5- en 6-extensie voor responsive tabellen in artikelen. Je maakt een
tabel in de beheeromgeving (of op de website, als je daar rechten voor hebt) en
toont hem in een artikel met een korte code zoals `{table 1}`. Pas je de tabel
later aan, dan verandert hij overal waar de code staat.

Auteur: **Wouter** ([WoodyF4u](https://github.com/WoodyF4u)) · [CompactWeb](https://compactweb.nl)
Broncode & releases: **https://github.com/WoodyF4u/Compact-Tables**
Vragen of problemen: [open een issue op GitHub](https://github.com/WoodyF4u/Compact-Tables/issues).

*[This README is also available in English: README.md](README.md)*

---

## Wat zit er in het pakket

| Onderdeel | Wat het doet |
|---|---|
| `com_compacttables` | Het component: overzicht van je tabellen, het bewerkscherm met editorbalk, het tabblad Stijl met live voorbeeld, opties en rechten, en een helppagina. |
| `plg_content_compacttables` | Vervangt `{table N}` in een artikel door de tabel. |
| `plg_editors-xtd_compacttables` | Editorknop (TinyMCE en JCE, beheer en website) om een tabel te kiezen, te maken, te bewerken of te verwijderen zonder het artikel te verlaten. |

## Mogelijkheden

- Rijen en kolommen toevoegen, verwijderen en rijen verslepen; Enter voor een nieuwe regel in een cel, Tab naar de volgende cel.
- Editorbalk met vet, cursief, onderstrepen, doorhalen, sub/superscript en links naar een menu-item, artikel, webadres, telefoonnummer of e-mailadres. De indeling van de balk stel je in onder Opties.
- Tabblad Stijl: kleuren, lettergrootte en vetheid van de bovenste rij en de overige cellen, buitenrand en aparte verticale/horizontale lijnen, breedte en uitlijning, ruimte tussen de cellen, en wat er gebeurt op kleine schermen (kolommen houden, zijwaarts scrollen of rijen stapelen). Wat je leeg laat, volgt je sitetemplate.
- Werkt in de lichte en de donkere weergave van Joomla.
- Rechten voor maken, bewerken en verwijderen per gebruikersgroep, in de beheeromgeving en op de website.

## Installatie

Download `pkg_compacttables_vX.Y.Z.zip` bij de
[nieuwste release](https://github.com/WoodyF4u/Compact-Tables/releases/latest) en installeer het via
**Systeem → Installeren → Extensies**. Beide plugins worden automatisch ingeschakeld.

Installeren over een oudere versie heen behoudt alle tabellen en instellingen.
Installeren over de voorganger *Tabel Maker* neemt diens tabellen (met dezelfde
nummers), opties en rechten over en verwijdert Tabel Maker daarna.

## Updates

Na installatie verschijnen nieuwe versies vanzelf onder **Systeem → Updates**
(en in de updatemelding op het configuratiescherm). Ze worden aangeboden via
[`updates.xml`](updates.xml) in deze repository.

## Een nieuwe versie uitbrengen (voor de beheerder)

1. Zet de gewijzigde bronbestanden in deze repository (onder `packages/…`) en
   voeg een kopje toe aan [`CHANGELOG.md`](CHANGELOG.md).
2. Op GitHub: **Releases → Draft a new release**, maak een nieuwe tag zoals
   `v1.0.1` (altijd `v` + drie getallen) en klik op **Publish release**.
3. De workflow in `.github/workflows/release.yml` zet dat versienummer in alle
   manifesten, bouwt `pkg_compacttables_v1.0.1.zip`, hangt het aan de release en
   werkt `updates.xml` bij. Sites zien de update binnen een paar uur (of meteen
   met *Controleren op updates*).

Versienummers hoef je nooit met de hand aan te passen: de tag bepaalt het.

## Vereisten

- Joomla 5.0 of nieuwer (gebouwd en getest op Joomla 6)
- PHP 8.1 of nieuwer
- MySQL/MariaDB of PostgreSQL

## Licentie

GNU General Public License versie 2 of later, zie [LICENSE.txt](LICENSE.txt).
