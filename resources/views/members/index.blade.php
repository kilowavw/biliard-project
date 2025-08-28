@extends('default')

@section('title', 'Manajemen Member')

@section('content')
<div class="container mx-auto p-4 md:p-6">
    <h1 class="text-3xl font-bold mb-6 text-white">Manajemen Member</h1>

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

    {{-- Error messages for Create Member Modal (if any) --}}
    @if ($errors->createMember->any())
        <div id="alert-create-error" class="flex items-center p-4 mb-4 text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
            <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
            </svg>
            <span class="sr-only">Error</span>
            <div class="ms-3 text-sm font-medium">
                Gagal menambahkan member. Harap periksa input Anda.
                <ul class="mt-1.5 list-disc list-inside">
                    @foreach ($errors->createMember->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-red-50 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-gray-700" data-dismiss-target="#alert-create-error" aria-label="Close">
                <span class="sr-only">Close</span>
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- Error messages for Edit Member Modal (if any) --}}
    @if ($errors->editMember->any())
        <div id="alert-edit-error" class="flex items-center p-4 mb-4 text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
            <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
            </svg>
            <span class="sr-only">Error</span>
            <div class="ms-3 text-sm font-medium">
                Gagal memperbarui member. Harap periksa input Anda.
                <ul class="mt-1.5 list-disc list-inside">
                    @foreach ($errors->editMember->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-red-50 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-gray-700" data-dismiss-target="#alert-edit-error" aria-label="Close">
                <span class="sr-only">Close</span>
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
            </button>
        </div>
    @endif

    <div class="flex flex-col md:flex-row justify-between items-center mb-4 space-y-3 md:space-y-0 md:space-x-4">
        <button type="button" data-modal-target="create-member-modal" data-modal-toggle="create-member-modal" class="inline-flex items-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
            <svg class="w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 5a1 1 0 0 1 1 1v3h3a1 1 0 1 1 0 2h-3v3a1 1 0 1 1-2 0v-3H6a1 1 0 1 1 0-2h3V6a1 1 0 0 1 1-1Z"/>
            </svg>
            Tambah Member Baru
        </button>
        <div class="relative w-full md:w-auto">
            <form method="GET" action="{{ route('members.index') }}" class="flex flex-col md:flex-row items-center space-y-2 md:space-y-0 md:space-x-2">
                <div class="relative w-full md:w-64">
                    <div class="absolute inset-y-0 left-0 rtl:inset-r-0 rtl:right-0 flex items-center ps-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                        </svg>
                    </div>
                    <input type="text" id="memberSearchInput" name="search" value="{{ $search }}" class="block p-2.5 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg w-full bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Cari member...">
                </div>
                <select name="status" onchange="this.form.submit()" class="block w-full md:w-auto p-2.5 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="Aktif" {{ $status == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="Nonaktif" {{ $status == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    <option value="Expired" {{ $status == 'Expired' ? 'selected' : '' }}>Expired</option>
                </select>
                <button type="submit" class="p-2.5 text-sm font-medium text-white bg-blue-700 rounded-lg border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 w-full md:w-auto">
                <i class="fa-solid fa-magnifying-glass"></i>
                    <span class="sr-only md:not-sr-only md:ms-2"></span>
                </button>
            </form>
        </div>
    </div>

    <div class="bg-[#1e1e1e] p-6 rounded-lg shadow-md overflow-x-auto">
        <table id="membersTable" class="min-w-full leading-normal text-white">
            <thead>
                <tr class="bg-[#2a2a2a] text-gray-300 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">Nama Member</th>
                    <th class="py-3 px-6 text-left">Kode Member</th>
                    <th class="py-3 px-6 text-left">Email</th>
                    <th class="py-3 px-6 text-left">No. Telepon</th>
                    <th class="py-3 px-6 text-left">Diskon (%)</th>
                    <th class="py-3 px-6 text-left">Tanggal Daftar</th>
                    <th class="py-3 px-6 text-left">Kadaluarsa</th>
                    <th class="py-3 px-6 text-left">Status</th>
                    <th class="py-3 px-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-400 text-sm font-light">
                @forelse($members as $member)
                    <tr class="border-b border-gray-700 hover:bg-[#232323] member-row" id="member-{{ $member->id }}">
                        <td class="px-6 py-4 font-medium text-white whitespace-nowrap">{{ $member->nama_member }}</td>
                        <td class="px-6 py-4">{{ $member->kode_member }}</td>
                        <td class="px-6 py-4">{{ $member->email ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $member->no_telepon ?? '-' }}</td>
                        <td class="px-6 py-4">{{ number_format($member->diskon_persen, 0) }}%</td>
                        <td class="px-6 py-4">{{ $member->tanggal_daftar->format('d M Y') }}</td>
                        <td class="px-6 py-4">{{ $member->tanggal_kadaluarsa->format('d M Y') }}</td>
                        <td class="px-6 py-4">
                            <span class="relative inline-block px-3 py-1 font-semibold leading-tight">
                                <span aria-hidden class="absolute inset-0 opacity-50 rounded-full
                                    @if($member->status_keanggotaan == 'aktif') bg-green-600
                                    @elseif($member->status_keanggotaan == 'expired') bg-yellow-600
                                    @else bg-red-600 @endif"></span>
                                <span class="relative">{{ $member->status_keanggotaan }}</span>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex item-center justify-center space-x-2">
                                {{-- Edit button opens modal --}}
                                <button type="button" data-modal-target="edit-member-modal" data-modal-toggle="edit-member-modal"
                                        class="w-8 h-8 rounded-full bg-yellow-500 hover:bg-yellow-600 flex items-center justify-center text-white edit-button"
                                        title="Edit"
                                        data-member-id="{{ $member->id }}"
                                        data-member-nama="{{ $member->nama_member }}"
                                        data-member-kode="{{ $member->kode_member }}"
                                        data-member-email="{{ $member->email ?? '' }}"
                                        data-member-telepon="{{ $member->no_telepon ?? '' }}"
                                        data-member-daftar="{{ $member->tanggal_daftar->format('Y-m-d') }}"
                                        data-member-kadaluarsa="{{ $member->tanggal_kadaluarsa->format('Y-m-d') }}"
                                        data-member-status="{{ $member->status_keanggotaan }}"
                                        data-member-diskon="{{ $member->diskon_persen }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                {{-- Delete button opens delete confirmation modal --}}
                                <button type="button" data-modal-target="delete-member-modal-{{ $member->id }}" data-modal-toggle="delete-member-modal-{{ $member->id }}"
                                        class="w-8 h-8 rounded-full bg-red-600 hover:bg-red-700 flex items-center justify-center text-white" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>

                                {{-- Delete Confirmation Modal for each member (Flowbite) --}}
                                <div id="delete-member-modal-{{ $member->id }}" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                                    <div class="relative p-4 w-full max-w-md max-h-full">
                                        <div class="relative bg-gray-700 rounded-lg shadow dark:bg-gray-700">
                                            <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="delete-member-modal-{{ $member->id }}">
                                                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                                                </svg>
                                                <span class="sr-only">Close modal</span>
                                            </button>
                                            <div class="p-4 md:p-5 text-center">
                                                <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                                </svg>
                                                <h3 class="mb-5 text-lg font-normal text-gray-400 dark:text-gray-400">Anda yakin ingin menghapus member {{ $member->nama_member }} ({{ $member->kode_member }}) ini?</h3>
                                                <form action="{{ route('members.destroy', $member->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
                                                        Ya, hapus
                                                    </button>
                                                    <button data-modal-hide="delete-member-modal-{{ $member->id }}" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Tidak, batal</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-gray-500">Tidak ada data member.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination Links --}}
    <div class="mt-4">
        {{ $members->appends(request()->query())->links('pagination::tailwind') }}
    </div>

</div>

{{-- Create Member Modal (Flowbite) --}}
<div id="create-member-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-md max-h-full">
        <div class="relative bg-gray-700 rounded-lg shadow dark:bg-gray-700">
            <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="create-member-modal">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
            <div class="px-6 py-6 lg:px-8">
                <h3 class="mb-4 text-xl font-medium text-white dark:text-white">Tambah Member Baru</h3>
                <form class="space-y-6" action="{{ route('members.store') }}" method="POST">
                    @csrf
                    <div class="mb-5">
                        <label for="create_nama_member" class="block mb-2 text-sm font-medium text-gray-300">Nama Member <span class="text-red-500">*</span></label>
                        <input type="text" id="create_nama_member" name="nama_member" value="{{ old('nama_member') }}"
                               class="bg-gray-50 border border-gray-600 text-black text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 @error('nama_member', 'createMember') border-red-500 @enderror"
                               placeholder="Nama Lengkap Member" required>
                        @error('nama_member', 'createMember')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-5">
                        <label for="create_kode_member" class="block mb-2 text-sm font-medium text-gray-300">Kode Member <span class="text-red-500">*</span></label>
                        <input type="text" id="create_kode_member" name="kode_member" value="{{ old('kode_member', $defaultCreateKodeMember) }}"
                               class="bg-gray-50 border border-gray-600 text-black text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 @error('kode_member', 'createMember') border-red-500 @enderror"
                               placeholder="Kode Unik Member" required>
                        @error('kode_member', 'createMember')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-5">
                        <label for="create_email" class="block mb-2 text-sm font-medium text-gray-300">Email</label>
                        <input type="email" id="create_email" name="email" value="{{ old('email') }}"
                               class="bg-gray-50 border border-gray-600 text-black  text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 @error('email', 'createMember') border-red-500 @enderror"
                               placeholder="nama@contoh.com">
                        @error('email', 'createMember')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-5">
                        <label for="create_no_telepon" class="block mb-2 text-sm font-medium text-gray-300">No. Telepon</label>
                        <input type="text" id="create_no_telepon" name="no_telepon" value="{{ old('no_telepon') }}"
                               class="bg-gray-50 border border-gray-600 text-black  text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 @error('no_telepon', 'createMember') border-red-500 @enderror"
                               placeholder="08123456789">
                        @error('no_telepon', 'createMember')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-5">
                        <label for="create_tanggal_daftar" class="block mb-2 text-sm font-medium text-gray-300">Tanggal Daftar <span class="text-red-500">*</span></label>
                        <input type="date" id="create_tanggal_daftar" name="tanggal_daftar" value="{{ old('tanggal_daftar', $defaultCreateTanggalDaftar) }}"
                               class="bg-gray-50 border border-gray-600 text-black  text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 @error('tanggal_daftar', 'createMember') border-red-500 @enderror"
                               required>
                        @error('tanggal_daftar', 'createMember')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-5">
                        <label for="create_tanggal_kadaluarsa" class="block mb-2 text-sm font-medium text-gray-300">Tanggal Kadaluarsa <span class="text-red-500">*</span></label>
                        <input type="date" id="create_tanggal_kadaluarsa" name="tanggal_kadaluarsa" value="{{ old('tanggal_kadaluarsa', $defaultCreateTanggalKadaluarsa) }}"
                               class="bg-gray-50 border border-gray-600 text-black  text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 @error('tanggal_kadaluarsa', 'createMember') border-red-500 @enderror"
                               required>
                        @error('tanggal_kadaluarsa', 'createMember')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-5">
                        <label for="create_status_keanggotaan" class="block mb-2 text-sm font-medium text-gray-300">Status Keanggotaan <span class="text-red-500">*</span></label>
                        <select id="create_status_keanggotaan" name="status_keanggotaan"
                                class="bg-gray-50 border border-gray-600 text-black  text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 @error('status_keanggotaan', 'createMember') border-red-500 @enderror" required>
                            <option value="Aktif" {{ old('status_keanggotaan', 'Aktif') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="Nonaktif" {{ old('status_keanggotaan') == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                            <option value="Expired" {{ old('status_keanggotaan') == 'Expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                        @error('status_keanggotaan', 'createMember')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-5">
                        <label for="create_diskon_persen" class="block mb-2 text-sm font-medium text-gray-300">Diskon Persen (%) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" id="create_diskon_persen" name="diskon_persen" value="{{ old('diskon_persen', 0) }}"
                               class="bg-gray-50 border border-gray-600 text-black  text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 @error('diskon_persen', 'createMember') border-red-500 @enderror"
                               required min="0" max="100">
                        @error('diskon_persen', 'createMember')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Tambah Member</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Edit Member Modal (Flowbite) --}}
<div id="edit-member-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-md max-h-full">
        <div class="relative bg-gray-700 rounded-lg shadow dark:bg-gray-700">
            <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="edit-member-modal">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
            <div class="px-6 py-6 lg:px-8">
                <h3 class="mb-4 text-xl font-medium text-white dark:text-white">Edit Member</h3>
                <form class="space-y-6" id="edit-member-form" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-5">
                        <label for="edit_nama_member" class="block mb-2 text-sm font-medium text-gray-300">Nama Member <span class="text-red-500">*</span></label>
                        <input type="text" id="edit_nama_member" name="nama_member"
                               class="bg-gray-50 border border-gray-600 text-black text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 @error('nama_member', 'editMember') border-red-500 @enderror"
                               placeholder="Nama Lengkap Member" required>
                        @error('nama_member', 'editMember')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-5">
                        <label for="edit_kode_member" class="block mb-2 text-sm font-medium text-gray-300">Kode Member <span class="text-red-500">*</span></label>
                        <input type="text" id="edit_kode_member" name="kode_member"
                               class="bg-gray-50 border border-gray-600 text-black text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 @error('kode_member', 'editMember') border-red-500 @enderror"
                               placeholder="Kode Unik Member" required>
                        @error('kode_member', 'editMember')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-5">
                        <label for="edit_email" class="block mb-2 text-sm font-medium text-gray-300">Email</label>
                        <input type="email" id="edit_email" name="email"
                               class="bg-gray-50 border border-gray-600 text-black text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 @error('email', 'editMember') border-red-500 @enderror"
                               placeholder="nama@contoh.com">
                        @error('email', 'editMember')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-5">
                        <label for="edit_no_telepon" class="block mb-2 text-sm font-medium text-gray-300">No. Telepon</label>
                        <input type="text" id="edit_no_telepon" name="no_telepon"
                               class="bg-gray-50 border border-gray-600 text-black text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 @error('no_telepon', 'editMember') border-red-500 @enderror"
                               placeholder="08123456789">
                        @error('no_telepon', 'editMember')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-5">
                        <label for="edit_tanggal_daftar" class="block mb-2 text-sm font-medium text-gray-300">Tanggal Daftar <span class="text-red-500">*</span></label>
                        <input type="date" id="edit_tanggal_daftar" name="tanggal_daftar"
                               class="bg-gray-50 border border-gray-600 text-black text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 @error('tanggal_daftar', 'editMember') border-red-500 @enderror"
                               required>
                        @error('tanggal_daftar', 'editMember')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-5">
                        <label for="edit_tanggal_kadaluarsa" class="block mb-2 text-sm font-medium text-gray-300">Tanggal Kadaluarsa <span class="text-red-500">*</span></label>
                        <input type="date" id="edit_tanggal_kadaluarsa" name="tanggal_kadaluarsa"
                               class="bg-gray-50 border border-gray-600 text-black text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 @error('tanggal_kadaluarsa', 'editMember') border-red-500 @enderror"
                               required>
                        @error('tanggal_kadaluarsa', 'editMember')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-5">
                        <label for="edit_status_keanggotaan" class="block mb-2 text-sm font-medium text-gray-300">Status Keanggotaan <span class="text-red-500">*</span></label>
                        <select id="edit_status_keanggotaan" name="status_keanggotaan"
                                class="bg-gray-50 border border-gray-600 text-black text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 @error('status_keanggotaan', 'editMember') border-red-500 @enderror" required>
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                            <option value="Expired">Expired</option>
                        </select>
                        @error('status_keanggotaan', 'editMember')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-5">
                        <label for="edit_diskon_persen" class="block mb-2 text-sm font-medium text-gray-300">Diskon Persen (%) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" id="edit_diskon_persen" name="diskon_persen"
                               class="bg-gray-50 border border-gray-600 text-black text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 @error('diskon_persen', 'editMember') border-red-500 @enderror"
                               required min="0" max="100">
                        @error('diskon_persen', 'editMember')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Update Member</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mengisi data ke modal Edit Member
        const editButtons = document.querySelectorAll('.edit-button');
        const editMemberForm = document.getElementById('edit-member-form');
        const editNamaMemberInput = document.getElementById('edit_nama_member');
        const editKodeMemberInput = document.getElementById('edit_kode_member');
        const editEmailInput = document.getElementById('edit_email');
        const editNoTeleponInput = document.getElementById('edit_no_telepon');
        const editTanggalDaftarInput = document.getElementById('edit_tanggal_daftar');
        const editTanggalKadaluarsaInput = document.getElementById('edit_tanggal_kadaluarsa');
        const editStatusKeanggotaanSelect = document.getElementById('edit_status_keanggotaan');
        const editDiskonPersenInput = document.getElementById('edit_diskon_persen');

        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const memberId = this.getAttribute('data-member-id');
                const memberNama = this.getAttribute('data-member-nama');
                const memberKode = this.getAttribute('data-member-kode');
                const memberEmail = this.getAttribute('data-member-email');
                const memberTelepon = this.getAttribute('data-member-telepon');
                const memberDaftar = this.getAttribute('data-member-daftar'); // YYYY-MM-DD
                const memberKadaluarsa = this.getAttribute('data-member-kadaluarsa'); // YYYY-MM-DD
                const memberStatus = this.getAttribute('data-member-status');
                const memberDiskon = this.getAttribute('data-member-diskon');

                editMemberForm.action = `{{ url('members') }}/${memberId}`;
                editNamaMemberInput.value = memberNama;
                editKodeMemberInput.value = memberKode;
                editEmailInput.value = memberEmail;
                editNoTeleponInput.value = memberTelepon;
                editTanggalDaftarInput.value = memberDaftar;
                editTanggalKadaluarsaInput.value = memberKadaluarsa;
                editStatusKeanggotaanSelect.value = memberStatus;
                editDiskonPersenInput.value = memberDiskon;
            });
        });

        // Auto-open modal if validation errors exist
        @if($errors->createMember->any() || session('showEditModal'))
            const { Modal } = Flowbite;

            @if($errors->createMember->any())
                const createModalElement = document.getElementById('create-member-modal');
                const createModal = new Modal(createModalElement, {});
                createModal.show();
                // Old input for create is automatically repopulated
            @elseif(session('showEditModal'))
                const editModalElement = document.getElementById('edit-member-modal');
                const editModal = new Modal(editModalElement, {});
                editModal.show();

                // Re-populate the edit form with old input and correct action
                const memberIdOnError = "{{ session('showEditModal') }}";
                editMemberForm.action = `{{ url('members') }}/${memberIdOnError}`;
                
                editNamaMemberInput.value = "{{ old('nama_member') }}" || editNamaMemberInput.value;
                editKodeMemberInput.value = "{{ old('kode_member') }}" || editKodeMemberInput.value;
                editEmailInput.value = "{{ old('email') }}" || editEmailInput.value;
                editNoTeleponInput.value = "{{ old('no_telepon') }}" || editNoTeleponInput.value;
                editTanggalDaftarInput.value = "{{ old('tanggal_daftar') }}" || editTanggalDaftarInput.value;
                editTanggalKadaluarsaInput.value = "{{ old('tanggal_kadaluarsa') }}" || editTanggalKadaluarsaInput.value;
                editStatusKeanggotaanSelect.value = "{{ old('status_keanggotaan') }}" || editStatusKeanggotaanSelect.value;
                editDiskonPersenInput.value = "{{ old('diskon_persen') }}" || editDiskonPersenInput.value;
            @endif
        @endif

        // Search functionality
        $('#memberSearchInput').on('input', function() {
            const keyword = $(this).val().toLowerCase();
            const selectedStatus = $('select[name="status"]').val(); // Ambil status yang dipilih

            $('.member-row').each(function() {
                const row = $(this);
                const rowText = row.text().toLowerCase();
                const rowStatus = row.find('span.relative').text().trim(); // Ambil teks status dari elemen span

                const matchesKeyword = rowText.includes(keyword);
                const matchesStatus = (selectedStatus === 'all' || rowStatus === selectedStatus);

                if (matchesKeyword && matchesStatus) {
                    row.show();
                } else {
                    row.hide();
                }
            });

            // Sembunyikan pagination saat mencari
            const paginationContainer = $('.pagination');
            if (keyword.length > 0 || selectedStatus !== 'all') { // Sembunyikan jika ada keyword atau filter status
                paginationContainer.hide();
            } else {
                paginationContainer.show();
            }
        });

        // Trigger search on status change (already handled by form.submit() in HTML)
        // No additional JS needed here for status change if form.submit() is used.
    });
</script>
@endsection