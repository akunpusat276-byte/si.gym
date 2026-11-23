<div class="row">
    <div class="col-md-6 mb-3">
        <label for="package_name" class="form-label">Nama Paket</label>
        <input id="package_name" class="form-control" name="name" value="{{ old('name', $package?->name ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label for="package_category" class="form-label">Kategori Paket</label>
        <input id="package_category" class="form-control" name="category" value="{{ old('category', $package?->category ?? '') }}" list="package-categories" placeholder="Basic, Regular, Premium, Annual" required>
        <datalist id="package-categories"><option value="Basic"><option value="Regular"><option value="Premium"><option value="Annual"></datalist>
    </div>
</div>
<div class="mb-3">
    <label for="package_description" class="form-label">Deskripsi</label>
    <textarea id="package_description" class="form-control" name="description" rows="3">{{ old('description', $package?->description ?? '') }}</textarea>
</div>
<div class="mb-3">
    <label for="package_facilities" class="form-label">Fasilitas</label>
    <textarea id="package_facilities" class="form-control" name="facilities" rows="3" placeholder="Contoh: Kardio, area bebas, locker">{{ old('facilities', $package?->facilities ?? '') }}</textarea>
</div>
<div class="mb-3">
    <label for="package_image" class="form-label">Gambar Paket</label>
    <input id="package_image" class="form-control" type="file" name="image" accept="image/jpeg,image/png,image/webp">
    <div class="form-text">JPG, PNG, atau WEBP, maksimal 2 MB.</div>
    <img id="package-image-preview" src="{{ $package?->imageUrl() }}" alt="{{ $package ? 'Foto '.$package->name : 'Preview gambar paket' }}" class="img-thumbnail mt-2 {{ $package?->imageUrl() ? '' : 'd-none' }}" style="max-width:260px;max-height:180px;object-fit:cover">
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="package_duration" class="form-label">Durasi Membership (bulan)</label>
        <input id="package_duration" class="form-control" type="number" name="duration" value="{{ old('duration', $package?->duration ?? '') }}" min="1" step="1" required>
    </div>
    <div class="col-md-6 mb-3">
        <label for="package_price" class="form-label">Harga Paket (Rp)</label>
        <input id="package_price" class="form-control" type="number" name="price" value="{{ old('price', $package?->price ?? '') }}" min="0" step="1" required>
    </div>
</div>
<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="active" @checked(old('is_active', $package?->is_active ?? true))>
    <label class="form-check-label" for="active">Paket aktif</label>
</div>
@push('scripts')
    <script>
        document.getElementById('package_image')?.addEventListener('change', event => {
            const file = event.target.files?.[0];
            const preview = document.getElementById('package-image-preview');

            if (file && preview) {
                preview.src = URL.createObjectURL(file);
                preview.classList.remove('d-none');
            }
        });
    </script>
@endpush
