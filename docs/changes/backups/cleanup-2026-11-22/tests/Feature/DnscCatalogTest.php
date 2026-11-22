<?php

namespace Tests\Feature;

use App\Matching\ProgramCatalog;
use App\Models\Campus;
use App\Models\Program;
use App\Models\User;
use Database\Seeders\DnscCatalogSeeder;
use Database\Seeders\ScholarshipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** The matching form's campus → program lists (config/dnsc_campuses.php → DnscCatalogSeeder). */
class DnscCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_seeder_fills_every_campus_with_its_programs_and_is_safe_to_rerun(): void
    {
        $this->seed(DnscCatalogSeeder::class);
        $this->seed(DnscCatalogSeeder::class);   // no duplicates

        $this->assertSame(['Asuncion Campus', 'Carmen Campus', 'Main (Panabo Campus)', 'Samal Campus', 'Santo Tomas Campus', 'Talaingod Campus'], Campus::orderBy('campus_name')->pluck('campus_name')->all());
        $this->assertSame(15 + 2 + 2 + 1 + 1 + 1, Program::count());   // DNSC's official list: Main 15, Carmen 2, Samal 2, Talaingod 1, Asuncion 1, Santo Tomas 1
        $this->assertSame(18, Program::distinct()->count('program_name'));   // every program is offered somewhere
    }

    public function test_each_campus_lists_only_the_programs_dnsc_offers_there(): void
    {
        $this->seed(DnscCatalogSeeder::class);
        $at = fn (string $campus) => Program::whereHas('campus', fn ($q) => $q->where('campus_name', $campus))->orderBy('program_name')->pluck('program_name')->all();

        // From https://dnsc.edu.ph/program-offering-by-campus/ (docs/sources/dnsc/program-offering-by-campus/NOTES.md).
        $this->assertSame(['Bachelor of Science in Agro-Forestry'], $at('Talaingod Campus'));
        $this->assertSame(['Bachelor of Physical Education', 'Bachelor of Science in Criminology'], $at('Carmen Campus'));
        $this->assertSame(['Bachelor of Science in Information Technology'], $at('Asuncion Campus'));
        $this->assertSame(['Bachelor of Science in Information Technology', 'Bachelor of Science in Marine Biology'], $at('Samal Campus'));
        $this->assertSame(['Bachelor of Secondary Education - Mathematics'], $at('Santo Tomas Campus'));

        $main = $at('Main (Panabo Campus)');
        $this->assertCount(15, $main);
        foreach (['Bachelor of Science in Agro-Forestry', 'Bachelor of Science in Criminology', 'Bachelor of Physical Education'] as $notAtMain) {
            $this->assertNotContains($notAtMain, $main);
        }
    }

    public function test_a_program_removed_from_a_campus_disappears_but_the_rest_keep_their_ids(): void
    {
        $this->seed(DnscCatalogSeeder::class);
        $mainName = 'Main (Panabo Campus)';
        $keep = Program::where('program_name', 'Bachelor of Science in Information Technology')->whereHas('campus', fn ($q) => $q->where('campus_name', $mainName))->value('id');

        $campuses = config('dnsc_campuses.campuses');
        $campuses[$mainName]['programs'] = array_values(array_diff($campuses[$mainName]['programs'], ['Bachelor of Science in Tourism Management']));
        config(['dnsc_campuses.campuses' => $campuses]);
        $this->seed(DnscCatalogSeeder::class);

        $main = Campus::where('campus_name', $mainName)->firstOrFail();
        $this->assertSame(14, Program::where('campus_id', $main->id)->count());
        $this->assertFalse(Program::where('campus_id', $main->id)->where('program_name', 'Bachelor of Science in Tourism Management')->exists());
        $this->assertSame($keep, Program::where('campus_id', $main->id)->where('program_name', 'Bachelor of Science in Information Technology')->value('id'));   // a program that stays keeps its id
        $this->assertSame(2, Program::whereHas('campus', fn ($q) => $q->where('campus_name', 'Carmen Campus'))->count());   // other campuses unchanged
    }

    public function test_every_program_is_known_to_the_rules_and_has_a_field(): void
    {
        $this->seed(DnscCatalogSeeder::class);

        $ruleNames = [];
        foreach (config('scholarships.scholarships') as $s) {
            foreach ($s['rules'] ?? [] as $rule) {
                if (($rule['type'] ?? null) === 'program_in') {
                    $ruleNames = array_merge($ruleNames, $rule['pass'] ?? [], $rule['maybe'] ?? [], $rule['not'] ?? []);
                }
            }
        }
        foreach (Program::distinct()->pluck('program_name') as $name) {
            $this->assertContains($name, $ruleNames, "{$name} is not in the DOST program lists, so it would always be 'confirm'.");
            $this->assertNotSame(ProgramCatalog::UNRECOGNIZED, ProgramCatalog::fieldFor($name), "{$name} has no field in config/program_fields.php.");
        }
    }

    public function test_the_matching_form_lists_the_campuses(): void
    {
        $this->seed(ScholarshipSeeder::class);
        $this->seed(DnscCatalogSeeder::class);
        $u = new User;
        $u->forceFill(['name' => 'Ana', 'email' => 'ana@example.test', 'password' => Hash::make('x'), 'role' => 'student', 'status' => 'active'])->save();

        $html = $this->actingAs($u)->get('/student/application')->assertOk()->getContent();
        foreach (['Main (Panabo Campus)', 'Carmen Campus', 'Samal Campus', 'Talaingod Campus', 'Asuncion Campus', 'Santo Tomas Campus', 'Bachelor of Science in Marine Biology'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
    }
}
