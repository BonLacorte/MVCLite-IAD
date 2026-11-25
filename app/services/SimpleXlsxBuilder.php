<?php

class SimpleXlsxBuilder
{
    public function build(array $headers, array $rows, string $sheetName = 'Sheet1', ?array $columnWidths = null): string
    {
        $sheetRows = array_merge([$headers], $rows);
        $safeSheetName = $this->xmlEscape(substr($sheetName, 0, 31));

        return $this->buildZipArchive([
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
                '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
                '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
                '<Default Extension="xml" ContentType="application/xml"/>' .
                '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' .
                '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' .
                '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' .
                '</Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
                '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
                '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' .
                '</Relationships>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
                '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
                '<sheets><sheet name="' . $safeSheetName . '" sheetId="1" r:id="rId1"/></sheets>' .
                '</workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
                '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
                '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' .
                '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' .
                '</Relationships>',
            'xl/styles.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
                '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
                '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>' .
                '<fills count="1"><fill><patternFill patternType="none"/></fill></fills>' .
                '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>' .
                '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>' .
                '<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/></cellXfs>' .
                '</styleSheet>',
            'xl/worksheets/sheet1.xml' => $this->buildWorksheetXml($sheetRows, $columnWidths),
        ]);
    }

    private function buildWorksheetXml(array $rows, ?array $columnWidths): string
    {
        $widths = $columnWidths ?: array_fill(0, max(1, count($rows[0] ?? [])), 24);
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><cols>';

        foreach ($widths as $index => $width) {
            $column = $index + 1;
            $xml .= '<col min="' . $column . '" max="' . $column . '" width="' . (float)$width . '" customWidth="1"/>';
        }

        $xml .= '</cols><sheetData>';

        foreach ($rows as $rowIndex => $row) {
            $rowNumber = $rowIndex + 1;
            $xml .= '<row r="' . $rowNumber . '">';
            foreach ($row as $columnIndex => $value) {
                $style = $rowIndex === 0 ? ' s="1"' : '';
                $cellRef = $this->columnName($columnIndex + 1) . $rowNumber;
                $xml .= '<c r="' . $cellRef . '" t="inlineStr"' . $style . '><is><t>' .
                    $this->xmlEscape((string)$value) .
                    '</t></is></c>';
            }
            $xml .= '</row>';
        }

        return $xml . '</sheetData></worksheet>';
    }

    private function buildZipArchive(array $files): string
    {
        $localData = '';
        $centralDirectory = '';
        $offset = 0;

        foreach ($files as $name => $data) {
            $crc = crc32($data);
            $size = strlen($data);
            $nameLength = strlen($name);
            $localHeader = pack('VvvvvvVVVvv', 0x04034b50, 20, 0, 0, 0, 0, $crc, $size, $size, $nameLength, 0) . $name;
            $localData .= $localHeader . $data;
            $centralDirectory .= pack('VvvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0, 0, 0, 0, $crc, $size, $size, $nameLength, 0, 0, 0, 0, 0, $offset) . $name;
            $offset += strlen($localHeader) + $size;
        }

        $centralSize = strlen($centralDirectory);
        $centralOffset = strlen($localData);
        $fileCount = count($files);
        $end = pack('VvvvvVVv', 0x06054b50, 0, 0, $fileCount, $fileCount, $centralSize, $centralOffset, 0);

        return $localData . $centralDirectory . $end;
    }

    private function columnName(int $number): string
    {
        $name = '';
        while ($number > 0) {
            $number--;
            $name = chr(65 + ($number % 26)) . $name;
            $number = intdiv($number, 26);
        }
        return $name;
    }

    private function xmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }
}
