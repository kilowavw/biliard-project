{{-- working one --}}
@extends('default')

@section('title', 'Dashboard Kasir')

@section('content')

<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4 text-black">Dashboard Kasir</h1>

    <div id="meja-container"
        class="grid grid-cols-1 sm:grid-cols-2 [@media(min-width:768px)_and_(max-width:870px)]:grid-cols-2
               [@media(min-width:871px)_and_(max-width:1025px)]:grid-cols-3 lg:grid-cols-4 gap-4 text-black">
        @foreach ($mejas as $meja)
            <div id="meja-card-{{ $meja->id }}"
                class="p-4 border rounded shadow cursor-pointer transition @if($meja->status === 'dipakai') bg-green-100 @else bg-neutral-600 @endif"
                onclick="toggleDetail({{ $meja->id }})">

                <h2 class="text-lg font-semibold">{{ $meja->nama_meja }}</h2>
                <img src="{{ asset('gambar/Meja2.webp') }}" alt="Meja" width="200" class="mx-auto my-2">
                <p id="status-meja-{{ $meja->id }}">Status: {{ $meja->status }}</p>

                <div id="detail-{{ $meja->id }}" class="hidden mt-3 space-y-2" onclick="event.stopPropagation();">
                    @if ($meja->status === 'kosong')
                        <button id="btn-pesan-{{ $meja->id }}"
                                onclick="event.stopPropagation(); openModal({{ $meja->id }})"
                                class="w-full px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none">
                            Pesan
                        </button>
                    @else
                        <p class="text-sm text-gray-700">Meja sedang dipakai</p>
                    @endif
                    <div id="penyewaan-{{ $meja->id }}"></div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div id="pesanModal" style="display:none" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md text-black">
        <h2 class="text-xl font-bold mb-4">Pemesanan Meja</h2>
        <form id="formPesan" method="POST">
            @csrf
            <input type="hidden" name="meja_id" id="modal_meja_id">

            <div class="mb-3">
                <label for="kode_member" class="block text-sm font-medium text-gray-700">Kode Member (Opsional)</label>
                <div class="flex">
                    <input type="text" name="kode_member" id="kode_member"
                        class="flex-grow border-gray-300 rounded-l-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-2">
                    <button type="button" id="btn-validate-member" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-r-md hover:bg-gray-300">
                        <i class="fas fa-check"></i>
                    </button>
                </div>
                <p id="member_info_preview" class="text-sm text-green-600 mt-1 italic" style="display:none;"></p>
            </div>

            <div class="mb-3">
                <label for="nama_penyewa" class="block text-sm font-medium text-gray-700">Nama Penyewa <span class="text-red-500">*</span></label>
                <input type="text" name="nama_penyewa" id="nama_penyewa" required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-2">
            </div>

            <div class="mb-3">
                <label for="paket_id_select" class="block text-sm font-medium text-gray-700">Pilih Paket (Opsional)</label>
                <select name="paket_id" id="paket_id_select"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-2">
                    <option value="">-- Pilih Paket --</option>
                </select>
                <div id="paket_deskripsi_preview" class="text-sm text-gray-500 mt-1 italic" style="display:none;"></div>
            </div>

            <div id="non_paket_options" class="space-y-3">
                <div class="flex items-center">
                    <input type="checkbox" id="is_sepuasnya" name="is_sepuasnya"
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="is_sepuasnya" class="ml-2 block text-sm text-gray-900">Main Sepuasnya</label>
                </div>
                <div class="mb-3" id="durasi_jam_wrapper">
                    <label for="durasi_jam" class="block text-sm font-medium text-gray-700">Durasi (Jam, contoh: 1.5 untuk 1 jam 30 menit)</label>
                    <input type="number" name="durasi_jam" id="durasi_jam" min="0.01" step="0.01"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-2">
                </div>
            </div>

            <div class="flex justify-end mt-4 space-x-2">
                <button type="button" onclick="closeModal()"
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Batal</button>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Mulai</button>
            </div>
        </form>
    </div>
</div>

<div id="addDurationModal" style="display:none" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md text-black">
        <h2 class="text-xl font-bold mb-4">Tambah Durasi Meja <span id="add_duration_meja_nama"></span></h2>
        <form id="formAddDuration">
            @csrf
            <input type="hidden" name="penyewaan_id" id="add_duration_penyewaan_id">
            <p class="mb-2 text-gray-700">Penyewa: <strong id="add_duration_nama_penyewa"></strong></p>
            <p class="mb-2 text-gray-700">Durasi Saat Ini: <strong id="add_duration_current_durasi"></strong></p>
            <div class="mb-3">
                <label for="additional_durasi_jam" class="block text-sm font-medium text-gray-700">Durasi Tambahan (Jam)</label>
                <input type="number" name="additional_durasi_jam" id="additional_durasi_jam" required min="0.01" step="0.01"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-2">
            </div>
            <div class="flex justify-end mt-4 space-x-2">
                <button type="button" onclick="closeAddDurationModal()"
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Batal</button>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Tambah Durasi</button>
            </div>
        </form>
    </div>
</div>

