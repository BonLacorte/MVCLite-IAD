<?php

class QuestionnairePdfService
{
    public function buildQuestionnairePdf(array $questionnaire, array $sections): string
    {
        $pdf = new SimpleQuestionnairePdfDocument('BASELINE ASSESSMENT OF INTERNAL CONTROLS SYSTEM');
        $this->addHeader($pdf, 'QUESTIONNAIRE');
        $pdf->text('Date: ' . date('F j, Y'), 10);
        $pdf->space(4);
        $this->addQuestionnaireMeta($pdf, $questionnaire);
        $pdf->space(4);
        $pdf->heading('Questions:', 11);
        $counter = 1;
        $this->renderQuestionnaireSections($pdf, $sections, $counter, false);
        return $pdf->output();
    }

    public function buildDhVerificationPdf(array $questionnaire, array $sections, array $user): string
    {
        $pdf = new SimpleQuestionnairePdfDocument('BASELINE ASSESSMENT OF INTERNAL CONTROLS SYSTEM');
        $this->addHeader($pdf, 'DEPARTMENT HEAD VERIFICATION FORM');
        $pdf->text('Date: ' . date('F j, Y'), 10);
        $pdf->space(4);
        $this->addQuestionnaireMeta($pdf, $questionnaire);
        $pdf->space(4);
        $pdf->heading('Questions For Review:', 11);

        $items = [];
        $this->collectForReviewQuestions($sections, $items);

        if (empty($items)) {
            $pdf->text('No responses are currently submitted for review.', 10);
        } else {
            foreach ($items as $item) {
                $this->renderDhQuestion($pdf, $item);
            }
        }

        $pdf->space(8);
        $this->renderSignatureArea($pdf, $user);
        return $pdf->output();
    }

    private function addHeader(SimpleQuestionnairePdfDocument $pdf, string $documentTitle): void
    {
        $pdf->center('REPUBLIC OF THE PHILIPPINES', 9);
        $pdf->center('DEPARTMENT OF AGRICULTURE', 9);
        $pdf->center('PHILIPPINE COCONUT AUTHORITY', 10, true);
        $pdf->center('Elliptical Road, Diliman, Quezon City 1101 Philippines', 8);
        $pdf->center('Tel. Nos.: (02) 8928-4501 to 09 | Direct Line: (02) 8926-7631', 8);
        $pdf->center('Website: pca.gov.ph', 8);
        $pdf->space(5);
        $pdf->center('BASELINE ASSESSMENT OF INTERNAL CONTROLS SYSTEM', 11, true);
        $pdf->center($documentTitle, 11, true);
        $pdf->space(6);
    }

    private function addQuestionnaireMeta(SimpleQuestionnairePdfDocument $pdf, array $questionnaire): void
    {
        $pdf->text('Questionnaire Title: ' . ($questionnaire['title'] ?? ''), 10);
        $description = trim((string)($questionnaire['description'] ?? ''));
        if ($description !== '') {
            $pdf->text('Description: ' . $description, 10);
        }
    }

    private function renderQuestionnaireSections(SimpleQuestionnairePdfDocument $pdf, array $sections, int &$counter, bool $isChild): void
    {
        foreach ($sections as $section) {
            $sectionTitle = trim((string)($section['title'] ?? ''));
            if ($sectionTitle !== '') {
                $pdf->space(3);
                $pdf->heading(($isChild ? 'Subsection: ' : 'Section: ') . $sectionTitle, 10);
            }

            foreach (($section['questions'] ?? []) as $question) {
                $this->renderBlankQuestion($pdf, $question, $counter);
                $counter++;
            }

            if (!empty($section['subsections'])) {
                $this->renderQuestionnaireSections($pdf, $section['subsections'], $counter, true);
            }
        }
    }

