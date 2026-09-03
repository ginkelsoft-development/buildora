<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Fields;

use Ginkelsoft\Buildora\Fields\Types\CurrencyField;
use Ginkelsoft\Buildora\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PDO;
use TypeError;

/**
 * Reproductie van issue #143.
 *
 * CurrencyField::getDisplayValue() geeft de waarde ongewijzigd door aan
 * number_format() (zie src/Fields/Types/CurrencyField.php, regel ~51).
 * number_format() heeft de signatuur number_format(int|float $num, ...).
 * PHP coerceert in "weak mode" een well-formed numerieke string (zoals
 * '12.50') automatisch naar float, maar gooit een TypeError zodra de
 * string niet (volledig) numeriek is — bijvoorbeeld een lege string ''
 * of een niet-numerieke waarde als 'abc'.
 *
 * Bevindingen van deze reproductie:
 * - Het letterlijke voorbeeld uit het issue ('12.50' uit een DECIMAL-kolom)
 *   crasht NIET op PHP 8.4: het is een well-formed numerieke string en
 *   wordt door PHP automatisch gecoerced. Dit is vastgelegd als groene
 *   controletest hieronder.
 * - Wel reproduceerbaar met exact dezelfde TypeError als in het issue:
 *   een lege string '' (bijv. een leeg ingevuld formulierveld dat nog
 *   niet naar null is gecast) en een niet-numerieke string 'abc'
 *   (bijv. corrupte data of een verkeerd gekoppeld veld). Dit zijn
 *   plausibele varianten van "de waarde is een string" uit een
 *   DECIMAL-kolom of formulierinvoer.
 *
 * @see https://github.com/ginkelsoft-development/buildora/issues/143
 */
class CurrencyFieldTest extends TestCase
{
    /**
     * Maak een eenvoudig "record"-object met een price-property, zoals
     * CurrencyField::getDisplayValue() dat via $model->{$this->name}
     * uitleest. Dit hoeft geen Eloquent-model te zijn.
     */
    private function makeRecord(mixed $price): object
    {
        return new class ($price) {
            public function __construct(public mixed $price)
            {
            }
        };
    }

    /** @test */
    public function itFormatsAFloatValueCorrectly(): void
    {
        $field = CurrencyField::make('price');

        $this->assertSame('€ 12,50', $field->getDisplayValue($this->makeRecord(12.5)));
    }

    /** @test */
    public function itFormatsAnIntegerValueCorrectly(): void
    {
        $field = CurrencyField::make('price');

        $this->assertSame('€ 12,00', $field->getDisplayValue($this->makeRecord(12)));
    }

    /** @test */
    public function itReturnsADashForANullValue(): void
    {
        $field = CurrencyField::make('price');

        $this->assertSame('-', $field->getDisplayValue($this->makeRecord(null)));
    }

    /**
     * Reproductie van het letterlijke voorbeeld uit issue #143, nu met een
     * echt Eloquent-model met een gemigreerde DECIMAL-kolom (zonder cast).
     *
     * Let op: SQLite/PDO geeft DECIMAL-kolommen standaard terug als native
     * float (dynamic typing), terwijl MySQL's PDO-driver DECIMAL-kolommen
     * altijd als string teruggeeft. Om dat laatste realistisch na te
     * bootsen wordt PDO::ATTR_STRINGIFY_FETCHES aangezet op de
     * testconnectie, zodat de waarde net als bij MySQL als string
     * binnenkomt.
     *
     * Uitkomst: dit crasht niet op PHP 8.4, omdat '12.50' een well-formed
     * numerieke string is die number_format() weak-typed accepteert.
     */
    /** @test */
    public function itDoesNotCrashForAWellFormedNumericStringFromADecimalColumn(): void
    {
        config([
            'database.connections.testbench.options' => [
                PDO::ATTR_STRINGIFY_FETCHES => true,
            ],
        ]);
        $this->app['db']->purge('testbench');

        Schema::create('currency_field_test_products', function (Blueprint $table): void {
            $table->id();
            $table->decimal('price', 10, 2)->nullable();
        });

        $model = new class extends Model {
            protected $table = 'currency_field_test_products';
            protected $guarded = [];
            public $timestamps = false;
        };

        $model->newQuery()->create(['price' => '12.50']);
        $record = $model->newQuery()->first();

        // Sanity check: de waarde komt als string binnen, zoals bij MySQL.
        $this->assertIsString($record->price);

        $field = CurrencyField::make('price');

        $this->assertSame('€ 12,50', $field->getDisplayValue($record));
    }

    /**
     * Reproductie: number_format() gooit een TypeError zodra de waarde een
     * lege string is.
     */
    /** @test */
    public function itCurrentlyThrowsATypeErrorForAnEmptyStringValue(): void
    {
        $field = CurrencyField::make('price');

        $this->expectException(TypeError::class);
        $this->expectExceptionMessageMatches('/number_format\(\).*must be of type int\|float, string given/');

        $field->getDisplayValue($this->makeRecord(''));
    }

    /**
     * Reproductie: number_format() gooit een TypeError zodra de waarde een
     * niet-numerieke string is.
     */
    /** @test */
    public function itCurrentlyThrowsATypeErrorForANonNumericStringValue(): void
    {
        $field = CurrencyField::make('price');

        $this->expectException(TypeError::class);
        $this->expectExceptionMessageMatches('/number_format\(\).*must be of type int\|float, string given/');

        $field->getDisplayValue($this->makeRecord('abc'));
    }

    /**
     * Grensgeval vastgelegd voor ná de fix van issue #143: een lege string
     * zou net als null een '-' moeten opleveren in plaats van een crash.
     *
     * Deze test faalt bewust (rood) zolang CurrencyField::getDisplayValue()
     * geen defensieve cast/validatie heeft. Bedoeld voor Noor bij het
     * doorvoeren van de fix uit issue #143.
     */
    /** @test */
    public function itShouldReturnADashInsteadOfCrashingForAnEmptyStringValue(): void
    {
        $field = CurrencyField::make('price');

        $this->assertSame('-', $field->getDisplayValue($this->makeRecord('')));
    }

    /**
     * Grensgeval vastgelegd voor ná de fix van issue #143: een
     * niet-numerieke string mag niet crashen.
     *
     * Deze test faalt bewust (rood) zolang de fix niet is doorgevoerd.
     * Bedoeld voor Noor bij het doorvoeren van de fix uit issue #143.
     */
    /** @test */
    public function itShouldNotCrashForANonNumericStringValue(): void
    {
        $field = CurrencyField::make('price');

        $result = $field->getDisplayValue($this->makeRecord('abc'));

        $this->assertIsString($result);
    }
}
