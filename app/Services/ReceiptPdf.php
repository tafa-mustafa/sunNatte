<?php

namespace App\Services;

class ReceiptPdf
{
    public function render(array $fields): string
    {
        return $this->renderLines([
            'SUNU NATT',
            'Recu de cotisation',
            '',
            'Recu : '.($fields['reference'] ?? ''),
            'Date : '.($fields['date'] ?? ''),
            'Membre : '.($fields['membre'] ?? ''),
            'Tontine : '.($fields['tontine'] ?? ''),
            'Montant : '.($fields['montant'] ?? '').' FCFA',
            'Moyen : '.($fields['provider'] ?? 'Wave'),
            '',
            'Teknon Group SUARL - Dakar',
        ]);
    }

    public function renderLines(array $lines): string
    {

        $content = "BT /F1 14 Tf 50 780 Td\n";
        foreach ($lines as $i => $line) {
            $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $this->ascii($line));
            $content .= $i === 0 ? "({$escaped}) Tj\n" : "0 -20 Td ({$escaped}) Tj\n";
        }
        $content .= "ET\n";

        $objects = [];
        $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[] = '<< /Type /Pages /Kids [3 0 R] /Count 1 >>';
        $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>';
        $objects[] = '<< /Length '.strlen($content).' >> stream'."\n".$content.'endstream';
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $i => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1).' 0 obj '.$object."\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= 'xref
0 '.count($offsets).'
0000000000 65535 f 
';
        for ($i = 1; $i < count($offsets); $i++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$i])."\n";
        }
        $pdf .= 'trailer << /Size '.count($offsets).' /Root 1 0 R >>
startxref
'.$xref.'
%%EOF';

        return $pdf;
    }

    private function ascii(string $value): string
    {
        $map = [
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'à' => 'a', 'â' => 'a', 'ù' => 'u', 'û' => 'u',
            'ô' => 'o', 'î' => 'i', 'ç' => 'c', 'É' => 'E',
            '’' => "'", '€' => 'EUR',
        ];

        return strtr($value, $map);
    }
}
