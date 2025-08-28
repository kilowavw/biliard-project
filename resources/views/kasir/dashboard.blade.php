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
                class="p-4 border rounded shadow @if($meja->status === 'dipakai') bg-green-100 @else bg-neutral-600 @endif">
                <h2 class="text-lg font-semibold">{{ $meja->nama_meja }}</h2>
                <img src="{{ asset('gambar/Meja2.webp') }}" alt="Meja" width="200" class="mx-auto my-2">
                <p id="status-meja-{{ $meja->id }}">Status: {{ $meja->status }}</p>

                @if ($meja->status === 'kosong')
                    <button id="btn-pesan-{{ $meja->id }}" onclick="openModal({{ $meja->id }})"
                        class="mt-2 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">Pesan</button>
                @endif
                <div id="penyewaan-{{ $meja->id }}"></div>
            </div>
        @endforeach
    </div>
</div>

{{-- Modal Pemesanan --}}
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
                <p id="paket_deskripsi_preview" class="text-sm text-gray-500 mt-1 italic" style="display:none;"></p>
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

{{-- Modal Tambah Durasi --}}
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

{{-- Modal Tambah Service --}}
<div id="addServiceModal" style="display:none" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-lg text-black">
        <h2 class="text-xl font-bold mb-4">Tambah Service Meja <span id="add_service_meja_nama"></span></h2>
        <form id="formAddService">
            @csrf
            <input type="hidden" name="penyewaan_id" id="add_service_penyewaan_id">
            <p class="mb-2 text-gray-700">Penyewa: <strong id="add_service_nama_penyewa"></strong></p>
            <p class="mb-2 text-gray-700">Total Service Saat Ini: <strong id="add_service_current_total"></strong></p>

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

