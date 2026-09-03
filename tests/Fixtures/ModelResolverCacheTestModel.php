<?php

namespace Ginkelsoft\Buildora\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Ginkelsoft\Buildora\Traits\HasBuildora;

/**
 * Fixture model used to test ModelResolver caching behaviour.
 */
class ModelResolverCacheTestModel extends Model
{
    use HasBuildora;

    protected $table = 'model_resolver_cache_test_models';
}
