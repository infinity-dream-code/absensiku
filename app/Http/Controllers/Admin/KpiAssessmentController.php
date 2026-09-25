<?php

namespace App\Http\Controllers\Admin;

use App\Exports\KpiAssessmentExport;
use App\Http\Controllers\Controller;
use App\Models\KpiAssessment;
use App\Models\KpiAssessmentDetail;
use App\Models\Role;
use App\Models\User;
use App\Services\KpiCalculator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class KpiAssessmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check()) {
                if ($request->is('admin/*')) {
                    return redirect()->route('admin.login');
                }

                return redirect()->route('login');
            }

            $user = Auth::user();
            if ($user->role === 'admin' || $user->isPenilai()) {
                return $next($request);
            }

            if ($request->is('admin/*')) {
                return redirect()->route('attendance.index')->with('error', 'Akses ditolak.');
            }

            return redirect()->route('attendance.index')->with('error', 'Akses ditolak.');
        });
    }

    public function index(Request $request)
    {
        $auth = Auth::user();
        $isAdmin = $auth->role === 'admin';
        $routePrefix = $this->routePrefix($isAdmin);
        $isPenilaiMode = !$isAdmin;

        $employees = $this->assessableEmployees($auth);

        $searchQuery = KpiAssessment::with(['user', 'penilai', 'role', 'details'])
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->orderByDesc('id');

        if ($request->filled('search_user_id')) {
            $searchQuery->where('id_user', (int) $request->search_user_id);
        }
        if ($request->filled('search_bulan')) {
            $searchQuery->where('bulan', (int) $request->search_bulan);
        }
        if ($request->filled('search_tahun')) {
            $searchQuery->where('tahun', (int) $request->search_tahun);
        }
        if (!$isAdmin) {
            $searchQuery->where('id_role', $auth->id_role);
        }

        $searchResults = $searchQuery->paginate(10)->withQueryString();
        $hasSearchFilter = $request->filled('search_user_id')
            || $request->filled('search_bulan')
            || $request->filled('search_tahun');

        return view('admin.kpi.index', compact(
            'employees',
            'searchResults',
            'hasSearchFilter',
            'isAdmin',
            'isPenilaiMode',
            'routePrefix',
            'auth'
        ));
    }

    public function form(Request $request)
    {
        $auth = Auth::user();
        $isAdmin = $auth->role === 'admin';
        $routePrefix = $this->routePrefix($isAdmin);
        $isPenilaiMode = !$isAdmin;

        $employees = $this->assessableEmployees($auth);
        $selectedUserId = (int) $request->input('user_id', 0);
        $bulan = (int) $request->input('bulan', (int) Carbon::now('Asia/Jakarta')->month);
        $tahun = (int) $request->input('tahun', (int) Carbon::now('Asia/Jakarta')->year);

        $selectedEmployee = $selectedUserId > 0
            ? User::with('kpiRole')->find($selectedUserId)
            : null;

        if ($selectedEmployee && !$this->canAssess($auth, $selectedEmployee)) {
            $selectedEmployee = null;
            $selectedUserId = 0;
        }

        $indicators = [];
        $existing = null;
        $scoreMap = [];
        $absensiInfo = null;
        $absensiIndex = null;

        if ($selectedEmployee && $selectedEmployee->id_role) {
            $role = Role::find($selectedEmployee->id_role);
            if ($role) {
                $indicators = KpiCalculator::indicatorsForUser($selectedEmployee, $role);
                $existing = KpiAssessment::with('details')
                    ->where('id_user', $selectedEmployee->id)
                    ->where('bulan', $bulan)
                    ->where('tahun', $tahun)
                    ->first();

                if ($existing) {
                    $detailMap = $existing->details->keyBy('nama_indikator');
                    foreach ($indicators as $i => $indicator) {
                        $scoreMap[$i] = (float) ($detailMap->get($indicator['nama'])?->skor ?? 0);
                        $savedBobot = $detailMap->get($indicator['nama'])?->bobot;
                        if ($savedBobot !== null) {
                            $indicators[$i]['bobot'] = (int) $savedBobot;
                        }
                    }
                }

                $absensiIndex = KpiCalculator::absensiIndex($indicators);
                if ($absensiIndex !== null) {
                    $absensiInfo = KpiCalculator::attendanceScore($selectedEmployee->id, $bulan, $tahun);
                    $scoreMap[$absensiIndex] = $absensiInfo['skor'];
                }
            }
        }

        return view('admin.kpi.form', compact(
            'employees',
            'selectedUserId',
            'selectedEmployee',
            'bulan',
            'tahun',
            'indicators',
            'existing',
            'scoreMap',
            'absensiInfo',
            'absensiIndex',
            'isAdmin',
            'isPenilaiMode',
            'routePrefix',
            'auth'
        ));
    }

    public function store(Request $request)
    {
        $auth = Auth::user();
        $isAdmin = $auth->role === 'admin';
        $redirectRoute = $this->routePrefix($isAdmin) . '.form';

        $skorInput = $request->input('skor', []);
        if (is_array($skorInput)) {
            $request->merge([
                'skor' => collect($skorInput)->map(function ($value) {
                    return is_string($value) ? str_replace(',', '.', trim($value)) : $value;
                })->all(),
            ]);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020|max:2100',
            'skor' => 'required|array',
            'skor.*' => 'required|numeric|min:1|max:10',
            'rekomendasi' => 'nullable|string|max:5000',
        ] + ($isAdmin ? [
            'bobot' => 'required|array',
            'bobot.*' => 'nullable|integer|min:0|max:100',
        ] : []));

        $employee = User::with('kpiRole')->findOrFail((int) $validated['user_id']);
        if (!$this->canAssess($auth, $employee)) {
            return redirect()->route($redirectRoute)->with('error', 'Karyawan tidak boleh dinilai oleh akun ini.');
        }
        if (!$employee->kpiRole) {
            return redirect()->route($redirectRoute)->with('error', 'Karyawan belum punya Role KPI.');
        }

        $scores = collect($validated['skor'])->map(fn ($skor) => KpiCalculator::parseSkor($skor))->values()->all();
        $indicators = KpiCalculator::indicatorsForUser($employee, $employee->kpiRole);
        if ($isAdmin) {
            $indicators = KpiCalculator::applyBobotOverrides($indicators, $validated['bobot'] ?? []);
            KpiCalculator::persistBobotForUser($employee, $indicators, $validated['bobot'] ?? []);
            $employee->refresh();
        }

        $absensiIndex = KpiCalculator::absensiIndex($indicators);
        if ($absensiIndex !== null) {
            $absensiInfo = KpiCalculator::attendanceScore($employee->id, (int) $validated['bulan'], (int) $validated['tahun']);
            $scores[$absensiIndex] = $absensiInfo['skor'];
        }

        $calc = KpiCalculator::calculateFromIndicators($indicators, $scores);

        DB::transaction(function () use ($validated, $employee, $auth, $calc) {
            $assessment = KpiAssessment::updateOrCreate(
                [
                    'id_user' => $employee->id,
                    'bulan' => (int) $validated['bulan'],
                    'tahun' => (int) $validated['tahun'],
                ],
                [
                    'id_penilai' => $auth->id,
                    'id_role' => $employee->id_role,
                    'skor_akhir' => $calc['skor_akhir'],
                    'kategori' => $calc['kategori'],
                    'rekomendasi' => $validated['rekomendasi'] ?? null,
                ]
            );

            KpiAssessmentDetail::where('id_kpi_assessment', $assessment->id)->delete();
            foreach ($calc['details'] as $detail) {
                KpiAssessmentDetail::create([
                    'id_kpi_assessment' => $assessment->id,
                    'nama_indikator' => $detail['nama_indikator'],
                    'bobot' => $detail['bobot'],
                    'skor' => $detail['skor'],
                    'nilai_akhir' => $detail['nilai_akhir'],
                ]);
            }
        });

        return redirect()->route($redirectRoute, [
            'user_id' => $employee->id,
            'bulan' => $validated['bulan'],
            'tahun' => $validated['tahun'],
        ])->with('success', 'Penilaian KPI berhasil disimpan. Skor akhir: ' . $calc['skor_akhir'] . ' (' . $calc['kategori'] . ')');
    }

    public function export(Request $request)
    {
        $auth = Auth::user();

        $request->validate([
            'id' => 'required|exists:kpi_assessments,id',
        ]);

        $assessment = KpiAssessment::with(['user', 'penilai', 'role', 'details'])->findOrFail((int) $request->id);
        if (!$this->canAssess($auth, $assessment->user)) {
            abort(403, 'Akses ditolak.');
        }

        $filename = 'KPI_' . str_replace(' ', '_', $assessment->user->name) . '_'
            . KpiCalculator::monthName($assessment->bulan) . '_' . $assessment->tahun . '.xlsx';

        return Excel::download(new KpiAssessmentExport($assessment), $filename);
    }

    public function roleIndicators(Request $request)
    {
        $auth = Auth::user();
        if ($auth->role !== 'admin') {
            abort(403, 'Hanya admin yang dapat mengelola indikator role.');
        }

        $roles = Role::orderBy('role')->get();
        $selectedRoleId = (int) $request->input('role_id', $roles->first()?->id ?? 0);
        $selectedRole = $roles->firstWhere('id', $selectedRoleId);
        $indicators = $selectedRole
            ? KpiCalculator::indicatorsForRole($selectedRole)
            : [];

        return view('admin.kpi.role-indicators', compact(
            'roles',
            'selectedRole',
            'selectedRoleId',
            'indicators'
        ));
    }

    public function updateRoleIndicators(Request $request)
    {
        $auth = Auth::user();
        if ($auth->role !== 'admin') {
            abort(403, 'Hanya admin yang dapat mengelola indikator role.');
        }

        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'nama' => 'required|array|min:1',
            'nama.*' => 'required|string|max:255',
            'bobot' => 'required|array',
            'bobot.*' => 'nullable|integer|min:0|max:100',
        ]);

        $rows = [];
        foreach ($validated['nama'] as $i => $nama) {
            $nama = trim((string) $nama);
            if ($nama === '') {
                continue;
            }
            $rows[] = [
                'nama' => $nama,
                'bobot' => (int) ($validated['bobot'][$i] ?? 0),
            ];
        }

        if (empty($rows)) {
            return back()->with('error', 'Minimal satu indikator harus diisi.');
        }

        $role = Role::findOrFail((int) $validated['role_id']);
        $role->update(['kpi_indikator' => $rows]);

        return redirect()
            ->route('admin.kpi.indikator-role', ['role_id' => $role->id])
            ->with('success', 'Indikator role "' . $role->role . '" berhasil disimpan.');
    }

    public function storeUserIndicator(Request $request)
    {
        $auth = Auth::user();
        $isAdmin = $auth->role === 'admin';
        $routePrefix = $this->routePrefix($isAdmin);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020|max:2100',
            'nama_indikator' => 'required|string|max:255',
            'bobot' => 'nullable|integer|min:1|max:50',
        ]);

        $employee = User::with('kpiRole')->findOrFail((int) $validated['user_id']);
        if (!$this->canAssess($auth, $employee)) {
            return back()->with('error', 'Karyawan tidak boleh dinilai oleh akun ini.');
        }

        $nama = trim($validated['nama_indikator']);
        $list = $employee->kpi_indikator_individu ?? [];

        foreach ($list as $item) {
            if (strcasecmp(trim((string) ($item['nama'] ?? '')), $nama) === 0) {
                return back()->with('error', 'Indikator "' . $nama . '" sudah ada.');
            }
        }

        $entry = ['nama' => $nama];
        if ($isAdmin && isset($validated['bobot']) && $validated['bobot'] !== null && $validated['bobot'] !== '') {
            $entry['bobot'] = (int) $validated['bobot'];
        }
        $list[] = $entry;
        $employee->update(['kpi_indikator_individu' => $list]);

        return redirect()->route($routePrefix . '.form', [
            'user_id' => $employee->id,
            'bulan' => $validated['bulan'],
            'tahun' => $validated['tahun'],
        ])->with('success', 'Indikator individu berhasil ditambahkan.');
    }

    public function updateUserIndicator(Request $request)
    {
        $auth = Auth::user();
        $isAdmin = $auth->role === 'admin';
        $routePrefix = $this->routePrefix($isAdmin);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'index' => 'required|integer|min:0',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020|max:2100',
            'nama_indikator' => 'required|string|max:255',
            'bobot' => 'nullable|integer|min:1|max:50',
        ]);

        $employee = User::findOrFail((int) $validated['user_id']);
        if (!$this->canAssess($auth, $employee)) {
            return back()->with('error', 'Akses ditolak.');
        }

        $list = $employee->kpi_indikator_individu ?? [];
        $index = (int) $validated['index'];
        if (!isset($list[$index])) {
            return back()->with('error', 'Indikator tidak ditemukan.');
        }

        $nama = trim($validated['nama_indikator']);
        foreach ($list as $i => $item) {
            if ($i !== $index && strcasecmp(trim((string) ($item['nama'] ?? '')), $nama) === 0) {
                return back()->with('error', 'Indikator "' . $nama . '" sudah ada.');
            }
        }

        $entry = ['nama' => $nama];
        if ($isAdmin && isset($validated['bobot']) && $validated['bobot'] !== null && $validated['bobot'] !== '') {
            $entry['bobot'] = (int) $validated['bobot'];
        } elseif (isset($list[$index]['bobot'])) {
            $entry['bobot'] = (int) $list[$index]['bobot'];
        }
        $list[$index] = $entry;
        $employee->update(['kpi_indikator_individu' => array_values($list)]);

        return redirect()->route($routePrefix . '.form', [
            'user_id' => $employee->id,
            'bulan' => $validated['bulan'],
            'tahun' => $validated['tahun'],
        ])->with('success', 'Indikator individu berhasil diperbarui.');
    }

    public function destroyUserIndicator(Request $request)
    {
        $auth = Auth::user();
        $isAdmin = $auth->role === 'admin';
        $routePrefix = $this->routePrefix($isAdmin);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'index' => 'required|integer|min:0',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020|max:2100',
        ]);

        $employee = User::findOrFail((int) $validated['user_id']);
        if (!$this->canAssess($auth, $employee)) {
            return back()->with('error', 'Akses ditolak.');
        }

        $list = $employee->kpi_indikator_individu ?? [];
        unset($list[(int) $validated['index']]);
        $employee->update(['kpi_indikator_individu' => array_values($list)]);

        return redirect()->route($routePrefix . '.form', [
            'user_id' => $employee->id,
            'bulan' => $validated['bulan'],
            'tahun' => $validated['tahun'],
        ])->with('success', 'Indikator individu dihapus.');
    }

    private function routePrefix(bool $isAdmin): string
    {
        return $isAdmin ? 'admin.kpi' : 'kpi';
    }

    private function assessableEmployees(User $auth)
    {
        $query = User::with('kpiRole')
            ->where('role', 'user')
            ->where('jenis', 1)
            ->whereNotNull('id_role')
            ->orderBy('name');

        if ($auth->role !== 'admin') {
            $query->where('id_role', $auth->id_role);
        }

        return $query->get(['id', 'name', 'nip', 'nik', 'id_role']);
    }

    private function canAssess(User $auth, User $employee): bool
    {
        if ($employee->role !== 'user' || (int) $employee->jenis !== 1 || empty($employee->id_role)) {
            return false;
        }

        if ($auth->role === 'admin') {
            return true;
        }

        return $auth->isPenilai() && (int) $auth->id_role === (int) $employee->id_role;
    }
}
