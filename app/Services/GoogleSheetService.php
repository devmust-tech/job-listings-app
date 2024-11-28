<?php

namespace App\Services;

use Google_Client;
use Google_Service_Sheets;

class GoogleSheetService
{
    protected $client;
    protected $service;
    protected $spreadsheetId;

    public function __construct()
    {
        $this->spreadsheetId = env('GOOGLE_SPREADSHEET_ID');

        // Initialize Google Client
        $this->client = new Google_Client();
        $this->client->setApplicationName('Laravel Google Sheets Integration');
        $this->client->setScopes([Google_Service_Sheets::SPREADSHEETS_READONLY]);
        $this->client->setAuthConfig(storage_path('app/google/google-credentials.json'));

        // Initialize Sheets Service
        $this->service = new Google_Service_Sheets($this->client);
    }

    /**
     * Fetch data from the specified range in the spreadsheet.
     *
     * @param string $range
     * @return array
     */
    public function fetchData(string $range): array
    {
        $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
        return $response->getValues() ?? [];
    }
}
