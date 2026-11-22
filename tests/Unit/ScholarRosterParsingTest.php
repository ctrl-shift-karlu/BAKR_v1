<?php

namespace Tests\Unit;

use App\Models\Scholar;
use App\Services\Provider\ScholarRoster;
use PHPUnit\Framework\TestCase;

/** How the roster reads typed values: Scholar IDs (as typed, no format), mobile numbers, birthdays and genders. */
class ScholarRosterParsingTest extends TestCase
{
    public function test_scholar_ids_are_kept_as_typed(): void
    {
        $this->assertSame('SCID-2026-0001', Scholar::normalizeCode('  SCID-2026-0001 '));
        $this->assertSame('dost 12/34', Scholar::normalizeCode('dost   12/34'));   // inner spaces collapsed, case kept
        $this->assertSame('A1', Scholar::normalizeCode('A1'));                     // any shape is fine
        $this->assertNull(Scholar::normalizeCode('   '));
        $this->assertNull(Scholar::normalizeCode(null));
    }

    public function test_mobile_numbers_are_stored_as_09_numbers(): void
    {
        foreach (['0917 123 4567', '+63 917-123-4567', '639171234567', '9171234567', '(0917) 123.4567'] as $in) {
            $this->assertSame('09171234567', ScholarRoster::normalizeMobile($in), $in);
        }
        foreach (['12345', '082 123 4567', '0917 123 456', 'call me'] as $bad) {
            $this->assertNull(ScholarRoster::normalizeMobile($bad), $bad);
        }
    }

    public function test_birthdays_from_excel_and_text(): void
    {
        $this->assertSame('2005-03-14', ScholarRoster::birthdateValue('38425'));        // Excel date cell
        $this->assertSame('2005-03-14', ScholarRoster::birthdateValue('2005-03-14'));
        $this->assertSame('2005-03-14', ScholarRoster::birthdateValue('03/14/2005'));   // month first
        $this->assertSame('2005-03-04', ScholarRoster::birthdateValue('3/4/2005'));
        $this->assertSame('2005-03-14', ScholarRoster::birthdateValue('March 14, 2005'));
        $this->assertSame('14/03/2005', ScholarRoster::birthdateValue('14/03/2005'));   // no 14th month: left as typed (reported)
        $this->assertSame('2005', ScholarRoster::birthdateValue('2005'));               // a bare year is not a day number
    }

    public function test_gender_is_male_or_female_only(): void
    {
        $this->assertSame(['male' => 'Male', 'female' => 'Female'], Scholar::GENDERS);
        $this->assertSame('female', ScholarRoster::genderKey('F'));
        $this->assertSame('female', ScholarRoster::genderKey(' female '));
        $this->assertSame('male', ScholarRoster::genderKey('Male'));
        $this->assertSame('male', ScholarRoster::genderKey('m'));
        // Anything else is left as typed, so validation reports the row.
        $this->assertSame('Prefer not to say', ScholarRoster::genderKey('Prefer not to say'));
        $this->assertSame('other', ScholarRoster::genderKey('other'));
    }
}
