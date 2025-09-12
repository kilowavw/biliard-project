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

{{-- MODAL PEMESANAN (sudah ada) --}}
<div id="pesanModal" style="display:none" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md text-black">
        <h2 class="text-xl font-bold mb-4">Pemesanan Meja <span id="modal_meja_nama_pesan"></span></h2>
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
                    <input type="checkbox" id="is_sepuasnya" name="is_sepuasnya_form_input"
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="is_sepuasnya" class="ml-2 block text-sm text-gray-900">Main Sepuasnya</label>
                </div>
                <div class="mb-3" id="durasi_jam_wrapper">
                    <label for="durasi_jam" class="block text-sm font-medium text-gray-700">Durasi (Jam, contoh: 1.5 untuk 1 jam 30 menit)</label>
                    <input type="number" name="durasi_jam" id="durasi_jam" min="0.01" step="0.01"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-2">
                    {{-- <p class="text-red-500 text-xs mt-1">Durasi minimal 1 jam untuk durasi tetap.</p> --}} {{-- KOMENTAR: Untuk masa testing --}}
                </div>
            </div>

            <div class="flex justify-end mt-4 space-x-2">
                <button type="button" onclick="closeModal()"
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Batal</button>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Mulai Proses</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL KONFIRMASI PEMESANAN (BARU untuk Revisi 3) --}}
<div id="confirmPesanModal" style="display:none" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md text-black">
        <h2 class="text-xl font-bold mb-4">Konfirmasi Pemesanan</h2>
        <p class="mb-2">Meja <strong id="confirm_meja_nama"></strong> (Status: <span class="text-green-600 font-semibold">Dipakai</span>)</p>
        <p class="mb-2">Penyewa: <strong id="confirm_nama_penyewa"></strong></p>
        <p id="confirm_member_info" class="mb-2 text-sm text-blue-700" style="display:none;"></p>
        <p class="mb-2">Tipe Pesanan: <strong id="confirm_tipe_pesanan"></strong></p>
        <p id="confirm_durasi_info" class="mb-2" style="display:none;">Durasi: <strong id="confirm_durasi_jam"></strong></p>
        <p id="confirm_paket_info" class="mb-2" style="display:none;">Paket: <strong id="confirm_paket_nama"></strong></p>
        
        <p class="mt-4 text-center text-lg font-semibold text-gray-800">Meja sudah menyala. Apakah Anda yakin ingin memulai billing?</p>

        <div class="flex justify-end mt-6 space-x-2">
            <button type="button" id="btn-cancel-confirm-pesan"
                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Batal & Reset Meja</button>
            <button type="button" id="btn-confirm-pesan"
                class="px-4 py-2 bg-green-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">Ya, Mulai Billing</button>
        </div>
    </div>
</div>

{{-- MODAL UBAH MEJA (BARU untuk Revisi 1) --}}
<div id="transferMejaModal" style="display:none" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md text-black">
        <h2 class="text-xl font-bold mb-4">Pindah Meja</h2>
        <form id="formTransferMeja">
            @csrf
            <input type="hidden" name="penyewaan_id" id="transfer_penyewaan_id">
            <p class="mb-2">Penyewa: <strong id="transfer_nama_penyewa"></strong></p>
            <p class="mb-2">Meja Saat Ini: <strong id="transfer_meja_lama_nama"></strong></p>

            <div class="mb-3">
                <label for="meja_baru_id" class="block text-sm font-medium text-gray-700">Pilih Meja Baru <span class="text-red-500">*</span></label>
                <select name="meja_baru_id" id="meja_baru_id" required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-2">
                    <option value="">-- Pilih Meja --</option>
                    {{-- Opsi meja kosong akan dimuat via JS --}}
                </select>
            </div>

            <div class="flex justify-end mt-4 space-x-2">
                <button type="button" onclick="closeTransferMejaModal()"
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Batal</button>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Pindahkan Meja</button>
            </div>
        </form>
    </div>
</div>


{{-- MODAL TAMBAH DURASI (sudah ada) --}}
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

{{-- MODAL TAMBAH SERVICE (sudah ada) --}}
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

