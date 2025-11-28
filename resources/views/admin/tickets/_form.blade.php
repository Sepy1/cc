<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

    <div>
        <label class="block text-sm font-medium text-gray-700">Nama Pelapor</label>
        <input type="text" name="reporter_name"
               value="{{ old('reporter_name', $ticket->reporter_name ?? '') }}"
               class="mt-1 block w-full border rounded-md p-2">
        @error('reporter_name') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">No. Telepon</label>
        <input type="text" name="phone"
               value="{{ old('phone', $ticket->phone ?? '') }}"
               class="mt-1 block w-full border rounded-md p-2">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Email</label>
        <input type="email" name="email"
               value="{{ old('email', $ticket->email ?? '') }}"
               class="mt-1 block w-full border rounded-md p-2">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Kategori</label>
        <input type="text" name="category"
               value="{{ old('category', $ticket->category ?? '') }}"
               class="mt-1 block w-full border rounded-md p-2">
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Judul</label>
        <input type="text" name="title"
               value="{{ old('title', $ticket->title ?? '') }}"
               class="mt-1 block w-full border rounded-md p-2">
        @error('title') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Detail</label>
        <textarea name="detail" rows="4" 
                  class="mt-1 block w-full border rounded-md p-2">{{ old('detail', $ticket->detail ?? '') }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Status</label>
        <select name="status" class="mt-1 block w-full border rounded-md p-2">
            <option value="open"     {{ old('status', $ticket->status ?? '') == 'open' ? 'selected':'' }}>Open</option>
            <option value="pending"  {{ old('status', $ticket->status ?? '') == 'pending' ? 'selected':'' }}>Pending</option>
            <option value="resolved" {{ old('status', $ticket->status ?? '') == 'resolved' ? 'selected':'' }}>Resolved</option>
            <option value="closed"   {{ old('status', $ticket->status ?? '') == 'closed' ? 'selected':'' }}>Closed</option>
        </select>
    </div>

</div>

<div class="mt-6">
    <button class="px-4 py-2 bg-indigo-600 text-white rounded shadow hover:bg-indigo-700">
        {{ $submitLabel }}
    </button>
</div>
