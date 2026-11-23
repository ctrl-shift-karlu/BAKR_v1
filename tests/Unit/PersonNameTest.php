<?php

namespace Tests\Unit;

use App\Support\PersonName;
use PHPUnit\Framework\TestCase;

/** Names in parts (owner, 2026-11-23): compose, suffixes, and the one-time split of old one-line names. */
class PersonNameTest extends TestCase
{
    public function test_parts_are_cleaned_and_composed(): void
    {
        $p = PersonName::parts(['first_name' => '  Juan  ', 'middle_name' => 'Andres', 'last_name' => 'Dela   Cruz', 'suffix' => 'jr']);
        $this->assertSame(['first_name' => 'Juan', 'middle_name' => 'Andres', 'last_name' => 'Dela Cruz', 'suffix' => 'Jr.', 'full' => 'Juan Andres Dela Cruz Jr.'], $p);

        // Middle name and suffix are optional.
        $this->assertSame('Ana Reyes', PersonName::parts(['first_name' => 'Ana', 'middle_name' => '', 'last_name' => 'Reyes', 'suffix' => ''])['full']);
        $this->assertNull(PersonName::parts(['first_name' => 'Ana', 'last_name' => 'Reyes'])['middle_name']);
    }

    public function test_suffixes_are_understood(): void
    {
        foreach (['jr' => 'Jr.', 'JR.' => 'Jr.', 'Junior' => 'Jr.', 'sr' => 'Sr.', 'iii' => 'III', '3rd' => 'III', 'IV' => 'IV', '' => null] as $typed => $stored) {
            $this->assertSame($stored, PersonName::normalizeSuffix($typed), "suffix {$typed}");
        }
        $this->assertSame('Esq.', PersonName::normalizeSuffix('Esq.'));   // unknown: kept as typed, so validation refuses it
    }

    public function test_old_one_line_names_are_split_once(): void
    {
        $this->assertSame(['first_name' => 'Maria', 'middle_name' => null, 'last_name' => 'Santos', 'suffix' => null], PersonName::split('Maria Santos'));
        $this->assertSame(['first_name' => 'Juan', 'middle_name' => null, 'last_name' => 'Dela Cruz', 'suffix' => 'Jr.'], PersonName::split('Juan Dela Cruz Jr.'));
        $this->assertSame(['first_name' => 'Maria Luz', 'middle_name' => null, 'last_name' => 'De los Santos', 'suffix' => 'III'], PersonName::split('Maria Luz De los Santos III'));
        $this->assertSame(['first_name' => 'Ana', 'middle_name' => null, 'last_name' => null, 'suffix' => null], PersonName::split('Ana'));
        $this->assertSame(['first_name' => null, 'middle_name' => null, 'last_name' => null, 'suffix' => null], PersonName::split('  '));
    }

    public function test_only_name_characters_are_allowed(): void
    {
        foreach (['Juan', 'Ñiño', "O'Brien", 'Santos-Cruz', 'Ma. Luz', 'José'] as $ok) {
            $this->assertMatchesRegularExpression(PersonName::PATTERN, $ok);
        }
        foreach (['Juan1', '<b>', '-Ana', 'a@b'] as $bad) {
            $this->assertDoesNotMatchRegularExpression(PersonName::PATTERN, $bad);
        }
    }
}
