<?php

namespace Ginkelsoft\Buildora\Tests\Fixtures\Models;

use Ginkelsoft\Buildora\Traits\HasBuildora;
use Illuminate\Database\Eloquent\Model;

/**
 * Minimaal Eloquent-model, uitsluitend gebruikt om DataFetcher tegen een
 * echte (sqlite) database te kunnen testen.
 */
class DataFetcherTestModel extends Model
{
    use HasBuildora;

    protected $table = 'df_test_items';

    protected $guarded = [];

    public $timestamps = false;
}
