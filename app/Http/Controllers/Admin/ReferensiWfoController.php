<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferensiWfo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferensiWfoController extends Controller
{
    private array $allowedDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || Auth::user()->role !== 'admin') {
                return redirect()->route('admin.login');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $employees = User::where('role', 'user')
            ->where('jenis', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'nip', 'nik']);

        $selectedUserId = (int) $request->input('user_id', 0);
        $selectedReference = null;

        if ($selectedUserId > 0) {
            $selectedReference = ReferensiWfo::where('custid', $selectedUserId)->first();
        }

        $references = ReferensiWfo::with('user')
            ->whereIn('custid', $employees->pluck('id'))
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.referensi-wfo.index', [
            'employees' => $employees,
            'selectedUserId' => $selectedUserId,
            'selectedReference' => $selectedReference,
            'references' => $references,
            'allowedDays' => $this->allowedDays,
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:daily,berjadwal',
            'hari' => 'nullable|array',
            'hari.*' => 'in:monday,tuesday,wednesday,thursday,friday',
        ]);

        $user = User::where('id', (int) $validated['user_id'])
            ->where('role', 'user')
            ->where('jenis', 1)
            ->firstOrFail();

        $days = [];
        if ($validated['type'] === 'berjadwal') {
            $days = array_values(array_unique($validated['hari'] ?? []));
            if (count($days) === 0) {
                return back()->withInput()->with('error', 'Untuk tipe berjadwal, pilih minimal 1 hari.');
            }
        }

        ReferensiWfo::updateOrCreate(
            ['custid' => $user->id],
            [
                'type' => $validated['type'],
                'hari' => $validated['type'] === 'berjadwal' ? json_encode($days) : null,
            ]
        );

        return redirect()
            ->route('admin.referensi-wfo.index', ['user_id' => $user->id])
            ->with('success', 'Referensi WFO berhasil disimpan.');
    }
}
