Reactie voor GitHub-issue #142 (ginkelsoft-development/buildora)
=================================================================

LET OP: Bram heeft vanuit deze werkplaats geen GitHub-toegang (geen `gh`
CLI, geen credentials) en kan deze reactie dus niet zelf plaatsen. Plak
onderstaande tekst als comment op issue #142, bijvoorbeeld met:

    gh issue comment 142 --repo ginkelsoft-development/buildora --body-file .zyra/issue-142-comment.md

--- comment hieronder dit lijntje ---

Reproductie bevestigd op branch `zyra/reproductie-van-issue-142-ginkelsoft-dev-c803adf2`
(commit 24fce54), in `tests/Unit/Fields/FieldContractTest.php`.

**Oorzaak**: `Field::make()` gebruikt `new self()` in plaats van
`new static()`. Bij subklassen die `make()` niet zelf overschrijven
(zoals `HasOneField`) is `self` lexicaal gebonden aan `Field`, waardoor
late static binding niet werkt.

**Test 1 — `HasOneField::make()` geeft geen `HasOneField` terug**

```
HasOneField::make() geeft een Ginkelsoft\Buildora\Fields\Field terug in
plaats van een HasOneField - Field::make() gebruikt "new self()" i.p.v.
"new static()".
Failed asserting that an instance of class Ginkelsoft\Buildora\Fields\Field
is an instance of class Ginkelsoft\Buildora\Fields\Types\HasOneField.
```

**Test 2 — `ViewField::make('first_name')` heeft een leeg label**

`ViewField::make()` overschrijft `make()` wel, maar gebruikt als
standaardwaarde `?string $label = ''` i.p.v. `null`. Daardoor triggert
`$label ?? ucfirst(...)` de afleiding uit de naam nooit.

```
ViewField::make('first_name') levert een leeg label op in plaats van
"First Name".
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
-'First Name'
+''
```

**Conclusie**: beide symptomen uit de issue zijn gereproduceerd met twee
automatische tests die nu falen (rood). Er is bewust geen productiecode
aangepast — dit is puur de reproductie, klaar om door Noor opgepakt te
worden voor de fix (`new static()` in `Field::make()`, en het label van
`ViewField` opnieuw bekijken).

Draai lokaal: `vendor/bin/phpunit tests/Unit/Fields/FieldContractTest.php`
Proefdraaien van de package (Orchestra Testbench): `.zyra/proef.sh`
