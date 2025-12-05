@extends('layouts.app')
@section('content')
<form action="{{ route('officer.tickets.update', $ticket) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
    @csrf
    @method('PUT')
    {{-- ...existing code... (title, category, reporter_name, email, phone, detail) ... --}}

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm text-gray-700 mb-1">Tipe Pelapor</label>
            <select name="reporter_type" class="w-full border rounded-md p-2 text-sm">
                <option value="umum" {{ old('reporter_type', $ticket->reporter_type)=='umum' ? 'selected' : '' }}>Umum</option>
                <option value="nasabah" {{ old('reporter_type', $ticket->reporter_type)=='nasabah' ? 'selected' : '' }}>Nasabah</option>
            </select>
            @error('reporter_type') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="block text-sm text-gray-700 mb-1">Media Closing</label>
            @php $mc = old('media_closing', $ticket->media_closing); @endphp
            <select name="media_closing" class="w-full border rounded-md p-2 text-sm">
                <option value="">-</option>
                <option value="whatsapp" {{ $mc=='whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                <option value="telepon" {{ $mc=='telepon' ? 'selected' : '' }}>Telepon</option>
                <option value="offline" {{ $mc=='offline' ? 'selected' : '' }}>Offline</option>
            </select>
            @error('media_closing') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm text-gray-700 mb-1">ID KTP</label>
            <input type="text" name="id_ktp" value="{{ old('id_ktp', $ticket->id_ktp) }}" class="w-full border rounded-md p-2 text-sm" />
        </div>
        <div>
            <label class="block text-sm text-gray-700 mb-1">Nomor Rekening</label>
            <input type="text" name="nomor_rekening" value="{{ old('nomor_rekening', $ticket->nomor_rekening) }}" class="w-full border rounded-md p-2 text-sm" />
        </div>
        <div>
            <label class="block text-sm text-gray-700 mb-1">Nama Ibu</label>
            <input type="text" name="nama_ibu" value="{{ old('nama_ibu', $ticket->nama_ibu) }}" class="w-full border rounded-md p-2 text-sm" />
        </div>
        <div>
            <label class="block text-sm text-gray-700 mb-1">Kode Kantor</label>
            <input type="text" name="kode_kantor" value="{{ old('kode_kantor', $ticket->kode_kantor) }}" class="w-full border rounded-md p-2 text-sm" />
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm text-gray-700 mb-1">Alamat</label>
            <textarea name="alamat" rows="3" class="w-full border rounded-md p-2 text-sm">{{ old('alamat', $ticket->alamat) }}</textarea>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm text-gray-700 mb-1">Attachment KTP (opsional)</label>
            <input type="file" name="attachment_ktp" accept=".jpg,.jpeg,.png,.pdf" class="w-full text-sm" />
            @if($ticket->attachment_ktp)
                <p class="text-xs text-gray-500 mt-1">Saat ini: {{ basename($ticket->attachment_ktp) }}</p>
            @endif
        </div>
        <div>
            <label class="block text-sm text-gray-700 mb-1">Attachment Bukti (opsional)</label>
            <input type="file" name="attachment_bukti" accept=".jpg,.jpeg,.png,.pdf,.zip" class="w-full text-sm" />
            @if($ticket->attachment_bukti)
                <p class="text-xs text-gray-500 mt-1">Saat ini: {{ basename($ticket->attachment_bukti) }}</p>
            @endif
        </div>
    </div>

    <div class="flex justify-end">
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">Simpan</button>
    </div>
</form>
@endsection