    private function renderBlankQuestion(SimpleQuestionnairePdfDocument $pdf, array $question, int $counter): void
    {
        $area = trim((string)($question['area'] ?? ''));
        $criteria = trim((string)($question['criteria'] ?? ''));
        $refNo = trim((string)($question['ref_no'] ?? ''));
        $questionText = trim((string)($question['question_text'] ?? ''));

        if ($area !== '') {
            $pdf->text('Area: ' . $area, 9);
        }
        if ($criteria !== '') {
            $pdf->text('Criteria: ' . $criteria, 9);
        }

        $prefix = $counter . '. ' . ($refNo !== '' ? '(' . $refNo . ') ' : '');
        $pdf->text($prefix . $questionText, 10);
        $pdf->text('Answer (Yes/No): ' . str_repeat('_', 72), 9);
        $pdf->text('Explanation:', 9);
        $pdf->lineText();
        $pdf->lineText();
        $pdf->text('Documents:', 9);
        $pdf->lineText();
        $pdf->space(3);
    }

    private function collectForReviewQuestions(array $sections, array &$items): void
    {
        foreach ($sections as $section) {
            foreach (($section['questions'] ?? []) as $question) {
                $status = strtolower((string)($question['latest_answer_status'] ?? ''));
                if ($status === 'for review') {
                    $answer = $question['answers'][0] ?? [];
                    $items[] = [
                        'question' => $question,
                        'answer' => $answer,
                        'files' => $question['for_review_uploaded_files'] ?? ($answer['uploaded_files'] ?? []),
                    ];
                }
            }

            if (!empty($section['subsections'])) {
                $this->collectForReviewQuestions($section['subsections'], $items);
            }
        }
    }

    private function renderDhQuestion(SimpleQuestionnairePdfDocument $pdf, array $item): void
    {
        $question = $item['question'];
        $answer = $item['answer'];
        $refNo = trim((string)($question['ref_no'] ?? ''));
        $questionText = trim((string)($question['question_text'] ?? ''));
        $answerValue = trim((string)($answer['answer'] ?? ''));
        $explanation = trim((string)($answer['explanation'] ?? ''));

        $pdf->space(3);
        $pdf->text(($refNo !== '' ? '(' . $refNo . ') ' : '') . $questionText, 10);
        $pdf->text('- Answer: ' . ($answerValue !== '' ? $answerValue : 'N/A'), 9);
        $pdf->text('- Explanation:', 9);
        $pdf->text($explanation !== '' ? $explanation : 'No explanation provided.', 9);

        $files = $item['files'] ?? [];
        if (!empty($files)) {
            $pdf->text('- Uploaded Files:', 9);
            foreach ($files as $file) {
                $pdf->text('  - ' . basename((string)($file['file_path'] ?? '')), 8);
            }
        }

        $pdf->text('- Department Head Decision:   [ ] Accepted    [ ] Returned', 9);
        $pdf->text('- Remarks:', 9);
        $pdf->lineText();
        $pdf->lineText();
    }

    private function renderSignatureArea(SimpleQuestionnairePdfDocument $pdf, array $user): void
    {
        $pdf->text('Prepared by:                                      Reviewed by:', 10);
        $pdf->space(8);
        $name = trim((string)($user['name'] ?? ''));
        $position = trim((string)($user['position'] ?? ''));
        $department = trim((string)($user['department_office_unit'] ?? ''));
        $pdf->text('_____________________________                    _____________________________', 9);
        $pdf->text('Signature over printed name                      Signature over printed name', 8);
        if ($name !== '') {
            $pdf->text($name, 9);
        }
        if ($position !== '') {
            $pdf->text($position, 9);
        }
        if ($department !== '') {
            $pdf->text($department, 9);
        }
    }
}

class SimpleQuestionnairePdfDocument
{
    private array $pages = [];
    private array $current = [];
    private float $x = 54;
    private float $y = 790;
    private float $pageWidth = 595;
    private float $pageHeight = 842;
    private float $bottom = 52;
    private float $lineHeight = 12;
    private string $title;

    public function __construct(string $title = '')
    {
        $this->title = $title;
        $this->newPage();
    }

    public function center(string $text, int $size = 10, bool $bold = false): void
    {
        $this->ensureSpace($this->lineHeight);
        $width = $this->textWidth($text, $size);
        $x = max(36, ($this->pageWidth - $width) / 2);
        $this->writeText($x, $this->y, $text, $size, $bold);
        $this->y -= $this->lineHeight;
    }

