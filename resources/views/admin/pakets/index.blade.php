<!-- resources/views/admin/pakets/index.blade.php -->
@extends('default')

@section('title', 'Manajemen Paket')

@section('content')
<div class="container mx-auto p-4 md:p-6">
    <h1 class="text-3xl font-bold mb-6 text-white">Manajemen Paket</h1>

    {{-- Success/Error Messages (Flowbite/Tailwind style) --}}
    @if(session('success'))
        <div id="alert-success" class="flex items-center p-4 mb-4 text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
            <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
            </svg>
            <span class="sr-only">Info</span>
            <div class="ms-3 text-sm font-medium">
                {{ session('success') }}
            </div>
            <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-green-50 text-green-500 rounded-lg focus:ring-2 focus:ring-green-400 p-1.5 hover:bg-green-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-green-400 dark:hover:bg-gray-700" data-dismiss-target="#alert-success" aria-label="Close">
                <span class="sr-only">Close</span>
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div id="alert-danger" class="flex items-center p-4 mb-4 text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
            <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
            </svg>
            <span class="sr-only">Info</span>
            <div class="ms-3 text-sm font-medium">
                {{ session('error') }}
            </div>
            <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-red-50 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-gray-700" data-dismiss-target="#alert-danger" aria-label="Close">
                <span class="sr-only">Close</span>
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
            </button>
        </div>
    @endif

    <div class="flex justify-between items-center mb-4">
        <button data-modal-target="create-paket-modal" data-modal-toggle="create-paket-modal" class="block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800" type="button">
            Tambah Paket Baru
        </button>
    </div>

    <div class="bg-[#1e1e1e] p-6 rounded-lg shadow-md overflow-x-auto">
        <table id="paketsTable" class="min-w-full leading-normal text-white">
            <thead>
                <tr class="bg-[#2a2a2a] text-gray-300 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">ID</th>
                    <th class="py-3 px-6 text-left">Nama Paket</th>
                    <th class="py-3 px-6 text-left">Harga</th>
                    <th class="py-3 px-6 text-left">Durasi (Jam)</th>
                    <th class="py-3 px-6 text-left">Services</th>
                    <th class="py-3 px-6 text-left">Aktif</th>
                    <th class="py-3 px-6 text-left">Dibuat Pada</th>
                    <th class="py-3 px-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-400 text-sm font-light">
                @forelse ($pakets as $paket)
                    <tr class="border-b border-gray-700 hover:bg-[#232323]">
                        <td class="py-3 px-6 text-left whitespace-nowrap">{{ $paket->id }}</td>
                        <td class="py-3 px-6 text-left">{{ $paket->nama_paket }}</td>
                        <td class="py-3 px-6 text-left">Rp{{ number_format($paket->harga_paket, 0, ',', '.') }}</td>
                        <td class="py-3 px-6 text-left">{{ $paket->durasi_jam > 0 ? $paket->durasi_jam . ' jam' : 'Fleksibel' }}</td>
                        <td class="py-3 px-6 text-left">
                        @php
                            $servicesInPaket = json_decode($paket->service_detail_paket, true);
                        @endphp
                        @if($servicesInPaket && count($servicesInPaket) > 0)
                            <ul class="list-disc list-inside text-xs">
                                @foreach($servicesInPaket as $service)
                                    <li>{{ $service['nama'] }} ({{ $service['jumlah'] }}x)</li>
                                @endforeach
                            </ul>
                        @else
                            <span class="text-gray-500 italic">Tidak ada</span>
                        @endif
                        </td>
                        <td class="py-3 px-6 text-left">
                            @if($paket->aktif)
                                <span class="bg-green-600 text-white text-xs font-semibold px-2.5 py-0.5 rounded-full dark:bg-green-900 dark:text-green-300">Aktif</span>
                            @else
                                <span class="bg-red-600 text-white text-xs font-semibold px-2.5 py-0.5 rounded-full dark:bg-red-900 dark:text-red-300">Tidak Aktif</span>
                            @endif
                        </td>
                        <td class="py-3 px-6 text-left">{{ $paket->created_at->format('d M Y H:i') }}</td>
                        <td class="py-3 px-6 text-center">
                            <div class="flex item-center justify-center space-x-2">
                                <button type="button"
                                    data-modal-target="edit-paket-modal"
                                    data-modal-toggle="edit-paket-modal"
                                    data-paket-id="{{ $paket->id }}"
                                    data-paket-nama="{{ $paket->nama_paket }}"
                                    data-paket-harga="{{ $paket->harga_paket }}"
                                    data-paket-durasi="{{ $paket->durasi_jam }}"
                                    data-paket-deskripsi="{{ addslashes($paket->deskripsi) }}"
                                    data-paket-aktif="{{ $paket->aktif ? 'true' : 'false' }}"
                                    data-paket-services="{{ json_encode($paket->service_detail_paket) }}"
                                    class="w-8 h-8 rounded-full bg-yellow-500 hover:bg-yellow-600 flex items-center justify-center text-white edit-button"
                                    title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('admin.pakets.destroy', $paket->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus paket ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-full bg-red-600 hover:bg-red-700 flex items-center justify-center text-white" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-4 px-6 text-center text-gray-500">Belum ada paket yang terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            {{ $pakets->links('pagination::tailwind') }}
        </div>
    </div>
</div>

{{-- Create Paket Modal (Flowbite) --}}
<div id="create-paket-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-2xl max-h-full">
        <!-- Modal content -->
        <div class="relative bg-gray-700 rounded-lg shadow dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-semibold text-white dark:text-white">
                    Tambah Paket Baru
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="create-paket-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <form class="p-4 md:p-5" action="{{ route('admin.pakets.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="create_nama_paket" class="block mb-2 text-sm font-medium text-white dark:text-white">Nama Paket</label>
                        <input type="text" name="nama_paket" id="create_nama_paket" value="{{ old('nama_paket') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 @error('nama_paket', 'storePaket') border-red-500 @enderror" placeholder="Contoh: Paket Hemat" required="">
                        @error('nama_paket', 'storePaket')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="create_harga_paket" class="block mb-2 text-sm font-medium text-white dark:text-white">Harga Paket</label>
                        <input type="number" name="harga_paket" id="create_harga_paket" value="{{ old('harga_paket') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 @error('harga_paket', 'storePaket') border-red-500 @enderror" placeholder="0" required="" min="0">
                        @error('harga_paket', 'storePaket')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="create_durasi_jam" class="block mb-2 text-sm font-medium text-white dark:text-white">Durasi (Jam)</label>
                        <input type="number" name="durasi_jam" id="create_durasi_jam" value="{{ old('durasi_jam', 0) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 @error('durasi_jam', 'storePaket') border-red-500 @enderror" placeholder="0" required="" min="0" step="0.01">
                        <p class="text-gray-400 text-xs mt-1">Durasi jam yang termasuk dalam paket. Isi 0 jika fleksibel.</p>
                        @error('durasi_jam', 'storePaket')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="create_deskripsi" class="block mb-2 text-sm font-medium text-white dark:text-white">Deskripsi (Opsional)</label>
                        <textarea name="deskripsi" id="create_deskripsi" rows="4" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 @error('deskripsi', 'storePaket') border-red-500 @enderror" placeholder="Deskripsi paket">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi', 'storePaket')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <h4 class="font-semibold mt-4 mb-2 text-white">Service yang Termasuk dalam Paket:</h4>
                <div id="create_package_service_list" class="max-h-64 overflow-y-auto border border-gray-600 rounded p-2 mb-4">
                    @forelse($services as $service)
                        <div class="flex items-center justify-between py-1 border-b border-gray-600 last:border-b-0">
                            <span class="font-medium text-gray-300">{{ $service->nama }} (Rp {{ number_format($service->harga, 0, ',', '.') }}) <small>(Stok: {{ $service->stok }})</small></span>
                            <input type="number" name="services[{{ $service->id }}][jumlah]" data-service-id="{{ $service->id }}"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 w-20 p-1 text-center dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                                   value="{{ old('services.'.$service->id.'.jumlah', 0) }}" min="0" step="1"> {{-- Added step="1" --}}
                            <input type="hidden" name="services[{{ $service->id }}][id]" value="{{ $service->id }}">
                        </div>
                    @empty
                        <p class="text-gray-400 text-sm">Tidak ada service yang tersedia. Tambahkan service terlebih dahulu.</p>
                    @endforelse
                </div>

                <div class="mb-4">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="aktif" value="1" id="create_aktif" class="sr-only peer" {{ old('aktif', true) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-500 peer-checked:bg-blue-600"></div>
                        <span class="ms-3 text-sm font-medium text-white dark:text-gray-300">Aktif</span>
                    </label>
                    @error('aktif', 'storePaket')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                    <i class="fas fa-plus me-1 -ms-1"></i>
                    Tambah Paket
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Edit Paket Modal (Flowbite) --}}
<div id="edit-paket-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-2xl max-h-full">
        <!-- Modal content -->
        <div class="relative bg-gray-700 rounded-lg shadow dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-semibold text-white dark:text-white">
                    Edit Paket
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="edit-paket-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <form id="edit-paket-form" class="p-4 md:p-5" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="edit_nama_paket" class="block mb-2 text-sm font-medium text-white dark:text-white">Nama Paket</label>
                        <input type="text" name="nama_paket" id="edit_nama_paket" value="{{ old('nama_paket') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 @error('nama_paket', 'updatePaket') border-red-500 @enderror" placeholder="Contoh: Paket Hemat" required="">
                        @error('nama_paket', 'updatePaket')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="edit_harga_paket" class="block mb-2 text-sm font-medium text-white dark:text-white">Harga Paket</label>
                        <input type="number" name="harga_paket" id="edit_harga_paket" value="{{ old('harga_paket') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 @error('harga_paket', 'updatePaket') border-red-500 @enderror" placeholder="0" required="" min="0">
                        @error('harga_paket', 'updatePaket')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="edit_durasi_jam" class="block mb-2 text-sm font-medium text-white dark:text-white">Durasi (Jam)</label>
                        <input type="number" name="durasi_jam" id="edit_durasi_jam" value="{{ old('durasi_jam') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 @error('durasi_jam', 'updatePaket') border-red-500 @enderror" placeholder="0" required="" min="0" step="0.01">
                        <p class="text-gray-400 text-xs mt-1">Durasi jam yang termasuk dalam paket. Isi 0 jika fleksibel.</p>
                        @error('durasi_jam', 'updatePaket')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="edit_deskripsi" class="block mb-2 text-sm font-medium text-white dark:text-white">Deskripsi (Opsional)</label>
                        <textarea name="deskripsi" id="edit_deskripsi" rows="4" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 @error('deskripsi', 'updatePaket') border-red-500 @enderror" placeholder="Deskripsi paket">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi', 'updatePaket')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <h4 class="font-semibold mt-4 mb-2 text-white">Service yang Termasuk dalam Paket:</h4>
                <div id="edit_package_service_list" class="max-h-64 overflow-y-auto border border-gray-600 rounded p-2 mb-4">
                    @forelse($services as $service)
                        <div class="flex items-center justify-between py-1 border-b border-gray-600 last:border-b-0">
                            <span class="font-medium text-gray-300">{{ $service->nama }} (Rp {{ number_format($service->harga, 0, ',', '.') }}) <small>(Stok: {{ $service->stok }})</small></span>
                            <input type="number" name="services[{{ $service->id }}][jumlah]" data-service-id="{{ $service->id }}"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 w-20 p-1 text-center dark:bg-gray-600 dark:border-gray-500 dark:text-white edit-package-service-qty" value="0" min="0" step="1"> {{-- Added step="1" --}}
                            <input type="hidden" name="services[{{ $service->id }}][id]" value="{{ $service->id }}">
                        </div>
                    @empty
                        <p class="text-gray-400 text-sm">Tidak ada service yang tersedia. Tambahkan service terlebih dahulu.</p>
                    @endforelse
                </div>

                <div class="mb-4">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="aktif" value="1" id="edit_aktif" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-500 peer-checked:bg-blue-600"></div>
                        <span class="ms-3 text-sm font-medium text-white dark:text-gray-300">Aktif</span>
                    </label>
                    @error('aktif', 'updatePaket')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                    <i class="fas fa-save me-1 -ms-1"></i>
                    Perbarui Paket
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script> {{-- Added Flowbite JS CDN --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editButtons = document.querySelectorAll('.edit-button');
        const editPaketForm = document.getElementById('edit-paket-form');
        const editNamaPaketInput = document.getElementById('edit_nama_paket');
        const editHargaPaketInput = document.getElementById('edit_harga_paket');
        const editDurasiJamInput = document.getElementById('edit_durasi_jam');
        const editDeskripsiTextarea = document.getElementById('edit_deskripsi');
        const editAktifToggle = document.getElementById('edit_aktif');

        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const paketId = this.getAttribute('data-paket-id');
                console.log('Paket ID dari data-attribute:', paketId); // <<< TAMBAHKAN INI
                const paketNama = this.getAttribute('data-paket-nama');
                const paketHarga = this.getAttribute('data-paket-harga');
                const paketDurasi = this.getAttribute('data-paket-durasi');
                const paketDeskripsi = this.getAttribute('data-paket-deskripsi');
                const paketAktif = this.getAttribute('data-paket-aktif') === 'true';
                const paketServicesJson = this.getAttribute('data-paket-services');
                let paketServices = [];
                try {
                    paketServices = JSON.parse(paketServicesJson);
                } catch (e) {
                    console.error("Error parsing package services JSON:", e);
                }

                editPaketForm.action = `{{ url('admin/pakets') }}/${paketId}`;
                console.log('Form action disetel ke:', editPaketForm.action); // <<< TAMBAHKAN INI
                editNamaPaketInput.value = paketNama;
                editHargaPaketInput.value = paketHarga;
                editDurasiJamInput.value = paketDurasi;
                editDeskripsiTextarea.value = paketDeskripsi;
                editAktifToggle.checked = paketAktif;

                // Reset all service quantities in edit modal first
                document.querySelectorAll('#edit_package_service_list input[name^="services"]').forEach(input => { // Select inputs by name pattern
                    input.value = 0;
                });

                // Fill service quantities based on current package's services
                if (paketServices && Array.isArray(paketServices)) {
                    paketServices.forEach(serviceItem => {
                        const input = document.querySelector(`#edit_package_service_list input[name="services[${serviceItem.id}][jumlah]"]`); // Select by full name
                        if (input) {
                            input.value = serviceItem.jumlah;
                        }
                    });
                }
            });
        });

        // Auto-open modal if validation errors exist
        @if($errors->storePaket->any() || session('paket_id_on_error'))
            const { Modal } = Flowbite; // Pastikan Flowbite JS sudah dimuat

            @if($errors->storePaket->any())
                const createModalElement = document.getElementById('create-paket-modal');
                const createModal = new Modal(createModalElement, {});
                createModal.show();
                // Old input for create is automatically repopulated by Blade's old() helper
            @elseif(session('paket_id_on_error'))
                const editModalElement = document.getElementById('edit-paket-modal');
                const editModal = new Modal(editModalElement, {});
                editModal.show();

                // Re-populate the edit form with old input and correct action
                const paketIdOnError = "{{ session('paket_id_on_error') }}";
                editPaketForm.action = `{{ url('admin/pakets') }}/${paketIdOnError}`;
                
                // Get old input for each field, fallback to empty string if not found
                editNamaPaketInput.value = "{{ old('nama_paket', '') }}";
                editHargaPaketInput.value = "{{ old('harga_paket', '') }}";
                editDurasiJamInput.value = "{{ old('durasi_jam', '') }}";
                editDeskripsiTextarea.value = "{{ old('deskripsi', '') }}";
                editAktifToggle.checked = "{{ old('aktif') }}" === '1' || "{{ old('aktif') }}" === true; // Check if old('aktif') is '1' or boolean true

                // Repopulate service quantities from old input
                @if(old('services'))
                    document.querySelectorAll('#edit_package_service_list input[name^="services"]').forEach(input => {
                        input.value = 0; // Reset all first
                    });
                    @foreach(old('services') as $serviceId => $serviceData)
                        const input = document.querySelector(`#edit_package_service_list input[name="services[{{ $serviceId }}][jumlah]"]`);
                        if (input) {
                            input.value = "{{ (int)($serviceData['jumlah'] ?? 0) }}";
                        }
                    @endforeach
                @endif
            @endif
        @endif
    });
</script>
@endsection