<div id="addServiceModal" style="display:none" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-lg text-black">
        <h2 class="text-xl font-bold mb-4">Tambah Service Meja <span id="add_service_meja_nama"></span></h2>
        <form id="formAddService">
            @csrf
            <input type="hidden" name="penyewaan_id" id="add_service_penyewaan_id">
            <p class="mb-2 text-gray-700">Penyewa: <strong id="add_service_nama_penyewa"></strong></p>
            <p class="mb-2 text-gray-700">Total Service Tambahan Saat Ini: <strong id="add_service_current_total"></strong></p>

            <h3 class="font-semibold mt-4 mb-2 text-gray-800">Daftar Service Tersedia:</h3>
            <div id="service_list_container" class="max-h-64 overflow-y-auto border rounded p-2 mb-4">
            </div>

            <p class="text-lg font-bold text-gray-800">Total Tambahan Service: <strong id="current_service_add_total" class="text-neutral-600">Rp 0</strong></p>

            <div class="flex justify-end mt-4 space-x-2">
                <button type="button" onclick="closeAddServiceModal()"
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Batal</button>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Tambah Service</button>
            </div>
        </form>
    </div>
</div>

<div id="paymentModal" style="display:none" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
     <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto text-black">
        <h2 class="text-xl font-bold mb-4">Detail Pembayaran</h2>
        <form id="formPembayaran">
            @csrf
            <input type="hidden" name="penyewaan_id" id="payment_penyewaan_id">
            <p class="mb-1">Meja: <strong id="payment_meja_nama"></strong></p>
            <p class="mb-1">Penyewa: <strong id="payment_nama_penyewa"></strong></p>
            <p id="payment_member_info" class="mb-1 text-sm text-green-600" style="display: none;"></p>
            <p id="payment_paket_info" class="mb-1 text-sm font-semibold text-purple-700" style="display: none;"></p>
            <p class="mb-1">Durasi Booking: <strong id="payment_durasi"></strong></p>
            <p id="payment_mode_sepuasnya_info" style="display: none;" class="text-sm text-gray-500 italic">(Mode Main Sepuasnya)</p>
            <p class="mb-1">Waktu Mulai: <strong id="payment_waktu_mulai"></strong></p>
            <p class="mb-1">Waktu Selesai (Terjadwal): <strong id="payment_waktu_selesai_terjadwal"></strong></p>
            <p class="mb-1">Waktu Selesai (Aktual): <strong id="payment_waktu_selesai_aktual"></strong></p>
            <p class="mb-1"><span id="label_harga_sewa">Harga Per Jam</span>: <strong id="payment_harga_per_jam_or_paket"></strong></p>
            <p class="mb-1">Subtotal Sewa: <strong id="payment_subtotal_main"></strong></p>
            <p class="mb-1">Total Service Tambahan: <strong id="payment_total_service"></strong></p>
            <ul id="payment_service_detail" class="list-disc list-inside text-sm text-gray-600 mb-2 pl-5"></ul>

            <div class="mb-3 mt-4">
                <label for="kode_kupon" class="block text-sm font-medium text-gray-700">Kode Kupon (Opsional)</label>
                <input type="text" name="kode_kupon" id="kode_kupon"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-2">
                <small class="text-gray-500">Biarkan kosong jika tidak ada kupon.</small>
            </div>
            <p class="text-lg font-bold mb-1">Diskon Member: <strong id="payment_diskon_member" class="text-red-500"></strong></p>
            <p class="text-lg font-bold mb-1">Diskon Kupon: <strong id="payment_diskon_kupon" class="text-red-500"></strong></p>
            <p class="text-xl font-bold mt-2">Total Pembayaran: <strong id="payment_total_final" class="text-blue-600"></strong></p>

            <div class="flex justify-end mt-4 space-x-2">
                <button type="button" onclick="closePaymentModal()"
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Batal</button>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Bayar Sekarang</button>
                <button type="button" id="btn-pay-qris" onclick="handleQrisPayment()"
                    class="px-4 py-2 bg-neutral-600 text-white rounded-md hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-neutral-500">Bayar dengan QRIS</button>
                <button type="button" id="btn-print-struk" onclick="printPaymentStruk()"
                    class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">Cetak Struk</button>
            </div>
        </form>
    </div>
</div>


@if(session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: "{{ session('error') }}",
        confirmButtonText: 'OK'
    });
</script>
@endif

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: "{{ session('success') }}",
        confirmButtonText: 'OK'
    });
</script>
@endif

{{-- Definisi variabel global yang akan digunakan di dashboard.js --}}
<script>
    // Ini harus dieksekusi pertama kali di head atau sebelum script external
    window.serverTime = new Date("{{ $serverTime }}");
    window.userRole = "{{ $userRole }}";
    window.csrfToken = "{{ csrf_token() }}";
    window.initialPakets = @json($activePakets); // Data paket
    window.routes = {
        basePath: "{{ url('/kasir') }}",
        pesanDurasi: "{{ route('kasir.pesanDurasi') }}",
        pesanPaket: "{{ route('kasir.pesanPaket') }}",
        pesanSepuasnya: "{{ route('kasir.pesanSepuasnya') }}",
        apiPenyewaanAktif: "{{ route('kasir.api.penyewaanAktif') }}",
        apiServices: "{{ route('api.services') }}",
        apiMemberValidate: "{{ route('api.member.validate') }}"
        // Tambahkan rute lain jika ada yang digunakan di JS
    };
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/dashboard.js') }}"></script>
@endsection