@extends('default')

@section('title', 'Dashboard Pemandu')

@section('content')

{{-- Pastikan Toastr dan SweetAlert2 di-include di default.blade.php atau di sini --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
{{-- Jika Anda menggunakan SweetAlert2 untuk error message, pastikan juga sudah di-include --}}
{{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}


<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4 text-black">Dashboard Pemandu</h1>

    <div id="meja-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 text-black">
        @foreach ($mejas as $meja)
        <div id="meja-card-{{ $meja->id }}" class="p-4 border rounded shadow @if($meja->status === 'dipakai') bg-red-100 @else bg-green-100 @endif">
            <h2 class="text-lg font-semibold">{{ $meja->nama_meja }}</h2>
            <p id="status-meja-{{ $meja->id }}">Status: {{ $meja->status }}</p>

            @if ($meja->status === 'kosong')
            <button id="btn-pesan-{{ $meja->id }}" data-meja-id="{{ $meja->id }}" class="mt-2 btn btn-primary btn-pesan-meja">Pesan</button>
            @endif
            <div id="penyewaan-{{ $meja->id }}"></div>
        </div>
        @endforeach
    </div>
</div>

{{-- Modal Pemesanan --}}
<div id="pesanModal" style="display:none" class="modal-overlay">
    <div class="modal-content">
        <h2 class="text-xl font-bold mb-4">Pemesanan Meja</h2>
        <form id="formPesan" method="POST">
            @csrf
            <input type="hidden" name="meja_id" id="modal_meja_id">

            <!-- Nama Penyewa -->
            <div class="mb-3">
                <label for="nama_penyewa" class="form-label">Nama Penyewa</label>
                <input type="text" name="nama_penyewa" id="nama_penyewa" required class="form-input">
            </div>

            <!-- Checkbox Member -->
            <div class="mb-3">
                <label class="inline-flex items-center">
                    <input type="checkbox" id="is_member" name="is_member" class="form-checkbox h-5 w-5 text-blue-600">
                    <span class="ml-2 text-gray-700">Member</span>
                </label>
            </div>

            <!-- No. Telp (hanya muncul kalau member dicentang) -->
            <div class="mb-3" id="no_telp_wrapper" style="display:none;">
                <label for="no_telp" class="form-label">No. Telepon</label>
                <input type="text" name="no_telp" id="no_telp" class="form-input" placeholder="Masukkan nomor telepon">
                <p id="diskon_info" class="text-green-600 text-sm mt-1" style="display:none;">
                    Anda mendapatkan potongan 5%!
                </p>
            </div>

            <!-- Pilih Paket -->
            <div class="mb-3">
                <label for="paket_id_select" class="form-label">Pilih Paket (Opsional)</label>
                <select name="paket_id" id="paket_id_select" class="form-input">
                    <option value="">-- Pilih Paket --</option>
                    {{-- Options will be loaded by JS --}}
                </select>
                <p id="paket_deskripsi_preview" class="text-gray-400 text-xs mt-1 italic" style="display:none;"></p>
            </div>

            <!-- Non Paket Options -->
            <div id="non_paket_options">
                <div class="mb-3">
                    <label class="inline-flex items-center">
                        <input type="checkbox" id="is_sepuasnya" name="is_sepuasnya" class="form-checkbox h-5 w-5 text-blue-600">
                        <span class="ml-2 text-gray-700">Main Sepuasnya</span>
                    </label>
                </div>
                <div class="mb-3" id="durasi_jam_wrapper">
                    <label for="durasi_jam" class="form-label">Durasi (Jam, contoh: 1.5 untuk 1 jam 30 menit)</label>
                    <input type="number" name="durasi_jam" id="durasi_jam" min="0.01" step="0.01" class="form-input">
                </div>
            </div>

            <!-- Buttons -->
            <div class="modal-footer">
                <button type="button" onclick="closeModal()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary">Mulai</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Tambah Durasi --}}
<div id="addDurationModal" style="display:none" class="modal-overlay">
    <div class="modal-content">
        <h2 class="text-xl font-bold mb-4">Tambah Durasi Meja <span id="add_duration_meja_nama"></span></h2>
        <form id="formAddDuration">
            @csrf
            <input type="hidden" name="penyewaan_id" id="add_duration_penyewaan_id">
            <p class="mb-2">Penyewa: <strong id="add_duration_nama_penyewa"></strong></p>
            <p class="mb-2">Durasi Saat Ini: <strong id="add_duration_current_durasi"></strong></p>
            <div class="mb-3">
                <label for="additional_durasi_jam" class="form-label">Durasi Tambahan (Jam)</label>
                <input type="number" name="additional_durasi_jam" id="additional_durasi_jam" required min="0.01" step="0.01" class="form-input">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAddDurationModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Tambah Durasi</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Tambah Service --}}
