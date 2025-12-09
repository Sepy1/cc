@extends('layouts.app')

@section('content')
@php
    $statusColors = [
        'open'    => 'bg-green-100 text-green-800',
        'pending' => 'bg-yellow-100 text-yellow-800',
        'closed'  => 'bg-gray-100 text-gray-700',
        'resolved'=> 'bg-blue-100 text-blue-800',
        'rejected'=> 'bg-red-100 text-red-800',
    ];
    $typeColors = [
        'nasabah' => 'bg-emerald-100 text-emerald-800',
        'umum'    => 'bg-gray-100 text-gray-700',
    ];
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Combined Filter Card (responsive) --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 mb-6">
        <div class="flex items-start justify-between gap-4 flex-col sm:flex-row">
            <div class="flex-1">
                <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">Daftar Tiket</h1>
            </div>

            <div class="w-full sm:w-auto mt-4 sm:mt-0">
                <form action="{{ route('admin.tickets.index') }}" method="GET" class="flex flex-col sm:flex-row sm:items-center gap-2">
                    <label for="q" class="sr-only">Cari tiket</label>
                    <input id="q" name="q" value="{{ request('q') }}" type="search"
                        placeholder="Cari nomor, judul, reporter..."
                        class="w-full sm:w-64 px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >

                    @php
                        $statuses = ['' => 'Semua', 'open' => 'Open', 'pending' => 'Pending', 'progress' => 'Progress', 'resolved' => 'Resolved', 'closed' => 'Closed', 'rejected' => 'Rejected'];
                        $currentStatus = request('status', '');
                    @endphp
                    <select id="status" name="status"
                            class="w-full sm:w-40 px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($statuses as $val => $label)
                            <option value="{{ $val }}" @selected($currentStatus === $val)>{{ $label }}</option>
                        @endforeach
                    </select>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded-md shadow-sm hover:bg-indigo-700">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18A7.5 7.5 0 1010.5 3a7.5 7.5 0 000 15z"/></svg>
                            Tampilkan
                        </button>

                        {{-- tombol buka modal (diganti dari link) --}}
                        <button type="button" id="openCreateModal" class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded-md shadow-sm hover:bg-indigo-700">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Buat
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

   {{-- LIST TIKET (CARD GRID) --}}
@if($tickets->count())
    <div id="ticketsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($tickets as $t)
            @php
                $type = strtolower($t->reporter_type ?? ($t->is_nasabah ? 'nasabah' : 'umum'));
                $typeBadge = $typeColors[$type] ?? $typeColors['umum'];
                $statusKey = strtolower($t->status ?? 'unknown');
                $badgeClass = $statusColors[$statusKey] ?? 'bg-gray-100 text-gray-700';
            @endphp

            <article class="
                bg-white border border-gray-200 
                rounded-2xl shadow-sm 
                hover:shadow-lg hover:border-gray-300 
                transition-all duration-300 ease-out 
                hover:-translate-y-1
            ">
                <div class="px-5 py-5 flex flex-col h-full">

                    {{-- Header No Tiket + Status + small detail on mobile --}}
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('admin.tickets.show', $t->id) }}"
                                class="text-sm font-semibold text-indigo-600 hover:underline truncate">
                                {{ $t->ticket_no }}
                            </a>

                            <div class="mt-1 text-lg font-semibold text-gray-900 leading-snug truncate">
                                {{ \Illuminate\Support\Str::limit($t->title, 80) }}
                            </div>
                        </div>

                        <div class="text-right ml-3 flex-shrink-0">
                            <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full {{ $badgeClass }}">
                                {{ ucfirst($t->status) }}
                            </span>
                            <div class="mt-2 text-xs text-gray-400">
                                {{ $t->created_at?->diffForHumans() }}
                            </div>
                        </div>
                    </div>

                    {{-- Reporter --}}
                    <div class="mt-4">
                        <div class="flex items-center gap-2">
                            <div class="text-sm font-medium text-gray-800 truncate">
                                {{ $t->reporter_name }}
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full {{ $typeBadge }}">
                                {{ $type === 'nasabah' ? 'Nasabah' : 'Umum' }}
                            </span>
                        </div>
                        <div class="text-xs text-gray-400 mt-1 truncate">
                            {{ $t->email ?? '-' }}
                        </div>
                    </div>

                    {{-- Detail preview (moved to bottom for consistent card height) --}}
                    @if(!empty($t->detail))
                        <div class="mt-4 text-sm text-gray-600 line-clamp-3 leading-relaxed sm:hidden">
                            {{-- on small screens show a slightly shorter preview --}}
                            {{ \Illuminate\Support\Str::limit($t->detail, 160) }}
                        </div>
                        <div class="mt-4 text-sm text-gray-600 line-clamp-3 leading-relaxed hidden sm:block">
                            {{ \Illuminate\Support\Str::limit($t->detail, 200) }}
                        </div>
                    @endif

                    {{-- Actions --}}
                    <div class="mt-5 mt-auto flex justify-end">
                        <a href="{{ route('admin.tickets.show', $t->id) }}"
                            class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg shadow hover:bg-indigo-700 hover:shadow-md transition">
                            Lihat
                        </a>
                    </div>

                </div>
            </article>

        @endforeach
    </div>
