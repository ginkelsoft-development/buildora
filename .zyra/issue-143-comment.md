Reactie voor GitHub-issue #143 (ginkelsoft-development/buildora)
=================================================================

LET OP: Bram heeft vanuit deze werkplaats geen GitHub-toegang (geen `gh`
CLI, geen credentials) en kan deze reactie dus niet zelf plaatsen. Plak
onderstaande tekst als comment op issue #143, bijvoorbeeld met:

    gh issue comment 143 --repo ginkelsoft-development/buildora --body-file .zyra/issue-143-comment.md

--- comment hieronder dit lijntje ---

Reproductie bevestigd op branch `zyra/reproductie-van-issue-143-ginkelsoft-dev-1c96643d`,
in `tests/Unit/Fields/CurrencyFieldTest.php`.

**Oorzaak**: `CurrencyField::getDisplayValue()` geeft de waarde ongewijzigd
door aan `number_format()` (`src/Fields/Types/CurrencyField.php`, regel 51).
`number_format()` heeft de signatuur `number_format(int|float $num, ...)`.

**Genuanceerde bevinding**: het letterlijke voorbeeld uit het issue —
`'12.50'` uit een DECIMAL-kolom — crasht **niet** op PHP 8.4. PHP coerceert
in "weak mode" een well-formed numerieke string automatisch naar `float`,
ook als de waarde via PDO als string binnenkomt (getest met een echt
Eloquent-model + `PDO::ATTR_STRINGIFY_FETCHES`, zoals MySQL's DECIMAL-gedrag).
Deze groene controletest staat ook in de testklasse.

De crash uit het issue is wel exact reproduceerbaar met twee plausibele
varianten van "de waarde is een string":

**Test 1 — lege string (`''`)**, bijv. een leeg formulierveld dat nog niet
naar `null` is gecast:

```
TypeError: number_format(): Argument #1 ($num) must be of type int|float,
string given
```

**Test 2 — niet-numerieke string (`'abc'`)**, bijv. corrupte data of een
verkeerd gekoppeld veld: dezelfde `TypeError`.

Beide tests (`itCurrentlyThrowsATypeErrorForAnEmptyStringValue`,
`itCurrentlyThrowsATypeErrorForANonNumericStringValue`) verwachten deze
`TypeError` expliciet en slagen dus (groen) omdat ze de huidige bugsituatie
correct vastleggen.

Daarnaast zijn er twee grensgeval-tests opgenomen voor ná de fix
(`itShouldReturnADashInsteadOfCrashingForAnEmptyStringValue`,
`itShouldNotCrashForANonNumericStringValue`), die bewust **rood** zijn
zolang de fix niet is doorgevoerd:

```
1) itShouldReturnADashInsteadOfCrashingForAnEmptyStringValue
TypeError: number_format(): Argument #1 ($num) must be of type int|float, string given

2) itShouldNotCrashForANonNumericStringValue
TypeError: number_format(): Argument #1 ($num) must be of type int|float, string given
```

**Conclusie**: de kern van issue #143 klopt — `CurrencyField` mist een
defensieve cast/validatie voordat de waarde naar `number_format()` gaat —
maar het concrete voorbeeld met `'12.50'` is op PHP 8.4 geen reproductie
meer. Lege strings en niet-numerieke strings crashen wel degelijk met
dezelfde `TypeError` als gemeld. Er is bewust geen productiecode aangepast;
dit is puur de reproductie, klaar voor Noor om op te pakken (defensieve
cast/validatie in `CurrencyField::getDisplayValue()`, met '-' als resultaat
voor lege/niet-numerieke waarden, analoog aan de bestaande null-afhandeling).

Draai lokaal: `vendor/bin/phpunit tests/Unit/Fields/CurrencyFieldTest.php`
Proefdraaien van de package (Orchestra Testbench): `.zyra/proef.sh`
