@extends('default')

@section('title', 'Dashboard Kasir')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlalDLuLz0YxOyqjF7xJ6sQ/k9z/hH+hR5/t9/h2Q+A/hH8/l+g8/Q5/h2Q/A/k9z/g8/h2Q+A/k9z/g8/h2Q+A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4 text-black">Dashboard Kasir</h1>

    <div id="meja-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 text-black">
        @foreach ($mejas as $meja)
        <div id="meja-card-{{ $meja->id }}" class="p-4 border rounded shadow @if($meja->status === 'dipakai') bg-red-100 @else bg-green-100 @endif">
            <h2 class="text-lg font-semibold">{{ $meja->nama_meja }}</h2>
            <p id="status-meja-{{ $meja->id }}">Status: {{ $meja->status }}</p>

            @if ($meja->status === 'kosong')
            <button id="btn-pesan-{{ $meja->id }}" onclick="openModal({{ $meja->id }})" class="mt-2 btn btn-primary">Pesan</button>
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
            <div class="mb-3">
                <label for="nama_penyewa" class="form-label">Nama Penyewa</label>
                <input type="text" name="nama_penyewa" id="nama_penyewa" required class="form-input">
            </div>
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
                <button type="button" onclick="closeAddDurationModal()" class="btn btn-secondary">Batal</button>
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
                <button type="button" onclick="closeAddServiceModal()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary">Tambah Service</button>
            </div>
        </form>
    </div>
</div>


{{-- Modal Pembayaran --}}
<div id="paymentModal" style="display:none" class="modal-overlay">
    <div class="modal-content">
        <h2 class="text-xl font-bold mb-4">Detail Pembayaran</h2>
        <form id="formPembayaran">
            @csrf
            <input type="hidden" name="penyewaan_id" id="payment_penyewaan_id">
            <p>Meja: <strong id="payment_meja_nama"></strong></p>
            <p>Penyewa: <strong id="payment_nama_penyewa"></strong></p>
            <p>Durasi Booking: <strong id="payment_durasi"></strong></p>
            <p id="payment_mode_sepuasnya_info" style="display: none;" class="text-sm text-gray-500 italic">(Mode Main Sepuasnya)</p>
            <p>Waktu Mulai: <strong id="payment_waktu_mulai"></strong></p>
            <p>Waktu Selesai (Terjadwal): <strong id="payment_waktu_selesai_terjadwal"></strong></p>
            <p>Waktu Selesai (Aktual): <strong id="payment_waktu_selesai_aktual"></strong></p>
            <p>Harga Per Jam: <strong id="payment_harga_per_jam"></strong></p>
            <p>Subtotal Main: <strong id="payment_subtotal_main"></strong></p>
            <p>Total Service: <strong id="payment_total_service"></strong></p>
            <ul id="payment_service_detail" class="list-disc list-inside text-sm text-gray-600 mb-2"></ul>

            <div class="mb-3 mt-4">
                <label for="kode_kupon" class="form-label">Kode Kupon (Opsional)</label>
                <input type="text" name="kode_kupon" id="kode_kupon" class="form-input">
                <small class="text-gray-500">Biarkan kosong jika tidak ada kupon.</small>
            </div>
            <p class="text-lg font-bold">Diskon: <strong id="payment_diskon" class="text-red-500"></strong></p>
            <p class="text-xl font-bold">Total Pembayaran: <strong id="payment_total_final" class="text-green-600"></strong></p>
            <div class="modal-footer">
                <button type="button" onclick="closePaymentModal()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary">Bayar Sekarang</button>
            </div>
        </form>
    </div>
</div>

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

