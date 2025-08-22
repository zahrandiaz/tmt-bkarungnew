<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
// [BARU] Tambahkan use statement yang diperlukan
use Carbon\Carbon;
use App\Exports\ActivityLogExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ActivityLogController extends Controller
{
    /**
     * [BARU] Private method untuk mengambil dan memfilter data log.
     */
    private function getActivityData(Request $request)
    {
        $search = $request->query('search');
        $period = $request->query('period', 'all_time'); // Default ke 'all_time'

        $query = Activity::query()->with(['causer', 'subject']);

        // Filter berdasarkan pencarian
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('causer', function ($subQuery) use ($search) {
                      $subQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter berdasarkan periode
        if ($period !== 'all_time') {
            $dateRange = match ($period) {
                'today' => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
                'this_week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
                'this_month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
                'this_year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            };
            $query->whereBetween('created_at', $dateRange);
        }

        return $query->latest();
    }

    /**
     * Menampilkan daftar log aktivitas.
     */
    public function index(Request $request)
    {
        // [BARU] Cek jika ada permintaan ekspor
        if ($request->has('export')) {
            $format = $request->input('export');
            $activities = $this->getActivityData($request)->get(); // Ambil semua data tanpa paginasi

            if ($format === 'csv') {
                return Excel::download(new ActivityLogExport($activities), 'laporan-log-aktivitas-'.now()->format('Y-m-d').'.csv');
            }
            if ($format === 'pdf') {
                $pdf = Pdf::loadView('activity_log.pdf.index', ['activities' => $activities]);
                return $pdf->stream('laporan-log-aktivitas-'.now()->format('Y-m-d').'.pdf');
            }
        }
        
        // Jika bukan ekspor, tampilkan dengan paginasi
        $activities = $this->getActivityData($request)->paginate(20)->appends($request->query());

        return view('activity_log.index', [
            'activities' => $activities,
            'search' => $request->query('search'),
            'period' => $request->query('period', 'all_time'),
        ]);
    }

    /**
     * Menghapus semua log aktivitas.
     */
    public function reset()
    {
        try {
            Activity::truncate();
            return redirect()->route('activity-log.index')->with('success', 'Semua riwayat log aktivitas berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus riwayat log: ' . $e->getMessage());
        }
    }
}