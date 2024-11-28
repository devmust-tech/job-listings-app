<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Google_Client;
use Illuminate\Http\Request;
use Google_Service_Sheets;

class SpreadsheetController extends Controller
{
    public function importFromGoogleSheets()
    {
        $spreadsheetId = '18d88ANCBZc2UNbfiX6BUscfQJbQnD4wGthlDghy-GAs'; // Replace with your spreadsheet ID
        $credentialsPath = storage_path('app/google/credentials.json'); // Path to your credentials.json

        // Initialize Google Client
        $client = new Google_Client();
        $client->setApplicationName('Google Sheets Integration');
        $client->setScopes(Google_Service_Sheets::SPREADSHEETS_READONLY);
        $client->setAuthConfig($credentialsPath);
        $client->setAccessType('offline');

        $service = new Google_Service_Sheets($client);

        // Get all sheets in the spreadsheet
        $spreadsheet = $service->spreadsheets->get($spreadsheetId);
        $sheets = $spreadsheet->getSheets();

        foreach ($sheets as $sheet) {
            $sheetName = $sheet->getProperties()->getTitle();

            // Fetch data from the sheet
            $range = $sheetName . '!A1:F'; // Adjust range if needed
            $response = $service->spreadsheets_values->get($spreadsheetId, $range);
            $rows = $response->getValues();

            // Skip if no data
            if (empty($rows)) {
                continue;
            }

            // Process rows and insert into the database
            $this->processSheetData($rows);
        }

        return response()->json(['message' => 'Data imported successfully.']);
    }

    private function processSheetData($rows)
    {
        // Extract headers from the first row
        $headers = array_map('strtolower', $rows[0]); // Ensure consistent column names
        unset($rows[0]); // Remove the header row

        foreach ($rows as $row) {
            $data = array_combine($headers, $row);

            // Insert or update data in the database (prevent duplicates)
            Job::updateOrCreate(
                ['job_url' => $data['job url']], // Unique field for avoiding duplicates
                [
                    'company' => $data['company'] ?? null,
                    'title' => $data['title'] ?? null,
                    'location' => $data['location'] ?? null,
                    'posting_date' => $data['posting date'] ?? null,
                    'description' => $data['description'] ?? null,
                ]
            );
        }
    }


    public function get_all_jobs()
    {
        $jobs = Job::all();
        return response()->json($jobs);
    }
}
