@extends('layouts.admin')

@section('header', isset($service) ? 'Edit Service' : 'Tambah Service Baru')

@section('content')
    <div class="max-w-2xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
        <div class="p-6 border-b dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Formulir Layanan</h3>
        </div>

        <form action="{{ isset($service) ? route('services.update', $service->id) : route('services.store') }}" method="POST"
            class="p-6 space-y-6">
            @csrf
            @if (isset($service))
                @method('PUT')
            @endif

            <div>
                <label for="name" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Nama
                    Service</label>
                <input type="text" name="name" id="name" value="{{ old('name', $service->name ?? '') }}"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-unila-blue-500 focus:border-unila-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                    placeholder="Contoh: Lupa Password SSO" required>
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center">
                <input id="is_active" name="is_active" type="checkbox" value="1"
                    {{ old('is_active', $service->is_active ?? true) ? 'checked' : '' }}
                    class="w-5 h-5 text-unila-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-unila-blue-500 dark:focus:ring-unila-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                <label for="is_active" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Aktifkan Layanan
                    ini?</label>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t dark:border-gray-700">
                <a href="{{ route('services.index') }}"
                    class="px-5 py-2.5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 focus:z-10 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                    Cancel
                </a>
                <button type="submit"
                    class="text-white bg-unila-blue-600 hover:bg-unila-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                    {{ isset($service) ? 'Update' : 'Save' }}
                </button>
            </div>
        </form>
    </div>
@endsection
