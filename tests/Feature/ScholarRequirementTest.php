<?php

namespace Tests\Feature;

use App\Models\Scholar;
use App\Models\Scholarship;
use App\Models\User;
use App\Support\AcademicTerm;
use Database\Seeders\ScholarshipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Scholar accounts upload their maintaining requirements privately (scholar.requirements.*). */
class ScholarRequirementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seed(ScholarshipSeeder::class);
    }

    private function account(string $email = 'scholar@example.test', string $role = 'scholar'): User
    {
        $u = new User;
        $u->forceFill(['name' => 'Maria Santos', 'email' => $email, 'password' => Hash::make('ScholarPass123'), 'role' => $role, 'status' => 'active'])->save();

        return $u;
    }

    private function scholarOf(User $u): Scholar
    {
        return Scholar::create(['user_id' => $u->id, 'scholarship_id' => Scholarship::where('slug', 'ched-tdp')->value('id'), 'status' => 'active']);
    }

    public function test_scholar_uploads_a_requirement_privately(): void
    {
        $u = $this->account();
        $s = $this->scholarOf($u);
        $r = $s->requirements()->create(['term' => AcademicTerm::current(), 'name' => 'Certificate of Registration', 'status' => 'missing']);

        $this->actingAs($u);
        $this->get('/scholar/requirements')->assertOk()->assertSee('Certificate of Registration');

        $this->post("/scholar/requirements/{$r->id}/upload", ['file' => UploadedFile::fake()->create('cor.pdf', 100, 'application/pdf')])->assertRedirect();
        $r->refresh();
        $this->assertSame('submitted', $r->status);
        $this->assertSame('cor.pdf', $r->original_name);
        $this->assertStringStartsWith("scholar-requirements/{$s->id}/", $r->file_path);
        $this->assertStringNotContainsString('cor', basename($r->file_path));   // random stored name
        Storage::disk('local')->assertExists($r->file_path);

        $this->get("/scholar/requirements/{$r->id}/file")->assertOk()->assertDownload('cor.pdf');

        // Replacing removes the old file.
        $old = $r->file_path;
        $this->post("/scholar/requirements/{$r->id}/upload", ['file' => UploadedFile::fake()->create('cor.png', 50, 'image/png')])->assertRedirect();
        Storage::disk('local')->assertMissing($old);
    }

    public function test_uploads_are_validated(): void
    {
        $u = $this->account();
        $r = $this->scholarOf($u)->requirements()->create(['term' => AcademicTerm::current(), 'name' => 'COR', 'status' => 'missing']);
        $this->actingAs($u);

        $this->post("/scholar/requirements/{$r->id}/upload", ['file' => UploadedFile::fake()->create('evil.php', 10, 'application/x-php')])->assertSessionHasErrors('file');
        $this->post("/scholar/requirements/{$r->id}/upload", ['file' => UploadedFile::fake()->create('big.pdf', 6000, 'application/pdf')])->assertSessionHasErrors('file');
        $this->assertSame('missing', $r->fresh()->status);

        $r->update(['status' => 'approved']);
        $this->post("/scholar/requirements/{$r->id}/upload", ['file' => UploadedFile::fake()->create('cor.pdf', 10, 'application/pdf')])->assertStatus(409);
    }

    public function test_each_document_takes_only_its_own_file_types_and_size(): void
    {
        $u = $this->account();
        $r = $this->scholarOf($u)->requirements()->create([
            'term' => AcademicTerm::current(), 'name' => 'COR', 'status' => 'missing',
            'accepted_types' => ['pdf'], 'max_kb' => 1024, 'instructions' => 'Signed copy only.',
        ]);
        $this->actingAs($u);

        $html = $this->get('/scholar/requirements')->assertOk()->assertSee('Signed copy only.')->assertSee('PDF, up to 1 MB')->getContent();
        $this->assertStringContainsString('data-upload-types="pdf"', $html);
        $this->assertStringContainsString('data-upload-max-kb="1024"', $html);

        $this->post("/scholar/requirements/{$r->id}/upload", ['file' => UploadedFile::fake()->create('cor.png', 10, 'image/png')])
            ->assertSessionHasErrors(['file' => 'Upload a PDF file.']);
        $this->post("/scholar/requirements/{$r->id}/upload", ['file' => UploadedFile::fake()->create('cor.pdf', 2000, 'application/pdf')])
            ->assertSessionHasErrors(['file' => 'The file must be 1 MB or smaller.']);
        $this->post("/scholar/requirements/{$r->id}/upload", ['file' => UploadedFile::fake()->create('cor.pdf', 500, 'application/pdf')])->assertSessionHasNoErrors();
        $this->assertSame('submitted', $r->fresh()->status);
    }

    public function test_a_document_that_applies_only_to_some_never_makes_a_scholar_inactive_or_blocks_a_stipend(): void
    {
        $s = $this->scholarOf($this->account());
        $term = AcademicTerm::current();
        $s->requirements()->create(['term' => $term, 'name' => 'COR', 'status' => 'approved', 'due_on' => now()->subDay()]);
        $s->requirements()->create(['term' => $term, 'name' => 'If on leave: LOA', 'status' => 'missing', 'is_optional' => true, 'due_on' => now()->subDay()]);

        $this->assertSame('active', $s->fresh()->standing());
        $this->assertNull($s->fresh()->load('scholarship')->releaseBlocker($term));
    }

    public function test_an_unsent_only_if_it_applies_document_is_not_a_to_do(): void
    {
        $u = $this->account();
        $s = $this->scholarOf($u);
        $term = AcademicTerm::current();
        $s->requirements()->create(['term' => $term, 'name' => 'COR', 'status' => 'approved']);
        $s->requirements()->create(['term' => $term, 'name' => 'If on leave: LOA', 'status' => 'missing', 'is_optional' => true]);
        $this->actingAs($u);

        $this->get('/scholar')->assertOk()->assertDontSee('Upload: If on leave: LOA')->assertSee('1/1');
        $this->get('/scholar/requirements')->assertOk()->assertSee('If on leave: LOA')   // still listed, can still be sent
            ->assertSee('Nothing needs your action right now.');
    }

    public function test_a_terminated_scholar_cannot_upload_any_more(): void
    {
        $u = $this->account();
        $s = $this->scholarOf($u);
        $r = $s->requirements()->create(['term' => AcademicTerm::current(), 'name' => 'COR', 'status' => 'missing']);
        $s->update(['status' => 'terminated']);
        $this->actingAs($u);

        $this->get('/scholar')->assertOk()->assertDontSee('Upload: COR')->assertSee('was terminated');
        $this->assertStringNotContainsString('data-upload-url', $this->get('/scholar/requirements')->getContent());
        $this->post("/scholar/requirements/{$r->id}/upload", ['file' => UploadedFile::fake()->create('cor.pdf', 10, 'application/pdf')])->assertForbidden();
        $this->assertSame('missing', $r->fresh()->status);
    }

    public function test_a_scholar_cannot_touch_another_scholars_requirement(): void
    {
        $r = $this->scholarOf($this->account('owner@example.test'))->requirements()
            ->create(['term' => AcademicTerm::current(), 'name' => 'COR', 'status' => 'submitted', 'file_path' => 'scholar-requirements/1/x.pdf']);
        Storage::disk('local')->put('scholar-requirements/1/x.pdf', 'secret');

        $this->actingAs($this->account());
        $this->get("/scholar/requirements/{$r->id}/file")->assertNotFound();
        $this->post("/scholar/requirements/{$r->id}/upload", ['file' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')])->assertNotFound();
    }

    public function test_student_accounts_have_no_upload_routes(): void
    {
        $student = $this->account('student@example.test', 'student');
        $r = $this->scholarOf($this->account())->requirements()->create(['term' => AcademicTerm::current(), 'name' => 'COR', 'status' => 'missing']);

        $this->actingAs($student);
        $this->post("/scholar/requirements/{$r->id}/upload", ['file' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')])->assertForbidden();
        $this->post("/student/requirements/{$r->id}/upload", ['file' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')])->assertNotFound();
        $this->get('/student/scholarship')->assertNotFound();
        $this->assertSame('missing', $r->fresh()->status);
    }
}
