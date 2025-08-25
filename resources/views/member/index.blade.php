@extends('default')

@section('title', 'Dashboard Member')

@section('content')
<div class="container mx-auto p-6">

    <h2 class="text-3xl font-bold mb-6 text-white">Data Member</h2>

    {{-- Alert Success --}}
    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    {{-- Tombol Tambah --}}
    <button data-modal-target="createModal" data-modal-toggle="createModal"
        class="mb-4 px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">
        + Tambah Member
    </button>

    {{-- Tabel --}}
    <div class="bg-[#1e1e1e] p-6 rounded-lg shadow-md overflow-x-auto">
        <table id="mejasTable" class="min-w-full leading-normal text-white">
            <thead>
                <tr class="bg-[#2a2a2a] text-gray-300 uppercase text-sm leading-normal">
                    <th scope="col" class="px-6 py-3">ID Member</th>
                    <th scope="col" class="px-6 py-3">Nama</th>
                    <th scope="col" class="px-6 py-3">No Telp</th>
                    <th scope="col" class="px-6 py-3">Tgl Bergabung</th>
                    <th scope="col" class="px-6 py-3">Status</th>
                    <th scope="col" class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-400 text-sm font-light">
                @forelse($members as $m)
                    <tr class="bg-[#1e1e1e] border-b border-gray-700">
                        <td class="px-6 py-4">{{ $m->id_member }}</td>
                        <td class="px-6 py-4">{{ $m->nama_member }}</td>
                        <td class="px-6 py-4">{{ $m->no_telp }}</td>
                        <td class="px-6 py-4">{{ $m->tgl_bergabung }}</td>
                         <td class="py-3 px-6 text-left">
                            @if($m->aktif)
                                <span class="bg-green-600 text-white text-xs font-semibold px-2.5 py-0.5 rounded-full dark:bg-green-900 dark:text-green-300">
                                    Aktif
                                </span>
                            @else
                                <span class="bg-red-600 text-white text-xs font-semibold px-2.5 py-0.5 rounded-full dark:bg-red-900 dark:text-red-300">
                                    Tidak Aktif
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 flex gap-2">
                            {{-- Tombol Edit --}}
                            <button data-modal-target="editModal{{ $m->id_member }}"
                                    data-modal-toggle="editModal{{ $m->id_member }}"
                                    class="px-3 py-1 text-sm bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">
                                Edit
                            </button>

                            {{-- Tombol Hapus --}}
                            <form action="{{ route('members.destroy', $m->id_member) }}" method="POST"
                                  onsubmit="return confirm('Yakin hapus?')">
                                @csrf
                                @method('DELETE')
                                <button class="px-3 py-1 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>

                    {{-- Modal Edit --}}
                    <div id="editModal{{ $m->id_member }}" tabindex="-1" aria-hidden="true"
                         class="hidden fixed top-0 right-0 left-0 z-50 w-full p-4 overflow-x-hidden overflow-y-auto h-[calc(100%-1rem)] max-h-full flex justify-center items-center">
                        <div class="relative w-full max-w-md max-h-full">
                            <div class="relative bg-gray-800 rounded-lg shadow">
                                <div class="flex items-center justify-between p-4 border-b border-gray-600">
                                    <h3 class="text-lg font-semibold text-white">Edit Member</h3>
                                    <button type="button" class="text-gray-400 hover:text-white"
                                            data-modal-hide="editModal{{ $m->id_member }}">
                                        ✕
                                    </button>
                                </div>
                                <form action="{{ route('members.update', $m->id_member) }}" method="POST" class="p-6">
                                    @csrf
                                    @method('PUT')
                                    <div class="mb-4">
                                        <label class="block mb-1 text-sm font-medium text-gray-300">Nama</label>
                                        <input type="text" name="nama_member" value="{{ $m->nama_member }}"
                                               class="w-full px-3 py-2 bg-gray-700 text-white rounded-lg" required>
                                    </div>
                                    <div class="mb-4">
                                        <label class="block mb-1 text-sm font-medium text-gray-300">No Telp</label>
                                        <input type="text" name="no_telp" value="{{ $m->no_telp }}"
                                               class="w-full px-3 py-2 bg-gray-700 text-white rounded-lg" required>
                                    </div>
                                    <div class="mb-4">
                                        <label class="block mb-1 text-sm font-medium text-gray-300">Tgl Bergabung</label>
                                        <input type="date" name="tgl_bergabung" value="{{ $m->tgl_bergabung }}"
                                               class="w-full px-3 py-2 bg-gray-700 text-white rounded-lg" required>
                                    </div>
                                    <div class="flex justify-end gap-2">
                                        <button type="button" data-modal-hide="editModal{{ $m->id_member }}"
                                                class="px-4 py-2 bg-gray-600 rounded-lg text-white">Batal</button>
                                        <button type="submit"
                                                class="px-4 py-2 bg-green-600 rounded-lg text-white hover:bg-green-700">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                @empty
                    <tr>
                        <td colspan="5" class="text-center text-gray-400 py-4">Belum ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $members->links() }}
    </div>
</div>

{{-- Modal Create --}}
<div id="createModal" tabindex="-1" aria-hidden="true"
     class="hidden fixed top-0 right-0 left-0 z-50 w-full p-4 overflow-x-hidden overflow-y-auto h-[calc(100%-1rem)] max-h-full flex justify-center items-center">
    <div class="relative w-full max-w-md max-h-full">
        <div class="relative bg-gray-800 rounded-lg shadow">
            <div class="flex items-center justify-between p-4 border-b border-gray-600">
                <h3 class="text-lg font-semibold text-white">Tambah Member</h3>
                <button type="button" class="text-gray-400 hover:text-white" data-modal-hide="createModal">✕</button>
            </div>
            <form action="{{ route('members.store') }}" method="POST" class="p-6">
                @csrf
                <div class="mb-4">
                    <label class="block mb-1 text-sm font-medium text-gray-300">Nama</label>
                    <input type="text" name="nama_member" class="w-full px-3 py-2 bg-gray-700 text-white rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label class="block mb-1 text-sm font-medium text-gray-300">No Telp</label>
                    <input type="text" name="no_telp" class="w-full px-3 py-2 bg-gray-700 text-white rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label class="block mb-1 text-sm font-medium text-gray-300">Tgl Bergabung</label>
                    <input type="date" name="tgl_bergabung" class="w-full px-3 py-2 bg-gray-700 text-white rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="aktif" value="1" id="create_aktif" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-800 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        <span class="ms-3 text-sm font-medium text-white">Aktif</span>
                    </label>
                    <p class="text-red-500 text-xs italic mt-2" id="create-aktif-error"></p>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" data-modal-hide="createModal"
                            class="px-4 py-2 bg-gray-600 rounded-lg text-white">Batal</button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 rounded-lg text-white hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
