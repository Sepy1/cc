@extends('layouts.app')

@section('header')
<h2 class="font-semibold text-xl text-gray-800">Buat Tiket Baru</h2>
@endsection

@section('content')
<div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
    <form action="{{ route('admin.tickets.store') }}" method="POST">
        @csrf
        @include('admin.tickets._form', ['submitLabel' => 'Simpan'])
    </form>
</div>
@endsection
