@extends('layouts.app')

@section('header')
<h2 class="font-semibold text-xl text-gray-800">
    Edit Tiket #{{ $ticket->ticket_no }}
</h2>
@endsection

@section('content')
<div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">

    <form action="{{ route('admin.tickets.update', $ticket->id) }}" method="POST">
        @csrf
        @method('PUT')

        @include('admin.tickets._form', [
            'ticket' => $ticket,
            'submitLabel' => 'Perbarui'
        ])

        <div class="form-group">
            <label for="status">Status</label>
            <select name="status" id="status" class="form-control">
                <option value="open" {{ old('status', $ticket->status) == 'open' ? 'selected' : '' }}>Open</option>
                <option value="pending" {{ old('status', $ticket->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="resolved" {{ old('status', $ticket->status) == 'resolved' ? 'selected' : '' }}>Resolved</option>
                <option value="closed" {{ old('status', $ticket->status) == 'closed' ? 'selected' : '' }}>Closed</option>
                <option value="rejected" {{ old('status', $ticket->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            @error('status') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="form-group" id="tindak-lanjut-wrapper" style="{{ old('status', $ticket->status) == 'closed' ? '' : 'display:none;' }}">
            <label for="tindak_lanjut">Tindak Lanjut <span class="text-danger">*</span></label>
            <textarea name="tindak_lanjut" id="tindak_lanjut" class="form-control" rows="4" @if(old('status', $ticket->status) == 'closed') required @endif>{{ old('tindak_lanjut', $ticket->tindak_lanjut) }}</textarea>
            @error('tindak_lanjut') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <button class="btn btn-primary" type="submit">Simpan</button>
    </form>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var statusEl = document.getElementById('status');
    var wrapper = document.getElementById('tindak-lanjut-wrapper');
    var field = document.getElementById('tindak_lanjut');

    function toggleTindak() {
        if (!statusEl) return;
        if (statusEl.value === 'closed') {
            wrapper.style.display = '';
            field.setAttribute('required', 'required');
        } else {
            wrapper.style.display = 'none';
            field.removeAttribute('required');
        }
    }

    statusEl.addEventListener('change', toggleTindak);
    toggleTindak();
});
</script>
@endpush
