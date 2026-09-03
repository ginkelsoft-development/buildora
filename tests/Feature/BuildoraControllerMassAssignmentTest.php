<?php

namespace Ginkelsoft\Buildora\Tests\Feature;

use Ginkelsoft\Buildora\Http\Controllers\BuildoraController;
use Ginkelsoft\Buildora\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * Regression tests for issue #120.
 *
 * Vraag: kan een aanvaller via store()/update() van BuildoraController velden
 * of relaties beïnvloeden die niet in Resource::defineFields() staan?
 *
 * Bevindingen:
 * - Kolommen (via ::create()/->update()) waren al veilig: $filteredData wordt
 *   altijd teruggebracht tot de veldnamen uit defineFields().
 * - handleRelationships() gebruikte echter $request->all() ongefilterd. Elke
 *   public, parameterloze methode op het model die een Eloquent-relatie
 *   teruggeeft (bv. een BelongsToMany "roles" relatie) kon zo gesynchroniseerd
 *   worden door simpelweg een gelijknamig veld in de request mee te sturen,
 *   ook al stond die relatie niet in defineFields(). Dat is een mass
 *   assignment-kwetsbaarheid op relatieniveau (bv. privilege-escalatie via
 *   een niet-geëxposeerde "roles"-relatie).
 */
class BuildoraControllerMassAssignmentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('mass_assignment_test_roles', function ($table) {
            $table->increments('id');
            $table->string('name');
        });

        Schema::create('mass_assignment_test_projects', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('mass_assignment_test_project_role', function ($table) {
            $table->unsignedInteger('mass_assignment_test_project_id');
            $table->unsignedInteger('mass_assignment_test_role_id');
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('mass_assignment_test_project_role');
        Schema::dropIfExists('mass_assignment_test_projects');
        Schema::dropIfExists('mass_assignment_test_roles');

        parent::tearDown();
    }

    /** @test */
    public function store_does_not_sync_a_relation_that_is_not_defined_in_define_fields(): void
    {
        $role = MassAssignmentTestRole::create(['name' => 'admin']);

        $request = Request::create('/buildora/resource/massassignmenttestproject', 'POST', [
            'name' => 'Legit project',
            // "roles" bestaat niet in MassAssignmentTestProjectBuildora::defineFields(),
            // maar wel als BelongsToMany-relatie op het model.
            'roles' => [$role->id],
        ]);

        $controller = new BuildoraController();
        $controller->store($request, 'massassignmenttestproject');

        $project = MassAssignmentTestProject::firstOrFail();

        $this->assertSame('Legit project', $project->name);
        $this->assertCount(
            0,
            $project->roles,
            'Een relatie die niet in defineFields() staat, mag niet via de request gesynchroniseerd worden.'
        );
    }

    /** @test */
    public function update_does_not_sync_a_relation_that_is_not_defined_in_define_fields(): void
    {
        $project = MassAssignmentTestProject::create(['name' => 'Legit project']);
        $role = MassAssignmentTestRole::create(['name' => 'admin']);

        $request = Request::create('/buildora/resource/massassignmenttestproject/' . $project->id, 'PUT', [
            'name' => 'Renamed project',
            'roles' => [$role->id],
        ]);

        $controller = new BuildoraController();
        $controller->update($request, 'massassignmenttestproject', $project->id);

        $project->refresh();

        $this->assertSame('Renamed project', $project->name);
        $this->assertCount(
            0,
            $project->roles,
            'Een relatie die niet in defineFields() staat, mag niet via de request gesynchroniseerd worden.'
        );
    }

    /** @test */
    public function store_still_syncs_a_relation_that_is_explicitly_defined_in_define_fields(): void
    {
        $role = MassAssignmentTestRole::create(['name' => 'admin']);

        $request = Request::create('/buildora/resource/massassignmenttestprojectwithroles', 'POST', [
            'name' => 'Legit project',
            'roles' => [$role->id],
        ]);

        $controller = new BuildoraController();
        $controller->store($request, 'massassignmenttestprojectwithroles');

        $project = MassAssignmentTestProject::firstOrFail();

        $this->assertSame('Legit project', $project->name);
        $this->assertCount(
            1,
            $project->roles,
            'Een relatie die wel in defineFields() staat, moet nog steeds werken.'
        );
    }
}

/**
 * Testfixtures voor deze testclass. Ze staan bewust in dit bestand zodat de
 * test zelfstandig blijft draaien zonder aparte host-app.
 */
class MassAssignmentTestRole extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'mass_assignment_test_roles';
    protected $fillable = ['name'];
    public $timestamps = false;
}

class MassAssignmentTestProject extends \Illuminate\Database\Eloquent\Model
{
    use \Ginkelsoft\Buildora\Traits\HasBuildora;

    protected $table = 'mass_assignment_test_projects';
    protected $fillable = ['name'];

    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            MassAssignmentTestRole::class,
            'mass_assignment_test_project_role',
            'mass_assignment_test_project_id',
            'mass_assignment_test_role_id'
        );
    }
}

namespace App\Buildora\Resources;

use Ginkelsoft\Buildora\Fields\Types\BelongsToManyField;
use Ginkelsoft\Buildora\Fields\Types\TextField;
use Ginkelsoft\Buildora\Resources\BuildoraResource;
use Ginkelsoft\Buildora\Tests\Feature\MassAssignmentTestProject;

/**
 * Resource waarvan defineFields() de "roles"-relatie bewust NIET blootstelt.
 */
class MassAssignmentTestProjectBuildora extends BuildoraResource
{
    public function defineFields(): array
    {
        return [
            TextField::make('name', 'Name')->validation(['required', 'string', 'max:255']),
        ];
    }

    public static function modelClass(): string
    {
        return MassAssignmentTestProject::class;
    }
}

/**
 * Resource waarvan defineFields() de "roles"-relatie wel expliciet definieert,
 * om te bevestigen dat legitiem gebruik van relatievelden blijft werken.
 */
class MassAssignmentTestProjectWithRolesBuildora extends BuildoraResource
{
    public function defineFields(): array
    {
        return [
            TextField::make('name', 'Name')->validation(['required', 'string', 'max:255']),
            BelongsToManyField::make('roles', 'Roles'),
        ];
    }

    public static function modelClass(): string
    {
        return MassAssignmentTestProject::class;
    }
}
