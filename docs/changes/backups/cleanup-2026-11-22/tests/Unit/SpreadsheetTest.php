<?php

namespace Tests\Unit;

use App\Support\Spreadsheet;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use ZipArchive;

/** The .xlsx reader on a file shaped the way Microsoft Excel saves it (shared strings, numbers, gaps). */
class SpreadsheetTest extends TestCase
{
    private function excelStyleFile(): string
    {
        $main = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
        $rel = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
        $parts = [
            '[Content_Types].xml' => '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"/>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="'.$main.'" xmlns:r="'.$rel.'"><sheets><sheet name="Roster" sheetId="1" r:id="rId3"/><sheet name="Other" sheetId="2" r:id="rId4"/></sheets></workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId4" Type="x" Target="worksheets/sheet2.xml"/><Relationship Id="rId3" Type="x" Target="/xl/worksheets/sheet1.xml"/></Relationships>',
            'xl/sharedStrings.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><sst xmlns="'.$main.'" count="5" uniqueCount="5">'
                .'<si><t>Full name</t></si><si><t>Student number</t></si><si><t>Stipend</t></si>'
                .'<si><r><rPr><b/></rPr><t>Juan </t></r><r><t>Dela Cruz</t></r></si><si><t xml:space="preserve"> Ana Reyes </t></si></sst>',
            'xl/worksheets/sheet1.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="'.$main.'"><sheetData>'
                .'<row r="1"><c r="A1" t="s"><v>0</v></c><c r="B1" t="s"><v>1</v></c><c r="D1" t="s"><v>2</v></c></row>'
                .'<row r="2"><c r="A2" t="s"><v>3</v></c><c r="B2"><v>2023000123</v></c><c r="D2"><v>7500.5</v></c></row>'
                .'<row r="3"/>'
                .'<row r="5"><c r="A5" t="s"><v>4</v></c><c r="B5"><v>2.023000124E9</v></c></row>'
                .'</sheetData></worksheet>',
            'xl/worksheets/sheet2.xml' => '<?xml version="1.0"?><worksheet xmlns="'.$main.'"><sheetData><row r="1"><c r="A1" t="inlineStr"><is><t>WRONG SHEET</t></is></c></row></sheetData></worksheet>',
        ];

        $path = tempnam(sys_get_temp_dir(), 'xl');
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::OVERWRITE);
        foreach ($parts as $name => $xml) {
            $zip->addFromString($name, $xml);
        }
        $zip->close();

        return $path;
    }

    public function test_reads_the_first_sheet_like_excel_saves_it(): void
    {
        $rows = Spreadsheet::read($this->excelStyleFile(), 'xlsx');

        $this->assertSame([
            ['Full name', 'Student number', '', 'Stipend'],
            ['Juan Dela Cruz', '2023000123', '', '7500.5'],
            ['Ana Reyes', '2023000124'],
        ], $rows);
    }

    public function test_old_xls_and_broken_files_give_a_readable_error(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('.xlsx');
        Spreadsheet::read(__FILE__, 'xls');
    }

    public function test_a_non_zip_xlsx_is_rejected(): void
    {
        $this->expectException(RuntimeException::class);
        Spreadsheet::read(__FILE__, 'xlsx');
    }

    public function test_the_writer_round_trips(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'xl');
        file_put_contents($path, Spreadsheet::xlsx([['Full name', 'Email'], ['Ñiño & <Co>', 'a@b.test']]));

        $this->assertSame([['Full name', 'Email'], ['Ñiño & <Co>', 'a@b.test']], Spreadsheet::read($path, 'xlsx'));
    }
}
