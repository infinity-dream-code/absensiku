<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\KpiAssessment;
use App\Models\Leave;
use App\Services\KpiCalculator;
use Illuminate\Support\Facades\Storage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (Auth::check() && Auth::user()->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $today = Carbon::today('Asia/Jakarta');
        $attendance = Attendance::with('logs')->where('user_id', Auth::id())
            ->whereDate('attendance_date', $today)
            ->first();

        $settings = \App\Models\Setting::getSettings();
        $summary = $this->buildAttendanceSummary($request, Auth::id(), $settings);
        $kpiView = $this->buildKpiView($request, Auth::id());

        return view('attendance.index', compact('attendance', 'settings', 'summary', 'kpiView'));
    }

    /**
     * KPI karyawan yang sedang login, filter bulan/tahun.
     */
    private function buildKpiView(Request $request, int $userId): array
    {
        $now = Carbon::now('Asia/Jakarta');
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $availableYears = KpiAssessment::where('id_user', $userId)
            ->select('tahun')
            ->distinct()
            ->pluck('tahun')
            ->push($now->year)
            ->filter()
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        $selectedYear = (int) $request->input('kpi_year', $now->year);
        if (!in_array($selectedYear, $availableYears, true)) {
            $availableYears[] = $selectedYear;
            rsort($availableYears);
        }

        $selectedMonth = (int) $request->input('kpi_month', $now->month);
        $selectedMonth = max(1, min(12, $selectedMonth));

        $assessment = KpiAssessment::with(['details', 'penilai', 'role'])
            ->where('id_user', $userId)
            ->where('tahun', $selectedYear)
            ->where('bulan', $selectedMonth)
            ->first();

        $details = [];
        if ($assessment) {
            foreach ($assessment->details as $detail) {
                $details[] = [
                    'nama' => $detail->nama_indikator,
                    'bobot' => (int) $detail->bobot,
                    'skor' => KpiCalculator::formatSkor($detail->skor),
                    'nilai_akhir' => rtrim(rtrim(number_format((float) $detail->nilai_akhir, 1, ',', ''), '0'), ','),
                ];
            }
        }

        return [
            'year' => $selectedYear,
            'month' => $selectedMonth,
            'years' => $availableYears,
            'months' => $monthNames,
            'period_label' => ($monthNames[$selectedMonth] ?? $selectedMonth) . ' ' . $selectedYear,
            'exists' => (bool) $assessment,
            'skor_akhir' => $assessment ? rtrim(rtrim(number_format((float) $assessment->skor_akhir, 1, ',', ''), '0'), ',') : null,
            'kategori' => $assessment->kategori ?? null,
            'rekomendasi' => trim((string) ($assessment->rekomendasi ?? '')) ?: null,
            'penilai' => $assessment?->penilai?->name,
            'role' => $assessment?->role?->role,
            'details' => $details,
        ];
    }

    /**
     * Ringkasan absensi karyawan: filter tahun + bulan (atau semua bulan).
     */
    private function buildAttendanceSummary(Request $request, int $userId, $settings): array
    {
        $now = Carbon::now('Asia/Jakarta');
        $selectedYear = (int) $request->input('year', $now->year);
        $monthInput = $request->input('month', $now->month);
        $allMonths = $monthInput === 'all' || $monthInput === '' || $monthInput === null;
        $selectedMonth = $allMonths ? null : max(1, min(12, (int) $monthInput));

        $availableYears = Attendance::where('user_id', $userId)
            ->selectRaw('YEAR(attendance_date) as year')
            ->distinct()
            ->pluck('year')
            ->merge(
                Leave::where('user_id', $userId)
                    ->selectRaw('YEAR(leave_date) as year')
                    ->distinct()
                    ->pluck('year')
            )
            ->push($now->year)
            ->filter()
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        if (!in_array($selectedYear, $availableYears, true)) {
            $availableYears[] = $selectedYear;
            rsort($availableYears);
        }

        $leaveQuery = Leave::where('user_id', $userId)
            ->whereYear('leave_date', $selectedYear);
        if ($selectedMonth) {
            $leaveQuery->whereMonth('leave_date', $selectedMonth);
        }

        $leaveRows = $leaveQuery
            ->orderByDesc('leave_date')
            ->orderByDesc('id')
            ->get(['leave_date', 'leave_type', 'notes']);

        $leaveCounts = [
            'sakit' => 0,
            'izin' => 0,
            'cuti' => 0,
        ];
        $leaveTypeLabels = [
            'sakit' => 'Sakit',
            'izin' => 'Izin',
            'cuti' => 'Cuti',
        ];
        $latestLeaves = [];
        foreach ($leaveRows as $leave) {
            $type = strtolower((string) $leave->leave_type);
            if (isset($leaveCounts[$type])) {
                $leaveCounts[$type]++;
            }

            if (count($latestLeaves) < 10) {
                $leaveDate = $leave->leave_date instanceof Carbon
                    ? $leave->leave_date->copy()->locale('id')
                    : Carbon::parse($leave->leave_date, 'Asia/Jakarta')->locale('id');

                $latestLeaves[] = [
                    'date' => $leaveDate->isoFormat('D MMM YYYY'),
                    'type' => $leaveTypeLabels[$type] ?? ucfirst($type),
                    'type_key' => $type,
                    'notes' => trim((string) ($leave->notes ?? '')) ?: null,
                ];
            }
        }

        $attendanceQuery = Attendance::with('logs')
            ->where('user_id', $userId)
            ->whereYear('attendance_date', $selectedYear)
            ->whereNotNull('check_in');
        if ($selectedMonth) {
            $attendanceQuery->whereMonth('attendance_date', $selectedMonth);
        }

        $checkInEndTime = $settings->check_in_end ?: '10:00:00';
        $tepatWaktu = 0;
        $terlambat = 0;
        $lateRows = [];

        foreach ($attendanceQuery->orderByDesc('attendance_date')->orderByDesc('id')->get() as $row) {
            if ($row->isLateStatus($checkInEndTime)) {
                $terlambat++;

                if (count($lateRows) < 10) {
                    $date = $row->attendance_date instanceof Carbon
                        ? $row->attendance_date->copy()->locale('id')
                        : Carbon::parse($row->attendance_date, 'Asia/Jakarta')->locale('id');
                    $checkIn = $row->earliestCheckInTime();

                    $lateRows[] = [
                        'date' => $date->isoFormat('D MMM YYYY'),
                        'time' => $checkIn ? $checkIn->format('H:i') : '-',
                        'work_type' => $row->latestWorkType(),
                    ];
                }
            } else {
                $tepatWaktu++;
            }
        }

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $periodLabel = $selectedMonth
            ? ($monthNames[$selectedMonth] . ' ' . $selectedYear)
            : ('Tahun ' . $selectedYear);

        $cutiMax = 12;
        $cutiUsedYear = (int) Leave::where('user_id', $userId)
            ->whereYear('leave_date', $selectedYear)
            ->where('leave_type', 'cuti')
            ->count();
        $sisaCuti = max(0, $cutiMax - $cutiUsedYear);

        return [
            'year' => $selectedYear,
            'month' => $selectedMonth,
            'all_months' => $selectedMonth === null,
            'years' => $availableYears,
            'months' => $monthNames,
            'period_label' => $periodLabel,
            'sakit' => $leaveCounts['sakit'],
            'izin' => $leaveCounts['izin'],
            'cuti' => $leaveCounts['cuti'],
            'cuti_max' => $cutiMax,
            'cuti_used_year' => $cutiUsedYear,
            'sisa_cuti' => $sisaCuti,
            'tepat_waktu' => $tepatWaktu,
            'terlambat' => $terlambat,
            'total_hadir' => $tepatWaktu + $terlambat,
            'total_izin' => array_sum($leaveCounts),
            'latest_leaves' => $latestLeaves,
            'latest_late' => $lateRows,
        ];
    }

    public function checkIn(Request $request)
    {
        $request->validate([
            'work_type' => 'required|in:WFA,WFO,WFH',
            'notes' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        // Log request
        Log::info('Check-in request', [
            'user_id' => Auth::id(),
            'work_type' => $request->work_type,
            'has_location' => $request->has('latitude') && $request->has('longitude'),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'timestamp' => now('Asia/Jakarta')->toDateTimeString()
        ]);

        $today = Carbon::today('Asia/Jakarta');
        $now = Carbon::now('Asia/Jakarta');

        // Get or create attendance record for today
        $existing = Attendance::where('user_id', Auth::id())
            ->whereDate('attendance_date', $today)
            ->first();

        // Prevent check-in if already checked out today
        if ($existing && $existing->check_out) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah check-out hari ini. Tidak dapat check-in lagi setelah check-out.'
            ], 400);
        }

        $locationName = null;
        
        // Get location name via reverse geocoding if latitude and longitude are available
        if ($request->latitude && $request->longitude) {
            try {
                $locationName = $this->getLocationName($request->latitude, $request->longitude);
            } catch (\Exception $e) {
                Log::warning('Failed to get location name', [
                    'user_id' => Auth::id(),
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $data = [
            'user_id' => Auth::id(),
            'attendance_date' => $today,
            'work_type' => $request->work_type,
            'notes' => $request->notes,
            'check_in' => Carbon::now('Asia/Jakarta'),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'location_valid' => true,
            'location_name' => $locationName,
            'is_telat_wfo' => 0,
        ];

        // Telat WFO: jam absen WFO > 10:30:00 (meski sudah WFA lebih pagi)
        $earliestForTelat = $existing && $existing->check_in
            ? Carbon::parse($existing->check_in, 'Asia/Jakarta')
            : $now;
        $data['is_telat_wfo'] = Attendance::resolveIsTelatWfo(
            $request->work_type === 'WFO',
            $earliestForTelat,
            $now
        ) ? 1 : 0;

        // Validate location for WFO (jika lokasi tersedia)
        if ($request->work_type === 'WFO') {
            if ($request->latitude && $request->longitude) {
                $settings = \App\Models\Setting::getSettings();

                Log::info('WFO Location validation', [
                    'user_id' => Auth::id(),
                    'user_location' => ['lat' => $request->latitude, 'lng' => $request->longitude],
                    'office_location' => ['lat' => $settings->latitude, 'lng' => $settings->longitude],
                    'radius' => $settings->radius
                ]);

                if ($settings->latitude && $settings->longitude && $settings->radius) {
                    $distance = $this->calculateDistance(
                        $settings->latitude,
                        $settings->longitude,
                        $request->latitude,
                        $request->longitude
                    );

                    // Convert radius from meters to kilometers
                    $radiusKm = $settings->radius / 1000;

                    Log::info('Location distance calculation', [
                        'user_id' => Auth::id(),
                        'distance_km' => $distance,
                        'radius_km' => $radiusKm,
                        'is_valid' => $distance <= $radiusKm
                    ]);

                    if ($distance > $radiusKm) {
                        $data['location_valid'] = false;
                    }
                } else {
                    Log::warning('Office location not configured', [
                        'user_id' => Auth::id()
                    ]);
                }
            } else {
                // Jika WFO tapi tidak ada lokasi, set location_valid = false
                Log::warning('WFO check-in without location', [
                    'user_id' => Auth::id()
                ]);
                $data['location_valid'] = false;
            }
        }

        // Handle image upload to Cloudinary
        $imageUrl = null;
        if ($request->hasFile('image')) {
            try {
                $uploadedFile = Cloudinary::upload($request->file('image')->getRealPath(), [
                    'folder' => 'attendance_images',
                    'resource_type' => 'image'
                ]);
                $imageUrl = $uploadedFile->getSecurePath();
                $data['image'] = $imageUrl;
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengupload gambar: ' . $e->getMessage()
                ], 500);
            }
        }

        // Create or update attendance record
        if ($existing) {
            if (!$existing->check_in) {
                // First check-in of the day (record existed but no check_in yet)
                $data['check_in'] = $now;
                $existing->update($data);
                $attendance = $existing;
            } else {
                // Absen kedua+: check_in awal tetap; work_type ikut terakhir
                // WFA jam 8 + WFO > 10:30 → tetap is_telat_wfo = 1
                $earliest = Carbon::parse($existing->check_in, 'Asia/Jakarta');
                $updateExtra = [
                    'work_type' => $request->work_type,
                    'is_telat_wfo' => Attendance::resolveIsTelatWfo(
                        $request->work_type === 'WFO',
                        $earliest,
                        $now
                    ) ? 1 : 0,
                ];
                // Catatan ikut absen terakhir (boleh kosong)
                $updateExtra['notes'] = $request->input('notes');
                if ($request->filled('latitude')) {
                    $updateExtra['latitude'] = $request->latitude;
                    $updateExtra['longitude'] = $request->longitude;
                    if (isset($data['location_name'])) {
                        $updateExtra['location_name'] = $data['location_name'];
                    }
                    if (array_key_exists('location_valid', $data)) {
                        $updateExtra['location_valid'] = $data['location_valid'];
                    }
                }
                $existing->update($updateExtra);
                $attendance = $existing->fresh();
            }
        } else {
            // First check-in of the day
            $data['check_in'] = $now;
            $attendance = Attendance::create($data);
        }

        // Simpan log untuk setiap check-in (termasuk absen kedua di hari yang sama)
        $logData = [
            'attendance_id' => $attendance->id,
            'check_in_time' => $now,
            'status' => $request->work_type,
            'notes' => $request->notes,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'location_name' => $locationName,
            'image' => $imageUrl,
        ];
        
        AttendanceLog::create($logData);

        // Pastikan kolom work_type record utama = jenis absen terakhir
        $attendance->forceFill([
            'work_type' => $request->work_type,
            'is_telat_wfo' => Attendance::resolveIsTelatWfo(
                $request->work_type === 'WFO',
                $existing && $existing->check_in
                    ? Carbon::parse($existing->check_in, 'Asia/Jakarta')
                    : $now,
                $now
            ) ? 1 : 0,
        ])->saveQuietly();
        $attendance->refresh();

        $message = 'Check-in berhasil!';
        if ($request->work_type === 'WFO' && isset($data['location_valid']) && !$data['location_valid']) {
            $message = 'Check-in berhasil! Namun lokasi Anda berada di luar jangkauan kantor. Hubungi admin jika Anda merasa salah.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'location_valid' => $data['location_valid'] ?? true,
            'attendance' => $attendance
        ]);
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     * Returns distance in kilometers
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get location name from coordinates using reverse geocoding (OpenStreetMap Nominatim)
     * Returns location name in format: "Village/Kelurahan, City, Province" (e.g., "Lempongsari, Kota Semarang, Jawa Tengah")
     */
    private function getLocationName($latitude, $longitude)
    {
        $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$latitude}&lon={$longitude}&zoom=18&addressdetails=1";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Absensi ICT App');
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept-Language: id,en'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200 || !$response) {
            return null;
        }
        
        $data = json_decode($response, true);
        
        if (!$data || !isset($data['address'])) {
            return null;
        }
        
        $address = $data['address'];
        $locationParts = [];
        
        // Helper function to check if string is RW/RT or just a number
        $isRWRTOrNumber = function($str) {
            $str = trim($str);
            // Check if it's RW, RT, or just numbers
            if (preg_match('/^(RW|RT)[\s\-]?\d+/i', $str)) {
                return true;
            }
            // Check if it's just a number or very short number-like string
            if (preg_match('/^\d+$/', $str) && strlen($str) <= 3) {
                return true;
            }
            return false;
        };
        
        // 1. Village/Kelurahan (prioritas utama untuk area)
        // Skip quarter/residential karena biasanya RW/RT
        if (isset($address['village']) && !$isRWRTOrNumber($address['village'])) {
            $locationParts[] = $address['village'];
        } elseif (isset($address['neighbourhood']) && !$isRWRTOrNumber($address['neighbourhood'])) {
            $locationParts[] = $address['neighbourhood'];
        } elseif (isset($address['suburb']) && !$isRWRTOrNumber($address['suburb'])) {
            // Suburb bisa jadi kelurahan, tapi skip jika RW/RT
            $locationParts[] = $address['suburb'];
        } elseif (isset($address['quarter']) && !$isRWRTOrNumber($address['quarter'])) {
            // Quarter hanya jika bukan RW/RT
            $locationParts[] = $address['quarter'];
        }
        
        // 2. City/Kota atau Town
        if (isset($address['city'])) {
            $locationParts[] = $address['city'];
        } elseif (isset($address['town'])) {
            $locationParts[] = $address['town'];
        } elseif (isset($address['municipality'])) {
            $locationParts[] = $address['municipality'];
        }
        
        // 3. Province/Provinsi
        if (isset($address['state'])) {
            $locationParts[] = $address['state'];
        } elseif (isset($address['region'])) {
            $locationParts[] = $address['region'];
        }
        
        // Jika belum dapat village/kelurahan, coba parse dari display_name
        // Biasanya format: "Road, Village/Kelurahan, Kecamatan, City, Province"
        if (empty($locationParts) && isset($data['display_name'])) {
            $displayName = $data['display_name'];
            $parts = explode(',', $displayName);
            $cleanedParts = [];
            
            foreach ($parts as $part) {
                $part = trim($part);
                // Skip jika kosong, RW/RT, atau angka saja
                if (empty($part) || $isRWRTOrNumber($part)) {
                    continue;
                }
                // Skip bagian yang hanya koordinat
                if (preg_match('/^[\d\.°\'"SNWE\s]+$/', $part)) {
                    continue;
                }
                // Skip jika mengandung kode pos (5 digit angka)
                if (preg_match('/^\d{5}$/', $part)) {
                    continue;
                }
                $cleanedParts[] = $part;
            }
            
            // Cari village/kelurahan (biasanya bagian ke-2 atau ke-3 setelah road)
            // Format umum: "Jl. X, Lempongsari, Kecamatan Y, Kota Semarang, Jawa Tengah"
            $village = null;
            $city = null;
            $province = null;
            
            foreach ($cleanedParts as $index => $part) {
                // Cari village (biasanya tidak mengandung "Kota", "Kecamatan", "Jl", "Jalan")
                if (!$village && !preg_match('/\b(kota|kabupaten|kecamatan|kab|kec|jl|jalan|street|road|kota|semarang)\b/i', $part)) {
                    if (strlen($part) > 3 && $index < 3) {
                        $village = $part;
                    }
                }
                
                // Cari city (biasanya mengandung "Kota" atau nama kota besar)
                if (!$city && preg_match('/\b(kota|semarang|surabaya|jakarta|bandung|yogyakarta|malang)\b/i', $part)) {
                    $city = $part;
                }
                
                // Cari province (biasanya "Jawa Tengah", "Jawa Barat", dll)
                if (!$province && preg_match('/\b(jawa|sumatera|kalimantan|sulawesi|bali|ntb|ntt|papua)\b/i', $part)) {
                    $province = $part;
                }
            }
            
            // Rebuild dengan format yang benar
            if ($village || $city || $province) {
                $locationParts = [];
                if ($village) $locationParts[] = $village;
                if ($city) $locationParts[] = $city;
                if ($province) $locationParts[] = $province;
                
                if (!empty($locationParts)) {
                    return implode(', ', $locationParts);
                }
            }
            
            // Fallback: ambil 2-3 bagian tengah (biasanya area, city, province)
            if (count($cleanedParts) >= 2) {
                $startIdx = min(1, count($cleanedParts) - 2); // Mulai dari index 1 (skip road jika ada)
                $endIdx = min($startIdx + 3, count($cleanedParts));
                $relevantParts = array_slice($cleanedParts, $startIdx, $endIdx - $startIdx);
                
                if (!empty($relevantParts)) {
                    return implode(', ', $relevantParts);
                }
            }
        }
        
        // Return locationParts jika sudah ada
        if (!empty($locationParts)) {
            return implode(', ', $locationParts);
        }
        
        return null;
    }

    public function checkOut(Request $request)
    {
        $today = Carbon::today('Asia/Jakarta');

        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('attendance_date', $today)
            ->first();

        if (!$attendance || !$attendance->check_in) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum check-in hari ini!'
            ], 400);
        }

        if ($attendance->check_out) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah check-out hari ini!'
            ], 400);
        }

        $attendance->update([
            'check_out' => Carbon::now('Asia/Jakarta')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check-out berhasil!',
            'attendance' => $attendance
        ]);
    }
}
