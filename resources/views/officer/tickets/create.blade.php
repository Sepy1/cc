@extends('layouts.app')
@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-semibold mb-6">Buat Tiket</h1>
    <form action="{{ route('officer.tickets.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm text-gray-700 mb-1">Nama Pelapor</label>
            <input type="text" name="reporter_name" value="{{ old('reporter_name') }}" class="w-full border rounded-md p-2 text-sm" required>
            @error('reporter_name') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded-md p-2 text-sm">
                @error('email') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="block text-sm text-gray-700 mb-1">Telepon</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="w-full border rounded-md p-2 text-sm">
                @error('phone') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-700 mb-1">Kategori</label>
                <input type="text" name="category" value="{{ old('category') }}" class="w-full border rounded-md p-2 text-sm">
                @error('category') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="block text-sm text-gray-700 mb-1">Tipe Pelapor</label>
                <select name="reporter_type" class="w-full border rounded-md p-2 text-sm">
                    <option value="umum" {{ old('reporter_type')=='umum' ? 'selected' : '' }}>Umum</option>
                    <option value="nasabah" {{ old('reporter_type')=='nasabah' ? 'selected' : '' }}>Nasabah</option>
                </select>
                @error('reporter_type') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm text-gray-700 mb-1">Judul</label>
            <input type="text" name="title" value="{{ old('title') }}" class="w-full border rounded-md p-2 text-sm" required>
            @error('title') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="block text-sm text-gray-700 mb-1">Detail</label>
            <textarea name="detail" rows="4" class="w-full border rounded-md p-2 text-sm">{{ old('detail') }}</textarea>
            @error('detail') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
        </div>

        {{-- Data Nasabah (opsional) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-700 mb-1">ID KTP</label>
                <input type="text" name="id_ktp" value="{{ old('id_ktp') }}" class="w-full border rounded-md p-2 text-sm">
            </div>
            <div>
                <label class="block text-sm text-gray-700 mb-1">Nomor Rekening</label>
                <input type="text" name="nomor_rekening" value="{{ old('nomor_rekening') }}" class="w-full border rounded-md p-2 text-sm">
            </div>
            <div>
                <label class="block text-sm text-gray-700 mb-1">Nama Ibu</label>
                <input type="text" name="nama_ibu" value="{{ old('nama_ibu') }}" class="w-full border rounded-md p-2 text-sm">
            </div>
            <div>
                <label class="block text-sm text-gray-700 mb-1">Kode Kantor</label>
                <input type="text" name="kode_kantor" value="{{ old('kode_kantor') }}" class="w-full border rounded-md p-2 text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm text-gray-700 mb-1">Alamat</label>
                <textarea name="alamat" rows="3" class="w-full border rounded-md p-2 text-sm">{{ old('alamat') }}</textarea>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-700 mb-1">Media Closing</label>
                <select name="media_closing" class="w-full border rounded-md p-2 text-sm">
                    <option value="">-</option>
                    <option value="whatsapp" {{ old('media_closing')=='whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                    <option value="telepon" {{ old('media_closing')=='telepon' ? 'selected' : '' }}>Telepon</option>
                    <option value="offline" {{ old('media_closing')=='offline' ? 'selected' : '' }}>Offline</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-700 mb-1">Status Awal</label>
                <select name="status" class="w-full border rounded-md p-2 text-sm">
                    <option value="open" {{ old('status')=='open' ? 'selected' : '' }}>Open</option>
                    <option value="pending" {{ old('status')=='pending' ? 'selected' : '' }}>Pending</option>
                </select>
                @error('status') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- Lampiran --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-700 mb-1">Attachment KTP</label>
                <input type="file" name="attachment_ktp" accept=".jpg,.jpeg,.png,.pdf" class="w-full text-sm">
                @error('attachment_ktp') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="block text-sm text-gray-700 mb-1">Attachment Bukti</label>
                <input type="file" name="attachment_bukti" accept=".jpg,.jpeg,.png,.pdf,.zip" class="w-full text-sm">
                @error('attachment_bukti') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">Simpan</button>
        </div>
    </form>
</div>
@endsection