{{-- Modal Pembayaran --}}
<div id="paymentModal" style="display:none" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
     <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto text-black">
        <h2 class="text-xl font-bold mb-4">Detail Pembayaran</h2>
        <form id="formPembayaran">
            @csrf
            <input type="hidden" name="penyewaan_id" id="payment_penyewaan_id">
            <p class="mb-1">Meja: <strong id="payment_meja_nama"></strong></p>
            <p class="mb-1">Penyewa: <strong id="payment_nama_penyewa"></strong></p>
            <p id="payment_member_info" class="mb-1 text-sm text-green-600" style="display: none;"></p>
            <p class="mb-1">Durasi Booking: <strong id="payment_durasi"></strong></p>
            <p id="payment_mode_sepuasnya_info" style="display: none;" class="text-sm text-gray-500 italic">(Mode Main Sepuasnya)</p>
            <p class="mb-1">Waktu Mulai: <strong id="payment_waktu_mulai"></strong></p>
            <p class="mb-1">Waktu Selesai (Terjadwal): <strong id="payment_waktu_selesai_terjadwal"></strong></p>
            <p class="mb-1">Waktu Selesai (Aktual): <strong id="payment_waktu_selesai_aktual"></strong></p>
            <p class="mb-1">Harga Per Jam: <strong id="payment_harga_per_jam"></strong></p>
            <p class="mb-1">Subtotal Main: <strong id="payment_subtotal_main"></strong></p>
            <p class="mb-1">Total Service: <strong id="payment_total_service"></strong></p>
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
<script>
    const serverTime = new Date("{{ $serverTime }}");
    const clientTimeAtLoad = new Date();
    const serverClientOffset = serverTime.getTime() - clientTimeAtLoad.getTime();
    const userRole = "{{ $userRole }}";

    const getCalibratedNow = () => new Date(new Date().getTime() + serverClientOffset);
    const countdownIntervals = {};
    let currentActiveRentals = {};
    let allAvailableServices = [];
    let allAvailablePakets = @json($activePakets); // Load all packages from server
    let memberData = { valid: false, nama_member: '', diskon_persen: 0 }; // Global member data

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

    const openModal = async (mejaId) => {
        getEl('modal_meja_id').value = mejaId;
        getEl('formPesan').reset();
        getEl('kode_member').value = '';
        getEl('nama_penyewa').value = '';
        getEl('member_info_preview').style.display = 'none';
        memberData = { valid: false, nama_member: '', diskon_persen: 0 }; // Reset member data
        
        getEl('paket_id_select').value = '';
        getEl('paket_deskripsi_preview').style.display = 'none';
        getEl('paket_id_select').style.display = 'block'; // Show package field by default

        getEl('non_paket_options').style.display = 'block';
        getEl('is_sepuasnya').checked = false;
        getEl('durasi_jam_wrapper').style.display = 'block';
        getEl('durasi_jam').required = true;
        
        getEl('formPesan').action = '{{ route('kasir.pesanDurasi') }}';

        populatePaketsDropdown(); // Populate all packages initially
        toggleModal('pesanModal', true);
    };
    const closeModal = () => toggleModal('pesanModal', false);
    const openAddDurationModal = (penyewaanId) => {
        const p=currentActiveRentals[penyewaanId]; if (!p || p.is_sepuasnya) { Swal.fire('Info', 'Tidak bisa menambah durasi untuk penyewaan ini.', 'info'); return; }
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
        fetchPaymentDetails(penyewaanId); toggleModal('paymentModal', true);
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
        const mejaId = penyewaanData.meja_id; const actionsCont = document.querySelector(`#penyewaan-${mejaId} .flex-wrap`);
        if (actionsCont) {
            actionsCont.innerHTML = `
                @if(Auth::check() && Auth::user()->role == 'admin')
                <button type="button" class="px-3 py-1 bg-red-200 hover:bg-red-300 text-red-900 rounded-md text-xs" onclick="event.preventDefault(); confirmDeletePenyewaan(${penyewaanData.id}, '${penyewaanData.meja_nama}');">
                    <i class="fa-solid fa-trash"></i> Hapus
                </button>
                @endif
                <button type="button" class="px-3 py-1 bg-yellow-200 hover:bg-yellow-300 text-yellow-900 rounded-md text-xs" onclick="event.preventDefault(); openAddServiceModal(${penyewaanData.id});">
                    <i class="fa-solid fa-wine-bottle"></i> Tambah Service
                </button>
                <button type="button" class="px-3 py-1 bg-green-500 text-white rounded-md text-xs hover:bg-green-600" onclick="event.preventDefault(); openPaymentModal(${penyewaanData.id});">
                    <i class="fa-solid fa-cash-register"></i> Bayar Sekarang
                </button>
            `;
        }
        openPaymentModal(penyewaanData.id);
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

                if (penyewaanForThisMeja) {
                    card.classList.remove('bg-neutral-600'); card.classList.add('bg-green-100');
                    if (statusMejaEl) statusMejaEl.innerText = 'Status: dipakai';
                    if (pesanBtnEl) pesanBtnEl.style.display = 'none';

                    const isTimeUp = penyewaanForThisMeja.waktu_selesai && (new Date(penyewaanForThisMeja.waktu_selesai)).getTime() - getCalibratedNow().getTime() <= 0;
                    const isSepuasnya = penyewaanForThisMeja.is_sepuasnya;

                    let timerDisplay = `⏳ <span id="countdown-${penyewaanForThisMeja.id}">--:--:--</span>`;
                    if (isSepuasnya) timerDisplay = `⏳ <span id="running-timer-${penyewaanForThisMeja.id}">00:00:00</span> (Main Sepuasnya)`;
                    else if (isTimeUp) timerDisplay = `⏳ Waktu habis!`;

                    let actionButtonsHtml = '';
                    let deleteButtonHtml = '';

                    @if(Auth::check() && Auth::user()->role == 'admin')
                    deleteButtonHtml = `
                        <button type="button" class="px-3 py-1 bg-red-200 hover:bg-red-300 text-red-900 rounded-md text-xs" onclick="event.preventDefault(); confirmDeletePenyewaan(${penyewaanForThisMeja.id}, '${penyewaanForThisMeja.meja_nama}');">
                            <i class="fa-solid fa-trash"></i> Hapus
                        </button>
                    `;
                    @endif

                    if (isSepuasnya || isTimeUp) {
                        actionButtonsHtml = `
                            ${deleteButtonHtml}
                            <button type="button" class="px-3 py-1 bg-yellow-200 hover:bg-yellow-300 text-yellow-900 rounded-md text-xs" onclick="event.preventDefault(); openAddServiceModal(${penyewaanForThisMeja.id});">
                                <i class="fa-solid fa-wine-bottle"></i> Tambah Service
                            </button>
                            <button type="button" class="px-3 py-1 bg-green-500 text-white rounded-md text-xs hover:bg-green-600" onclick="event.preventDefault(); openPaymentModal(${penyewaanForThisMeja.id});">
                                <i class="fa-solid fa-cash-register"></i> Bayar Sekarang
                            </button>
                        `;
                    } else {
                        actionButtonsHtml = `
                            ${deleteButtonHtml}
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
                            <li class="flex justify-between items-center py-0.5 text-sm text-gray-600">
                                <span>${s.nama} (${s.jumlah}x) - ${fmtRp(s.subtotal)}</span>
                                <button type="button" class="text-red-500 hover:text-red-700 text-base" onclick="removeServiceFromPenyewaan(${penyewaanForThisMeja.id}, ${s.id}, '${s.nama}')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </li>
                        `).join('');
                        serviceDetailHtml = `
                            <div class="cursor-pointer flex justify-between items-center py-2 border-t mt-2 font-semibold text-gray-700" onclick="toggleServiceDropdown(${penyewaanForThisMeja.id})">
                                <span>Layanan Tambahan (${penyewaanForThisMeja.service_detail.length})</span>
                                <i id="service-toggle-icon-${penyewaanForThisMeja.id}" class="fa-solid fa-chevron-down"></i>
                            </div>
                            <ul id="service-list-${penyewaanForThisMeja.id}" class="list-disc list-inside px-2 hidden">
                                ${serviceItems}
                            </ul>
                        `;
                    } else {
                        serviceDetailHtml = `
                            <div class="cursor-pointer flex justify-between items-center py-2 border-t mt-2 font-semibold text-gray-700" onclick="toggleServiceDropdown(${penyewaanForThisMeja.id})">
                                <span>Tidak ada Layanan Tambahan</span>
                                <i id="service-toggle-icon-${penyewaanForThisMeja.id}" class="fa-solid fa-chevron-down"></i>
                            </div>
                            <ul id="service-list-${penyewaanForThisMeja.id}" class="list-disc list-inside px-2 hidden">
                                <li class="py-0.5 text-gray-500 italic">Belum ada service.</li>
                            </ul>
                        `;
                    }

                    let memberInfoHtml = '';
                    if (penyewaanForThisMeja.member_id) {
                        memberInfoHtml = `<p class="text-sm font-medium text-blue-700">Member: ${penyewaanForThisMeja.member_nama} (${penyewaanForThisMeja.member_kode})</p>`;
                    }

                    if (penyewaanDivEl) {
                        penyewaanDivEl.innerHTML = `
                            <div class="mt-3 bg-white rounded-lg shadow-sm p-3 border border-red-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-700"><i class="fa-solid fa-user"></i> ${penyewaanForThisMeja.nama_penyewa}</p>
                                        ${memberInfoHtml}
                                        <p class="text-xs text-gray-500"><i class="fa-solid fa-clock"></i> Mulai: ${fmtTime(penyewaanForThisMeja.waktu_mulai)} WIB</p>
                                        <p class="text-xs text-gray-500"><i class="fa-solid fa-hourglass-half"></i> Durasi: ${isSepuasnya ? 'Main Sepuasnya' : fmtDur(penyewaanForThisMeja.durasi_jam)}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-block bg-red-100 text-red-700 text-xs font-semibold px-2 py-1 rounded-full">DIPAKAI</span>
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
                            else if (penyewaanForThisMeja.status === 'berlangsung' && !isTimeUp) startCountdown(timerEl, penyewaanForThisMeja.waktu_selesai, penyewaanForThisMeja);
                            else if (isTimeUp) { timerEl.innerText = 'Waktu habis!'; timerEl.classList.add('text-red-700','font-bold'); }
                        }
                    }
                } else {
                    card.classList.remove('bg-green-100'); card.classList.add('bg-neutral-600');
                    if (statusMejaEl) statusMejaEl.innerText = 'Status: kosong';
                    if (penyewaanDivEl) penyewaanDivEl.innerHTML = '';

                    if (!pesanBtnEl) {
                        pesanBtnEl = document.createElement('button'); pesanBtnEl.id = `btn-pesan-${mejaId}`; pesanBtnEl.className = 'mt-2 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50'; pesanBtnEl.innerText = 'Pesan';
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
        const p = currentActiveRentals[penyewaanId]; if (!p) { Swal.fire('Error', 'Detail penyewaan tidak ditemukan.', 'error'); closePaymentModal(); return; }

        const hargaPerJam = parseFloat(p.harga_per_jam || 0);
        let durasiUntukPerhitungan = parseFloat(p.durasi_jam || 0);
        const initialTotalService = parseFloat(p.total_service || 0);
        const serviceDetails = p.service_detail;
        const diskonMemberPersen = parseFloat(p.diskon_member_persen || 0);

        let waktuSelesaiAktual = getCalibratedNow();
        if (p.is_sepuasnya) { durasiUntukPerhitungan = (waktuSelesaiAktual.getTime() - new Date(p.waktu_mulai).getTime()) / (1000 * 60 * 60); }

        const updateTotalDisplay = (diskonPersenKupon = 0) => {
            const subtotalMain = durasiUntukPerhitungan * hargaPerJam;
            const totalBeforeAllDiscounts = subtotalMain + initialTotalService;

            const totalDiskonGabunganPersen = diskonMemberPersen + diskonPersenKupon;
            const finalDiskonPersen = Math.min(totalDiskonGabunganPersen, 100);

            const totalDiskonAmount = (totalBeforeAllDiscounts * finalDiskonPersen) / 100;
            const finalTotal = totalBeforeAllDiscounts - totalDiskonAmount;

            // Diskon member ditampilkan terpisah
            const diskonMemberAmount = (totalBeforeAllDiscounts * diskonMemberPersen) / 100;
            const diskonKuponAmount = (totalBeforeAllDiscounts * diskonPersenKupon) / 100; // Ini adalah diskon kupon yang 'seharusnya' tanpa batasan 100%

            getEl('payment_subtotal_main').innerText = fmtRp(subtotalMain);
            getEl('payment_total_service').innerText = fmtRp(initialTotalService);
            getEl('payment_diskon_member').innerText = `${fmtRp(diskonMemberAmount)} (${diskonMemberPersen}%)`;
            getEl('payment_diskon_kupon').innerText = `${fmtRp(diskonKuponAmount)} (${diskonPersenKupon}%)`;
            getEl('payment_total_final').innerText = fmtRp(finalTotal);
        };

        getEl('payment_meja_nama').innerText = p.meja_nama;
        getEl('payment_nama_penyewa').innerText = p.nama_penyewa;

        if (p.member_id) {
            getEl('payment_member_info').innerText = `Member: ${p.member_nama} (${p.member_kode})`;
            getEl('payment_member_info').style.display = 'block';
        } else {
            getEl('payment_member_info').style.display = 'none';
        }

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
                    if (res.ok) { const data = await res.json(); diskonPersen = parseFloat(data.diskon_persen) || 0; Swal.fire('Berhasil!', `Kupon "${kuponCode}" berhasil diterapkan! Diskon ${diskonPersen}%`, 'success'); }
                    else { const errData = await res.json(); Swal.fire('Error', 'Kupon tidak valid: ' + (errData.message || 'Kode kupon tidak ditemukan atau kadaluarsa.'), 'error'); }
                } catch (e) { Swal.fire('Error', 'Terjadi kesalahan jaringan atau server saat memvalidasi kupon.','error'); console.error('Error validating kupon:', e); }
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

            // Jika bukan member, jangan tampilkan paket member
            if (!isMember && isMemberPaket) {
                return;
            }

            // Jika member, semua paket tampil
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
        
        memberData = { valid: false, nama_member: '', diskon_persen: 0 }; // Reset global

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
                    // Jika member valid
                    memberData = data;
                    memberInfoPreview.innerText = `Member: ${data.nama_member} (Diskon: ${data.diskon_persen}%)`;
                    memberInfoPreview.style.display = 'block';
                    memberInfoPreview.classList.remove('text-red-600');
                    memberInfoPreview.classList.add('text-green-600');

                    namaPenyewaInput.value = data.nama_member;
                    namaPenyewaInput.readOnly = true;

                    populatePaketsDropdown(true); // tampilkan semua paket
                } else {
                    // Jika bukan member
                    memberInfoPreview.innerText = data.message || 'Kode Member tidak valid.';
                    memberInfoPreview.style.display = 'block';
                    memberInfoPreview.classList.remove('text-green-600');
                    memberInfoPreview.classList.add('text-red-600');

                    namaPenyewaInput.value = '';
                    namaPenyewaInput.readOnly = false;

                    populatePaketsDropdown(false); // sembunyikan paket member
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
            // Input kosong
            memberInfoPreview.style.display = 'none';
            namaPenyewaInput.value = '';
            namaPenyewaInput.readOnly = false;

            populatePaketsDropdown(false); // default: sembunyikan paket member
        }
    }, 500)); // Debounce untuk mencegah terlalu banyak request

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

                getEl('formPesan').action = '{{ route('kasir.pesanPaket') }}';

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

            getEl('formPesan').action = '{{ route('kasir.pesanDurasi') }}';
        }
    });

    getEl('is_sepuasnya').addEventListener('change', function() {
        if (getEl('paket_id_select').value) return;
        const durasiWrapper = getEl('durasi_jam_wrapper');
        const durasiInput = getEl('durasi_jam');
        const paketSelect = getEl('paket_id_select');

        if (this.checked) {
            durasiWrapper.style.display = 'none';
            durasiInput.removeAttribute('required');
            durasiInput.value = '';
            paketSelect.style.display = 'none'; // Hide package field when "Main Sepuasnya" is checked
            getEl('formPesan').action = '{{ route('kasir.pesanSepuasnya') }}';
        } else {
            durasiWrapper.style.display = 'block';
            durasiInput.setAttribute('required', 'required');
            paketSelect.style.display = 'block'; // Show package field when unchecked
            getEl('formPesan').action = '{{ route('kasir.pesanDurasi') }}';
        }
    });

    getEl('formAddDuration').addEventListener('submit', async (e) => {
        e.preventDefault(); const pId = getEl('add_duration_penyewaan_id').value; const addDur = getEl('additional_durasi_jam').value;
        const submitBtn = e.target.querySelector('button[type="submit"]'); submitBtn.disabled = true; submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menambah...';
        try {
            const res = await fetch(`{{ url('/kasir/penyewaan/') }}/${pId}/add-duration`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ additional_durasi_jam: addDur }) });
            const data = await res.json(); if (res.ok) { Swal.fire('Berhasil!', data.message, 'success'); closeAddDurationModal(); fetchAndRenderMejas(); }
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
            if (res.ok) { Swal.fire('Berhasil!', data.message, 'success'); closeAddServiceModal(); fetchAndRenderMejas(); }
            else { Swal.fire('Gagal!', data.message || 'Terjadi kesalahan.', 'error'); }
        } catch (error) { Swal.fire('Error!', 'Terjadi kesalahan jaringan atau server saat menambah service.', 'error'); console.error('Error adding service:', error); }
        finally { submitBtn.disabled = false; submitBtn.innerHTML = 'Tambah Service'; }
    });

    getEl('formPembayaran').addEventListener('submit', async (e) => {
        e.preventDefault(); const pId = getEl('payment_penyewaan_id').value; const kupon = getEl('kode_kupon').value;
        const submitBtn = e.target.querySelector('button[type="submit"]'); submitBtn.disabled = true; submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
        try {
            const res = await fetch(`{{ url('/kasir/penyewaan/') }}/${pId}/bayar`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ kode_kupon: kupon }) });
            const data = await res.json();
            if (res.ok) {
                Swal.fire('Berhasil!', data.message + ' Total bayar: ' + fmtRp(data.total_bayar), 'success'); closePaymentModal();
                if (countdownIntervals[pId]) { clearInterval(countdownIntervals[pId]); delete countdownIntervals[pId]; }
                fetchAndRenderMejas();
            } else { Swal.fire('Gagal!', data.message || 'Terjadi kesalahan.', 'error'); console.error('Pembayaran gagal:', data); }
        } catch (error) { Swal.fire('Error!', 'Terjadi kesalahan jaringan atau server saat memproses pembayaran.', 'error'); console.error('Error submitting payment:', error); }
        finally { submitBtn.disabled = false; submitBtn.innerHTML = 'Bayar Sekarang'; }
    });

    const handleQrisPayment = async () => {
        const penyewaanId = getEl('payment_penyewaan_id').value;
        const kuponCode = getEl('kode_kupon').value;
        const qrisButton = getEl('btn-pay-qris');

        qrisButton.disabled = true;
        qrisButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses QRIS...';

        try {
            const res = await fetch(`{{ url('/kasir/penyewaan/') }}/${penyewaanId}/bayar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    kode_kupon: kuponCode,
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
            qrisButton.innerHTML = 'Bayar dengan QRIS';
        }
    };

    const confirmDeletePenyewaan = async (penyewaanId, mejaNama) => {
        if (userRole !== 'admin') {
            Swal.fire({ icon: 'error', title: 'Akses Ditolak!', text: 'Penghapusan meja hanya bisa dilakukan oleh Supervisor.', confirmButtonText: 'OK' });
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
            if (res.ok) {
                Swal.fire('Dihapus!', data.message, 'success');
                if (countdownIntervals[penyewaanId]) { clearInterval(countdownIntervals[penyewaanId]); delete countdownIntervals[penyewaanId]; }
                fetchAndRenderMejas();
            } else { Swal.fire('Gagal!', data.message || 'Terjadi kesalahan saat menghapus.', 'error'); }
        } catch (error) { console.error('Error deleting penyewaan:', error); Swal.fire('Error!', 'Terjadi kesalahan jaringan atau server.', 'error'); }
    };

    document.addEventListener('DOMContentLoaded', () => {
        getEl('formPesan').action = '{{ route('kasir.pesanDurasi') }}';
        populatePaketsDropdown(); // Initial populate
        fetchAndRenderMejas();
        setInterval(fetchAndRenderMejas, 5000);
    });
</script>
@endsection