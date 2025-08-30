@extends('default')

@section('title', 'Daftar Meja')

@section('content')
<div class="container mx-auto p-4 md:p-6">
    <h1 class="text-3xl font-bold mb-6 text-white">Daftar Meja</h1>

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
        <button data-modal-target="create-meja-modal" data-modal-toggle="create-meja-modal" class="block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800" type="button">
            Tambah Meja Baru
        </button>
    </div>

    <div class="bg-[#1e1e1e] p-6 rounded-lg shadow-md overflow-x-auto">
        <table id="mejasTable" class="min-w-full leading-normal text-white">
            <thead>
                <tr class="bg-[#2a2a2a] text-gray-300 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">ID</th>
                    <th class="py-3 px-6 text-left">Nama Meja</th>
                    <th class="py-3 px-6 text-left">Status</th>
                    <th class="py-3 px-6 text-left">Dibuat Pada</th>
                    <th class="py-3 px-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-400 text-sm font-light">
                @forelse ($mejas as $meja)
                    <tr class="border-b border-gray-700 hover:bg-[#232323]">
                        <td class="py-3 px-6 text-left whitespace-nowrap">{{ $meja->id }}</td>
                        <td class="py-3 px-6 text-left">{{ $meja->nama_meja }}</td>
                        <td class="py-3 px-6 text-left">
                            <span class="relative inline-block px-3 py-1 font-semibold leading-tight">
                                <span aria-hidden class="absolute inset-0 opacity-50 rounded-full
                                   {{ $meja->status == 'kosong' ? 'bg-green-600' : ($meja->status == 'waktu_habis' ? 'bg-yellow-500' : 'bg-red-600') }}"></span>
                                <span class="relative">{{ ucfirst($meja->status) }}</span>
                            </span>
                        </td>
                        <td class="py-3 px-6 text-left">{{ $meja->created_at->format('d M Y H:i') }}</td>
                        <td class="py-3 px-6 text-center">
                            <div class="flex item-center justify-center space-x-2">
                                <button type="button"
                                    data-modal-target="edit-meja-modal"
                                    data-modal-toggle="edit-meja-modal"
                                    data-meja-id="{{ $meja->id }}"
                                    data-meja-name="{{ $meja->nama_meja }}"
                                    class="w-8 h-8 rounded-full bg-yellow-500 hover:bg-yellow-600 flex items-center justify-center text-white edit-button"
                                    title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('admin.mejas.destroy', $meja->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus meja ini?');">
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
                        <td colspan="5" class="py-4 px-6 text-center text-gray-500">Belum ada meja yang terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            {{ $mejas->links('pagination::tailwind') }} {{-- Menggunakan Tailwind pagination --}}
        </div>
    </div>
</div>

{{-- Create Meja Modal (Flowbite) --}}
<div id="create-meja-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-md max-h-full">
        <!-- Modal content -->
        <div class="relative bg-gray-700 rounded-lg shadow dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-semibold text-white dark:text-white">
                    Tambah Meja Baru
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="create-meja-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <form class="p-4 md:p-5" action="{{ route('admin.mejas.store') }}" method="POST">
                @csrf
                <div class="grid gap-4 mb-4 grid-cols-1">
                    <div>
                        <label for="create_nama_meja" class="block mb-2 text-sm font-medium text-white dark:text-white">Nama Meja</label>
                        <input type="text" name="nama_meja" id="create_nama_meja" value="{{ old('nama_meja') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Misal: Meja 1" required="">
                        {{-- Error feedback for create modal --}}
                        @error('nama_meja', 'storeMeja') {{-- Target error bag 'storeMeja' --}}
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <button type="submit" class="text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                    <svg class="me-1 -ms-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
                    Tambah Meja
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Edit Meja Modal (Flowbite) --}}
<div id="edit-meja-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-md max-h-full">
        <!-- Modal content -->
        <div class="relative bg-gray-700 rounded-lg shadow dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-semibold text-white dark:text-white">
                    Edit Meja
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="edit-meja-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <form id="edit-meja-form" class="p-4 md:p-5" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="grid gap-4 mb-4 grid-cols-1">
                    <div>
                        <label for="edit_nama_meja" class="block mb-2 text-sm font-medium text-white dark:text-white">Nama Meja</label>
                        <input type="text" name="nama_meja" id="edit_nama_meja" value="{{ old('nama_meja') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Misal: Meja 1" required="">
                        {{-- Error feedback for edit modal --}}
                        @error('nama_meja', 'updateMeja') {{-- Target error bag 'updateMeja' --}}
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <button type="submit" class="text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                    <i class="fas fa-save me-1 -ms-1"></i>
                    Perbarui Meja
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    // JavaScript untuk mengisi form edit modal
    document.addEventListener('DOMContentLoaded', function() {
        const editButtons = document.querySelectorAll('.edit-button');
        const editMejaForm = document.getElementById('edit-meja-form');
        const editNamaMejaInput = document.getElementById('edit_nama_meja');

        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const mejaId = this.getAttribute('data-meja-id');
                const mejaName = this.getAttribute('data-meja-name');

                editMejaForm.action = `{{ url('admin/mejas') }}/${mejaId}`;
                editNamaMejaInput.value = mejaName;
            });
        });

        // Auto-open modal if validation errors exist
        @if($errors->storeMeja->any() || session('meja_id_on_error')) // Check specific error bags
            const { Modal } = Flowbite; // Destructure Modal from Flowbite global object

            @if($errors->storeMeja->any())
                const createModalElement = document.getElementById('create-meja-modal');
                const createModal = new Modal(createModalElement, {});
                createModal.show();
                // Repopulate with old input is automatic for create
            @elseif(session('meja_id_on_error'))
                const editModalElement = document.getElementById('edit-meja-modal');
                const editModal = new Modal(editModalElement, {});
                editModal.show();

                // Re-populate the edit form with old input and correct action
                const mejaIdOnError = "{{ session('meja_id_on_error') }}";
                const namaMejaOld = "{{ old('nama_meja') }}";
                
                editMejaForm.action = `{{ url('admin/mejas') }}/${mejaIdOnError}`;
                editNamaMejaInput.value = namaMejaOld;
            @endif
        @endif
    });
</script>
@endsection