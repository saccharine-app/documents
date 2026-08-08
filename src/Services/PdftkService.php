<?php

namespace Saccharine\Documents\Services;

use App\Interfaces\FillablePdfInterface;
use Illuminate\Support\Arr;

class PdftkService implements FillablePdfInterface
{
    public function fill(string $templatePath, array $payload): string
    {
        $hasPdftk = shell_exec('which pdftk');
        
        if (!$hasPdftk) {
            throw new \Exception('The PDFtk utility is not installed in this Docker environment.');
        }

        // Flatten nested arrays (e.g., ['deceased' => ['last_name' => 'Smith']] becomes ['deceased.last_name' => 'Smith'])
        // This bridges the gap between Eloquent models and strict PDF string fields!
        $flatPayload = Arr::dot($payload);

        $fdfData = $this->generateFdf($flatPayload);
        $fdfFile = tempnam(sys_get_temp_dir(), 'fdf');
        file_put_contents($fdfFile, $fdfData);

        $outputFile = tempnam(sys_get_temp_dir(), 'pdf');

        $cmd = sprintf(
            'pdftk %s fill_form %s output %s flatten',
            escapeshellarg($templatePath),
            escapeshellarg($fdfFile),
            escapeshellarg($outputFile)
        );

        exec($cmd);

        $filledPdf = file_get_contents($outputFile);

        @unlink($fdfFile);
        @unlink($outputFile);

        return $filledPdf;
    }
    
    /**
     * Generate the FDF string for PDFtk parsing.
     */
    protected function generateFdf(array $data): string
    {
        $fdf = "%FDF-1.2\n1 0 obj\n<<\n/FDF <<\n/Fields [\n";
        foreach ($data as $key => $value) {
            // Null safety check and sanitization
            $safeValue = $value === null ? '' : strval($value);
            $cleanValue = str_replace(['(', ')'], ['\\(', '\\)'], $safeValue);
            $fdf .= "<< /T (" . $key . ") /V (" . $cleanValue . ") >>\n";
        }
        $fdf .= "]\n>>\n>>\nendobj\ntrailer\n<<\n/Root 1 0 R\n>>\n%%EOF";
        return $fdf;
    }
}