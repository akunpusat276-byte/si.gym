<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::in(['admin', 'owner', 'member', 'pendaftar'])],
            'status' => ['nullable', Rule::in(['Aktif', 'Nonaktif'])],
        ]);
        $users = User::query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%");
                });
            })
            ->when($filters['role'] ?? null, function (Builder $query, string $role): void {
                $query->where('role', $role);
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function show(User $user): View
    {
        $user->load([
            'member.memberships.package',
            'registrations.package',
            'payments.package',
        ]);

        return view('users.show', compact('user'));
    }

    public function export(Request $request): Response
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        $filename = 'pengguna-si-gym-'.now()->format('Y-m-d').'.csv';
        $rows = User::query()->latest()->get(['name', 'email', 'role', 'phone', 'created_at']);
        $output = fopen('php://temp', 'r+');
        fputcsv($output, ['Nama', 'Email', 'Role', 'Telepon', 'Dibuat pada']);

        foreach ($rows as $user) {
            fputcsv($output, [
                $user->name,
                $user->email,
                strtoupper($user->role),
                $user->phone,
                $user->created_at?->format('Y-m-d H:i:s'),
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::in(['admin', 'owner', 'member', 'pendaftar'])],
            'phone' => ['nullable', 'string', 'max:30'],
            'gender' => ['nullable', Rule::in(['Laki-laki', 'Perempuan'])],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string'],
        ]);
        $data['password'] = Hash::make($data['password']);
        User::create($data);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::in(['admin', 'owner', 'member', 'pendaftar'])],
            'phone' => ['nullable', 'string', 'max:30'],
            'gender' => ['nullable', Rule::in(['Laki-laki', 'Perempuan'])],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string'],
        ]);
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }
        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is($request->user()), 422, 'Anda tidak dapat menghapus akun sendiri.');
        $user->delete();

        return back()->with('success', 'Pengguna berhasil dihapus.');
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is($request->user()), 422, 'Status akun sendiri tidak dapat dinonaktifkan.');
        $user->update(['status' => $user->status === 'Nonaktif' ? 'Aktif' : 'Nonaktif']);

        return back()->with('success', 'Status akun pengguna berhasil diperbarui.');
    }
}
