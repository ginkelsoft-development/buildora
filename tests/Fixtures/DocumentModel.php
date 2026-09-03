<?php

// Testfixture voor issue #121: minimaal Eloquent-model met een kolom voor
// een geüploade bijlage, gebruikt om het huidige (onveilige) gedrag van
// FileField/BuildoraController te reproduceren.

namespace App\Models;

use Ginkelsoft\Buildora\Traits\HasBuildora;
use Illuminate\Database\Eloquent\Model;

if (!class_exists(Document::class)) {
    class Document extends Model
    {
        use HasBuildora;

        protected $table = 'documents';

        protected $guarded = [];
    }
}
