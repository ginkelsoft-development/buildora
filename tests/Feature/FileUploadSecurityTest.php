<?php

namespace Ginkelsoft\Buildora\Tests\Feature;

use App\Models\Document;
use Ginkelsoft\Buildora\Http\Controllers\BuildoraController;
use Ginkelsoft\Buildora\Tests\TestCase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Reproductie voor issue #121: "FileField mist een verplichte
 * extensie/MIME-whitelist en deny-list voor executables."
 *
 * Deze tests leggen het HUIDIGE gedrag vast van de upload-flow die
 * BuildoraController::store()/update() gebruikt voor elk FileField:
 *
 *   $path = $uploadedFile->store($field->getPath() ?? 'uploads', $disk);
 *
 * Er wordt nergens een `mimes:`/`mimetypes:`/`max:`-regel afgeleid van
 * FileField::accept()/maxSize()/imageDimensions(). Die methodes vullen
 * alleen help-tekst en het HTML `accept`-attribuut + een client-side
 * Alpine.js-check (resources/views/components/input/file.blade.php) — beide
 * triviaal te omzeilen door de request direct te versturen (curl/Postman/
 * aangepaste HTML).
 *
 * Deze tests wijzigen geen productiecode; ze tonen alleen aan wat er nu
 * gebeurt, zodat een vervolgtaak de fix (server-side whitelist + deny-list
 * + finfo-detectie + UUID-bestandsnamen) kan bouwen en tegen deze tests kan
 * verifiëren (rood -> groen).
 */
class FileUploadSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        require_once __DIR__ . '/../Fixtures/DocumentModel.php';
        require_once __DIR__ . '/../Fixtures/DocumentBuildora.php';

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('attachment')->nullable();
            $table->timestamps();
        });

        Storage::fake('public');
    }

    /**
     * @test
     *
     * Kern van issue #121: een FileField met ->accept('image/*') slaat
     * gewoon een .php-bestand op, omdat 'accept' nooit tot een server-side
     * validatieregel wordt vertaald.
     */
    public function it_stores_a_php_file_even_though_the_field_only_accepts_images(): void
    {
        $malicious = UploadedFile::fake()->createWithContent(
            'shell.php',
            "<?php system(\$_GET['cmd']); ?>"
        );

        $response = (new BuildoraController())->store(
            $this->requestWithFile($malicious),
            'document'
        );

        $this->assertSame(302, $response->getStatusCode());

        $document = Document::first();
        $this->assertNotNull($document, 'Record is aangemaakt ondanks kwaadaardige upload.');
        $this->assertNotNull($document->attachment, 'Bestand is opgeslagen zonder server-side typecontrole.');

        Storage::disk('public')->assertExists($document->attachment);
    }

    /**
     * @test
     *
     * Zelfs zonder enige ->accept() te zetten, is er geen ingebouwde
     * deny-list voor uitvoerbare extensies (.php, .phtml, .phar, .cgi, ...).
     */
    public function it_has_no_builtin_deny_list_for_executable_extensions(): void
    {
        foreach (['shell.phtml', 'shell.phar', 'shell.cgi'] as $filename) {
            Document::query()->delete();

            $malicious = UploadedFile::fake()->createWithContent(
                $filename,
                "<?php system(\$_GET['cmd']); ?>"
            );

            (new BuildoraController())->store($this->requestWithFile($malicious), 'document');

            $document = Document::first();
            $this->assertNotNull(
                $document->attachment ?? null,
                "Bestand [$filename] had geweigerd moeten worden door een deny-list, maar is opgeslagen."
            );
        }
    }

    /**
     * @test
     *
     * ->maxSize(200) (200 KB) op het FileField wordt nergens afgedwongen:
     * een bestand van 5 MB wordt gewoon geaccepteerd en opgeslagen.
     */
    public function it_stores_a_file_that_exceeds_the_configured_max_size(): void
    {
        $oversized = UploadedFile::fake()->create('big.jpg', 5000); // 5000 KB, veld staat max. 200 KB toe

        (new BuildoraController())->store($this->requestWithFile($oversized), 'document');

        $document = Document::first();
        $this->assertNotNull($document);
        $this->assertNotNull($document->attachment, 'Te groot bestand is toch opgeslagen: maxSize() wordt niet gecontroleerd.');
    }

    /**
     * @test
     *
     * Documenteert het huidige, WEL veilige gedrag rond padtraversal: Laravel's
     * UploadedFile::store() gebruikt standaard een willekeurige hash-naam in
     * plaats van de door de client opgegeven bestandsnaam, dus een naam als
     * "../../../../etc/passwd" leidt niet tot het escapen van de upload-map.
     * Dit is gedrag van het Laravel-framework, niet een bewuste
     * beveiligingsmaatregel van Buildora/FileField — vastgelegd zodat een
     * toekomstige "mooiere bestandsnaam behouden"-feature dit niet stilzwijgend
     * doorbreekt.
     */
    public function path_traversal_in_the_original_filename_does_not_escape_the_upload_directory(): void
    {
        $traversal = UploadedFile::fake()->createWithContent(
            '../../../../etc/passwd',
            'root:x:0:0:root:/root:/bin/bash'
        );

        (new BuildoraController())->store($this->requestWithFile($traversal), 'document');

        $document = Document::first();
        $this->assertNotNull($document->attachment);
        $this->assertStringStartsWith('uploads/', $document->attachment);
        $this->assertStringNotContainsString('..', $document->attachment);

        Storage::disk('public')->assertExists($document->attachment);
    }

    private function requestWithFile(UploadedFile $file): Request
    {
        $request = Request::create('/buildora/document', 'POST');
        $request->files->set('attachment', $file);

        return $request;
    }
}
