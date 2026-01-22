<?php

namespace App\Http\Controllers;

use App\Services\Export\ExportService;
use App\Services\Import\ImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExportImportController extends Controller
{
    public function export(Request $request, ExportService $export)
    {
        $format = $request->query('format', 'json');
        $payload = $export->exportTasks($request->user(), $format);

        $headers = ['Content-Type' => $format === 'csv' ? 'text/csv' : 'application/json'];

        return response($payload, 200, $headers);
    }

    public function import(Request $request, ImportService $import): JsonResponse
    {
        $format = $request->input('format', 'json');
        $content = $request->input('payload', '');

        if ($content === '') {
            return response()->json(['message' => 'Empty payload'], 422);
        }

        $items = $import->importTasks($request->user(), $content, $format);

        return response()->json(['imported' => $items]);
    }
}