@else
    {{-- Empty state --}}
    <div class="bg-white border rounded-lg p-8 text-center shadow-sm">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6M9 16h6M7 8h10M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">Belum ada tiket</h3>
        <p class="mt-2 text-sm text-gray-500">Silakan buat tiket baru.</p>
        <div class="mt-4">
            {{-- tombol buka modal pada empty state juga --}}
            <button type="button" id="openCreateModalEmpty" class="px-4 py-2 bg-indigo-600 text-white rounded-md shadow hover:bg-indigo-700 transition">
                Buat Tiket Baru
            </button>
        </div>
    </div>
@endif

{{-- Pagination --}}
<div class="mt-6">
    {{ $tickets->withQueryString()->links() }}
</div>
</div>

{{-- Modal Create Ticket (rapi + scrollable) --}}
<div id="createModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4">
    <div id="createModalOverlay" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

    <div
        role="dialog" aria-modal="true"
        class="relative w-full max-w-3xl bg-white rounded-2xl shadow-lg overflow-hidden z-10"
        style="max-height: 90vh;"
    >
        {{-- Header (sticky) --}}
        <div class="sticky top-0 bg-indigo-600 z-20 flex items-center justify-between px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-white">Buat Tiket Baru</h3>
            <button id="closeCreateModal" class="text-gray-500 hover:text-gray-700" aria-label="Tutup">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Body (scrollable) --}}
        <div class="px-6 py-4 overflow-y-auto" style="max-height: calc(90vh - 7.5rem);">
            <form action="{{ route('admin.tickets.store') }}" method="POST" enctype="multipart/form-data" id="createTicketFormScrollable">
                @csrf

                {{-- validation errors --}}
                @if ($errors->any())
                    <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-100 p-3 rounded">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Nama Pelapor</label>
                        <input type="text" name="reporter_name" id="reporter_name" value="{{ old('reporter_name') }}" class="w-full border rounded-md p-2 text-sm" required>
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
                            <label class="block text-sm text-gray-700 mb-1">Pokok Aduan</label>
                            <select name="category" class="w-full border rounded-md p-2 text-sm">
                                <option value="">- Pilih Pokok Aduan -</option>
                                <option value="Tabungan"   {{ old('category')=='Tabungan' ? 'selected' : '' }}>Tabungan</option>
                                <option value="Kredit"     {{ old('category')=='Kredit' ? 'selected' : '' }}>Kredit</option>
                                <option value="Deposito"   {{ old('category')=='Deposito' ? 'selected' : '' }}>Deposito</option>
                                <option value="Informasi"  {{ old('category')=='Informasi' ? 'selected' : '' }}>Informasi</option>
                                <option value="Lainnya"    {{ old('category')=='Lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
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
                </div>
            </form>
        </div>

        {{-- Footer (sticky) --}}
        <div class="sticky bottom-0 bg-white z-20 px-6 py-3 border-t flex justify-end gap-2">
            <button type="button" id="cancelCreate" class="px-4 py-2 bg-gray-100 rounded-md hover:bg-gray-200">Batal</button>
            <button type="submit" form="createTicketFormScrollable" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Kirim</button>
        </div>
    </div>
</div>


{{-- Simple JS untuk modal dan toggle edit mode --}}
@push('scripts')
<script>
    (function () {
        // Toggle edit-mode existing safe code (tidak mengganggu modal)
        try {
            const toggle = document.getElementById('editMode');
            if (toggle) {
                const dots = toggle.querySelector('.dot');
                const ticketsGrid = document.getElementById('ticketsGrid');
                let editMode = false;

                function setEditMode(on) {
                    editMode = on;
                    toggle.classList.toggle('bg-indigo-600', on);
                    toggle.classList.toggle('bg-gray-300', !on);
                    if (dots) dots.style.transform = on ? 'translateX(20px)' : 'translateX(0)';
                    toggle.setAttribute('aria-pressed', String(on));

                    document.querySelectorAll('.edit-actions').forEach(el => el.classList.toggle('hidden', !on));
                    document.querySelectorAll('.small-edit').forEach(el => el.classList.toggle('hidden', !on));
                    if (ticketsGrid) {
                        ticketsGrid.querySelectorAll('article').forEach(card => {
                            card.classList.toggle('ring-2', on);
                            card.classList.toggle('ring-indigo-100', on);
                        });
                    }
                }

                setEditMode(false);
                toggle.addEventListener('click', () => setEditMode(!editMode));
            }
        } catch(e) {
            console.error('editMode init error', e);
        }

        // Modal create ticket
        const openBtn = document.getElementById('openCreateModal');
        const openBtn2 = document.getElementById('openCreateModalEmpty');
        const modal = document.getElementById('createModal');
        const overlay = document.getElementById('createModalOverlay');
        const closeBtn = document.getElementById('closeCreateModal');
        const cancelBtn = document.getElementById('cancelCreate');
        const firstField = document.getElementById('reporter_name');

        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            // fokus ke field pertama
            setTimeout(() => { if (firstField) firstField.focus(); }, 50);
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }

        if (openBtn) openBtn.addEventListener('click', openModal);
        if (openBtn2) openBtn2.addEventListener('click', openModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

        // klik di overlay untuk tutup
        if (overlay) overlay.addEventListener('click', closeModal);

        // tutup dengan Esc
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });

        // jika form submit dan validasi server balik error, biarkan modal terbuka:
        @if ($errors->any() && old())
            openModal();
        @endif

    })();
</script>
@endpush

@endsection