{{-- MODAL PEMBAYARAN (sudah ada) --}}
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
            <p class="mb-1"><span id="label_harga_sewa">Harga Per Jam</span>: <strong id="payment_harga_per_jam_or_paket"></strong> <span id="payment_harga_per_jam_saat_ini_info" class="text-xs text-gray-500 italic" style="display: none;">(Harga saat ini: <strong id="payment_harga_per_jam_aktual"></strong>)</span></p>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const serverTime = new Date("{{ $serverTime }}");
    const clientTimeAtLoad = new Date();
    const serverClientOffset = serverTime.getTime() - clientTimeAtLoad.getTime();
    const userRole = "{{ $userRole }}";

    const getCalibratedNow = () => new Date(new Date().getTime() + serverClientOffset);
    const countdownIntervals = {};
    let currentActiveRentals = {};
    let allAvailableServices = [];
    let allAvailablePakets = @json($activePakets);
    let memberData = { valid: false, nama_member: '', diskon_persen: 0 };
    // Tambahkan variabel untuk menyimpan data pemesanan sementara untuk konfirmasi
    let tempPemesananData = null; // Ini akan menyimpan data dari initiatePemesanan

    const getEl = (id) => document.getElementById(id);
    const toggleModal = (modalId, show) => { getEl(modalId).style.display = show ? 'flex' : 'none'; };
    const fmtTime = (dtStr) => { if (!dtStr) return '--:--'; const d=new Date(dtStr); return `${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`; };
    const fmtFullDt = (dtStr) => { if (!dtStr) return 'N/A'; const d=new Date(dtStr); const opt={year:'numeric',month:'short',day:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false,timeZoneName:'short'}; return d.toLocaleString('id-ID',opt); };
    const fmtDur = (decHrs) => {
        if (decHrs === null || isNaN(decHrs) || decHrs === 0) return 'N/A';
        const totalMins = decHrs * 60;
        const hrs = Math.floor(totalMins / 60);
        const mins = Math.round(totalMins % 60);
        let res = '';
        if (hrs > 0) res += `${hrs} Jam `;
        if (mins > 0) res += `${mins} Menit`;
        // if (hrs === 0 && mins === 0 && decHrs > 0) return 'Kurang dari 1 Menit'; // Ini bisa diaktifkan jika perlu presisi lebih rendah dari 1 menit
        return res.trim();
    };
    const fmtRp = (amt) => new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',minimumFractionDigits:0}).format(amt);
    const debounce = (func, delay) => { let timeout; return function(...args) { const context=this; clearTimeout(timeout); timeout=setTimeout(()=>func.apply(context,args),delay); }; };

    const openModal = async (mejaId) => {
        getEl('modal_meja_id').value = mejaId;
        getEl('modal_meja_nama_pesan').innerText = document.querySelector(`#meja-card-${mejaId} h2`).innerText; // Tampilkan nama meja
        getEl('formPesan').reset();
        getEl('kode_member').value = '';
        getEl('nama_penyewa').value = '';
        getEl('member_info_preview').style.display = 'none';
        getEl('member_info_preview').classList.remove('text-red-600');
        getEl('member_info_preview').classList.add('text-green-600');
        memberData = { valid: false, nama_member: '', diskon_persen: 0 };
        
        getEl('paket_id_select').value = '';
        getEl('paket_deskripsi_preview').style.display = 'none';
        getEl('paket_deskripsi_preview').innerHTML = '';
        getEl('paket_id_select').style.display = 'block';

        getEl('non_paket_options').style.display = 'block';
        getEl('is_sepuasnya').checked = false;
        getEl('is_sepuasnya').disabled = false; // Pastikan aktif
        getEl('durasi_jam_wrapper').style.display = 'block';
        getEl('durasi_jam').required = true;
        getEl('durasi_jam').disabled = false; // Pastikan aktif
        
        // Form action diubah agar selalu ke initiatePemesanan
        getEl('formPesan').action = '{{ route('kasir.initiatePemesanan') }}';

        populatePaketsDropdown(false);
        toggleModal('pesanModal', true);
    };
    const closeModal = () => toggleModal('pesanModal', false);

    // BARU: Fungsi untuk modal konfirmasi
    const openConfirmPemesananModal = (data) => {
        tempPemesananData = data; // Simpan data untuk konfirmasi final
        getEl('confirm_meja_nama').innerText = document.querySelector(`#meja-card-${data.meja_id} h2`).innerText;
        getEl('confirm_nama_penyewa').innerText = data.nama_penyewa;
        
        if (data.member_id) {
            getEl('confirm_member_info').innerText = `Member: ${memberData.nama_member} (Diskon: ${memberData.diskon_persen}%)`;
            getEl('confirm_member_info').style.display = 'block';
        } else {
            getEl('confirm_member_info').style.display = 'none';
        }

        getEl('confirm_tipe_pesanan').innerText = data.tipe_pesanan === 'durasi' ? 'Durasi Tetap' : (data.tipe_pesanan === 'sepuasnya' ? 'Main Sepuasnya' : `Paket (${data.paket_nama})`);

        if (data.tipe_pesanan === 'durasi') {
            getEl('confirm_durasi_info').style.display = 'block';
            getEl('confirm_durasi_jam').innerText = fmtDur(data.durasi_jam);
            getEl('confirm_paket_info').style.display = 'none';
        } else if (data.tipe_pesanan === 'paket') {
            getEl('confirm_durasi_info').style.display = 'none';
            getEl('confirm_paket_info').style.display = 'block';
            getEl('confirm_paket_nama').innerText = data.paket_nama;
        } else { // Sepuasnya
            getEl('confirm_durasi_info').style.display = 'none';
            getEl('confirm_paket_info').style.display = 'none';
        }

        toggleModal('confirmPesanModal', true);
    };
    const closeConfirmPemesananModal = () => toggleModal('confirmPesanModal', false);


    // BARU: Fungsi untuk modal transfer meja
    const openTransferMejaModal = async (penyewaanId) => {
        const p = currentActiveRentals[penyewaanId];
        if (!p) { Swal.fire('Error', 'Penyewaan tidak ditemukan.', 'error'); return; }

        getEl('transfer_penyewaan_id').value = penyewaanId;
        getEl('transfer_nama_penyewa').innerText = p.nama_penyewa;
        getEl('transfer_meja_lama_nama').innerText = p.meja_nama;
        getEl('formTransferMeja').reset();

        const mejaBaruSelect = getEl('meja_baru_id');
        mejaBaruSelect.innerHTML = '<option value="">-- Pilih Meja --</option>';

        // Ambil daftar meja kosong dari DOM (atau bisa dari API jika mau lebih dinamis)
        // Untuk sederhana, kita ambil dari data meja yang sudah di-render
        document.querySelectorAll('[id^="meja-card-"]').forEach(card => {
            const mejaStatusEl = card.querySelector('p[id^="status-meja-"]');
            const mejaId = parseInt(card.id.replace('meja-card-', ''));
            const mejaNama = card.querySelector('h2').innerText;

            if (mejaStatusEl && mejaStatusEl.innerText.includes('kosong') && mejaId !== p.meja_id) {
                const option = document.createElement('option');
                option.value = mejaId;
                option.innerText = mejaNama;
                mejaBaruSelect.appendChild(option);
            }
        });

        toggleModal('transferMejaModal', true);
    };
    const closeTransferMejaModal = () => toggleModal('transferMejaModal', false);


    const openAddDurationModal = (penyewaanId) => {
        const p=currentActiveRentals[penyewaanId];
        if (!p) { Swal.fire('Error', 'Penyewaan tidak ditemukan.', 'error'); return; }
        if (p.paket_id && p.durasi_jam > 0) {
             Swal.fire('Info', 'Tidak bisa menambah durasi untuk penyewaan paket dengan durasi tetap.', 'info');
             return;
        }
        if (p.is_sepuasnya) { Swal.fire('Info', 'Tidak bisa menambah durasi untuk penyewaan ini (Main Sepuasnya).', 'info'); return; }

        getEl('add_duration_penyewaan_id').value = penyewaanId; getEl('add_duration_meja_nama').innerText = p.meja_nama; getEl('add_duration_nama_penyewa').innerText = p.nama_penyewa; getEl('add_duration_current_durasi').innerText = fmtDur(p.durasi_jam); getEl('formAddDuration').reset(); toggleModal('addDurationModal', true);
    };
    const closeAddDurationModal = () => toggleModal('addDurationModal', false);
    const openAddServiceModal = async (penyewaanId) => {
        const p=currentActiveRentals[penyewaanId]; if (!p) { Swal.fire('Error', 'Penyewaan tidak ditemukan.', 'error'); return; }
        getEl('add_service_penyewaan_id').value = penyewaanId; getEl('add_service_meja_nama').innerText = p.meja_nama; getEl('add_service_nama_penyewa').innerText = p.nama_penyewa; getEl('add_service_current_total').innerText = fmtRp(p.total_service); getEl('current_service_add_total').innerText = fmtRp(0);
        await fetchAndRenderServicesForAdd();
        toggleModal('addServiceModal', true);
    };
    const closeAddServiceModal = () => toggleModal('addServiceModal', false);
    const openPaymentModal = (penyewaanId) => {
        if (getEl('paymentModal').style.display === 'flex') return;
        getEl('payment_penyewaan_id').value = penyewaanId; getEl('kode_kupon').value = '';
        fetchPaymentDetails(penyewaanId); // Panggil fungsi baru
        toggleModal('paymentModal', true);
    };
    const closePaymentModal = () => toggleModal('paymentModal', false);

    const startCountdown = (el, waktuSelesaiStr, penyewaanData) => {
        const waktuSelesai = new Date(waktuSelesaiStr); const pId = penyewaanData.id;
        if (countdownIntervals[pId]) clearInterval(countdownIntervals[pId]);
        const interval = setInterval(() => {
            const now = getCalibratedNow(); const dist = waktuSelesai.getTime() - now.getTime();
            if (dist <= 0) { clearInterval(interval); el.innerText = 'Waktu habis!'; el.classList.add('text-red-700','font-bold'); handleTimeUpUI(penyewaanData); }
            else { const h=String(Math.floor((dist/(1000*60*60))%24)).padStart(2,'0'); const m=String(Math.floor((dist/(1000*60))%60)).padStart(2,'0'); const s=String(Math.floor((dist/1000)%60)).padStart(2,'0'); el.innerText=`${h}:${m}:${s}`; }
        }, 1000); countdownIntervals[pId] = interval;
    };

    const startRunningTimer = (el, waktuMulaiStr, penyewaanData) => {
        const waktuMulai = new Date(waktuMulaiStr); const pId = penyewaanData.id;
        if (countdownIntervals[pId]) clearInterval(countdownIntervals[pId]);
        const interval = setInterval(() => {
            const now = getCalibratedNow(); const elapsed = now.getTime() - waktuMulai.getTime();
            const h=String(Math.floor((elapsed/(1000*60*60))%24)).padStart(2,'0'); const m=String(Math.floor((elapsed/(1000*60))%60)).padStart(2,'0'); const s=String(Math.floor((elapsed/1000)%60)).padStart(2,'0'); el.innerText=`${h}:${m}:${s}`;
        }, 1000); countdownIntervals[pId] = interval;
    };

    const handleTimeUpUI = (penyewaanData) => {
        const mejaId = penyewaanData.meja_id;
        const mejaCardEl = getEl(`meja-card-${mejaId}`);
        const statusMejaEl = mejaCardEl.querySelector(`#status-meja-${mejaId}`);
        const actionsCont = document.querySelector(`#penyewaan-${mejaId} .flex-wrap`);

        if (statusMejaEl) {
            statusMejaEl.innerText = 'Status: waktu_habis';
        }
        if (mejaCardEl) {
            mejaCardEl.classList.remove('bg-green-100', 'bg-neutral-600');
            mejaCardEl.classList.add('bg-red-200');
        }

        if (actionsCont) {
            actionsCont.innerHTML = `
                @if(Auth::check() && (Auth::user()->role == 'admin' || Auth::user()->role == 'supervisor'))
                <button type="button" class="px-3 py-1 bg-red-200 hover:bg-red-300 text-red-900 rounded-md text-xs" onclick="event.preventDefault(); confirmDeletePenyewaan(${penyewaanData.id}, '${penyewaanData.meja_nama}');">
                    <i class="fa-solid fa-trash"></i> Hapus
                </button>
                @endif
                <button type="button" class="px-3 py-1 bg-yellow-200 hover:bg-yellow-300 text-yellow-900 rounded-md text-xs" onclick="event.preventDefault(); openAddServiceModal(${penyewaanData.id});">
                    <i class="fa-solid fa-wine-bottle"></i> Tambah Service
                </button>
                <span class="px-3 py-1 bg-red-100 text-red-700 rounded-md text-xs">Waktu Habis! Menunggu Pembayaran.</span>
                @if(Request::is('kasir*'))
                <button type="button" class="px-3 py-1 bg-green-500 text-white rounded-md text-xs hover:bg-green-600" onclick="event.preventDefault(); openPaymentModal(${penyewaanData.id});">
                    <i class="fa-solid fa-cash-register"></i> Bayar Sekarang
                </button>
                @endif
            `;
        }
        // Tidak perlu updateMejaStatusOnServer di frontend karena backend sudah otomatis
        // updateMejaStatusOnServer(mejaId, 'waktu_habis');

        @if(Request::is('kasir*'))
            // openPaymentModal(penyewaanData.id); // Mungkin tidak perlu otomatis buka, tergantung alur user
        @endif
    };

    const toggleServiceDropdown = (penyewaanId) => {
        const serviceListEl = getEl(`service-list-${penyewaanId}`);
        const iconEl = getEl(`service-toggle-icon-${penyewaanId}`);
        if (serviceListEl.style.display === 'block') { serviceListEl.style.display = 'none'; iconEl.classList.replace('fa-chevron-up', 'fa-chevron-down'); }
        else { serviceListEl.style.display = 'block'; iconEl.classList.replace('fa-chevron-down', 'fa-chevron-up'); }
    };

    const removeServiceFromPenyewaan = async (penyewaanId, serviceId, serviceName) => {
        Swal.fire({
            title: 'Hapus Service?',
            text: `Yakin ingin menghapus "${serviceName}" dari penyewaan ini?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const res = await fetch(`{{ url('/kasir/penyewaan/') }}/${penyewaanId}/remove-service`, {
                        method: 'DELETE', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ service_id: serviceId })
                    });
                    const data = await res.json();
                    if (res.ok) { Swal.fire('Berhasil!', data.message, 'success'); fetchAndRenderMejas(); }
                    else { Swal.fire('Gagal!', data.message || 'Terjadi kesalahan.', 'error'); }
                } catch (error) { console.error('Error removing service:', error); Swal.fire('Error!', 'Terjadi kesalahan jaringan atau server saat menghapus service.', 'error'); }
            }
        });
    };

    const fetchAndRenderMejas = async () => {
        try {
            const res = await fetch('{{ route('kasir.api.penyewaanAktif') }}');
            const activeRentals = await res.json();
            currentActiveRentals = {}; activeRentals.forEach(p => currentActiveRentals[p.id] = p);

            document.querySelectorAll('[id^="meja-card-"]').forEach(card => {
                const mejaId = parseInt(card.id.replace('meja-card-', ''));
                const penyewaanForThisMeja = activeRentals.find(p => parseInt(p.meja_id) === mejaId);

                const statusMejaEl = card.querySelector(`#status-meja-${mejaId}`);
                let pesanBtnEl = card.querySelector(`#btn-pesan-${mejaId}`);
                const penyewaanDivEl = card.querySelector(`#penyewaan-${mejaId}`);
                const detailEl = getEl(`detail-${mejaId}`);

                if (penyewaanForThisMeja) {
                    const waktuSelesaiObj = penyewaanForThisMeja.waktu_selesai ? new Date(penyewaanForThisMeja.waktu_selesai) : null;
                    const now = getCalibratedNow();
                    const isTimeUpBackend = waktuSelesaiObj && (waktuSelesaiObj.getTime() - now.getTime() <= 0);

                    const currentMejaStatus = penyewaanForThisMeja.status_meja_saat_ini;

                    card.classList.remove('bg-neutral-600', 'bg-green-100', 'bg-red-200');
                    if (currentMejaStatus === 'dipakai') {
                        card.classList.add('bg-green-100');
                    } else if (currentMejaStatus === 'waktu_habis') {
                        card.classList.add('bg-red-200');
                    }
                    if (statusMejaEl) statusMejaEl.innerText = `Status: ${currentMejaStatus}`;
                    if (pesanBtnEl) pesanBtnEl.style.display = 'none';

                    const isSepuasnya = penyewaanForThisMeja.is_sepuasnya;
                    let timerDisplay = `⏳ <span id="countdown-${penyewaanForThisMeja.id}">--:--:--</span>`;
                    if (isSepuasnya) timerDisplay = `⏳ <span id="running-timer-${penyewaanForThisMeja.id}">00:00:00</span> (Main Sepuasnya)`;
                    else if (isTimeUpBackend) timerDisplay = `⏳ Waktu habis!`;

                    let actionButtonsHtml = '';
                    let deleteButtonHtml = '';

                    @if(Auth::check() && (Auth::user()->role == 'admin' || Auth::user()->role == 'supervisor'))
                        deleteButtonHtml = `
                            <button type="button" class="px-3 py-1 bg-red-200 hover:bg-red-300 text-red-900 rounded-md text-xs" onclick="event.preventDefault(); confirmDeletePenyewaan(${penyewaanForThisMeja.id}, '${penyewaanForThisMeja.meja_nama}');">
                                <i class="fa-solid fa-trash"></i> Hapus
                            </button>
                        `;
                    @endif

                    if (currentMejaStatus === 'waktu_habis') {
                        actionButtonsHtml = `
                            ${deleteButtonHtml}
                            <button type="button" class="px-3 py-1 bg-yellow-200 hover:bg-yellow-300 text-yellow-900 rounded-md text-xs" onclick="event.preventDefault(); openAddServiceModal(${penyewaanForThisMeja.id});">
                                <i class="fa-solid fa-wine-bottle"></i> Tambah Service
                            </button>
                            <span class="px-3 py-1 bg-red-100 text-red-700 rounded-md text-xs">Waktu Habis! Menunggu Pembayaran.</span>
                            <button type="button" class="px-3 py-1 bg-green-500 text-white rounded-md text-xs hover:bg-green-600" onclick="event.preventDefault(); openPaymentModal(${penyewaanForThisMeja.id});">
                                <i class="fa-solid fa-cash-register"></i> Bayar Sekarang
                            </button>
                        `;
                    } else { // status 'dipakai'
                        actionButtonsHtml = `
                            ${deleteButtonHtml}
                            @if(Auth::check() && (Auth::user()->role == 'admin' || Auth::user()->role == 'supervisor'))
                            <button type="button" class="px-3 py-1 bg-indigo-200 hover:bg-indigo-300 text-indigo-900 rounded-md text-xs" onclick="event.preventDefault(); openTransferMejaModal(${penyewaanForThisMeja.id});">
                                <i class="fa-solid fa-arrows-left-right-to-line"></i> Pindah Meja
                            </button>
                            @endif
                            <button type="button" class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-md text-xs" onclick="event.preventDefault(); openAddDurationModal(${penyewaanForThisMeja.id});">
                                <i class="fa-solid fa-stopwatch"></i> Tambah Waktu
                            </button>
                            <button type="button" class="px-3 py-1 bg-yellow-200 hover:bg-yellow-300 text-yellow-900 rounded-md text-xs" onclick="event.preventDefault(); openAddServiceModal(${penyewaanForThisMeja.id});">
                                <i class="fa-solid fa-wine-bottle"></i> Tambah Service
                            </button>
                            <button type="button" class="px-3 py-1 bg-green-500 text-white rounded-md text-xs hover:bg-green-600" onclick="event.preventDefault(); openPaymentModal(${penyewaanForThisMeja.id});">
                                <i class="fa-solid fa-cash-register"></i> Bayar
                            </button>
                        `;
                    }

                    let serviceDetailHtml = '';
                    if (penyewaanForThisMeja.service_detail && penyewaanForThisMeja.service_detail.length > 0) {
                        const serviceItems = penyewaanForThisMeja.service_detail.map(s => `
                            <li class="flex justify-between items-center py-0.5 text-sm ${ (s.subtotal || 0) > 0 ? 'text-gray-700' : 'text-gray-500 italic'}">
                                <span>${s.nama} (${s.jumlah}x) ${ (s.subtotal || 0) > 0 ? `- ${fmtRp(s.subtotal)}` : ''} ${ (s.subtotal || 0) === 0 && penyewaanForThisMeja.paket_id ? '(Paket)' : ''}</span>
                                <button type="button" class="text-red-500 hover:text-red-700 text-base" onclick="removeServiceFromPenyewaan(${penyewaanForThisMeja.id}, ${s.id}, '${s.nama}')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </li>
                        `).join('');
                        serviceDetailHtml = `
                            <div class="cursor-pointer flex justify-between items-center py-2 border-t mt-2 font-semibold text-gray-700" onclick="toggleServiceDropdown(${penyewaanForThisMeja.id})">
                                <span>Layanan Terkait (${penyewaanForThisMeja.service_detail.length})</span>
                                <i id="service-toggle-icon-${penyewaanForThisMeja.id}" class="fa-solid fa-chevron-down"></i>
                            </div>
                            <ul id="service-list-${penyewaanForThisMeja.id}" class="list-disc list-inside px-2 hidden">
                                ${serviceItems}
                            </ul>
                        `;
                    } else {
                        serviceDetailHtml = `
                            <div class="cursor-pointer flex justify-between items-center py-2 border-t mt-2 font-semibold text-gray-700" onclick="toggleServiceDropdown(${penyewaanForThisMeja.id})">
                                <span>Tidak ada Layanan Terkait</span>
                                <i id="service-toggle-icon-${penyewaanForThisMeja.id}" class="fa-solid fa-chevron-down"></i>
                            </div>
                            <ul id="service-list-${penyewaanForThisMeja.id}" class="list-disc list-inside px-2 hidden">
                                <li class="py-0.5 text-gray-500 italic">Belum ada layanan.</li>
                            </ul>
                        `;
                    }

                    let memberInfoHtml = '';
                    if (penyewaanForThisMeja.member_id) {
                        memberInfoHtml = `<p class="text-sm font-medium text-blue-700">Member: ${penyewaanForThisMeja.member_nama} (${penyewaanForThisMeja.member_kode})</p>`;
                    }

                    let paketInfoHtml = '';
                    if (penyewaanForThisMeja.paket_id && penyewaanForThisMeja.nama_paket) {
                         paketInfoHtml = `<p class="text-sm font-semibold text-purple-700">Paket: ${penyewaanForThisMeja.nama_paket}</p>`;
                    }

                    if (penyewaanDivEl) {
                        penyewaanDivEl.innerHTML = `
                            <div class="mt-3 bg-white rounded-lg shadow-sm p-3 border border-blue-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-700"><i class="fa-solid fa-user"></i> ${penyewaanForThisMeja.nama_penyewa}</p>
                                        ${memberInfoHtml}
                                        ${paketInfoHtml}
                                        <p class="text-xs text-gray-500"><i class="fa-solid fa-clock"></i> Mulai: ${fmtTime(penyewaanForThisMeja.waktu_mulai)} WIB</p>
                                        <p class="text-xs text-gray-500"><i class="fa-solid fa-hourglass-half"></i> Durasi: ${isSepuasnya ? 'Main Sepuasnya' : fmtDur(penyewaanForThisMeja.durasi_jam)}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-block ${currentMejaStatus === 'waktu_habis' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'} text-xs font-semibold px-2 py-1 rounded-full">${currentMejaStatus.toUpperCase().replace('_', ' ')}</span>
                                        <p class="text-sm font-bold text-red-700 mt-1">${timerDisplay}</p>
                                    </div>
                                </div>
                                ${serviceDetailHtml}
                                <div class="mt-3 flex flex-wrap gap-2 justify-end">${actionButtonsHtml}</div>
                            </div>
                        `;
                        const timerEl = isSepuasnya ? getEl(`running-timer-${penyewaanForThisMeja.id}`) : getEl(`countdown-${penyewaanForThisMeja.id}`);
                        if (timerEl) {
                            if (isSepuasnya) startRunningTimer(timerEl, penyewaanForThisMeja.waktu_mulai, penyewaanForThisMeja);
                            else if (currentMejaStatus === 'dipakai') startCountdown(timerEl, penyewaanForThisMeja.waktu_selesai, penyewaanForThisMeja);
                            else if (currentMejaStatus === 'waktu_habis') { timerEl.innerText = 'Waktu habis!'; timerEl.classList.add('text-red-700','font-bold'); }
                        }
                    }
                } else {
                    card.classList.remove('bg-green-100', 'bg-red-200', 'bg-yellow-100'); // Hapus warna merah juga jika ada
                    card.classList.add('bg-neutral-600');
                    if (statusMejaEl) statusMejaEl.innerText = 'Status: kosong';
                    if (penyewaanDivEl) penyewaanDivEl.innerHTML = '';

                    let currentPesanBtn = detailEl.querySelector(`#btn-pesan-${mejaId}`);
                    if (!currentPesanBtn) {
                        currentPesanBtn = document.createElement('button');
                        currentPesanBtn.id = `btn-pesan-${mejaId}`;
                        currentPesanBtn.className = 'w-full px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none';
                        currentPesanBtn.innerText = 'Pesan';
                        currentPesanBtn.onclick = (e) => { e.stopPropagation(); openModal(mejaId); };
                        detailEl.prepend(currentPesanBtn);
                    }
                    currentPesanBtn.style.display = 'block';

                    for (const id in countdownIntervals) {
                        if (currentActiveRentals[id] && currentActiveRentals[id].meja_id === mejaId) { clearInterval(countdownIntervals[id]); delete countdownIntervals[id]; }
                    }
                }

                if (activeCardId !== mejaId) {
                    detailEl.classList.add('hidden');
                } else {
                    detailEl.classList.remove('hidden');
                }
            });
        } catch (error) { console.error('Error fetching and rendering mejas:', error); }
    };

    const fetchPaymentDetails = async (penyewaanId) => {
        // Jika modal sudah terbuka, jangan buka lagi
        if (getEl('paymentModal').style.display === 'flex') return;
        
        // Dapatkan elemen tombol untuk menampilkan status loading
        const formPembayaran = getEl('formPembayaran');
        const submitBtn = formPembayaran.querySelector('button[type="submit"]');
        const qrisBtn = getEl('btn-pay-qris');
        
        // Nonaktifkan tombol dan tampilkan status loading
        submitBtn.disabled = true;
        qrisBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memuat...';
        qrisBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memuat...';

        const p = currentActiveRentals[penyewaanId];
        if (!p) {
            Swal.fire('Error', 'Detail penyewaan tidak ditemukan.', 'error');
            closePaymentModal();
            return;
        }

        getEl('payment_penyewaan_id').value = penyewaanId;
        getEl('kode_kupon').value = ''; // Selalu kosongkan kupon saat modal dibuka

        const serviceDetailsUntukModalBayar = (p.service_detail || []).filter(s => (s.subtotal || 0) > 0);
        const originalIsSepuasnya = p.is_sepuasnya; // Ini untuk label UI, bukan logika perhitungan

        // Fungsi internal untuk memuat dan memperbarui tampilan pembayaran dari backend
        const updatePaymentDisplayFromBackend = async (kuponCode = '') => {
            try {
                const url = `{{ url('/kasir/penyewaan') }}/${penyewaanId}/calculate-payment?${new URLSearchParams({ kode_kupon: kuponCode })}`;
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();

                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Gagal menghitung pembayaran dari backend.');
                }

                // Perbarui elemen UI berdasarkan data yang dikembalikan backend
                getEl('payment_meja_nama').innerText = p.meja_nama;
                getEl('payment_nama_penyewa').innerText = p.nama_penyewa;

                if (p.member_id) {
                    getEl('payment_member_info').innerText = `Member: ${p.member_nama} (${p.member_kode})`;
                    getEl('payment_member_info').style.display = 'block';
                } else {
                    getEl('payment_member_info').style.display = 'none';
                }

                if (p.paket_id && p.nama_paket) {
                    getEl('payment_paket_info').innerText = `Paket: ${p.nama_paket}`;
                    getEl('payment_paket_info').style.display = 'block';
                } else {
                    getEl('payment_paket_info').style.display = 'none';
                }
                
                // Cek apakah mode sepuasnya (termasuk paket dengan durasi 0)
                const isSepuasnyaOrPaketDurasiNol = originalIsSepuasnya || (p.paket_id && p.paket && (parseFloat(p.paket.isi_paket.durasi_jam || '1') === 0.0));
                getEl('payment_mode_sepuasnya_info').style.display = isSepuasnyaOrPaketDurasiNol ? 'block' : 'none';

                getEl('payment_waktu_mulai').innerText = fmtFullDt(p.waktu_mulai);
                getEl('payment_waktu_selesai_terjadwal').innerText = p.waktu_selesai ? fmtFullDt(p.waktu_selesai) : 'N/A'; // Tetap tampilkan jadwal jika ada
                getEl('payment_waktu_selesai_aktual').innerText = data.calculated_waktu_selesai ? fmtFullDt(data.calculated_waktu_selesai) : 'N/A';

                getEl('label_harga_sewa').innerText = p.paket_id ? 'Harga Paket' : 'Harga Per Jam';
                getEl('payment_harga_per_jam_or_paket').innerText = fmtRp(p.harga_per_jam);

                // Tampilkan info harga per jam aktual hanya jika mode sepuasnya dan harga berubah
                if (isSepuasnyaOrPaketDurasiNol && parseFloat(data.harga_per_jam_aktual) !== parseFloat(p.harga_per_jam)) {
                    getEl('payment_harga_per_jam_saat_ini_info').style.display = 'inline';
                    getEl('payment_harga_per_jam_aktual').innerText = fmtRp(data.harga_per_jam_aktual);
                } else {
                    getEl('payment_harga_per_jam_saat_ini_info').style.display = 'none';
                }


                getEl('payment_durasi').innerText = isSepuasnyaOrPaketDurasiNol ? `Dihitung ${fmtDur(data.calculated_durasi_jam)}` : fmtDur(data.calculated_durasi_jam);


                getEl('payment_subtotal_main').innerText = fmtRp(data.subtotal_sewa);
                getEl('payment_total_service').innerText = fmtRp(data.total_service_tambahan);
                
                // Hitung jumlah diskon dalam Rupiah untuk tampilan
                const totalSebelumDiskonTampilan = data.subtotal_sewa + data.total_service_tambahan;
                const diskonMemberAmountTampilan = (totalSebelumDiskonTampilan * data.diskon_member_persen) / 100;
                const diskonKuponAmountTampilan = (totalSebelumDiskonTampilan * data.diskon_kupon_persen) / 100;

                getEl('payment_diskon_member').innerText = `${fmtRp(diskonMemberAmountTampilan)} (${data.diskon_member_persen}%)`;
                getEl('payment_diskon_kupon').innerText = `${fmtRp(diskonKuponAmountTampilan)} (${data.diskon_kupon_persen}%)`;
                getEl('payment_total_final').innerText = fmtRp(data.final_total_bayar);

                const serviceDetailListEl = getEl('payment_service_detail');
                serviceDetailListEl.innerHTML = '';
                if (serviceDetailsUntukModalBayar && serviceDetailsUntukModalBayar.length > 0) {
                    serviceDetailsUntukModalBayar.forEach(s => {
                        const li = document.createElement('li');
                        li.innerText = `${s.nama} (${s.jumlah}x) - ${fmtRp(s.subtotal)}`;
                        serviceDetailListEl.appendChild(li);
                    });
                } else {
                    const li = document.createElement('li');
                    li.innerText = 'Tidak ada layanan tambahan.';
                    serviceDetailListEl.appendChild(li);
                }

            } catch (error) {
                Swal.fire('Error', 'Gagal memuat detail pembayaran: ' + error.message, 'error');
                console.error('Error fetching payment details:', error);
                closePaymentModal();
            } finally {
                // Aktifkan kembali tombol setelah selesai loading
                submitBtn.disabled = false;
                qrisBtn.disabled = false;
                submitBtn.innerHTML = 'Bayar Sekarang';
                qrisBtn.innerHTML = 'Bayar dengan QRIS';
            }
        };

        // Panggil fungsi untuk memuat data pertama kali saat modal dibuka
        await updatePaymentDisplayFromBackend();

        // Tambahkan event listener untuk input kupon dengan debounce
        getEl('kode_kupon').oninput = debounce(async function() {
            submitBtn.disabled = true; qrisBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menghitung...';
            qrisBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menghitung...';
            const kuponCode = this.value.trim();
            await updatePaymentDisplayFromBackend(kuponCode);
        }, 500);

        toggleModal('paymentModal', true);
    };

    const fetchAndRenderServicesForAdd = async () => {
        try {
            const res = await fetch('{{ route('api.services') }}');
            allAvailableServices = await res.json();
            const serviceListContainer = getEl('service_list_container'); serviceListContainer.innerHTML = '';

            allAvailableServices.forEach(s => {
                const row = document.createElement('div');
                row.className = 'flex items-center justify-between py-1 border-b last:border-b-0';
                row.innerHTML = `<span class="font-medium text-gray-800">${s.nama} (${fmtRp(s.harga)}) <span class="text-gray-500 text-xs">(Stok: ${s.stok})</span></span>
                                 <input type="number" data-service-id="${s.id}" data-service-price="${s.harga}" data-max-stock="${s.stok}"
                                        class="service-qty w-20 text-center border-gray-300 rounded-md shadow-sm sm:text-sm p-1" value="0" min="0" max="${s.stok}">`;
                serviceListContainer.appendChild(row);
            });

            serviceListContainer.querySelectorAll('.service-qty').forEach(input => {
                input.addEventListener('input', () => {
                    let total = 0;
                    serviceListContainer.querySelectorAll('.service-qty').forEach(qtyInput => {
                        const qty = parseInt(qtyInput.value) || 0;
                        const maxStock = parseInt(qtyInput.dataset.maxStock);

                        if (qty > maxStock) {
                            qtyInput.value = maxStock;
                            Swal.fire('Info', `Jumlah ${qtyInput.closest('div').querySelector('span').firstChild.textContent.trim()} melebihi stok yang tersedia (${maxStock}).`, 'info');
                            total += parseFloat(qtyInput.dataset.servicePrice) * maxStock;
                        } else {
                            total += parseFloat(qtyInput.dataset.servicePrice) * qty;
                        }
                    });
                    getEl('current_service_add_total').innerText = fmtRp(total);
                });
            });
        } catch (error) { Swal.fire('Error', 'Gagal memuat daftar service.', 'error'); console.error('Error fetching services:', error); }
    };

    const populatePaketsDropdown = (isMember = false) => {
        const paketSelect = getEl('paket_id_select');
        paketSelect.innerHTML = '<option value="">-- Pilih Paket --</option>';

        allAvailablePakets.forEach(paket => {
            const isMemberPaket = paket.nama_paket.toLowerCase().startsWith('member');

            if (!isMember && isMemberPaket) {
                return;
            }

            const option = document.createElement('option');
            option.value = paket.id;
            option.innerText = paket.nama_paket;
            paketSelect.appendChild(option);
        });
    };

    getEl('kode_member').addEventListener('input', debounce(async function() {
        const kodeMember = this.value.trim();
        const memberInfoPreview = getEl('member_info_preview');
        const namaPenyewaInput = getEl('nama_penyewa');
        
        memberData = { valid: false, nama_member: '', diskon_persen: 0 };

        if (kodeMember.length > 0) {
            try {
                const res = await fetch(`{{ route('api.member.validate') }}?kode_member=${encodeURIComponent(kodeMember)}`, {
                    headers: { 'Accept': 'application/json' }
                });

                let data;
                if (res.headers.get("content-type")?.includes("application/json")) {
                    data = await res.json();
                } else {
                    throw new Error("Server tidak mengembalikan JSON");
                }

                if (res.ok && data.valid) {
                    memberData = data;
                    memberInfoPreview.innerText = `Member: ${data.nama_member} (Diskon: ${data.diskon_persen}%)`;
                    memberInfoPreview.style.display = 'block';
                    memberInfoPreview.classList.remove('text-red-600');
                    memberInfoPreview.classList.add('text-green-600');

                    namaPenyewaInput.value = data.nama_member;
                    namaPenyewaInput.readOnly = true;

                    populatePaketsDropdown(true);
                } else {
                    memberInfoPreview.innerText = data.message || 'Kode Member tidak valid.';
                    memberInfoPreview.style.display = 'block';
                    memberInfoPreview.classList.remove('text-green-600');
                    memberInfoPreview.classList.add('text-red-600');

                    namaPenyewaInput.value = '';
                    namaPenyewaInput.readOnly = false;

                    populatePaketsDropdown(false);
                }
            } catch (error) {
                console.error('Error validating member:', error);
                memberInfoPreview.innerText = 'Gagal memvalidasi kode member.';
                memberInfoPreview.style.display = 'block';
                memberInfoPreview.classList.remove('text-green-600');
                memberInfoPreview.classList.add('text-red-600');

                namaPenyewaInput.value = '';
                namaPenyewaInput.readOnly = false;

                populatePaketsDropdown(false);
            }
        } else {
            memberInfoPreview.style.display = 'none';
            namaPenyewaInput.value = '';
            namaPenyewaInput.readOnly = false;

            populatePaketsDropdown(false);
        }
    }, 500));

       getEl('paket_id_select').addEventListener('change', function() {
        const selectedPaketId = this.value;
        const nonPaketOptionsDiv = getEl('non_paket_options');
        const isSepuasnyaCheckbox = getEl('is_sepuasnya');
        const durasiJamWrapper = getEl('durasi_jam_wrapper');
        const durasiJamInput = getEl('durasi_jam');
        const paketDeskripsiPreview = getEl('paket_deskripsi_preview');

        if (selectedPaketId) {
            const selectedPaket = allAvailablePakets.find(p => p.id == selectedPaketId);
            if (selectedPaket) {
                const isiPaket = selectedPaket.isi_paket;

                if (isiPaket.durasi_jam === 0) {
                    isSepuasnyaCheckbox.checked = true;
                    durasiJamWrapper.style.display = 'none';
                    durasiJamInput.removeAttribute('required');
                } else {
                    isSepuasnyaCheckbox.checked = false;
                    durasiJamWrapper.style.display = 'block';
                    durasiJamInput.setAttribute('required', 'required');
                    durasiJamInput.value = isiPaket.durasi_jam;
                }
                
                nonPaketOptionsDiv.style.display = 'none';
                isSepuasnyaCheckbox.disabled = true;
                durasiJamInput.disabled = true;

                let descHtml = []; 

                if (isiPaket.harga_paket) descHtml.push(`<p><strong>Harga:</strong> ${fmtRp(isiPaket.harga_paket)}</p>`);
                if (isiPaket.durasi_jam !== undefined) {
                    if (isiPaket.durasi_jam > 0) descHtml.push(`<p><strong>Durasi:</strong> ${fmtDur(isiPaket.durasi_jam)}</p>`);
                    else descHtml.push(`<p><strong>Durasi:</strong> Main Sepuasnya</p>`);
                }
                
                if (isiPaket.deskripsi_tambahan) {
                    descHtml.push(`<p class="mt-2 text-gray-700"><strong>Deskripsi Paket:</strong> ${isiPaket.deskripsi_tambahan}</p>`);
                }

                if (isiPaket.services && isiPaket.services.length > 0) {
                    let serviceListHtml = '<p class="mt-2 mb-1 text-gray-700"><strong>Termasuk Layanan:</strong></p><ul class="list-disc list-inside ml-4 text-gray-600">';
                    isiPaket.services.forEach(s => {
                        const serviceName = s.nama || 'Nama Service Tidak Diketahui';
                        const serviceCount = s.jumlah || 0;
                        serviceListHtml += `<li>${serviceName} (${serviceCount}x)</li>`;
                    });
                    serviceListHtml += '</ul>';
                    descHtml.push(serviceListHtml);
                } else {
                    descHtml.push('<p class="mt-2 text-gray-600">Tidak ada layanan tambahan dalam paket ini.</p>');
                }
                
                paketDeskripsiPreview.innerHTML = descHtml.join('');
                paketDeskripsiPreview.style.display = 'block';

                // Form action akan ke initiatePemesanan, bukan lagi langsung pesanPaket
            }
        } else {
            nonPaketOptionsDiv.style.display = 'block';
            isSepuasnyaCheckbox.disabled = false;
            durasiJamInput.disabled = false;
            durasiJamInput.value = '';
            isSepuasnyaCheckbox.checked = false;
            durasiJamWrapper.style.display = 'block';
            durasiJamInput.required = true;

            paketDeskripsiPreview.style.display = 'none';
            paketDeskripsiPreview.innerHTML = '';

            // Form action akan ke initiatePemesanan, bukan lagi langsung pesanDurasi
        }
    });

    getEl('is_sepuasnya').addEventListener('change', function() {
        if (getEl('paket_id_select').value) return; // Jangan override jika paket dipilih
        const durasiWrapper = getEl('durasi_jam_wrapper');
        const durasiInput = getEl('durasi_jam');
        const paketSelect = getEl('paket_id_select');
        const paketDeskripsiPreview = getEl('paket_deskripsi_preview');

        if (this.checked) {
            durasiWrapper.style.display = 'none';
            durasiInput.removeAttribute('required');
            durasiInput.value = '';
            paketSelect.style.display = 'none'; // Sembunyikan pilihan paket jika sepuasnya
            paketDeskripsiPreview.style.display = 'none';
            paketDeskripsiPreview.innerHTML = '';
            // Form action akan ke initiatePemesanan
        } else {
            durasiWrapper.style.display = 'block';
            durasiInput.setAttribute('required', 'required');
            paketSelect.style.display = 'block'; // Tampilkan lagi pilihan paket
            populatePaketsDropdown(memberData.valid);
            // Form action akan ke initiatePemesanan
        }
    });

    // MODIFIKASI: Handler submit formPesan untuk initiatePemesanan
    getEl('formPesan').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';

        try {
            const formData = new FormData(form);
            const mejaId = formData.get('meja_id');
            const isSepuasnyaChecked = getEl('is_sepuasnya').checked;
            const paketId = formData.get('paket_id');

            let tipePesanan;
            if (paketId) {
                tipePesanan = 'paket';
            } else if (isSepuasnyaChecked) {
                tipePesanan = 'sepuasnya';
            } else {
                tipePesanan = 'durasi';
            }
            formData.append('tipe_pesanan', tipePesanan);

            const res = await fetch(form.action, { // form.action sudah disetel ke initiatePemesanan
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                // Swal.fire('Berhasil!', data.message, 'success'); // Tidak perlu Swal di sini, langsung ke konfirmasi
                closeModal(); // Tutup modal pemesanan
                fetchAndRenderMejas(); // Perbarui status meja menjadi 'dipakai'

                // Buka modal konfirmasi dengan data yang dikembalikan backend
                openConfirmPemesananModal(data.pemesanan_data);

            } else {
                let errorMessage = data.message || 'Terjadi kesalahan.';
                if (data.errors) {
                    const errorList = Object.values(data.errors).map(err => `<li>${err.join(', ')}</li>`).join('');
                    errorMessage += `<ul class="text-left mt-2 list-disc list-inside">${errorList}</ul>`;
                }
                Swal.fire('Gagal!', errorMessage, 'error');
            }
        } catch (error) {
            console.error('Error submitting form initiate:', error);
            Swal.fire('Error!', 'Terjadi kesalahan jaringan atau server saat memulai proses pemesanan.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Mulai Proses';
        }
    });

    // BARU: Handler untuk konfirmasi pemesanan
    getEl('btn-confirm-pesan').addEventListener('click', async () => {
        if (!tempPemesananData) {
            Swal.fire('Error', 'Data pemesanan tidak ditemukan untuk dikonfirmasi.', 'error');
            return;
        }

        const confirmBtn = getEl('btn-confirm-pesan');
        const cancelBtn = getEl('btn-cancel-confirm-pesan');
        confirmBtn.disabled = true;
        cancelBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memulai...';

        try {
            const res = await fetch('{{ route('kasir.confirmPemesanan') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(tempPemesananData) // Kirim data pemesanan dari initiate
            });
            const data = await res.json();

            if (data.success) {
                Swal.fire('Berhasil!', data.message, 'success');
                closeConfirmPemesananModal();
                fetchAndRenderMejas(); // Perbarui dashboard untuk menampilkan penyewaan aktif
            } else {
                let errorMessage = data.message || 'Gagal memulai billing.';
                if (data.errors) {
                    const errorList = Object.values(data.errors).map(err => `<li>${err.join(', ')}</li>`).join('');
                    errorMessage += `<ul class="text-left mt-2 list-disc list-inside">${errorList}</ul>`;
                }
                Swal.fire('Gagal!', errorMessage, 'error');
                // Jika konfirmasi gagal, reset meja
                await resetMejaStatus(tempPemesananData.meja_id);
            }
        } catch (error) {
            console.error('Error confirming pemesanan:', error);
            Swal.fire('Error!', 'Terjadi kesalahan jaringan atau server saat mengkonfirmasi pemesanan.', 'error');
            await resetMejaStatus(tempPemesananData.meja_id);
        } finally {
            confirmBtn.disabled = false;
            cancelBtn.disabled = false;
            confirmBtn.innerHTML = 'Ya, Mulai Billing';
            tempPemesananData = null; // Reset data sementara
        }
    });

    // BARU: Handler untuk batal konfirmasi dan reset meja
    getEl('btn-cancel-confirm-pesan').addEventListener('click', async () => {
        if (!tempPemesananData) {
            Swal.fire('Error', 'Tidak ada pemesanan untuk dibatalkan.', 'error');
            return;
        }
        const mejaIdToReset = tempPemesananData.meja_id;
        
        const confirmBtn = getEl('btn-confirm-pesan');
        const cancelBtn = getEl('btn-cancel-confirm-pesan');
        confirmBtn.disabled = true;
        cancelBtn.disabled = true;
        cancelBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mereset...';

        try {
            await resetMejaStatus(mejaIdToReset);
            Swal.fire('Dibatalkan!', 'Pemesanan dibatalkan dan meja direset.', 'info');
            closeConfirmPemesananModal();
            fetchAndRenderMejas(); // Perbarui dashboard
        } catch (error) {
            console.error('Error canceling pemesanan:', error);
            Swal.fire('Error!', 'Gagal membatalkan pemesanan dan mereset meja.', 'error');
        } finally {
            confirmBtn.disabled = false;
            cancelBtn.disabled = false;
            cancelBtn.innerHTML = 'Batal & Reset Meja';
            tempPemesananData = null; // Reset data sementara
        }
    });

    // BARU: Fungsi untuk mereset status meja via API
    const resetMejaStatus = async (mejaId) => {
        try {
            const res = await fetch(`{{ url('/kasir/meja') }}/${mejaId}/reset-status`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            });
            const data = await res.json();
            if (!data.success) {
                console.error('Failed to reset meja status via API:', data.message);
                // Swal.fire('Error', 'Gagal mereset status meja di backend: ' + (data.message || 'Unknown error'), 'error');
            }
            return data.success;
        } catch (error) {
            console.error('Network error resetting meja status:', error);
            // Swal.fire('Error', 'Kesalahan jaringan saat mereset meja.', 'error');
            return false;
        }
    };


    getEl('formAddDuration').addEventListener('submit', async (e) => {
        e.preventDefault(); const pId = getEl('add_duration_penyewaan_id').value; const addDur = getEl('additional_durasi_jam').value;
        const submitBtn = e.target.querySelector('button[type="submit"]'); submitBtn.disabled = true; submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menambah...';
        try {
            const res = await fetch(`{{ url('/kasir/penyewaan/') }}/${pId}/add-duration`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ additional_durasi_jam: addDur }) });
            const data = await res.json();
            if (data.success) { Swal.fire('Berhasil!', data.message, 'success'); closeAddDurationModal(); fetchAndRenderMejas(); }
            else { Swal.fire('Gagal!', data.message || 'Terjadi kesalahan.', 'error'); }
        } catch (error) { Swal.fire('Error!', 'Terjadi kesalahan jaringan atau server saat menambah durasi.', 'error'); console.error('Error adding duration:', error); }
        finally { submitBtn.disabled = false; submitBtn.innerHTML = 'Tambah Durasi'; }
    });

    getEl('formAddService').addEventListener('submit', async (e) => {
        e.preventDefault(); const pId = getEl('add_service_penyewaan_id').value;
        const selSrv = [];
        let stockExceeded = false;
        Array.from(getEl('service_list_container').querySelectorAll('.service-qty')).forEach(input => {
            const qty = parseInt(input.value) || 0;
            const serviceId = input.dataset.serviceId;
            const maxStock = parseInt(input.dataset.maxStock);
            const serviceName = input.closest('div').querySelector('span').firstChild.textContent.trim();

            if (qty > 0) {
                if (qty > maxStock) {
                    Swal.fire('Info', `Jumlah ${serviceName} (${qty}) melebihi stok yang tersedia (${maxStock}). Mohon koreksi.`, 'info');
                    stockExceeded = true;
                    return;
                }
                selSrv.push({ service_id: serviceId, jumlah: qty });
            }
        });

        if (stockExceeded) return;
        if (selSrv.length === 0) { Swal.fire('Info', 'Pilih setidaknya satu service untuk ditambahkan.', 'info'); return; }

        const submitBtn = e.target.querySelector('button[type="submit"]'); submitBtn.disabled = true; submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menambah...';
        try {
            const res = await fetch(`{{ url('/kasir/penyewaan/') }}/${pId}/add-service`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ services: selSrv }) });
            const data = await res.json();
            if (data.success) { Swal.fire('Berhasil!', data.message, 'success'); closeAddServiceModal(); fetchAndRenderMejas(); }
            else { Swal.fire('Gagal!', data.message || 'Terjadi kesalahan.', 'error'); }
        } catch (error) { Swal.fire('Error!', 'Terjadi kesalahan jaringan atau server saat menambah service.', 'error'); console.error('Error adding service:', error); }
        finally { submitBtn.disabled = false; submitBtn.innerHTML = 'Tambah Service'; }
    });

    getEl('formPembayaran').addEventListener('submit', async (e) => {
        e.preventDefault();
        const pId = getEl('payment_penyewaan_id').value;
        const kupon = getEl('kode_kupon').value; // Ambil kode kupon dari input
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const qrisBtn = getEl('btn-pay-qris'); // Dapatkan juga tombol QRIS

        submitBtn.disabled = true;
        qrisBtn.disabled = true; // Nonaktifkan tombol QRIS juga
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';

        try {
            const res = await fetch(`{{ url('/kasir/penyewaan/') }}/${pId}/bayar`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ kode_kupon: kupon }) // Kirim kode kupon ke backend
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire('Berhasil!', data.message + ' Total bayar: ' + fmtRp(data.total_bayar), 'success');
                closePaymentModal();
                if (countdownIntervals[pId]) { clearInterval(countdownIntervals[pId]); delete countdownIntervals[pId]; }
                fetchAndRenderMejas();
            } else {
                Swal.fire('Gagal!', data.message || 'Terjadi kesalahan.', 'error');
                console.error('Pembayaran gagal:', data);
            }
        } catch (error) {
            Swal.fire('Error!', 'Terjadi kesalahan jaringan atau server saat memproses pembayaran.', 'error');
            console.error('Error submitting payment:', error);
        } finally {
            submitBtn.disabled = false;
            qrisBtn.disabled = false; // Aktifkan kembali tombol QRIS
            submitBtn.innerHTML = 'Bayar Sekarang';
        }
    });

    const handleQrisPayment = async () => {
        const penyewaanId = getEl('payment_penyewaan_id').value;
        const kuponCode = getEl('kode_kupon').value; // Ambil kode kupon dari input
        const qrisButton = getEl('btn-pay-qris');
        const normalPayButton = document.querySelector('#formPembayaran button[type="submit"]');

        qrisButton.disabled = true;
        normalPayButton.disabled = true;
        qrisButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses QRIS...';

        try {
            const res = await fetch(`{{ url('/kasir/penyewaan/') }}/${penyewaanId}/bayar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    kode_kupon: kuponCode, // Kirim kode kupon ke backend
                    is_qris: true
                })
            });

            const data = await res.json();

            if (res.ok) {
                Swal.fire({ icon: 'success', title: 'Pembayaran QRIS Berhasil!', text: data.message + ' Total bayar: ' + fmtRp(data.total_bayar), confirmButtonText: 'OK' });
                closePaymentModal();
                if (countdownIntervals[penyewaanId]) { clearInterval(countdownIntervals[penyewaanId]); delete countdownIntervals[penyewaanId]; }
                fetchAndRenderMejas();
            } else {
                Swal.fire({ icon: 'error', title: 'Pembayaran QRIS Gagal!', text: data.message || 'Terjadi kesalahan saat memproses pembayaran QRIS.', confirmButtonText: 'OK' });
                console.error('Pembayaran QRIS gagal:', data);
            }
        } catch (error) {
            Swal.fire({ icon: 'error', title: 'Terjadi Kesalahan Jaringan!', text: 'Terjadi kesalahan jaringan atau server saat memproses pembayaran QRIS.', confirmButtonText: 'OK' });
            console.error('Error submitting QRIS payment:', error);
        } finally {
            qrisButton.disabled = false;
            normalPayButton.disabled = false;
            qrisButton.innerHTML = 'Bayar dengan QRIS';
        }
    };

    // BARU: Handler untuk submit formTransferMeja
    getEl('formTransferMeja').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memindahkan...';

        const penyewaanId = getEl('transfer_penyewaan_id').value;
        const mejaBaruId = getEl('meja_baru_id').value;

        try {
            const res = await fetch(`{{ url('/kasir/penyewaan') }}/${penyewaanId}/transfer-meja`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ meja_baru_id: mejaBaruId })
            });
            const data = await res.json();

            if (data.success) {
                Swal.fire('Berhasil!', data.message, 'success');
                closeTransferMejaModal();
                fetchAndRenderMejas(); // Perbarui tampilan dashboard
            } else {
                let errorMessage = data.message || 'Gagal memindahkan meja.';
                if (data.errors) {
                    const errorList = Object.values(data.errors).map(err => `<li>${err.join(', ')}</li>`).join('');
                    errorMessage += `<ul class="text-left mt-2 list-disc list-inside">${errorList}</ul>`;
                }
                Swal.fire('Gagal!', errorMessage, 'error');
            }
        } catch (error) {
            console.error('Error transferring meja:', error);
            Swal.fire('Error!', 'Terjadi kesalahan jaringan atau server saat memindahkan meja.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Pindahkan Meja';
        }
    });


    const confirmDeletePenyewaan = async (penyewaanId, mejaNama) => {
        if (!['admin', 'supervisor'].includes(userRole)) {
            Swal.fire({ icon: 'error', title: 'Akses Ditolak!', text: 'Penghapusan meja hanya bisa dilakukan oleh Admin atau Supervisor.', confirmButtonText: 'OK' });
            return;
        }

        Swal.fire({
            title: 'Yakin menghapus penyewaan?',
            text: `Anda akan menghapus penyewaan untuk meja "${mejaNama}". Tindakan ini tidak dapat dibatalkan dan layanan yang digunakan akan dikembalikan stoknya.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (result.isConfirmed) {
                performDeletePenyewaan(penyewaanId);
            }
        });
    };

    const performDeletePenyewaan = async (penyewaanId) => {
        try {
            const res = await fetch(`{{ url('/kasir/penyewaan/') }}/${penyewaanId}/delete`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const data = await res.json();
            if (res.ok && data.success) {
                Swal.fire('Dihapus!', data.message, 'success');
                if (countdownIntervals[penyewaanId]) { clearInterval(countdownIntervals[penyewaanId]); delete countdownIntervals[penyewaanId]; }
                fetchAndRenderMejas();
            } else { Swal.fire('Gagal!', data.message || 'Terjadi kesalahan saat menghapus.', 'error'); }
        } catch (error) { console.error('Error deleting penyewaan:', error); Swal.fire('Error!', 'Terjadi kesalahan jaringan atau server.', 'error'); }
    };

    document.addEventListener('DOMContentLoaded', () => {
        getEl('formPesan').action = '{{ route('kasir.initiatePemesanan') }}'; // Pastikan aksi formPesan ke initiate
        populatePaketsDropdown(memberData.valid);
        fetchAndRenderMejas();
        setInterval(fetchAndRenderMejas, 5000);
    });
    
    let activeCardId = null;

    function toggleDetail(mejaId) {
        const detailEl = document.getElementById(`detail-${mejaId}`);

        if (activeCardId === mejaId) {
            detailEl.classList.toggle("hidden");
            if (detailEl.classList.contains("hidden")) {
                activeCardId = null;
            }
        } else {
            if (activeCardId !== null) {
                const prevDetailEl = document.getElementById(`detail-${activeCardId}`);
                if (prevDetailEl) prevDetailEl.classList.add("hidden");
            }
            detailEl.classList.remove("hidden");
            activeCardId = mejaId;
        }
    }
</script>
@endsection