<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\CheckIn;
use App\Models\CheckOut;
use App\Models\Payment;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // GET /dashboard
    public function index()
    {
        $today = now()->toDateString();

        $roomStats      = Room::selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status');
        $checkInsToday  = CheckIn::whereDate('check_in_time', $today)->count();
        $checkOutsToday = CheckOut::whereDate('check_out_time', $today)->count();
        $revenueToday   = Payment::whereDate('paid_at', $today)->where('status', 'paid')->sum('amount');
        $revenueMonth   = Payment::whereYear('paid_at', now()->year)->whereMonth('paid_at', now()->month)->where('status', 'paid')->sum('amount');
        $bookingsMonth  = Booking::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count();

        $recentBookings = Booking::with(['customer:id,name', 'room:id,room_number'])
            ->orderByDesc('created_at')->limit(8)
            ->get(['id','booking_number','customer_id','room_id','check_in_date','check_out_date','nights','room_total','status']);

        // Room map grouped by floor
        $roomsByFloor = Room::with('roomType')->orderBy('floor')->orderBy('room_number')->get()->groupBy('floor');

        // Hourly check-in / check-out data for the activity chart
        // Groups today's records into 6 buckets: 12AM, 4AM, 8AM, 12PM, 4PM, 8PM
        $checkInsByHour = CheckIn::whereDate('check_in_time', $today)
            ->selectRaw('HOUR(check_in_time) as hr, COUNT(*) as cnt')
            ->groupBy('hr')
            ->pluck('cnt', 'hr');

        $checkOutsByHour = CheckOut::whereDate('check_out_time', $today)
            ->selectRaw('HOUR(check_out_time) as hr, COUNT(*) as cnt')
            ->groupBy('hr')
            ->pluck('cnt', 'hr');

        // Aggregate into 4-hour buckets: [0-3, 4-7, 8-11, 12-15, 16-19, 20-23]
        $hourlyBuckets = [[0,3],[4,7],[8,11],[12,15],[16,19],[20,23]];

        $chartCheckIns  = [];
        $chartCheckOuts = [];
        foreach ($hourlyBuckets as [$from, $to]) {
            $ins  = 0;
            $outs = 0;
            for ($h = $from; $h <= $to; $h++) {
                $ins  += $checkInsByHour->get($h, 0);
                $outs += $checkOutsByHour->get($h, 0);
            }
            $chartCheckIns[]  = $ins;
            $chartCheckOuts[] = $outs;
        }

        return view('dashboard.index', compact(
            'roomStats','checkInsToday','checkOutsToday',
            'revenueToday','revenueMonth','bookingsMonth','recentBookings','roomsByFloor',
            'chartCheckIns','chartCheckOuts'
        ));
    }

    // GET /dashboard/analytics  [admin only]
    public function analytics(Request $request)
    {
        $year  = (int) $request->get('year',  now()->year);
        $month = (int) $request->get('month', now()->month);

        $dailyRevenue  = Payment::selectRaw('DATE(paid_at) as date, SUM(amount) as total')
            ->whereYear('paid_at', $year)->whereMonth('paid_at', $month)->where('status', 'paid')
            ->groupBy('date')->orderBy('date')->get();

        $bySource = Booking::selectRaw('booking_source, COUNT(*) as count')
            ->whereYear('created_at', $year)->whereMonth('created_at', $month)->groupBy('booking_source')->get();

        $byStatus = Booking::selectRaw('status, COUNT(*) as count')
            ->whereYear('created_at', $year)->groupBy('status')->get();

        $topRoomTypes = DB::table('bookings')
            ->join('rooms','bookings.room_id','=','rooms.id')
            ->join('room_types','rooms.room_type_id','=','room_types.id')
            ->selectRaw('room_types.name, COUNT(bookings.id) as bookings_count, SUM(bookings.room_total) as revenue')
            ->whereYear('bookings.created_at', $year)->groupBy('room_types.name')->orderByDesc('revenue')->get();

        $totalRooms    = Room::count();
        $occupiedDays  = Booking::whereYear('check_in_date', $year)->whereMonth('check_in_date', $month)
            ->whereIn('status', ['checked_in','checked_out'])->sum('nights');
        $daysInMonth   = now()->setYear($year)->setMonth($month)->daysInMonth;
        $occupancyRate = $totalRooms > 0 ? round(($occupiedDays / ($totalRooms * $daysInMonth)) * 100, 1) : 0;

        $totalBookings    = Booking::whereYear('created_at', $year)->whereMonth('created_at', $month)->count();
        $cancelledCount   = Booking::whereYear('created_at', $year)->whereMonth('created_at', $month)->where('status','cancelled')->count();
        $cancellationRate = $totalBookings > 0 ? round(($cancelledCount / $totalBookings) * 100, 1) : 0;

        return view('dashboard.analytics', compact(
            'year','month','dailyRevenue','bySource','byStatus',
            'topRoomTypes','occupancyRate','cancellationRate'
        ));
    }
}