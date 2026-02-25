<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class QuoteController extends Controller
{
    public function page(Request $request)
    {
        return Inertia::render('Quotes/Index');
    }

    public function index(Request $request)
    {
        $response = Http::get('https://zenquotes.io/api/quotes');

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json(['error' => 'Failed to fetch quotes'], 500);
    }
}
