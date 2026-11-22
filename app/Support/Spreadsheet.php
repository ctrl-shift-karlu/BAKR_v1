<?php

namespace App\Support;

use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

/**
 * Minimal spreadsheet reader/writer for the scholar roster import. No Composer package:
 * an .xlsx file is a zip of XML parts, read here with ZipArchive + SimpleXML.
 *
 * read():  first worksheet of an .xlsx, or a .csv/.txt file → list of rows (list of cell strings).
 *          Old binary .xls is not supported (the caller asks for "Save as .xlsx").
 * xlsx():      one-sheet .xlsx file (the roster template download; tests).
 * workbook():  several sheets with numbers, bold cells and column widths (admin Analytics export).
 *              xlsx() is workbook() with a single sheet.
 *
 * Safety: XML is parsed with LIBXML_NONET and without LIBXML_NOENT (no external entities),
 * each part is size-checked before it is unzipped (zip bombs), and rows are capped.
 */
final class Spreadsheet
{
    public const MAX_ROWS = 2000;

    public const MAX_COLUMNS = 30;

    /** Largest unzipped XML part we accept (the sheet, shared strings). */
    private const MAX_PART_BYTES = 25 * 1024 * 1024;

    private const NS_MAIN = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    private const NS_REL = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

    /**
     * @return list<list<string>> non-empty rows, each cell trimmed; at most MAX_ROWS + 1 rows
     *
     * @throws RuntimeException with a message that can be shown to the user
     */
    public static function read(string $path, string $extension): array
    {
        return match (strtolower($extension)) {
            'xlsx' => self::readXlsx($path),
            'csv', 'txt' => self::readCsv($path),
            'xls' => throw new RuntimeException('Old Excel files (.xls) cannot be read. In Excel, choose File › Save As › Excel Workbook (.xlsx) and upload that file.'),
            default => throw new RuntimeException('Upload an Excel file (.xlsx) or a CSV file.'),
        };
    }

