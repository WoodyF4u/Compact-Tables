# Compact Tables

A Joomla 5 and 6 extension for responsive tables in articles. You build a
table in the administrator (or on the website, if you have permission) and
show it in an article with a short code such as `{table 1}`. Change the table
later and it changes everywhere the code is used.

Author: **Wouter** ([WoodyF4u](https://github.com/WoodyF4u)) · [CompactWeb](https://compactweb.nl)
Source & releases: **https://github.com/WoodyF4u/Compact-Tables**
Questions or issues: please [open a GitHub issue](https://github.com/WoodyF4u/Compact-Tables/issues).

*[Deze README is ook beschikbaar in het Nederlands: README.nl.md](README.nl.md)*

---

## What's in the package

| Part | What it does |
|---|---|
| `com_compacttables` | The component: overview of your tables, the edit screen with editor bar, the Style tab with live preview, options and permissions, and a help page. |
| `plg_content_compacttables` | Replaces `{table N}` in an article with the table. |
| `plg_editors-xtd_compacttables` | Editor button (TinyMCE and JCE, backend and frontend) to pick, make, edit or delete a table without leaving the article. |

## Features

- Add, delete and drag rows and columns; Enter for a new line inside a cell, Tab to the next cell.
- Editor bar with bold, italic, underline, strikethrough, sub/superscript and links to a menu item, article, web address, phone number or email address. The bar layout is configurable under Options.
- Style tab: colours, font size and boldness of the top row and other cells, outer border and separate vertical/horizontal lines, width and alignment, room between cells, and what happens on small screens (keep columns, scroll sideways, or stack rows). Everything left empty follows your site template.
- Works in both Joomla's light and dark mode.
- Create/Edit/Delete permissions per user group, in the administrator and on the website alike.

## Installation

Download `pkg_compacttables_vX.Y.Z.zip` from the
[latest release](https://github.com/WoodyF4u/Compact-Tables/releases/latest) and install it under
**System → Install → Extensions**. Both plugins are switched on automatically.

Installing over an older version keeps all tables and settings. Installing over
the predecessor *Table Maker* takes over its tables (with the same numbers),
options and permissions, and removes Table Maker afterwards.

## Updates

Once installed, new versions appear by themselves under **System → Updates**
(and in the update notification on the control panel). They are offered through
[`updates.xml`](updates.xml) in this repository.

## Releasing a new version (for the maintainer)

1. Put the changed source files in this repository (under `packages/…`) and add
   a section to [`CHANGELOG.md`](CHANGELOG.md).
2. On GitHub: **Releases → Draft a new release**, create a new tag such as
   `v1.0.1` (always `v` + three numbers), and click **Publish release**.
3. The workflow in `.github/workflows/release.yml` then sets that version number
   in every manifest, builds `pkg_compacttables_v1.0.1.zip`, attaches it to the
   release and updates `updates.xml`. Sites pick up the update within a few hours
   (or straight away with *Check for Updates*).

You never need to edit version numbers by hand: the tag decides.

## Requirements

- Joomla 5.0 or newer (built and tested on Joomla 6)
- PHP 8.1 or newer
- MySQL/MariaDB or PostgreSQL

## License

GNU General Public License version 2 or later, see [LICENSE.txt](LICENSE.txt).
