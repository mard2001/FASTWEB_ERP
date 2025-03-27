<?php

namespace App\Http\Controllers\Helpers;

use Illuminate\Http\Request;
use Monolog\Handler\PushoverHandler;
use Smalot\PdfParser\Parser;

class PDFParserController
{
    public static function parsePDF(Request $request)
    {
        
        // Validate the incoming request to make sure the file is a valid PDF
        $request->validate([
            'pdf_file' => 'required|mimes:pdf|max:10240', // Limit the file size to 10MB
        ]);

        // Get the file from the request
        $file = $request->file('pdf_file');

        try {
            // Initialize the PDF parser
            $parser = new Parser();

            // Parse the PDF file
            $pdf = $parser->parseFile($file->getPathname());

            // Extract the text from the PDF
            $text = $pdf->getText();

            // Split the text by lines or sentences
            $lines = explode("\n", $text);

            // Trim each line or sentence
            $trimmed_lines = array_map('trim', $lines);

            // Join the lines back together (if needed)
            $trimmed_text = implode("\n", $trimmed_lines);

            // Return the extracted text as a response (or you can store it, manipulate it, etc.)
            return[
                'status' => true,
                'extracted_text' => $trimmed_text,
            ];
            
        } catch (\Exception $e) {
            // Handle errors
            return [
                'status' => false,
                'message' => 'Error parsing PDF: ' . $e->getMessage(),
            ];
        }
    }
}