    public function heading(string $text, int $size = 10): void
    {
        $this->text($text, $size, true);
    }

    public function text(string $text, int $size = 10, bool $bold = false): void
    {
        foreach ($this->wrap($text, $size) as $line) {
            $this->ensureSpace($this->lineHeight);
            $this->writeText($this->x, $this->y, $line, $size, $bold);
            $this->y -= $this->lineHeight;
        }
    }

    public function lineText(): void
    {
        $this->text(str_repeat('_', 105), 8);
    }

    public function space(float $height): void
    {
        $this->ensureSpace($height);
        $this->y -= $height;
    }

    public function output(): string
    {
        if (!empty($this->current)) {
            $this->pages[] = $this->current;
            $this->current = [];
        }

        $objects = [];
        $pageCount = count($this->pages);
        $catalogId = 1;
        $pagesId = 2;
        $fontRegularId = 3;
        $fontBoldId = 4;
        $nextId = 5;
        $pageIds = [];
        $contentIds = [];

        foreach ($this->pages as $index => $commands) {
            $pageId = $nextId++;
            $contentId = $nextId++;
            $pageIds[] = $pageId;
            $contentIds[] = $contentId;
            $pageNo = $index + 1;
            $footer = "BT /F1 8 Tf 520 32 Td (Page {$pageNo} of {$pageCount}) Tj ET\n";
            $stream = implode("\n", $commands) . "\n" . $footer;
            $objects[$contentId] = "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "endstream";
            $objects[$pageId] = "<< /Type /Page /Parent {$pagesId} 0 R /MediaBox [0 0 {$this->pageWidth} {$this->pageHeight}] /Resources << /Font << /F1 {$fontRegularId} 0 R /F2 {$fontBoldId} 0 R >> >> /Contents {$contentId} 0 R >>";
        }

        $objects[$catalogId] = "<< /Type /Catalog /Pages {$pagesId} 0 R >>";
        $objects[$pagesId] = "<< /Type /Pages /Kids [" . implode(' ', array_map(fn($id) => "{$id} 0 R", $pageIds)) . "] /Count {$pageCount} >>";
        $objects[$fontRegularId] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
        $objects[$fontBoldId] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>";
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$body}\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i] ?? 0);
        }
        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root {$catalogId} 0 R /Info << /Title (" . $this->escape($this->title) . ") >> >>\nstartxref\n{$xref}\n%%EOF";
        return $pdf;
    }

    private function newPage(): void
    {
        if (!empty($this->current)) {
            $this->pages[] = $this->current;
        }
        $this->current = [];
        $this->y = 790;
    }

    private function ensureSpace(float $height): void
    {
        if ($this->y - $height < $this->bottom) {
            $this->newPage();
        }
    }

    private function writeText(float $x, float $y, string $text, int $size, bool $bold): void
    {
        $font = $bold ? 'F2' : 'F1';
        $this->current[] = "BT /{$font} {$size} Tf " . round($x, 2) . ' ' . round($y, 2) . ' Td (' . $this->escape($text) . ') Tj ET';
    }

    private function wrap(string $text, int $size): array
    {
        $text = trim(preg_replace('/\s+/', ' ', $text));
        if ($text === '') {
            return [''];
        }

        $maxWidth = $this->pageWidth - ($this->x * 2);
        $words = explode(' ', $text);
        $lines = [];
        $line = '';

        foreach ($words as $word) {
            $candidate = $line === '' ? $word : $line . ' ' . $word;
            if ($this->textWidth($candidate, $size) <= $maxWidth || $line === '') {
                $line = $candidate;
                continue;
            }

            $lines[] = $line;
            $line = $word;
        }

        if ($line !== '') {
            $lines[] = $line;
        }

        return $lines;
    }

    private function textWidth(string $text, int $size): float
    {
        return strlen($text) * $size * 0.47;
    }

    private function escape(string $text): string
    {
        $text = str_replace(["\r", "\n", "\t"], ' ', $text);
        $text = preg_replace('/[^\x20-\x7E]/', '?', $text);
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}