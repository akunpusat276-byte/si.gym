<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'duration' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);
        $search = trim($validated['search'] ?? '');

        $packages = Package::query()
            ->when($search !== '', fn (Builder $query) => $query->where(
                fn (Builder $searchQuery) => $searchQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%"),
            ))
            ->when(isset($validated['duration']), fn (Builder $query) => $query->where('duration', $validated['duration']))
            ->when(isset($validated['status']), fn (Builder $query) => $query->where('is_active', $validated['status'] === 'active'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $durations = Package::query()->select('duration')->distinct()->orderBy('duration')->pluck('duration');

        return view('packages.index', compact('packages', 'durations'));
    }

    public function create(): View
    {
        return view('packages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'facilities' => ['nullable', 'string'],
            'duration' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('packages', 'public');
        }
        unset($data['image']);
        Package::create($data);

        return redirect()->route('packages.index')->with('success', 'Paket berhasil ditambahkan.');
    }

    public function show(Package $package): View
    {
        return view('packages.show', compact('package'));
    }

    public function edit(Package $package): View
    {
        return view('packages.edit', compact('package'));
    }

    public function update(Request $request, Package $package): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'facilities' => ['nullable', 'string'],
            'duration' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        if ($request->hasFile('image')) {
            $previousImage = $package->image_path;
            $data['image_path'] = $request->file('image')->store('packages', 'public');
            unset($data['image']);
            $package->update($data);
            $this->deleteUploadedImage($previousImage);
        } else {
            unset($data['image']);
            $package->update($data);
        }

        return redirect()->route('packages.index')->with('success', 'Paket berhasil diperbarui.');
    }

    public function destroy(Package $package): RedirectResponse
    {
        if (
            $package->registrations()->exists()
            || $package->payments()->exists()
            || $package->memberships()->exists()
        ) {
            return back()->with('error', 'Paket yang sudah digunakan tidak dapat dihapus. Nonaktifkan paket sebagai gantinya.');
        }

        $imagePath = $package->image_path;
        $package->delete();
        $this->deleteUploadedImage($imagePath);

        return back()->with('success', 'Paket berhasil dihapus.');
    }

    private function deleteUploadedImage(?string $imagePath): void
    {
        if ($imagePath !== null && ! str_starts_with($imagePath, 'images/')) {
            Storage::disk('public')->delete($imagePath);
        }
    }
}