    /** @return list<list<string>> */
    private static function readCsv(string $path): array
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new RuntimeException('The file could not be opened.');
        }

        $first = (string) fgets($handle);
        rewind($handle);
        // Excel in some regions saves CSV with semicolons.
        $delimiter = substr_count($first, ';') > substr_count($first, ',') ? ';' : ',';

        $rows = [];
        while (($cells = fgetcsv($handle, null, $delimiter, '"', '')) !== false) {
            if (count($rows) === 0 && isset($cells[0])) {
                $cells[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $cells[0]);   // UTF-8 BOM
            }
            if (self::push($rows, array_map(fn ($c) => self::clean((string) $c), $cells))) {
                break;
            }
        }
        fclose($handle);

        return $rows;
    }

    /**
     * Excel files are zip archives: without PHP's zip extension nothing .xlsx can be read or made. Throws a
     * message the page can show instead of a crash (2026-11-21: XAMPP's php.ini had `;extension=zip`).
     */
    private static function requireZip(): void
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Excel files can\'t be handled on this server right now (PHP\'s zip extension is off). Ask the BAKR administrator to turn on "extension=zip" in php.ini, or upload a CSV file instead.');
        }
    }

    /** @return list<list<string>> */
    private static function readXlsx(string $path): array
    {
        self::requireZip();
        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::RDONLY) !== true) {
            throw new RuntimeException('This is not a valid Excel (.xlsx) file.');
        }

        try {
            $shared = [];
            if ($zip->locateName('xl/sharedStrings.xml') !== false) {
                foreach (self::part($zip, 'xl/sharedStrings.xml')->children(self::NS_MAIN)->si as $si) {
                    $shared[] = self::richText($si);
                }
            }

            $sheet = self::part($zip, self::firstSheetPath($zip));
            $rows = [];
            foreach ($sheet->children(self::NS_MAIN)->sheetData->children(self::NS_MAIN)->row as $row) {
                $cells = [];
                $next = 0;
                foreach ($row->children(self::NS_MAIN)->c as $c) {
                    $attrs = $c->attributes();
                    $col = isset($attrs['r']) ? self::columnIndex((string) $attrs['r']) : $next;
                    $next = $col + 1;
                    if ($col >= self::MAX_COLUMNS) {
                        continue;
                    }
                    $cells[$col] = self::cellText($c, (string) ($attrs['t'] ?? ''), $shared);
                }
                if ($cells === []) {
                    continue;
                }
                $line = array_fill(0, max(array_keys($cells)) + 1, '');
                foreach ($cells as $i => $v) {
                    $line[$i] = $v;
                }
                if (self::push($rows, $line)) {
                    break;
                }
            }

            return $rows;
        } finally {
            $zip->close();
        }
    }

    /** Adds a row unless it is empty. Returns true once the row cap is passed (caller stops reading). */
    private static function push(array &$rows, array $cells): bool
    {
        $cells = array_slice($cells, 0, self::MAX_COLUMNS);
        if (implode('', $cells) === '') {
            return false;
        }
        $rows[] = $cells;

        return count($rows) > self::MAX_ROWS;
    }

    /** The text of one sheet cell: a shared string, inline text, a boolean or a number. */
    private static function cellText(SimpleXMLElement $c, string $type, array $shared): string
    {
        $main = $c->children(self::NS_MAIN);
        $v = isset($main->v) ? (string) $main->v : '';

        return self::clean(match ($type) {
            's' => $shared[(int) $v] ?? '',
            'inlineStr' => isset($main->is) ? self::richText($main->is) : '',
            'b' => $v === '1' ? 'TRUE' : 'FALSE',
            'str', 'e' => $v,
            default => self::number($v),
        });
    }

    /** Whole numbers stored as floats ("2021001234", "1.5E+3") come back without decimals or exponent. */
    private static function number(string $v): string
    {
        if ($v === '' || ! is_numeric($v)) {
            return $v;
        }
        $f = (float) $v;
        if (floor($f) === $f && abs($f) < 1e15) {
            return sprintf('%.0f', $f);
        }

        return rtrim(rtrim(sprintf('%.6F', $f), '0'), '.');
    }

    /** Text of an <si> / <is> element: plain <t>, or the runs <r><t> (phonetic <rPh> ignored). */
    private static function richText(SimpleXMLElement $el): string
    {
        $main = $el->children(self::NS_MAIN);
        if (isset($main->t)) {
            return (string) $main->t;
        }
        $out = '';
        foreach ($main->r as $run) {
            $out .= (string) $run->children(self::NS_MAIN)->t;
        }

        return $out;
    }

    /** The zip path of the workbook's first worksheet (read from the workbook relationships). */
    private static function firstSheetPath(ZipArchive $zip): string
    {
        $fallback = 'xl/worksheets/sheet1.xml';
        if ($zip->locateName('xl/workbook.xml') === false || $zip->locateName('xl/_rels/workbook.xml.rels') === false) {
            return $fallback;
        }

        $first = self::part($zip, 'xl/workbook.xml')->children(self::NS_MAIN)->sheets->children(self::NS_MAIN)->sheet[0] ?? null;
        $rid = $first ? (string) $first->attributes(self::NS_REL)['id'] : '';
        if ($rid === '') {
            return $fallback;
        }

        foreach (self::part($zip, 'xl/_rels/workbook.xml.rels')->children() as $rel) {
            if ((string) $rel['Id'] === $rid) {
                $target = ltrim((string) $rel['Target'], '/');

                return str_starts_with($target, 'xl/') ? $target : 'xl/'.$target;
            }
        }

        return $fallback;
    }

    /** Reads and parses one XML part of the zip, refusing parts larger than MAX_PART_BYTES (zip bombs). */
    private static function part(ZipArchive $zip, string $name): SimpleXMLElement
    {
        $stat = $zip->statName($name);
        if ($stat === false) {
            throw new RuntimeException('This Excel file has no worksheet BAKR can read.');
        }
        if ($stat['size'] > self::MAX_PART_BYTES) {
            throw new RuntimeException('This Excel file is too large. Split it into smaller files.');
        }

        $xml = $zip->getFromName($name);
        $doc = $xml === false ? false : simplexml_load_string($xml, SimpleXMLElement::class, LIBXML_NONET | LIBXML_COMPACT);
        if ($doc === false) {
            throw new RuntimeException('This Excel file is damaged and could not be read.');
        }

        return $doc;
    }

    /** "C12" → 2 (A = 0). */
    private static function columnIndex(string $ref): int
    {
        $letters = strtoupper(preg_replace('/\d+/', '', $ref));
        $n = 0;
        foreach (str_split($letters) as $ch) {
            $n = $n * 26 + (ord($ch) - 64);
        }

        return max(0, $n - 1);
    }

    /** Removes control characters from a cell value and trims it. */
    private static function clean(string $value): string
    {
        return trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '');
    }

    /**
     * Builds a one-sheet .xlsx file and returns its bytes (the roster template download and the tests).
     *
     * @param  list<list<string|int|float|null>>  $rows
     * @param  string  $sheetName  the name shown on the sheet tab
     * @return string the raw .xlsx file contents
     */
    public static function xlsx(array $rows, string $sheetName = 'Scholars'): string
    {
        return self::workbook([$sheetName => $rows]);
    }

    /** 0 → "A", 25 → "Z", 26 → "AA" (the reverse of columnIndex()). */
    private static function columnLetters(int $index): string
    {
        $letters = '';
        for ($index++; $index > 0; $index = intdiv($index - 1, 26)) {
            $letters = chr(65 + ($index - 1) % 26).$letters;
        }

        return $letters;
    }

    /**
     * Builds a workbook with several sheets (admin Analytics export) and returns its bytes.
     * Unlike xlsx(), ints and floats are written as real numbers (so Excel can add them up), a cell given
     * as ['bold' => value] is bold, and each column is made wide enough for its longest text (max 60).
     *
     * @param  array<string, list<list<string|int|float|array{bold:string|int|float}|null>>>  $sheets  name => rows
     */
    public static function workbook(array $sheets): string
    {
        $esc = fn ($s) => htmlspecialchars((string) $s, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $types = '';
        $sheetList = '';
        $rels = '';
        $parts = [];
        $n = 0;
        foreach ($sheets as $name => $rows) {
            $n++;
            // Sheet names: max 31 characters, none of : \ / ? * [ ]
            $safe = mb_substr(trim(preg_replace('/[:\\\\\/?*\[\]]/', ' ', (string) $name)) ?: 'Sheet'.$n, 0, 31);
            $widths = [];
            $xmlRows = '';
            foreach (array_values($rows) as $r => $cells) {
                $xmlCells = '';
                foreach (array_values($cells) as $c => $value) {
                    $bold = is_array($value);
                    $value = $bold ? ($value['bold'] ?? null) : $value;
                    if ($value === null || $value === '') {
                        continue;
                    }
                    $ref = self::columnLetters($c).($r + 1);
                    $style = $bold ? ' s="1"' : '';
                    $xmlCells .= is_int($value) || is_float($value)
                        ? '<c r="'.$ref.'"'.$style.'><v>'.(is_float($value) ? rtrim(rtrim(sprintf('%.2F', $value), '0'), '.') : $value).'</v></c>'
                        : '<c r="'.$ref.'"'.$style.' t="inlineStr"><is><t xml:space="preserve">'.$esc(self::clean((string) $value)).'</t></is></c>';
                    $widths[$c] = max($widths[$c] ?? 8, min(60, mb_strlen((string) $value) + 2));
                }
                $xmlRows .= '<row r="'.($r + 1).'">'.$xmlCells.'</row>';
            }
            $colsXml = '';
            foreach ($widths as $c => $w) {
                $colsXml .= '<col min="'.($c + 1).'" max="'.($c + 1).'" width="'.$w.'" customWidth="1"/>';
            }

            $parts["xl/worksheets/sheet{$n}.xml"] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="'.self::NS_MAIN.'">'
                .($colsXml !== '' ? '<cols>'.$colsXml.'</cols>' : '').'<sheetData>'.$xmlRows.'</sheetData></worksheet>';
            $types .= '<Override PartName="/xl/worksheets/sheet'.$n.'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
            $sheetList .= '<sheet name="'.$esc($safe).'" sheetId="'.$n.'" r:id="rId'.$n.'"/>';
            $rels .= '<Relationship Id="rId'.$n.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet'.$n.'.xml"/>';
        }
        $stylesId = $n + 1;

        $parts += [
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'.$types.'</Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="'.self::NS_MAIN.'" xmlns:r="'.self::NS_REL.'"><sheets>'.$sheetList.'</sheets></workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.$rels.'<Relationship Id="rId'.$stylesId.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>',
            // Style 0 = normal, 1 = bold.
            'xl/styles.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="'.self::NS_MAIN.'"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts><fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/></cellXfs></styleSheet>',
        ];

        self::requireZip();
        $tmp = tempnam(sys_get_temp_dir(), 'bakr-xlsx');
        $zip = new ZipArchive;
        $zip->open($tmp, ZipArchive::OVERWRITE);
        foreach ($parts as $name => $xml) {
            $zip->addFromString($name, $xml);
        }
        $zip->close();

        $bytes = (string) file_get_contents($tmp);
        @unlink($tmp);

        return $bytes;
    }
}
