<?php

// Testfixture voor issue #121: een Buildora-resource met een FileField dat
// (net als in de praktijk) ->accept() en ->maxSize() instelt. Deze fixture
// bewijst dat die instellingen puur decoratief zijn: er is geen server-side
// afdwinging van extensie/MIME-type of bestandsgrootte.

namespace App\Buildora\Resources;

use App\Models\Document;
use Ginkelsoft\Buildora\Fields\Types\FileField;
use Ginkelsoft\Buildora\Resources\BuildoraResource;

if (!class_exists(DocumentBuildora::class)) {
    class DocumentBuildora extends BuildoraResource
    {
        public static function modelClass(): string
        {
            return Document::class;
        }

        public function defineFields(): array
        {
            return [
                FileField::make('attachment')
                    ->accept('image/*')
                    ->maxSize(200) // 200 KB — zie FileUploadSecurityTest: wordt niet afgedwongen.
                    ->disk('public')
                    ->path('uploads'),
            ];
        }
    }
}
