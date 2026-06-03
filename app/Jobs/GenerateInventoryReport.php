<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\Report;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class GenerateInventoryReport implements ShouldQueue
{
    use Queueable;

    public function __construct(public Report $report)
    {
        //
    }

    public function handle(): void
    {
        $products = Product::orderBy('name')->get();
        $pdf = Pdf::loadView('reports.pdf', [
            'report' => $this->report,
            'products' => $products,
            'generatedAt' => now(),
        ]);

        $path = 'reports/inventory-report-'.$this->report->id.'.pdf';
        Storage::put($path, $pdf->output());

        $this->report->update([
            'status' => 'completed',
            'file_path' => $path,
            'generated_at' => now(),
        ]);
    }

    public function failed(): void
    {
        $this->report->update(['status' => 'failed']);
    }
}