<div id="addServiceModal" style="display:none" class="modal-overlay">
    <div class="modal-content max-w-lg">
        <h2 class="text-xl font-bold mb-4">Tambah Service Meja <span id="add_service_meja_nama"></span></h2>
        <form id="formAddService">
            @csrf
            <input type="hidden" name="penyewaan_id" id="add_service_penyewaan_id">
            <p class="mb-2">Penyewa: <strong id="add_service_nama_penyewa"></strong></p>
            <p class="mb-2">Total Service Saat Ini: <strong id="add_service_current_total"></strong></p>

            <h3 class="font-semibold mt-4 mb-2">Daftar Service Tersedia:</h3>
            <div id="service_list_container" class="max-h-64 overflow-y-auto border rounded p-2 mb-4">
                <!-- Services will be loaded here via JS -->
            </div>

            <p class="text-lg font-bold">Total Tambahan Service: <strong id="current_service_add_total" class="text-blue-600">Rp 0</strong></p>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAddServiceModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Tambah Service</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL PEMBAYARAN DIHAPUS DARI PANDU --}}


<style>
    /* Basic Modal Styles */
    .modal-overlay { position: fixed; inset: 0; background-color: rgba(0,0,0,0.75); display: flex; align-items: center; justify-content: center; z-index: 50; }
    .modal-content { background-color: white; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 10px 15px rgba(0,0,0,0.1); width: 100%; max-width: 28rem; color: black; }
    .modal-footer { display: flex; justify-content: flex-end; margin-top: 1rem; gap: 0.5rem; }
    .form-label { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem; }
    .form-input { display: block; width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #D1D5DB; border-radius: 0.375rem; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); color: #1F2937; font-size: 1rem; }
    .btn { padding: 0.5rem 1rem; border-radius: 0.375rem; font-weight: 500; font-size: 0.875rem; cursor: pointer; transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out; }
    .btn-primary { background-color: #3B82F6; color: white; border: 1px solid transparent; } .btn-primary:hover { background-color: #2563EB; }
    .btn-secondary { background-color: #F3F4F6; color: #374151; border: 1px solid #D1D5DB; } .btn-secondary:hover { background-color: #E5E7EB; }
    .btn-success { background-color: #10B981; color: white; border: 1px solid transparent; } .btn-success:hover { background-color: #059669; }
    .btn-xs { padding: 0.25rem 0.75rem; font-size: 0.75rem; }

    /* Styles for Service Dropdown */
    .service-dropdown-header { cursor: pointer; display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-top: 1px solid #E5E7EB; margin-top: 0.5rem; font-weight: 600; color: #4B5563; }
    .service-list { display: none; padding: 0.5rem 0; border-bottom: 1px solid #E5E7EB; }
    .service-item { display: flex; justify-content: space-between; align-items: center; padding: 0.25rem 0; font-size: 0.875rem; color: #6B7280; }
    .service-item-remove-btn { background: none; border: none; color: #EF4444; cursor: pointer; font-size: 1rem; padding: 0.25rem; line-height: 1; transition: color 0.15s ease-in-out; } .service-item-remove-btn:hover { color: #DC2626; }
</style>


@if($errors->any())
<script>
    // Pastikan Swal.fire terdefinisi, mungkin perlu include SweetAlert2 di default.blade.php
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: "{{ $errors->first() }}",
            confirmButtonText: 'OK'
        });
    } else {
        toastr.error("{{ $errors->first() }}", 'Error');
    }
</script>
@endif

<script>
    const serverTime = new Date("{{ $serverTime }}");
    const clientTimeAtLoad = new Date();
    const serverClientOffset = serverTime.getTime() - clientTimeAtLoad.getTime();

    const getCalibratedNow = () => new Date(new Date().getTime() + serverClientOffset);
    const countdownIntervals = {};
    let currentActiveRentals = {};
    let allAvailableServices = [];
    let allAvailablePakets = []; // To store fetched pakets

    // --- Utility Functions ---
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
        if (hrs === 0 && mins === 0 && decHrs > 0) return 'Kurang dari 1 Menit';
        return res.trim();
    };
    const fmtRp = (amt) => new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',minimumFractionDigits:0}).format(amt);
    const debounce = (func, delay) => { let timeout; return function(...args) { const context=this; clearTimeout(timeout); timeout=setTimeout(()=>func.apply(context,args),delay); }; };

    // --- Modal Control Functions (Didefinisikan secara global) ---
    window.openModal = async (mejaId) => { // Made async to load pakets
        getEl('modal_meja_id').value = mejaId;
        getEl('formPesan').reset();
        getEl('paket_id_select').value = ''; // Reset paket selection
        getEl('paket_deskripsi_preview').style.display = 'none'; // Hide preview

        // Show non-paket options by default
        getEl('non_paket_options').style.display = 'block';
        getEl('is_sepuasnya').checked = false;
        getEl('durasi_jam_wrapper').style.display = 'block';
        getEl('durasi_jam').required = true;

        getEl('formPesan').action = '{{ route('pemandu.pesanDurasi') }}'; // Set default action for Pemandu

        await fetchAndPopulatePakets(); // Fetch and populate pakets for the dropdown
        toggleModal('pesanModal', true);
    };
    window.closeModal = () => toggleModal('pesanModal', false);

    window.openAddDurationModal = (penyewaanId) => {
        const p=currentActiveRentals[penyewaanId]; if (!p || p.is_sepuasnya) { toastr.error('Tidak bisa menambah durasi untuk penyewaan ini.'); return; }
        getEl('add_duration_penyewaan_id').value = penyewaanId; getEl('add_duration_meja_nama').innerText = p.meja_nama; getEl('add_duration_nama_penyewa').innerText = p.nama_penyewa; getEl('add_duration_current_durasi').innerText = fmtDur(p.durasi_jam); getEl('formAddDuration').reset(); toggleModal('addDurationModal', true);
    };
    window.closeAddDurationModal = () => toggleModal('addDurationModal', false);

    window.openAddServiceModal = async (penyewaanId) => {
        const p=currentActiveRentals[penyewaanId]; if (!p) { toastr.error('Penyewaan tidak ditemukan.'); return; }
        getEl('add_service_penyewaan_id').value = penyewaanId; getEl('add_service_meja_nama').innerText = p.meja_nama; getEl('add_service_nama_penyewa').innerText = p.nama_penyewa; getEl('add_service_current_total').innerText = fmtRp(p.total_service); getEl('current_service_add_total').innerText = fmtRp(0);
        await fetchAndRenderServicesForAdd();
        toggleModal('addServiceModal', true);
    };
    window.closeAddServiceModal = () => toggleModal('addServiceModal', false);

    // --- Timer Logic ---
    const startCountdown = (el, waktuSelesaiStr, penyewaanData) => {
        const waktuSelesai = new Date(waktuSelesaiStr); const pId = penyewaanData.id;
        if (countdownIntervals[pId]) clearInterval(countdownIntervals[pId]);
        const interval = setInterval(() => {
            const now = getCalibratedNow(); const dist = waktuSelesai.getTime() - now.getTime();
            if (dist <= 0) { clearInterval(interval); el.innerText = 'Waktu habis!'; el.classList.add('text-red-700','font-bold'); handleTimeUpUI(penyewaanData); }
            else { const h=String(Math.floor((dist/(1000*60*60)))).padStart(2,'0'); const m=String(Math.floor((dist/(1000*60))%60)).padStart(2,'0'); const s=String(Math.floor((dist/1000)%60)).padStart(2,'0'); el.innerText=`${h}:${m}:${s}`; }
        }, 1000); countdownIntervals[pId] = interval;
    };

    const startRunningTimer = (el, waktuMulaiStr, penyewaanData) => {
        const waktuMulai = new Date(waktuMulaiStr); const pId = penyewaanData.id;
        if (countdownIntervals[pId]) clearInterval(countdownIntervals[pId]);
        const interval = setInterval(() => {
            const now = getCalibratedNow(); const elapsed = now.getTime() - waktuMulai.getTime();
            const h=String(Math.floor((elapsed/(1000*60*60)))).padStart(2,'0'); const m=String(Math.floor((elapsed/(1000*60))%60)).padStart(2,'0'); const s=String(Math.floor((elapsed/1000)%60)).padStart(2,'0'); el.innerText=`${h}:${m}:${s}`;
        }, 1000); countdownIntervals[pId] = interval;
    };

    const handleTimeUpUI = (penyewaanData) => {
        const mejaId = penyewaanData.meja_id; const actionsCont = document.querySelector(`#penyewaan-${mejaId} .flex-wrap`);
        if (actionsCont) {
            actionsCont.innerHTML = `
                <a href="#" class="btn btn-xs bg-yellow-200 hover:bg-yellow-300 text-yellow-900 px-3 py-1 rounded" onclick="event.preventDefault(); openAddServiceModal(${penyewaanData.id});">
                    <i class="fa-solid fa-wine-bottle"></i> Tambah Service
                </a>
                {{-- Tombol "Bayar Sekarang" dihapus untuk Pemandu --}}
            `;
        }
        // openPaymentModal(penyewaanData.id); // Dihapus karena Pemandu tidak memproses pembayaran
    };

    // --- Service Dropdown & Removal Logic ---
    window.toggleServiceDropdown = (penyewaanId) => { // Didefinisikan secara global
        const serviceListEl = getEl(`service-list-${penyewaanId}`);
        const iconEl = getEl(`service-toggle-icon-${penyewaanId}`);
        if (serviceListEl.style.display === 'block') { serviceListEl.style.display = 'none'; iconEl.classList.replace('fa-chevron-up', 'fa-chevron-down'); }
        else { serviceListEl.style.display = 'block'; iconEl.classList.replace('fa-chevron-down', 'fa-chevron-up'); }
    };

    window.removeServiceFromPenyewaan = async (penyewaanId, serviceId, serviceName) => { // Didefinisikan secara global
        if (!confirm(`Yakin ingin menghapus "${serviceName}" dari penyewaan ini?`)) return;
        try {
            const res = await fetch(`{{ url('/pemandu/penyewaan/') }}/${penyewaanId}/remove-service`, {
                method: 'DELETE', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ service_id: serviceId })
            });
            const data = await res.json();
            if (res.ok) { toastr.success(data.message); fetchAndRenderMejas(); }
            else { toastr.error('Gagal menghapus service: ' + (data.message || 'Terjadi kesalahan.')); }
        } catch (error) { console.error('Error removing service:', error); toastr.error('Terjadi kesalahan jaringan atau server saat menghapus service.'); }
    };

    // --- Data Fetching & Rendering ---
    const fetchAndRenderMejas = async () => {
        try {
            const res = await fetch('{{ route('pemandu.api.penyewaanAktif') }}'); // Rute API Pemandu
            const activeRentals = await res.json();
            currentActiveRentals = {}; activeRentals.forEach(p => currentActiveRentals[p.id] = p);

            document.querySelectorAll('[id^="meja-card-"]').forEach(card => {
                const mejaId = parseInt(card.id.replace('meja-card-', ''));
                const penyewaanForThisMeja = activeRentals.find(p => parseInt(p.meja_id) === mejaId);

                const statusMejaEl = card.querySelector(`#status-meja-${mejaId}`);
                let pesanBtnEl = card.querySelector(`.btn-pesan-meja[data-meja-id="${mejaId}"]`); // Perbaikan selektor
                const penyewaanDivEl = card.querySelector(`#penyewaan-${mejaId}`);

                if (penyewaanForThisMeja) {
                    card.classList.remove('bg-green-100'); card.classList.add('bg-red-100');
                    if (statusMejaEl) statusMejaEl.innerText = 'Status: dipakai';
                    if (pesanBtnEl) pesanBtnEl.style.display = 'none';

                    const isTimeUp = penyewaanForThisMeja.waktu_selesai && (new Date(penyewaanForThisMeja.waktu_selesai)).getTime() - getCalibratedNow().getTime() <= 0;
                    const isSepuasnya = penyewaanForThisMeja.is_sepuasnya;

                    let timerDisplay = `⏳ <span id="countdown-${penyewaanForThisMeja.id}">--:--:--</span>`;
                    if (isSepuasnya) timerDisplay = `⏳ <span id="running-timer-${penyewaanForThisMeja.id}">00:00:00</span> (Main Sepuasnya)`;
                    else if (isTimeUp) timerDisplay = `⏳ Waktu habis!`;

                    let actionButtonsHtml = `
                        <a href="#" class="btn btn-xs bg-gray-200 hover:bg-gray-300 text-gray-800 px-3 py-1 rounded" onclick="event.preventDefault(); openAddDurationModal(${penyewaanForThisMeja.id});">
                            <i class="fa-solid fa-stopwatch"></i> Tambah Waktu
                        </a>
                        <a href="#" class="btn btn-xs bg-yellow-200 hover:bg-yellow-300 text-yellow-900 px-3 py-1 rounded" onclick="event.preventDefault(); openAddServiceModal(${penyewaanForThisMeja.id});">
                            <i class="fa-solid fa-wine-bottle"></i> Tambah Service
                        </a>
                        {{-- Tombol Bayar Sekarang dihapus untuk Pemandu --}}
                    `;
                    // Jika waktu habis, tidak perlu tombol tambah waktu
                    if (isTimeUp && !isSepuasnya) { // Hanya jika durasi fixed dan sudah habis
                         actionButtonsHtml = `
                            <a href="#" class="btn btn-xs bg-yellow-200 hover:bg-yellow-300 text-yellow-900 px-3 py-1 rounded" onclick="event.preventDefault(); openAddServiceModal(${penyewaanForThisMeja.id});">
                                <i class="fa-solid fa-wine-bottle"></i> Tambah Service
                            </a>
                            {{-- Tombol Bayar Sekarang dihapus untuk Pemandu --}}
                        `;
                    }


                    let serviceDetailHtml = '';
                    if (penyewaanForThisMeja.service_detail && penyewaanForThisMeja.service_detail.length > 0) {
                        const serviceItems = penyewaanForThisMeja.service_detail.map(s => `
                            <li class="service-item">
                                <span>${s.nama} (${s.jumlah}x) - ${fmtRp(s.subtotal)}</span>
                                <button type="button" class="service-item-remove-btn" onclick="removeServiceFromPenyewaan(${penyewaanForThisMeja.id}, ${s.id}, '${s.nama}')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </li>
                        `).join('');
                        serviceDetailHtml = `
                            <div class="service-dropdown-header" onclick="toggleServiceDropdown(${penyewaanForThisMeja.id})">
                                <span>Layanan Tambahan (${penyewaanForThisMeja.service_detail.length})</span>
                                <i id="service-toggle-icon-${penyewaanForThisMeja.id}" class="fa-solid fa-chevron-down"></i>
                            </div>
                            <ul id="service-list-${penyewaanForThisMeja.id}" class="service-list">
                                ${serviceItems}
                            </ul>
                        `;
                    } else {
                        serviceDetailHtml = `
                            <div class="service-dropdown-header" onclick="toggleServiceDropdown(${penyewaanForThisMeja.id})">
                                <span>Tidak ada Layanan Tambahan</span>
                                <i id="service-toggle-icon-${penyewaanForThisMeja.id}" class="fa-solid fa-chevron-down"></i>
                            </div>
                            <ul id="service-list-${penyewaanForThisMeja.id}" class="service-list">
                                <li class="service-item text-gray-500 italic">Belum ada service.</li>
                            </ul>
                        `;
                    }

                    if (penyewaanDivEl) {
                        penyewaanDivEl.innerHTML = `
                            <div class="mt-3 bg-white rounded-lg shadow p-3 border border-red-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-700">👤 ${penyewaanForThisMeja.nama_penyewa}</p>
                                        <p class="text-xs text-gray-500">⏰ Mulai: ${fmtTime(penyewaanForThisMeja.waktu_mulai)} WIB</p>
                                        <p class="text-xs text-gray-500">🕒 Durasi: ${isSepuasnya ? 'Main Sepuasnya' : fmtDur(penyewaanForThisMeja.durasi_jam)}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-block bg-red-100 text-red-700 text-xs font-semibold px-2 py-1 rounded">DIPAKAI</span>
                                        <p class="text-sm font-bold text-red-700 mt-1">${timerDisplay}</p>
                                    </div>
                                </div>
                                ${serviceDetailHtml}
                                <div class="mt-3 flex flex-wrap gap-2">${actionButtonsHtml}</div>
                            </div>
                        `;
                        const timerEl = isSepuasnya ? getEl(`running-timer-${penyewaanForThisMeja.id}`) : getEl(`countdown-${penyewaanForThisMeja.id}`);
                        if (timerEl) {
                            if (isSepuasnya) startRunningTimer(timerEl, penyewaanForThisMeja.waktu_mulai, penyewaanForThisMeja);
                            else if (penyewaanForThisMeja.status === 'berlangsung' && !isTimeUp) startCountdown(timerEl, penyewaanForThisMeja.waktu_selesai, penyewaanForThisMeja);
                            else if (isTimeUp) { timerEl.innerText = 'Waktu habis!'; timerEl.classList.add('text-red-700','font-bold'); }
                        }
                    }
                } else {
                    card.classList.remove('bg-red-100'); card.classList.add('bg-green-100');
                    if (statusMejaEl) statusMejaEl.innerText = 'Status: kosong';
                    if (penyewaanDivEl) penyewaanDivEl.innerHTML = '';

                    // Jika tombol pesan tidak ada atau disembunyikan sebelumnya, buat ulang
                    if (!pesanBtnEl) {
                        pesanBtnEl = document.createElement('button');
                        pesanBtnEl.id = `btn-pesan-${mejaId}`;
                        pesanBtnEl.className = 'mt-2 btn btn-primary btn-pesan-meja'; // Tambahkan class untuk event listener
                        pesanBtnEl.innerText = 'Pesan';
                        pesanBtnEl.setAttribute('data-meja-id', mejaId); // Tambahkan data-id
                        card.appendChild(pesanBtnEl);
                    }
                    pesanBtnEl.style.display = 'block';

                    for (const id in countdownIntervals) {
                        if (currentActiveRentals[id] && currentActiveRentals[id].meja_id === mejaId) { clearInterval(countdownIntervals[id]); delete countdownIntervals[id]; }
                    }
                }
            });
            // Attach event listeners for new "Pesan" buttons
            document.querySelectorAll('.btn-pesan-meja').forEach(button => {
                // Hapus listener lama jika ada untuk mencegah duplikasi
                button.onclick = null;
                button.addEventListener('click', () => {
                    openModal(button.dataset.mejaId);
                });
            });

        } catch (error) { console.error('Error fetching and rendering mejas:', error); }
    };

    // fetchPaymentDetails dan semua terkait pembayaran DIHAPUS untuk Pemandu

    const fetchAndRenderServicesForAdd = async () => {
        try {
            const res = await fetch('{{ route('api.services') }}'); // Menggunakan API umum
            allAvailableServices = await res.json();
            const serviceListContainer = getEl('service_list_container'); serviceListContainer.innerHTML = '';

            allAvailableServices.forEach(s => {
                const row = document.createElement('div');
                row.className = 'flex items-center justify-between py-1 border-b last:border-b-0';
                row.innerHTML = `<span class="font-medium">${s.nama} (${fmtRp(s.harga)}) <span class="text-gray-500 text-xs">(Stok: ${s.stok})</span></span>
                                 <input type="number" data-service-id="${s.id}" data-service-price="${s.harga}" data-max-stock="${s.stok}"
                                        class="service-qty form-input w-20 text-center" value="0" min="0" max="${s.stok}">`;
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
                            toastr.warning(`Jumlah ${qtyInput.closest('div').querySelector('span').firstChild.textContent.trim()} melebihi stok yang tersedia (${maxStock}).`);
                            total += parseFloat(qtyInput.dataset.servicePrice) * maxStock;
                        } else {
                            total += parseFloat(qtyInput.dataset.servicePrice) * qty;
                        }
                    });
                    getEl('current_service_add_total').innerText = fmtRp(total);
                });
            });
        } catch (error) { toastr.error('Gagal memuat daftar service.'); console.error('Error fetching services:', error); }
    };

    // --- Fetch and Populate Pakets for Modal Pemesanan ---
    const fetchAndPopulatePakets = async () => {
        try {
            const res = await fetch('{{ route('api.pakets') }}'); // Menggunakan API umum
            allAvailablePakets = await res.json();
            const paketSelect = getEl('paket_id_select');
            paketSelect.innerHTML = '<option value="">-- Pilih Paket --</option>'; // Clear existing options

            allAvailablePakets.forEach(paket => {
                const option = document.createElement('option');
                option.value = paket.id;
                option.innerText = paket.nama_paket;
                paketSelect.appendChild(option);
            });
        } catch (error) {
            console.error('Error fetching pakets:', error);
            toastr.error('Gagal memuat daftar paket.');
        }
    };

    // --- Event Listeners for Forms ---
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

                let desc = [];
                if (isiPaket.harga_paket) desc.push(`Harga: ${fmtRp(isiPaket.harga_paket)}`);
                if (isiPaket.durasi_jam !== undefined) {
                    if (isiPaket.durasi_jam > 0) desc.push(`Durasi: ${fmtDur(isiPaket.durasi_jam)}`);
                    else desc.push(`Durasi: Main Sepuasnya`);
                }
                if (isiPaket.services && isiPaket.services.length > 0) desc.push(`Service: ${isiPaket.services.length} item`);
                if (isiPaket.deskripsi_tambahan) desc.push(isiPaket.deskripsi_tambahan);

                paketDeskripsiPreview.innerText = desc.join(' | ');
                paketDeskripsiPreview.style.display = 'block';

                getEl('formPesan').action = '{{ route('pemandu.pesanPaket') }}'; // Rute Pemandu
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

            getEl('formPesan').action = '{{ route('pemandu.pesanDurasi') }}'; // Rute Pemandu
        }
    });

    getEl('is_sepuasnya').addEventListener('change', function() {
        if (getEl('paket_id_select').value) return;
        const durasiWrapper = getEl('durasi_jam_wrapper'); const durasiInput = getEl('durasi_jam');
        if (this.checked) {
            durasiWrapper.style.display = 'none'; durasiInput.removeAttribute('required'); durasiInput.value = '';
            getEl('formPesan').action = '{{ route('pemandu.pesanSepuasnya') }}'; // Rute Pemandu
        } else {
            durasiWrapper.style.display = 'block'; durasiInput.setAttribute('required', 'required');
            getEl('formPesan').action = '{{ route('pemandu.pesanDurasi') }}'; // Rute Pemandu
        }
    });

    getEl('formPesan').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';

        try {
            const formData = new FormData(form);
            const body = Object.fromEntries(formData.entries());

            // Handle checkbox for is_sepuasnya if it's not part of a package
            if (!body.paket_id && form.querySelector('#is_sepuasnya').checked) {
                body.is_sepuasnya = true;
                delete body.durasi_jam; // Remove durasi_jam if sepuasnya
            } else if (!body.paket_id) {
                body.is_sepuasnya = false;
            }

            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(body)
            });

            const data = await res.json();
            if (res.ok) {
                toastr.success(data.message || 'Pemesanan berhasil!');
                closeModal();
                fetchAndRenderMejas();
            } else {
                toastr.error('Gagal memesan: ' + (data.message || 'Terjadi kesalahan.'));
                if (data.errors) {
                    for (const key in data.errors) {
                        toastr.error(data.errors[key][0]);
                    }
                }
            }
        } catch (error) {
            toastr.error('Terjadi kesalahan jaringan atau server saat memesan.');
            console.error('Error submitting formPesan:', error);
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Mulai';
        }
    });


    getEl('formAddDuration').addEventListener('submit', async (e) => {
        e.preventDefault(); const pId = getEl('add_duration_penyewaan_id').value; const addDur = getEl('additional_durasi_jam').value;
        const submitBtn = e.target.querySelector('button[type="submit"]'); submitBtn.disabled = submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menambah...';
        try {
            const res = await fetch(`{{ url('/pemandu/penyewaan/') }}/${pId}/add-duration`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ additional_durasi_jam: addDur }) }); // Rute Pemandu
            const data = await res.json(); if (res.ok) { toastr.success(data.message); closeAddDurationModal(); fetchAndRenderMejas(); }
            else { toastr.error('Gagal menambah durasi: ' + (data.message || 'Terjadi kesalahan.')); }
        } catch (error) { toastr.error('Terjadi kesalahan jaringan atau server saat menambah durasi.'); console.error('Error adding duration:', error); }
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
                    toastr.warning(`Jumlah ${serviceName} (${qty}) melebihi stok yang tersedia (${maxStock}). Mohon koreksi.`);
                    stockExceeded = true;
                    return;
                }
                selSrv.push({ service_id: serviceId, jumlah: qty });
            }
        });

        if (stockExceeded) return;
        if (selSrv.length === 0) { toastr.warning('Pilih setidaknya satu service untuk ditambahkan.'); return; }

        const submitBtn = e.target.querySelector('button[type="submit"]'); submitBtn.disabled = submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menambah...';
        try {
            const res = await fetch(`{{ url('/pemandu/penyewaan/') }}/${pId}/add-service`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ services: selSrv }) }); // Rute Pemandu
            const data = await res.json();
            if (res.ok) { toastr.success(data.message); closeAddServiceModal(); fetchAndRenderMejas(); }
            else { toastr.error('Gagal menambah service: ' + (data.message || 'Terjadi kesalahan.')); }
        } catch (error) { toastr.error('Terjadi kesalahan jaringan atau server saat menambah service.'); console.error('Error adding service:', error); }
        finally { submitBtn.disabled = false; submitBtn.innerHTML = 'Tambah Service'; }
    });

    // formPembayaran event listener DIHAPUS untuk Pemandu


    // --- Initial Load & Polling ---
    document.addEventListener('DOMContentLoaded', () => {
        // Event listener untuk tombol "Pesan"
        document.querySelectorAll('.btn-pesan-meja').forEach(button => {
            button.addEventListener('click', () => {
                openModal(button.dataset.mejaId);
            });
        });

        getEl('formPesan').action = '{{ route('pemandu.pesanDurasi') }}'; // Default action on load for Pemandu
        fetchAndRenderMejas();
        setInterval(fetchAndRenderMejas, 5000);
    });

</script>
@endsection