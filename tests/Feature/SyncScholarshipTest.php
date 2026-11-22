<?php

namespace Tests\Feature;

use App\Models\Scholarship;
use Database\Seeders\ScholarshipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** bakr:sync-scholarship updates ONE scholarship from config, leaving the others (and provider edits) alone. */
class SyncScholarshipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ScholarshipSeeder::class);
        // Simulate an older TDP row and a provider edit on another scholarship.
        DB::table('scholarships')->where('slug', 'ched-tdp')->update(['overview' => 'Old text', 'rules' => json_encode([])]);
        DB::table('scholarships')->where('slug', 'ched-tes')->update(['overview' => 'Edited by the CHED provider']);
    }

    public function test_preview_changes_nothing(): void
    {
        $this->artisan('bakr:sync-scholarship', ['slug' => 'ched-tdp'])->assertSuccessful();

        $this->assertSame('Old text', Scholarship::where('slug', 'ched-tdp')->value('overview'));
        $this->assertSame(0, DB::table('scholarship_revisions')->count());
    }

    public function test_apply_updates_only_that_scholarship_and_records_a_revision(): void
    {
        $this->artisan('bakr:sync-scholarship', ['slug' => 'ched-tdp', '--apply' => true])->assertSuccessful();

        $tdp = Scholarship::where('slug', 'ched-tdp')->firstOrFail();
        $this->assertStringContainsString('CHED-UniFAST grant', $tdp->overview);
        $this->assertContains('indigency', array_column($tdp->rules, 'key'));
        $this->assertNotContains('income', array_column($tdp->rules, 'key'));
        $this->assertSame('2026-11-15', $tdp->verified_on->format('Y-m-d'));

        $this->assertSame('Edited by the CHED provider', Scholarship::where('slug', 'ched-tes')->value('overview'));
        $rev = DB::table('scholarship_revisions')->first();
        $this->assertNull($rev->user_id);
        $this->assertSame($tdp->id, $rev->scholarship_id);
    }

    public function test_unknown_slug_fails(): void
    {
        $this->artisan('bakr:sync-scholarship', ['slug' => 'nope'])->assertFailed();
    }
}
