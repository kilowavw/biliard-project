@extends('default')

@section('title', 'Manajemen Kupon')

@section('content')
<div class="container mx-auto p-4 md:p-6">
    <h1 class="text-3xl font-bold mb-6 text-white">Manajemen Kupon</h1>

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
        <button data-modal-target="create-kupon-modal" data-modal-toggle="create-kupon-modal" class="block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800" type="button">
            Tambah Kupon Baru
        </button>
    </div>

    <div class="bg-[#1e1e1e] p-6 rounded-lg shadow-md overflow-x-auto">
        <table id="kuponsTable" class="min-w-full leading-normal text-white">
            <thead>
                <tr class="bg-[#2a2a2a] text-gray-300 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">ID</th>
                    <th class="py-3 px-6 text-left">Kode Kupon</th>
                    <th class="py-3 px-6 text-left">Diskon (%)</th>
                    <th class="py-3 px-6 text-left">Status</th>
                    <th class="py-3 px-6 text-left">Kadaluarsa</th>
                    <th class="py-3 px-6 text-left">Dibuat Pada</th>
                    <th class="py-3 px-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-400 text-sm font-light">
                @forelse ($kupons as $kupon)
                    <tr class="border-b border-gray-700 hover:bg-[#232323]">
                        <td class="py-3 px-6 text-left whitespace-nowrap">{{ $kupon->id }}</td>
                        <td class="py-3 px-6 text-left">{{ $kupon->kode }}</td>
                        <td class="py-3 px-6 text-left">{{ $kupon->diskon_persen }}%</td>
                        <td class="py-3 px-6 text-left">
                            @if($kupon->aktif)
                                <span class="bg-green-600 text-white text-xs font-semibold px-2.5 py-0.5 rounded-full dark:bg-green-900 dark:text-green-300">Aktif</span>
                            @else
                                <span class="bg-red-600 text-white text-xs font-semibold px-2.5 py-0.5 rounded-full dark:bg-red-900 dark:text-red-300">Tidak Aktif</span>
                            @endif
                        </td>
                        <td class="py-3 px-6 text-left">{{ $kupon->kadaluarsa ? $kupon->kadaluarsa->format('d M Y') : 'Tidak Ada' }}</td>
                        <td class="py-3 px-6 text-left">{{ $kupon->created_at->format('d M Y H:i') }}</td>
                        <td class="py-3 px-6 text-center">
                            <div class="flex item-center justify-center space-x-2">
                                <button type="button"
                                    data-modal-target="edit-kupon-modal"
                                    data-modal-toggle="edit-kupon-modal"
                                    data-kupon-id="{{ $kupon->id }}"
                                    data-kupon-kode="{{ $kupon->kode }}"
                                    data-kupon-diskon="{{ $kupon->diskon_persen }}"
                                    data-kupon-aktif="{{ $kupon->aktif ? 'true' : 'false' }}"
                                    data-kupon-kadaluarsa="{{ $kupon->kadaluarsa ? $kupon->kadaluarsa->format('Y-m-d') : '' }}"
                                    class="w-8 h-8 rounded-full bg-yellow-500 hover:bg-yellow-600 flex items-center justify-center text-white edit-button"
                                    title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('admin.kupons.destroy', $kupon->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kupon ini?');">
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
                        <td colspan="7" class="py-4 px-6 text-center text-gray-500">Belum ada kupon yang terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            {{ $kupons->links('pagination::tailwind') }}
        </div>
    </div>
</div>

{{-- Create Kupon Modal (Flowbite) --}}
<div id="create-kupon-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-md max-h-full">
        <!-- Modal content -->
        <div class="relative bg-gray-700 rounded-lg shadow dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-semibold text-white dark:text-white">
                    Tambah Kupon Baru
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="create-kupon-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <form class="p-4 md:p-5" action="{{ route('admin.kupons.store') }}" method="POST">
                @csrf
                <div class="grid gap-4 mb-4 grid-cols-1">
                    <div>
                        <label for="create_kode" class="block mb-2 text-sm font-medium text-white dark:text-white">Kode Kupon</label>
                        <input type="text" name="kode" id="create_kode" value="{{ old('kode') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Contoh: DISKON10" required="">
                        @error('kode', 'storeKupon')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="create_diskon_persen" class="block mb-2 text-sm font-medium text-white dark:text-white">Diskon Persen (%)</label>
                        <input type="number" name="diskon_persen" id="create_diskon_persen" value="{{ old('diskon_persen') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="1-100" required="" min="1" max="100">
                        @error('diskon_persen', 'storeKupon')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="create_kadaluarsa" class="block mb-2 text-sm font-medium text-white dark:text-white">Tanggal Kadaluarsa (Opsional)</label>
                        <input type="date" name="kadaluarsa" id="create_kadaluarsa" value="{{ old('kadaluarsa') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        @error('kadaluarsa', 'storeKupon')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="relative inline-flex items-center cursor-pointer mb-2">
                            <input type="checkbox" name="aktif" value="1" id="create_aktif" class="sr-only peer" {{ old('aktif', true) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-500 peer-checked:bg-blue-600"></div>
                            <span class="ms-3 text-sm font-medium text-white dark:text-gray-300">Aktif</span>
                        </label>
                        @error('aktif', 'storeKupon')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <button type="submit" class="text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                    <i class="fas fa-plus me-1 -ms-1"></i>
                    Tambah Kupon
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Edit Kupon Modal (Flowbite) --}}
<div id="edit-kupon-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-md max-h-full">
        <!-- Modal content -->
        <div class="relative bg-gray-700 rounded-lg shadow dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-semibold text-white dark:text-white">
                    Edit Kupon
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="edit-kupon-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <form id="edit-kupon-form" class="p-4 md:p-5" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="grid gap-4 mb-4 grid-cols-1">
                    <div>
                        <label for="edit_kode" class="block mb-2 text-sm font-medium text-white dark:text-white">Kode Kupon</label>
                        <input type="text" name="kode" id="edit_kode" value="{{ old('kode') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Contoh: DISKON10" required="">
                        @error('kode', 'updateKupon')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="edit_diskon_persen" class="block mb-2 text-sm font-medium text-white dark:text-white">Diskon Persen (%)</label>
                        <input type="number" name="diskon_persen" id="edit_diskon_persen" value="{{ old('diskon_persen') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="1-100" required="" min="1" max="100">
                        @error('diskon_persen', 'updateKupon')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="edit_kadaluarsa" class="block mb-2 text-sm font-medium text-white dark:text-white">Tanggal Kadaluarsa (Opsional)</label>
                        <input type="date" name="kadaluarsa" id="edit_kadaluarsa" value="{{ old('kadaluarsa') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        @error('kadaluarsa', 'updateKupon')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="relative inline-flex items-center cursor-pointer mb-2">
                            <input type="checkbox" name="aktif" value="1" id="edit_aktif" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-500 peer-checked:bg-blue-600"></div>
                            <span class="ms-3 text-sm font-medium text-white dark:text-gray-300">Aktif</span>
                        </label>
                        @error('aktif', 'updateKupon')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <button type="submit" class="text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                    <i class="fas fa-save me-1 -ms-1"></i>
                    Perbarui Kupon
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editButtons = document.querySelectorAll('.edit-button');
        const editKuponForm = document.getElementById('edit-kupon-form');
        const editKodeInput = document.getElementById('edit_kode');
        const editDiskonPersenInput = document.getElementById('edit_diskon_persen');
        const editAktifToggle = document.getElementById('edit_aktif');
        const editKadaluarsaInput = document.getElementById('edit_kadaluarsa');

        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const kuponId = this.getAttribute('data-kupon-id');
                const kuponKode = this.getAttribute('data-kupon-kode');
                const kuponDiskon = this.getAttribute('data-kupon-diskon');
                const kuponAktif = this.getAttribute('data-kupon-aktif') === 'true'; // Convert to boolean
                const kuponKadaluarsa = this.getAttribute('data-kupon-kadaluarsa'); // YYYY-MM-DD

                editKuponForm.action = `{{ url('admin/kupons') }}/${kuponId}`;
                editKodeInput.value = kuponKode;
                editDiskonPersenInput.value = kuponDiskon;
                editAktifToggle.checked = kuponAktif;
                editKadaluarsaInput.value = kuponKadaluarsa;
            });
        });

        // Auto-open modal if validation errors exist
        @if($errors->storeKupon->any() || session('kupon_id_on_error'))
            const { Modal } = Flowbite;

            @if($errors->storeKupon->any())
                const createModalElement = document.getElementById('create-kupon-modal');
                const createModal = new Modal(createModalElement, {});
                createModal.show();
                // Old input for create is automatically repopulated
            @elseif(session('kupon_id_on_error'))
                const editModalElement = document.getElementById('edit-kupon-modal');
                const editModal = new Modal(editModalElement, {});
                editModal.show();

                // Re-populate the edit form with old input and correct action
                const kuponIdOnError = "{{ session('kupon_id_on_error') }}";
                editKuponForm.action = `{{ url('admin/kupons') }}/${kuponIdOnError}`;
                
                // Get old input for each field, fallback to empty string if not found
                editKodeInput.value = "{{ old('kode') }}" || '';
                editDiskonPersenInput.value = "{{ old('diskon_persen') }}" || '';
                // For toggle, check if old('aktif') is '1' or true
                editAktifToggle.checked = "{{ old('aktif') }}" === '1' || "{{ old('aktif') }}" === true;
                editKadaluarsaInput.value = "{{ old('kadaluarsa') }}" || '';
            @endif
        @endif
    });
</script>
@endsection