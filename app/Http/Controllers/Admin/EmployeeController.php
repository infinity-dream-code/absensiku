<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || Auth::user()->role !== 'admin') {
                return redirect()->route('admin.login');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $employees = User::with('kpiRole')->where('role', 'user')->latest()->get();
        return view('admin.employees.index', compact('employees'));
    }

    public function create()
    {
        $roles = Role::orderBy('role')->get();
        return view('admin.employees.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nik' => 'required|string|unique:users,nik',
            'name' => 'required|string|max:255',
            'id_role' => 'nullable|exists:roles,id',
            'is_penilai' => 'nullable|boolean',
        ]);

        if ($request->boolean('is_penilai') && !$request->filled('id_role')) {
            return back()->withErrors(['id_role' => 'Penilai wajib punya Role KPI.'])->withInput();
        }

        // Generate username from first name (lowercase)
        $nameParts = explode(' ', trim($request->name));
        $firstName = strtolower($nameParts[0]);
        $baseUsername = $firstName;
        $username = $baseUsername;
        $counter = 1;

        // Check if username already exists, if yes, append number
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        User::create([
            'nik' => $request->nik,
            'name' => $request->name,
            'username' => $username,
            'email' => $request->nik . '@absensi.local', // Dummy email untuk kompatibilitas
            'password' => Hash::make('123456'), // Password default = 123456
            'role' => 'user',
            'id_role' => $request->id_role ?: null,
            'is_penilai' => $request->boolean('is_penilai') ? 1 : 0,
        ]);

        return redirect()->route('admin.employees.index')->with('success', "Karyawan berhasil ditambahkan! Username: {$username}, Password default: 123456");
    }

    public function show(User $employee)
    {
        if ($employee->role !== 'user') {
            return redirect()->route('admin.employees.index')->with('error', 'Akses ditolak!');
        }
        return redirect()->route('admin.employees.edit', $employee);
    }

    public function edit(User $employee)
    {
        if ($employee->role !== 'user') {
            return redirect()->route('admin.employees.index')->with('error', 'Akses ditolak!');
        }
        $roles = Role::orderBy('role')->get();
        return view('admin.employees.edit', compact('employee', 'roles'));
    }

    public function update(Request $request, User $employee)
    {
        if ($employee->role !== 'user') {
            return redirect()->route('admin.employees.index')->with('error', 'Akses ditolak!');
        }

        $request->validate([
            'nik' => 'required|string|unique:users,nik,' . $employee->id,
            'name' => 'required|string|max:255',
            'id_role' => 'nullable|exists:roles,id',
            'is_penilai' => 'nullable|boolean',
        ]);

        if ($request->boolean('is_penilai') && !$request->filled('id_role')) {
            return back()->withErrors(['id_role' => 'Penilai wajib punya Role KPI.'])->withInput();
        }

        // Generate username from first name (lowercase)
        $nameParts = explode(' ', trim($request->name));
        $firstName = strtolower($nameParts[0]);
        $baseUsername = $firstName;
        $username = $baseUsername;
        $counter = 1;

        // Check if username already exists (excluding current user), if yes, append number
        while (User::where('username', $username)->where('id', '!=', $employee->id)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        $employee->update([
            'nik' => $request->nik,
            'name' => $request->name,
            'username' => $username,
            'email' => $request->nik . '@absensi.local', // Update email dummy
            'id_role' => $request->id_role ?: null,
            'is_penilai' => $request->boolean('is_penilai') ? 1 : 0,
        ]);

        return redirect()->route('admin.employees.index')->with('success', 'Data karyawan berhasil diperbarui!');
    }

    public function destroy(User $employee)
    {
        if ($employee->role !== 'user') {
            return redirect()->route('admin.employees.index')->with('error', 'Akses ditolak!');
        }

        $employee->delete();

        return redirect()->route('admin.employees.index')->with('success', 'Karyawan berhasil dihapus!');
    }

    public function resetPassword(User $employee)
    {
        if ($employee->role !== 'user') {
            return redirect()->route('admin.employees.index')->with('error', 'Akses ditolak!');
        }

        $employee->update([
            'password' => Hash::make('123456'),
        ]);

        return redirect()->route('admin.employees.index')->with('success', "Password karyawan {$employee->name} berhasil direset menjadi 123456!");
    }

    public function toggleJenis(Request $request, User $employee)
    {
        if ($employee->role !== 'user') {
            return redirect()->route('admin.employees.index')->with('error', 'Akses ditolak!');
        }

        $request->validate([
            'jenis' => 'required|in:0,1',
        ]);

        $employee->update([
            'jenis' => (int) $request->jenis,
        ]);

        return redirect()->route('admin.employees.index')->with('success', "Jenis kerja {$employee->name} berhasil diupdate!");
    }
}
