<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateInventoryReport;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('reports.index', [
            'reports' => Report::latest()->paginate(15),
        ]);
    }

    public function store(): RedirectResponse
    {
        $report = Report::create([
            'title' => 'Laporan Inventory '.now()->format('Y-m-d H:i'),
            'status' => 'pending',
        ]);

        GenerateInventoryReport::dispatch($report)->onQueue('reports');

        return to_route('reports.index')->with('status', 'Permintaan cetak laporan masuk ke antrian.');
    }

    public function show(Report $report): Response|RedirectResponse
    {
        if (! $report->file_path || ! Storage::exists($report->file_path)) {
            return back()->with('status', 'Laporan belum selesai dibuat.');
        }

        return response(Storage::get($report->file_path), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="inventory-report-'.$report->id.'.pdf"',
        ]);
    }
}
