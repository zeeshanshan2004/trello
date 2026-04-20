<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReportsController extends Controller
{
    /**
     * Display the reports results page.
     */
    public function results()
    {
        return view('reports.results');
    }

    /**
     * Fetch match results from external API.
     */
    public function fetchMatchResults(Request $request)
    {
        $request->validate([
            'sport_id' => 'required|string',
            'from_date' => 'required|date',
            'to_date' => 'required|date',
        ]);

        $apiUrl = 'https://usabet9.com/api/v1/match/matchResults';
        $bearerToken = '6b75f70b182aead52f12c7b858230f36c18659c2';

        try {
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'accept-language' => 'en-US,en;q=0.9',
                'authorization' => 'Bearer ' . $bearerToken,
                'content-type' => 'application/json',
                'origin' => 'https://usabet9.com',
                'referer' => 'https://6wickett.com/completed-games',
            ])->post($apiUrl, [
                'sport_id' => $request->sport_id,
                'from_date' => $request->from_date,
                'to_date' => $request->to_date,
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json(),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'API request failed',
                    'status' => $response->status(),
                    'message' => $response->body(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Match Results API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch match results',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
