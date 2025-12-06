@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-semibold text-gray-900 mb-4">Profil Officer</h1>

    @if(session('success'))
        <div class="mb-4 px-3 py-2 rounded-md bg-emerald-50 text-emerald-700 text-sm">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 px-3 py-2 rounded-md bg-red-50 text-red-700 text-sm">
            <ul class="list-disc pl-4">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow border border-gray-100 p-6">
        <form action="{{ route('officer.profile.update') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm text-gray-700 mb-1" for="name">Nama</label>
                <input id="name" type="text" name="name" value="{{ old('name', $user->name ?? '') }}" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500" required>
            </div>
            <div>
                <label class="block text-sm text-gray-700 mb-1" for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email ?? '') }}" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500" required>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-700 mb-1" for="password">Password Baru</label>
                    <input id="password" type="password" name="password" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500" placeholder="Kosongkan bila tidak diubah">
                </div>
                <div>
                    <label class="block text-sm text-gray-700 mb-1" for="password_confirmation">Konfirmasi Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div class="pt-2">
                <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