<script>
    const serverTime = new Date("{{ $serverTime }}");
    const clientTimeAtLoad = new Date();
    const serverClientOffset = serverTime.getTime() - clientTimeAtLoad.getTime();

    const getCalibratedNow = () => new Date(new Date().getTime() + serverClientOffset);
    const countdownIntervals = {};
    let currentActiveRentals = {};
    let allAvailableServices = [];

    // --- Utility Functions ---
    const getEl = (id) => document.getElementById(id);
    const toggleModal = (modalId, show) => { getEl(modalId).style.display = show ? 'flex' : 'none'; };
    const fmtTime = (dtStr) => { if (!dtStr) return '--:--'; const d=new Date(dtStr); return `${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`; };
    const fmtFullDt = (dtStr) => { if (!dtStr) return 'N/A'; const d=new Date(dtStr); const opt={year:'numeric',month:'short',day:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false,timeZoneName:'short'}; return d.toLocaleString('id-ID',opt); };
    const fmtDur = (decHrs) => {
        if (decHrs === null || isNaN(decHrs)) return 'N/A';
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

    // --- Modal Control Functions ---
    const openModal = (mejaId) => {
        getEl('modal_meja_id').value = mejaId;
        getEl('formPesan').reset();
        getEl('is_sepuasnya').checked = false;
        getEl('durasi_jam_wrapper').style.display = 'block';
        getEl('durasi_jam').required = true;
        getEl('formPesan').action = '{{ route('kasir.pesanDurasi') }}';
        toggleModal('pesanModal', true);
    };
    const closeModal = () => toggleModal('pesanModal', false);
    const openAddDurationModal = (penyewaanId) => {
        const p=currentActiveRentals[penyewaanId]; if (!p || p.is_sepuasnya) { alert('Tidak bisa menambah durasi untuk penyewaan ini.'); return; }
        getEl('add_duration_penyewaan_id').value = penyewaanId; getEl('add_duration_meja_nama').innerText = p.meja_nama; getEl('add_duration_nama_penyewa').innerText = p.nama_penyewa; getEl('add_duration_current_durasi').innerText = fmtDur(p.durasi_jam); getEl('formAddDuration').reset(); toggleModal('addDurationModal', true);
    };
    const closeAddDurationModal = () => toggleModal('addDurationModal', false);
    const openAddServiceModal = async (penyewaanId) => {
        const p=currentActiveRentals[penyewaanId]; if (!p) { alert('Penyewaan tidak ditemukan.'); return; }
        getEl('add_service_penyewaan_id').value = penyewaanId; getEl('add_service_meja_nama').innerText = p.meja_nama; getEl('add_service_nama_penyewa').innerText = p.nama_penyewa; getEl('add_service_current_total').innerText = fmtRp(p.total_service); getEl('current_service_add_total').innerText = fmtRp(0);
        await fetchAndRenderServicesForAdd();
        toggleModal('addServiceModal', true);
    };
    const closeAddServiceModal = () => toggleModal('addServiceModal', false);
    const openPaymentModal = (penyewaanId) => {
        if (getEl('paymentModal').style.display === 'flex') return;
        getEl('payment_penyewaan_id').value = penyewaanId; getEl('kode_kupon').value = '';
        fetchPaymentDetails(penyewaanId); toggleModal('paymentModal', true);
    };
    const closePaymentModal = () => toggleModal('paymentModal', false);

    // --- Timer Logic ---
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
        const mejaId = penyewaanData.meja_id; const actionsCont = document.querySelector(`#penyewaan-${mejaId} .flex-wrap`);
        if (actionsCont) {
            actionsCont.innerHTML = `
                <a href="#" class="btn btn-xs bg-yellow-200 hover:bg-yellow-300 text-yellow-900 px-3 py-1 rounded" onclick="event.preventDefault(); openAddServiceModal(${penyewaanData.id});">
                    <i class="fa-solid fa-wine-bottle"></i> Tambah Service
                </a>
                <a href="#" class="btn btn-xs btn-success" onclick="event.preventDefault(); openPaymentModal(${penyewaanData.id});">
                    <i class="fa-solid fa-cash-register"></i> Bayar Sekarang
                </a>
            `;
        }
        openPaymentModal(penyewaanData.id);
    };

    // --- Service Dropdown & Removal Logic ---
    const toggleServiceDropdown = (penyewaanId) => {
        const serviceListEl = getEl(`service-list-${penyewaanId}`);
        const iconEl = getEl(`service-toggle-icon-${penyewaanId}`);
        if (serviceListEl.style.display === 'block') { serviceListEl.style.display = 'none'; iconEl.classList.replace('fa-chevron-up', 'fa-chevron-down'); }
        else { serviceListEl.style.display = 'block'; iconEl.classList.replace('fa-chevron-down', 'fa-chevron-up'); }
    };

    const removeServiceFromPenyewaan = async (penyewaanId, serviceId, serviceName) => {
        if (!confirm(`Yakin ingin menghapus "${serviceName}" dari penyewaan ini?`)) return;
        try {
            const res = await fetch(`{{ url('/kasir/penyewaan/') }}/${penyewaanId}/remove-service`, {
                method: 'DELETE', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ service_id: serviceId })
            });
            const data = await res.json();
            if (res.ok) { alert(data.message); fetchAndRenderMejas(); }
            else { alert('Gagal menghapus service: ' + (data.message || 'Terjadi kesalahan.')); }
        } catch (error) { console.error('Error removing service:', error); alert('Terjadi kesalahan jaringan atau server saat menghapus service.'); }
    };

    // --- Data Fetching & Rendering ---
    const fetchAndRenderMejas = async () => {
        try {
            const res = await fetch('{{ route('kasir.api.penyewaanAktif') }}');
            const activeRentals = await res.json();
            currentActiveRentals = {}; activeRentals.forEach(p => currentActiveRentals[p.id] = p);

            document.querySelectorAll('[id^="meja-card-"]').forEach(card => {
                const mejaId = parseInt(card.id.replace('meja-card-', ''));
                const penyewaanForThisMeja = activeRentals.find(p => parseInt(p.meja_id) === mejaId); // Use parseInt(p.meja_id)

                const statusMejaEl = card.querySelector(`#status-meja-${mejaId}`);
                let pesanBtnEl = card.querySelector(`#btn-pesan-${mejaId}`);
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

                    let actionButtonsHtml = '';
                    if (isSepuasnya || isTimeUp) {
                        actionButtonsHtml = `
                            <a href="#" class="btn btn-xs bg-yellow-200 hover:bg-yellow-300 text-yellow-900 px-3 py-1 rounded" onclick="event.preventDefault(); openAddServiceModal(${penyewaanForThisMeja.id});">
                                <i class="fa-solid fa-wine-bottle"></i> Tambah Service
                            </a>
                            <a href="#" class="btn btn-xs btn-success" onclick="event.preventDefault(); openPaymentModal(${penyewaanForThisMeja.id});">
                                <i class="fa-solid fa-cash-register"></i> Bayar Sekarang
                            </a>
                        `;
                    } else {
                        actionButtonsHtml = `
                            <a href="#" class="btn btn-xs bg-gray-200 hover:bg-gray-300 text-gray-800 px-3 py-1 rounded" onclick="event.preventDefault(); openAddDurationModal(${penyewaanForThisMeja.id});">
                                <i class="fa-solid fa-stopwatch"></i> Tambah Waktu
                            </a>
                            <a href="#" class="btn btn-xs bg-yellow-200 hover:bg-yellow-300 text-yellow-900 px-3 py-1 rounded" onclick="event.preventDefault(); openAddServiceModal(${penyewaanForThisMeja.id});">
                                <i class="fa-solid fa-wine-bottle"></i> Tambah Service
                            </a>
                            <a href="#" class="btn btn-xs btn-success" onclick="event.preventDefault(); openPaymentModal(${penyewaanForThisMeja.id});">
                                <i class="fa-solid fa-cash-register"></i> Bayar
                            </a>
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

                    if (!pesanBtnEl) {
                        pesanBtnEl = document.createElement('button'); pesanBtnEl.id = `btn-pesan-${mejaId}`; pesanBtnEl.className = 'mt-2 btn btn-primary'; pesanBtnEl.innerText = 'Pesan';
                        pesanBtnEl.onclick = () => openModal(mejaId); card.appendChild(pesanBtnEl);
                    }
                    pesanBtnEl.style.display = 'block';

                    for (const id in countdownIntervals) {
                        if (currentActiveRentals[id] && currentActiveRentals[id].meja_id === mejaId) { clearInterval(countdownIntervals[id]); delete countdownIntervals[id]; }
                    }
                }
            });
        } catch (error) { console.error('Error fetching and rendering mejas:', error); }
    };

    const fetchPaymentDetails = async (penyewaanId) => {
        const p=currentActiveRentals[penyewaanId]; if (!p) { alert('Detail penyewaan tidak ditemukan.'); closePaymentModal(); return; }

        const hargaPerJam = parseFloat(p.harga_per_jam || 0); let durasiUntukPerhitungan = parseFloat(p.durasi_jam || 0);
        const initialTotalService = parseFloat(p.total_service || 0); const serviceDetails = p.service_detail;

        let waktuSelesaiAktual = getCalibratedNow();
        if (p.is_sepuasnya) { durasiUntukPerhitungan = (waktuSelesaiAktual.getTime() - new Date(p.waktu_mulai).getTime()) / (1000 * 60 * 60); }

        const updateTotalDisplay = (diskonPersen = 0) => {
            const subtotalMain = durasiUntukPerhitungan * hargaPerJam;
            const totalBeforeDiscount = subtotalMain + initialTotalService; // NEW: Total gabungan
            const currentDiskon = (totalBeforeDiscount * diskonPersen) / 100; // NEW: Diskon dari total gabungan
            const finalTotal = totalBeforeDiscount - currentDiskon; // NEW: Total akhir

            getEl('payment_subtotal_main').innerText = fmtRp(subtotalMain); getEl('payment_total_service').innerText = fmtRp(initialTotalService);
            getEl('payment_diskon').innerText = fmtRp(currentDiskon); getEl('payment_total_final').innerText = fmtRp(finalTotal);
        };

        getEl('payment_meja_nama').innerText = p.meja_nama; getEl('payment_nama_penyewa').innerText = p.nama_penyewa;
        getEl('payment_durasi').innerText = p.is_sepuasnya ? 'N/A (Main Sepuasnya)' : fmtDur(p.durasi_jam);
        getEl('payment_mode_sepuasnya_info').style.display = p.is_sepuasnya ? 'block' : 'none';
        getEl('payment_waktu_mulai').innerText = fmtFullDt(p.waktu_mulai);
        getEl('payment_waktu_selesai_terjadwal').innerText = p.waktu_selesai ? fmtFullDt(p.waktu_selesai) : 'N/A';
        getEl('payment_waktu_selesai_aktual').innerText = fmtFullDt(waktuSelesaiAktual);
        getEl('payment_harga_per_jam').innerText = fmtRp(hargaPerJam);

        const serviceDetailListEl = getEl('payment_service_detail'); serviceDetailListEl.innerHTML = '';
        if (serviceDetails && serviceDetails.length > 0) {
            serviceDetails.forEach(s => { const li = document.createElement('li'); li.innerText = `${s.nama} (${s.jumlah}x) - ${fmtRp(s.subtotal)}`; serviceDetailListEl.appendChild(li); });
        } else { const li = document.createElement('li'); li.innerText = 'Tidak ada layanan tambahan.'; serviceDetailListEl.appendChild(li); }
        updateTotalDisplay();

        getEl('kode_kupon').oninput = debounce(async function() {
            const kuponCode = this.value.trim(); let diskonPersen = 0;
            if (kuponCode) {
                try {
                    const res = await fetch(`{{ route('api.kupon.validate') }}?code=${encodeURIComponent(kuponCode)}`);
                    if (res.ok) { const data = await res.json(); diskonPersen = parseFloat(data.diskon_persen) || 0; alert(`Kupon "${kuponCode}" berhasil diterapkan! Diskon ${diskonPersen}%`); }
                    else { const errData = await res.json(); alert('Kupon tidak valid: ' + (errData.message || 'Kode kupon tidak ditemukan atau kadaluarsa.')); }
                } catch (e) { alert('Error saat memvalidasi kupon. Silakan cek koneksi atau rute API Kupon.'); console.error('Error validating kupon:', e); }
            }
            updateTotalDisplay(diskonPersen);
        }, 500);
    };

    const fetchAndRenderServicesForAdd = async () => {
        try {
            const res = await fetch('{{ route('api.services') }}');
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
                            alert(`Jumlah ${qtyInput.closest('div').querySelector('span').firstChild.textContent.trim()} melebihi stok yang tersedia (${maxStock}).`);
                            total += parseFloat(qtyInput.dataset.servicePrice) * maxStock;
                        } else {
                            total += parseFloat(qtyInput.dataset.servicePrice) * qty;
                        }
                    });
                    getEl('current_service_add_total').innerText = fmtRp(total);
                });
            });
        } catch (error) { alert('Gagal memuat daftar service.'); console.error('Error fetching services:', error); }
    };

    // --- Event Listeners for Forms ---
    getEl('is_sepuasnya').addEventListener('change', function() {
        const durasiWrapper = getEl('durasi_jam_wrapper'); const durasiInput = getEl('durasi_jam');
        if (this.checked) {
            durasiWrapper.style.display = 'none'; durasiInput.removeAttribute('required'); durasiInput.value = '';
            getEl('formPesan').action = '{{ route('kasir.pesanSepuasnya') }}';
        } else {
            durasiWrapper.style.display = 'block'; durasiInput.setAttribute('required', 'required');
            getEl('formPesan').action = '{{ route('kasir.pesanDurasi') }}';
        }
    });

    getEl('formAddDuration').addEventListener('submit', async (e) => {
        e.preventDefault(); const pId = getEl('add_duration_penyewaan_id').value; const addDur = getEl('additional_durasi_jam').value;
        const submitBtn = e.target.querySelector('button[type="submit"]'); submitBtn.disabled = submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menambah...';
        try {
            const res = await fetch(`{{ url('/kasir/penyewaan/') }}/${pId}/add-duration`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ additional_durasi_jam: addDur }) });
            const data = await res.json(); if (res.ok) { alert(data.message); closeAddDurationModal(); fetchAndRenderMejas(); }
            else { alert('Gagal menambah durasi: ' + (data.message || 'Terjadi kesalahan.')); }
        } catch (error) { alert('Terjadi kesalahan jaringan atau server saat menambah durasi.'); console.error('Error adding duration:', error); }
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
                    alert(`Jumlah ${serviceName} (${qty}) melebihi stok yang tersedia (${maxStock}). Mohon koreksi.`);
                    stockExceeded = true;
                    return;
                }
                selSrv.push({ service_id: serviceId, jumlah: qty });
            }
        });

        if (stockExceeded) return;
        if (selSrv.length === 0) { alert('Pilih setidaknya satu service untuk ditambahkan.'); return; }

        const submitBtn = e.target.querySelector('button[type="submit"]'); submitBtn.disabled = submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menambah...';
        try {
            const res = await fetch(`{{ url('/kasir/penyewaan/') }}/${pId}/add-service`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ services: selSrv }) });
            const data = await res.json();
            if (res.ok) { alert(data.message); closeAddServiceModal(); fetchAndRenderMejas(); }
            else { alert('Gagal menambah service: ' + (data.message || 'Terjadi kesalahan.')); }
        } catch (error) { alert('Terjadi kesalahan jaringan atau server saat menambah service.'); console.error('Error adding service:', error); }
        finally { submitBtn.disabled = false; submitBtn.innerHTML = 'Tambah Service'; }
    });

    getEl('formPembayaran').addEventListener('submit', async (e) => {
        e.preventDefault(); const pId = getEl('payment_penyewaan_id').value; const kupon = getEl('kode_kupon').value;
        const submitBtn = e.target.querySelector('button[type="submit"]'); submitBtn.disabled = submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
        try {
            const res = await fetch(`{{ url('/kasir/penyewaan/') }}/${pId}/bayar`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ kode_kupon: kupon }) });
            const data = await res.json();
            if (res.ok) {
                alert(data.message + ' Total bayar: ' + fmtRp(data.total_bayar)); closePaymentModal();
                if (countdownIntervals[pId]) { clearInterval(countdownIntervals[pId]); delete countdownIntervals[pId]; }
                fetchAndRenderMejas();
            } else { alert('Gagal memproses pembayaran: ' + (data.message || 'Terjadi kesalahan.')); console.error('Pembayaran gagal:', data); }
        } catch (error) { alert('Terjadi kesalahan jaringan atau server saat memproses pembayaran.'); console.error('Error submitting payment:', error); }
        finally { submitBtn.disabled = false; submitBtn.innerHTML = 'Bayar Sekarang'; }
    });

    // --- Initial Load & Polling ---
    document.addEventListener('DOMContentLoaded', () => {
        getEl('formPesan').action = '{{ route('kasir.pesanDurasi') }}';
        fetchAndRenderMejas();
        setInterval(fetchAndRenderMejas, 5000);
    });
</script>
@endsection