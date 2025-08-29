<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;
use App\Exports\ActivityLogExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ActivityLogController extends Controller
{
    private function getActivityData(Request $request)
    {
        $search = $request->query('search');
        $period = $request->query('period'); 
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = Activity::query()->with(['causer', 'subject']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('causer', function ($subQuery) use ($search) {
                      $subQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // [DIUBAH] Logika filter tanggal yang lebih fleksibel
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);
        } elseif ($period && $period !== 'all_time') {
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

    public function index(Request $request)
    {
        if ($request->has('export')) {
            $format = $request->input('export');
            $activities = $this->getActivityData($request)->get();

            if ($format === 'csv') {
                return Excel::download(new ActivityLogExport($activities), 'laporan-log-aktivitas-'.now()->format('Y-m-d').'.csv');
            }
            if ($format === 'pdf') {
                $pdf = Pdf::loadView('activity_log.pdf.index', ['activities' => $activities]);
                return $pdf->stream('laporan-log-aktivitas-'.now()->format('Y-m-d').'.pdf');
            }
        }
        
        $activities = $this->getActivityData($request)->paginate(20)->withQueryString();

        return view('activity_log.index', [
            'activities' => $activities,
            'search' => $request->query('search'),
            'period' => $request->query('period', 'all_time'),
            // [BARU] Kirim tanggal ke view agar bisa ditampilkan kembali di form
            'startDate' => $request->query('start_date'),
            'endDate' => $request->query('end_date'),
        ]);
    }

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