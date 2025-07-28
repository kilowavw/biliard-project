@extends('default')

@section('title', 'Manajemen User')

@section('content')
<div class="mt-9">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-2xl font-semibold">Manajemen Pengguna</h1>
        <button class="btn btn-success" onclick="openAddModal()">
            <i class="fa fa-plus mr-2"></i> Tambah User
        </button>
    </div>

    <input type="text" id="searchInput" placeholder="Cari pengguna..." class="form-control mb-3">

    <div class="table-responsive">
        <table class="table table-dark table-bordered w-full text-sm">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="userTableBody">
                @foreach ($users as $index => $user)
                {{-- Baris utama --}}
                <tr class="user-row" data-index="{{ $index }}">
                    <td>
                        <span class="cursor-pointer toggle-collapse-btn" data-index="{{ $index }}">
                            <i class="fa fa-chevron-down transition-transform duration-300"></i>
                        </span>
                        {{ $user->name }}
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>{{ ucfirst($user->role) }}</td>
                    <td>
                        @if (auth()->id() !== $user->id)
                        <button class="btn btn-sm btn-warning me-2" onclick="openEditModal({{ $user }})">
                            <i class="fa fa-pen"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteUser({{ $user->id }})">
                            <i class="fa fa-trash"></i>
                        </button>
                        @else
                        <span class="text-gray-500 italic">Akun Anda</span>
                        @endif
                    </td>
                </tr>

                {{-- Baris collapsible --}}
                <tr id="collapseUser{{ $index }}" class="collapse-row hidden bg-gray-900 text-white">
                    <td colspan="4">
                        <div><strong>Email:</strong> {{ $user->email }}</div>
                        <div><strong>Role:</strong> {{ ucfirst($user->role) }}</div>
                        <div class="mt-2">
                            <strong>Aksi:</strong><br>
                            @if (auth()->id() !== $user->id)
                            <button class="btn btn-sm btn-warning me-2 mt-1" onclick="openEditModal({{ $user }})">
                                <i class="fa fa-pen"></i>
                            </button>
                            <button class="btn btn-sm btn-danger mt-1" onclick="deleteUser({{ $user->id }})">
                                <i class="fa fa-trash"></i>
                            </button>
                            @else
                            <span class="text-gray-400 italic">Akun Anda</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>


    <!-- Modal Form -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="userForm" class="modal-content bg-dark text-white border border-gray-700">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">Form Pengguna</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="user_id">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama</label>
                        <input type="text" id="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" class="form-control" required>
                    </div>
                    <div class="mb-3 password-field">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" class="form-control" placeholder="••••••••">
                        <small class="text-muted">Kosongkan jika tidak ingin mengganti password</small>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select id="role" class="form-select" required>
                            <option value="admin">Admin</option>
                            <option value="supervisor">Supervisor</option>
                            <option value="kasir">Kasir</option>
                            <option value="bos">Bos</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script -->
    <script>
        let userModal = new bootstrap.Modal(document.getElementById('userModal'));

        function openAddModal() {
            $('#user_id').val('');
            $('#name, #email, #password').val('');
            $('.password-field').show();
            userModal.show();
        }

        function openEditModal(user) {
            $('#user_id').val(user.id);
            $('#name').val(user.name);
            $('#email').val(user.email);
            $('#password').val('');
            $('.password-field').show();
            $('#role').val(user.role);
            userModal.show();
        }

        $('#userForm').on('submit', function(e) {
            e.preventDefault();
            let id = $('#user_id').val();
            let url = id ? `/admin/users/${id}` : `/admin/users`;
            let method = id ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                type: method,
                data: {
                    _token: '{{ csrf_token() }}',
                    name: $('#name').val(),
                    email: $('#email').val(),
                    password: $('#password').val(),
                    role: $('#role').val(),
                },
                success: function(res) {
                    Swal.fire('Sukses', res.success, 'success').then(() => location.reload());
                },
                error: function(err) {
                    let msg = err.responseJSON?.message || 'Terjadi kesalahan.';
                    if (msg.includes('email')) {
                        msg = 'Email sudah digunakan.';
                    }
                    Swal.fire('Gagal', msg, 'error');
                }
            });
        });

        function deleteUser(id) {
            Swal.fire({
                title: 'Yakin ingin hapus?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/admin/users/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            Swal.fire('Berhasil', res.success, 'success').then(() => location.reload());
                        },
                        error: function(err) {
                            Swal.fire('Gagal', err.responseJSON?.error || 'Terjadi kesalahan.', 'error');
                        }
                    });
                }
            });
        }

        $(document).ready(function() {
            // Toggle baris collapse saat ikon panah ditekan
            $('.toggle-collapse-btn').on('click', function(e) {
                const index = $(this).data('index');
                const collapseRow = $('#collapseUser' + index);
                const icon = $(this).find('i');

                collapseRow.toggleClass('hidden');
                icon.toggleClass('rotate-180');
            });

            // Pencarian hanya baris utama (user-row), dan auto hide collapse-nya
            $('#searchInput').on('input', function() {
                const keyword = $(this).val().toLowerCase();

                $('.user-row').each(function() {
                    const index = $(this).data('index');
                    const collapseRow = $('#collapseUser' + index);
                    const rowText = $(this).text().toLowerCase();

                    if (rowText.includes(keyword)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }

                    // Selalu sembunyikan baris collapse saat pencarian
                    collapseRow.addClass('hidden');
                });
            });
        });
    </script>
    @endsection