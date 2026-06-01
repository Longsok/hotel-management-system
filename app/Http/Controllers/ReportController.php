<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // GET /reports/bookings/daily
    public function dailyBookings(Request $request)
    {
        $date = $request->get('date', today()->toDateString());

        $bookings = Booking::with([
                'customer:id,name,phone,email',
                'room:id,room_number,floor,room_type_id',
                'room.roomType:id,name,base_price',
                'checkIn:id,booking_id,check_in_time,deposit_amount',
                'checkOut:id,booking_id,check_out_time',
                'invoice:id,booking_id,grand_total,status',
            ])
            ->where(function ($q) use ($date) {
                $q->whereDate('check_in_date', $date)
                  ->orWhereDate('check_out_date', $date)
                  ->orWhereDate('created_at', $date);
            })
            ->orderByDesc('created_at')
            ->get();

        $revenue        = Payment::whereDate('paid_at', $date)->where('status', 'paid')->sum('amount');
        $checkInsCount  = $bookings->filter(fn($b) => Carbon::parse($b->check_in_date)->toDateString()  === $date)->count();
        $checkOutsCount = $bookings->filter(fn($b) => Carbon::parse($b->check_out_date)->toDateString() === $date)->count();

        $summary = [
            'total_bookings' => $bookings->count(),
            'check_ins'      => $checkInsCount,
            'check_outs'     => $checkOutsCount,
            'revenue'        => round($revenue, 2),
            'by_status'      => $bookings->groupBy('status')->map->count(),
        ];

        return view('reports.daily-bookings', compact('date', 'bookings', 'summary'));
    }

    // GET /reports/bookings/monthly
    public function monthlyBookings(Request $request)
    {
        $year  = (int) $request->get('year',  now()->year);
        $month = (int) $request->get('month', now()->month);

        $bookings = Booking::with([
                'customer:id,name,phone',
                'room:id,room_number,floor,room_type_id',
                'room.roomType:id,name',
                'invoice:id,booking_id,grand_total,status',
            ])
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderByDesc('created_at')
            ->get();

        $revenueBreakdown = Payment::selectRaw('payment_type, SUM(amount) as total')
            ->whereYear('paid_at', $year)
            ->whereMonth('paid_at', $month)
            ->where('status', 'paid')
            ->groupBy('payment_type')
            ->pluck('total', 'payment_type');

        $dailyRevenue = Payment::selectRaw('DATE(paid_at) as date, SUM(amount) as total')
            ->whereYear('paid_at', $year)
            ->whereMonth('paid_at', $month)
            ->where('status', 'paid')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalRooms   = Room::count();
        $daysInMonth  = Carbon::create($year, $month, 1)->daysInMonth;
        $occupiedDays = $bookings->whereIn('status', ['checked_in', 'checked_out'])->sum('nights');
        $occupancyRate = $totalRooms > 0
            ? round(($occupiedDays / ($totalRooms * $daysInMonth)) * 100, 1) : 0;

        $byRoomType = DB::table('bookings')
            ->join('rooms',      'bookings.room_id',      '=', 'rooms.id')
            ->join('room_types', 'rooms.room_type_id',    '=', 'room_types.id')
            ->selectRaw('room_types.name as room_type, COUNT(bookings.id) as bookings_count, SUM(bookings.room_total) as revenue')
            ->whereYear('bookings.created_at', $year)
            ->whereMonth('bookings.created_at', $month)
            ->groupBy('room_types.name')
            ->orderByDesc('revenue')
            ->get();

        $summary = [
            'total_bookings'    => $bookings->count(),
            'confirmed'         => $bookings->where('status', 'confirmed')->count(),
            'checked_in'        => $bookings->where('status', 'checked_in')->count(),
            'checked_out'       => $bookings->where('status', 'checked_out')->count(),
            'cancelled'         => $bookings->where('status', 'cancelled')->count(),
            'total_revenue'     => round($revenueBreakdown->sum(), 2),
            'revenue_breakdown' => $revenueBreakdown,
            'occupancy_rate'    => $occupancyRate,
            'by_room_type'      => $byRoomType,
            'daily_revenue'     => $dailyRevenue,
            'by_status'         => $bookings->groupBy('status')->map->count(),
        ];

        return view('reports.monthly-bookings', compact('year', 'month', 'bookings', 'summary'));
    }

    // GET /reports/income
    public function income(Request $request)
    {
        $year  = (int) $request->get('year',  now()->year);
        $month = $request->get('month');        // nullable → full year

        $paymentsQuery = Payment::where('status', 'paid')->whereYear('paid_at', $year);

        if ($month) {
            $paymentsQuery->whereMonth('paid_at', (int) $month);
        }

        $totalIncome = $paymentsQuery->sum('amount');

        $byMonth = Payment::selectRaw('MONTH(paid_at) as month, YEAR(paid_at) as year, SUM(amount) as total')
            ->whereYear('paid_at', $year)
            ->where('status', 'paid')
            ->groupByRaw('YEAR(paid_at), MONTH(paid_at)')
            ->orderByRaw('YEAR(paid_at), MONTH(paid_at)')
            ->get();

        $byType = Payment::selectRaw('payment_type, SUM(amount) as total')
            ->whereYear('paid_at', $year)
            ->when($month, fn($q) => $q->whereMonth('paid_at', (int) $month))
            ->where('status', 'paid')
            ->groupBy('payment_type')
            ->get();

        return view('reports.income', compact('year', 'month', 'totalIncome', 'byMonth', 'byType'));
    }
}
