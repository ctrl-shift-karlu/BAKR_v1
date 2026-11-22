<?php

namespace Tests\Feature;

use App\Models\Scholarship;
use Database\Seeders\ScholarshipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Short codes shown in pills, avatars and charts: official abbreviations, "DNSC A/B/C" for DNSC. */
class ScholarshipShortNameTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_scholarship_has_its_official_short_code(): void
    {
        $this->seed(ScholarshipSeeder::class);

        $this->assertSame([
            'bfar-fsp' => 'FSP',
            'bpmsp' => 'BPMSP',
            'cebuana-clfi' => 'CLFI',
            'ched-cmsp' => 'CMSP',
            'ched-tdp' => 'TDP',
            'ched-tes' => 'TES',
            'coscho' => 'CoScho',
            'dnsc-academic' => 'DNSC Academic',
            'dnsc-gia-a' => 'DNSC A',
            'dnsc-gia-b' => 'DNSC B',
            'dnsc-sa-c' => 'DNSC C',
            'dost-sei' => 'DOST-SEI',
            'grabscholar' => 'GrabScholar',
            'landbank-ilp' => 'ILP',
        ], Scholarship::orderBy('slug')->get()->mapWithKeys(fn ($s) => [$s->slug => $s->shortName()])->all());
    }

    public function test_a_renamed_scholarship_keeps_its_code_and_an_unknown_one_falls_back(): void
    {
        $this->assertSame('DNSC A', (new Scholarship)->forceFill(['slug' => 'dnsc-gia-a', 'name' => 'Renamed by the provider'])->shortName());
        $this->assertSame('XYZ', (new Scholarship)->forceFill(['slug' => 'new-one', 'name' => 'Some New Grant (XYZ)'])->shortName());
        $this->assertSame('SNG', (new Scholarship)->forceFill(['slug' => 'new-two', 'name' => 'Some New Grant'])->shortName());
    }
}
