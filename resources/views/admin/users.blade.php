@extends('default')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="container mx-auto p-4 md:p-6">
    <h1 class="text-3xl font-bold mb-6 text-white">Manajemen Pengguna</h1>

    {{-- Alert Sukses --}}
    @if(session('success'))
        <div id="alert-success" class="flex items-center p-4 mb-4 text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
            <span class="ms-3 text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    {{-- Alert Error --}}
    @if(session('error'))
        <div id="alert-error" class="flex items-center p-4 mb-4 text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
            <span class="ms-3 text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Alert Validasi Email --}}
    @if($errors->has('email'))
        <div class="flex items-center p-4 mb-4 text-yellow-800 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300" role="alert">
            <span class="ms-3 text-sm font-medium">{{ $errors->first('email') }}</span>
        </div>
    @endif

    {{-- Tombol --}}
    <div class="flex justify-between items-center mb-4">
        <button data-modal-target="create-user-modal" data-modal-toggle="create-user-modal"
            class="text-white bg-blue-700 hover:bg-blue-800 px-5 py-2.5 rounded-lg">
            Tambah Pengguna Baru
        </button>
    </div>

    {{-- Search di bawah tombol --}}
    <div class="mb-4">
        <input type="text" id="searchInput" placeholder="Cari pengguna..."
            class="w-full px-4 py-2 rounded-lg text-sm bg-gray-800 border border-gray-600 text-white focus:ring-blue-500 focus:border-blue-500" />
    </div>

    {{-- Table --}}
    <div class="bg-[#1e1e1e] p-6 rounded-lg shadow-md overflow-x-auto">
        <table id="usersTable" class="min-w-full text-white">
            <thead>
                <tr class="bg-[#2a2a2a] text-gray-300 uppercase text-sm">
                    <th class="py-3 px-6 text-left">ID</th>
                    <th class="py-3 px-6 text-left">Nama</th>
                    <th class="py-3 px-6 text-left">Email</th>
                    <th class="py-3 px-6 text-left">Role</th>
                    <th class="py-3 px-6 text-left">Dibuat</th>
                    <th class="py-3 px-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="usersTableBody" class="text-gray-400 text-sm">
                @forelse($users as $user)
                    <tr class="border-b border-gray-700 hover:bg-[#232323]">
                        <td class="py-3 px-6">{{ $user->id }}</td>
                        <td class="py-3 px-6">{{ $user->name }}</td>
                        <td class="py-3 px-6">{{ $user->email }}</td>
                        <td class="py-3 px-6">
                            <span class="px-2 py-1 text-white text-xs font-semibold px-2.5 py-0.5 rounded-full
                                {{ $user->role === 'admin' ? 'bg-green-600' : 'bg-yellow-600' }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="py-3 px-6">{{ $user->created_at->format('d M Y H:i') }}</td>
                        <td class="py-3 px-6 text-center flex justify-center gap-2">
                            {{-- Edit --}}
                            <button type="button" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded edit-button"
                                data-modal-target="edit-user-modal" data-modal-toggle="edit-user-modal"
                                data-id="{{ $user->id }}" data-name="{{ $user->name }}" data-email="{{ $user->email }}" data-role="{{ $user->role }}"
                                @if($user->id == auth()->id()) data-self="true" @else data-self="false" @endif>
                                <i class="fas fa-edit"></i>
                            </button>

                            {{-- Delete (tidak bisa hapus diri sendiri) --}}
                            @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.delete', $user->id) }}" method="POST"
                                    onsubmit="return confirm('Yakin hapus pengguna ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-4 text-center text-gray-500">Tidak ada pengguna.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Create Modal --}}
<div id="create-user-modal" class="hidden fixed inset-0 z-50 justify-center items-center w-full h-full overflow-y-auto">
    <div class="bg-gray-700 rounded-lg shadow p-5 max-w-md w-full">
        <h3 class="text-xl font-bold text-white mb-4">Tambah Pengguna</h3>
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="text-white">Nama</label>
                <input type="text" name="name" class="w-full px-3 py-2 rounded bg-gray-800 border border-gray-600 text-white" required>
            </div>
            <div class="mb-4">
                <label class="text-white">Email</label>
                <input type="email" name="email" class="w-full px-3 py-2 rounded bg-gray-800 border border-gray-600 text-white" required>
            </div>
            <div class="mb-4">
                <label class="text-white">Password</label>
                <input type="password" name="password" class="w-full px-3 py-2 rounded bg-gray-800 border border-gray-600 text-white" required>
            </div>
            <div class="mb-4">
                <label class="text-white">Role</label>
                <select name="role" class="w-full px-3 py-2 rounded bg-gray-800 border border-gray-600 text-white" required>
                    <option value="admin">Admin</option>
                    <option value="bos">Bos</option>
                    <option value="supervisor">Supervisor</option>
                    <option value="kasir">Kasir</option>
                    <option value="pemandu">Pemandu</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded">Simpan</button>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div id="edit-user-modal" class="hidden fixed inset-0 z-50 justify-center items-center w-full h-full overflow-y-auto">
    <div class="bg-gray-700 rounded-lg shadow p-5 max-w-md w-full">
        <h3 class="text-xl font-bold text-white mb-4">Edit Pengguna</h3>
        <form id="editUserForm" action="" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="text-white">Nama</label>
                <input type="text" name="name" id="edit_name" class="w-full px-3 py-2 rounded bg-gray-800 border border-gray-600 text-white" required>
            </div>
            <div class="mb-4">
                <label class="text-white">Email</label>
                <input type="email" name="email" id="edit_email" class="w-full px-3 py-2 rounded bg-gray-800 border border-gray-600 text-white" required>
            </div>
            <div class="mb-4">
                <label class="text-white">Password (Kosongkan jika tidak diubah)</label>
                <input type="password" name="password" class="w-full px-3 py-2 rounded bg-gray-800 border border-gray-600 text-white">
            </div>
            <div class="mb-4">
                <label class="text-white">Role</label>
                <select name="role" id="edit_role" class="w-full px-3 py-2 rounded bg-gray-800 border border-gray-600 text-white" required></select>
            </div>
            <button type="submit" class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded">Update</button>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
    // Live Search
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let keyword = this.value.toLowerCase();
        let rows = document.querySelectorAll('#usersTableBody tr');
        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(keyword) ? '' : 'none';
        });
    });

    // Isi edit modal
    document.querySelectorAll('.edit-button').forEach(btn => {
        btn.addEventListener('click', function() {
            let id = this.dataset.id;
            let name = this.dataset.name;
            let email = this.dataset.email;
            let role = this.dataset.role;
            let isSelf = this.dataset.self === "true";

            document.getElementById('editUserForm').action = `/admin/users/${id}`;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;

            let roleSelect = document.getElementById('edit_role');
            roleSelect.innerHTML = `
                <option value="admin">Admin</option>
                <option value="bos">Bos</option>
                <option value="supervisor">Supervisor</option>
                <option value="kasir">Kasir</option>
                <option value="pemandu">Pemandu</option>
            `;
            roleSelect.value = role;

            // Jika user sedang login, disable select role
            if (isSelf) {
                roleSelect.disabled = true;
            } else {
                roleSelect.disabled = false;
            }
        });
    });
</script>
@endsection
