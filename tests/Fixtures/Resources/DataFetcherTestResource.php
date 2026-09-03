<?php

namespace Ginkelsoft\Buildora\Tests\Fixtures\Resources;

use Ginkelsoft\Buildora\Fields\Field;
use Ginkelsoft\Buildora\Fields\Types\TextField;
use Ginkelsoft\Buildora\Resources\BuildoraResource;
use Ginkelsoft\Buildora\Tests\Fixtures\Models\DataFetcherTestModel;

/**
 * Minimale resource, uitsluitend gebruikt om DataFetcher te testen.
 *
 * - "name" is expliciet sorteerbaar gemaakt.
 * - "secret_score" bestaat wel als echte kolom in de database, maar is
 *   bewust NIET als sorteerbaar gemarkeerd. Deze regressietest bewijst dat
 *   DataFetcher dat respecteert: alleen expliciet gedefinieerde, sorteerbare
 *   velden mogen als sorteerkolom gebruikt worden.
 */
class DataFetcherTestResource extends BuildoraResource
{
    public static function modelClass(): string
    {
        return DataFetcherTestModel::class;
    }

    public function defineFields(): array
    {
        return [
            TextField::make('name', 'Name'), // TextField is standaard sortable()
            Field::make('secret_score', 'Secret score'), // bewust niet sortable
        ];
    }
}
