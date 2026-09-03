Reactie voor GitHub-issue #122 (ginkelsoft-development/buildora)
=================================================================

LET OP: vanuit deze werkplaats is er geen GitHub-toegang (geen `gh` CLI,
geen credentials) en kan deze reactie dus niet zelf geplaatst worden. Plak
onderstaande tekst als comment op issue #122, bijvoorbeeld met:

    gh issue comment 122 --repo ginkelsoft-development/buildora --body-file .zyra/issue-122-comment.md

--- comment hieronder dit lijntje ---

**Audit-conclusie: kwetsbaar (stored XSS, OWASP A03:2021 - Injection).**
Fix doorgevoerd op branch `zyra/issue-122-buildora-onderzoeken-vaststell-ce181a48`.

**Bevinding**

`RichTextField` en `EditorField` (`src/Fields/Types/RichTextField.php`,
`EditorField.php`) hadden vóór deze fix geen enkele sanitization: de ruwe
request-waarde ging ongewijzigd de database in. Meerdere plekken renderen
die opgeslagen waarde vervolgens **unescaped**:

- `resources/views/components/input/richtext.blade.php:4` — `{!! $value !!}`
- `resources/views/form.blade.php` rendert per fieldtype de bijbehorende
  `components.input.{type}`-view (dus ook `richtext.blade.php`) met de
  opgeslagen/`old()`-waarde, op zowel de create- als edit-pagina.
- `resources/views/components/datatable.blade.php:228` —
  `<td x-html="formatCell(row, col.name)">`: `RowFormatter::format()`
  (`src/Datatable/RowFormatter.php:41-45`) zet de **rauwe** (niet
  ge-escapte) fieldwaarde in de JSON-respons van de datatable-endpoint, en
  Alpine's `x-html` injecteert die vervolgens als innerHTML in de
  index-tabel van élk resource. Dit raakt overigens niet alleen
  RichTextField/EditorField maar in principe elk veldtype — een aparte,
  bredere bevinding die het waard is om los op te pakken.

**Reproductie**: waarde `<script>alert(1)</script>` opslaan in een
EditorField/RichTextField kwam vóór de fix ongestript terug in
`richtext.blade.php`'s `{!! $value !!}` en in de datatable-JSON — in beide
gevallen een klassieke stored-XSS-uitvoering in de browser van elke
gebruiker (incl. admins) die de pagina bekijkt.

**Fix**

- Nieuwe hook `Field::prepareForStorage(mixed $value): mixed` (identity by
  default) die de controller vlak vóór `create()`/`update()` aanroept met
  de ruwe request-waarde per veld
  (`src/Http/Controllers/BuildoraController.php`).
- Nieuwe trait `Ginkelsoft\Buildora\Fields\Traits\SanitizesHtml`, gebruikt
  door `RichTextField` en `EditorField`, die `prepareForStorage()`
  overschrijft en de waarde door `ezyang/htmlpurifier` haalt:
  - `->sanitize(bool $enabled = true)` — **standaard aan**.
  - `->allowedTags(array $tags)` — configureerbare allow-list
    (HTMLPurifier `HTML.Allowed`-syntax), met een veilige default
    (basis-opmaak: `p`, `strong`, `em`, `ul/ol/li`, `a[href|title|target|rel]`,
    tabellen, etc. — géén `script`, geen event-handler-attributen, geen
    `javascript:`-URI's).
- Regressietests in
  `tests/Unit/Fields/RichTextAndEditorFieldXssTest.php` bevestigen dat
  `<script>alert(1)</script>`, `onerror=`-attributen en `javascript:`-URI's
  worden gestript, dat gewone opmaak behouden blijft, dat sanitization
  standaard aan staat, dat `allowedTags()` configureerbaar is, dat
  `sanitize(false)` expliciet uitgezet kan worden, en dat gewone
  fieldtypes (zonder de trait) ongewijzigd blijven werken.

**Nog open**: de bredere x-html/datatable-bevinding hierboven (geldt voor
alle fieldtypes, niet alleen RichText/Editor) valt buiten de scope van dit
issue en is niet in deze PR meegenomen.

Draai lokaal:
`vendor/bin/phpunit tests/Unit/Fields/RichTextAndEditorFieldXssTest.php`
Volledige suite: `vendor/bin/phpunit --testdox`
Proefdraaien van de package (Orchestra Testbench): `.zyra/proef.sh`
