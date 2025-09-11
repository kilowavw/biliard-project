// dashboard.js

// **PENTING: Variabel ini akan diinisialisasi dari window.* di bagian DOMContentLoaded**
let serverTime = new Date(); // Default sementara, akan di-overwrite
let clientTimeAtLoad = new Date();
let serverClientOffset = 0; // Default, akan dihitung ulang
let userRole = ''; // Default, akan di-overwrite

const getCalibratedNow = () => new Date(new Date().getTime() + serverClientOffset);
const countdownIntervals = {};
let currentActiveRentals = {};
let allAvailableServices = [];
let allAvailablePakets = [];
let memberData = { valid: false, nama_member: '', diskon_persen: 0 };

const getEl = (id) => document.getElementById(id);
const toggleModal = (modalId, show) => { getEl(modalId).style.display = show ? 'flex' : 'none'; };

// Fungsi format waktu yang lebih robust
const fmtTime = (dtStr) => {
    if (!dtStr) return '--:--';
    try {
        const d = new Date(dtStr);
        if (isNaN(d.getTime())) return '--:--'; // Cek apakah tanggal valid
        return `${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`;
    } catch (e) {
        console.error("Error formatting time:", dtStr, e);
        return '--:--';
    }
};

// Fungsi format tanggal lengkap yang lebih robust
const fmtFullDt = (dtStr) => {
    if (!dtStr) return 'N/A';
    try {
        const d = new Date(dtStr);
        if (isNaN(d.getTime())) return 'N/A'; // Cek apakah tanggal valid
        const opt={year:'numeric',month:'short',day:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false,timeZoneName:'short'};
        return d.toLocaleString('id-ID',opt);
    } catch (e) {
        console.error("Error formatting full date:", dtStr, e);
        return 'N/A';
    }
};

const fmtDur = (decHrs) => {
    // Pastikan decHrs adalah angka
    const numericDecHrs = parseFloat(decHrs);
    if (isNaN(numericDecHrs) || numericDecHrs <= 0) return 'N/A';

    const totalMins = numericDecHrs * 60;
    const hrs = Math.floor(totalMins / 60);
    const mins = Math.round(totalMins % 60);
    let res = '';
    if (hrs > 0) res += `${hrs} Jam `;
    if (mins > 0) res += `${mins} Menit`;
    if (hrs === 0 && mins === 0 && numericDecHrs > 0) return 'Kurang dari 1 Menit';
    return res.trim();
};

const fmtRp = (amt) => {
    // Pastikan amt adalah angka
    const numericAmt = parseFloat(amt);
    if (isNaN(numericAmt)) return 'Rp NaN'; // Ubah dari 'Rp 0' agar lebih jelas ada masalah

    return new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',minimumFractionDigits:0}).format(numericAmt);
};
const debounce = (func, delay) => { let timeout; return function(...args) { const context=this; clearTimeout(timeout); timeout=setTimeout(()=>func.apply(context,args),delay); }; };

const openModal = async (mejaId) => {
    getEl('modal_meja_id').value = mejaId;
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
    getEl('durasi_jam_wrapper').style.display = 'block';
    getEl('durasi_jam').required = true;

    getEl('formPesan').action = window.routes.pesanDurasi;
    populatePaketsDropdown(false);
    toggleModal('pesanModal', true);
};
const closeModal = () => toggleModal('pesanModal', false);
const openAddDurationModal = (penyewaanId) => {
    const p=currentActiveRentals[penyewaanId];
    if (!p) { Swal.fire('Error', 'Penyewaan tidak ditemukan.', 'error'); return; }
    // Memastikan p.durasi_jam di-parse sebagai float sebelum perbandingan
    const currentDurasiFloat = parseFloat(p.durasi_jam);
    if (p.paket_id && !isNaN(currentDurasiFloat) && currentDurasiFloat > 0) {
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
    fetchPaymentDetails(penyewaanId); toggleModal('paymentModal', true);
};
const closePaymentModal = () => toggleModal('paymentModal', false);

const startCountdown = (el, waktuSelesaiStr, penyewaanData) => {
    const waktuSelesai = new Date(waktuSelesaiStr);
    // Tambahkan pemeriksaan validitas tanggal
    if (isNaN(waktuSelesai.getTime())) {
        el.innerText = 'Waktu tidak valid!';
        el.classList.add('text-red-700', 'font-bold');
        return;
    }

    const pId = penyewaanData.id;
    if (countdownIntervals[pId]) clearInterval(countdownIntervals[pId]);
    const interval = setInterval(() => {
        const now = getCalibratedNow();
        const dist = waktuSelesai.getTime() - now.getTime();
        if (dist <= 0) {
            clearInterval(interval);
            el.innerText = 'Waktu habis!';
            el.classList.add('text-red-700','font-bold');
            handleTimeUpUI(penyewaanData);
        } else {
            const h=String(Math.floor((dist/(1000*60*60))%24)).padStart(2,'0');
            const m=String(Math.floor((dist/(1000*60))%60)).padStart(2,'0');
            const s=String(Math.floor((dist/1000)%60)).padStart(2,'0');
            el.innerText=`${h}:${m}:${s}`;
        }
    }, 1000);
    countdownIntervals[pId] = interval;
};

const startRunningTimer = (el, waktuMulaiStr, penyewaanData) => {
    const waktuMulai = new Date(waktuMulaiStr);
    // Tambahkan pemeriksaan validitas tanggal
    if (isNaN(waktuMulai.getTime())) {
        el.innerText = 'Waktu tidak valid!';
        el.classList.add('text-red-700', 'font-bold');
        return;
    }

    const pId = penyewaanData.id;
    if (countdownIntervals[pId]) clearInterval(countdownIntervals[pId]);
    const interval = setInterval(() => {
        const now = getCalibratedNow();
        const elapsed = now.getTime() - waktuMulai.getTime();
        const h=String(Math.floor((elapsed/(1000*60*60))%24)).padStart(2,'0');
        const m=String(Math.floor((elapsed/(1000*60))%60)).padStart(2,'0');
        const s=String(Math.floor((elapsed/1000)%60)).padStart(2,'0');
        el.innerText=`${h}:${m}:${s}`;
    }, 1000);
    countdownIntervals[pId] = interval;
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
            ${userRole === 'admin' || userRole === 'supervisor' ? `
            <button type="button" class="px-3 py-1 bg-red-200 hover:bg-red-300 text-red-900 rounded-md text-xs" onclick="event.preventDefault(); confirmDeletePenyewaan(${penyewaanData.id}, '${penyewaanData.meja_nama}');">
                <i class="fa-solid fa-trash"></i> Hapus
            </button>` : ''}
            <button type="button" class="px-3 py-1 bg-yellow-200 hover:bg-yellow-300 text-yellow-900 rounded-md text-xs" onclick="event.preventDefault(); openAddServiceModal(${penyewaanData.id});">
                <i class="fa-solid fa-wine-bottle"></i> Tambah Service
            </button>
            <span class="px-3 py-1 bg-red-100 text-red-700 rounded-md text-xs">Waktu Habis! Menunggu Pembayaran.</span>
            <button type="button" class="px-3 py-1 bg-green-500 text-white rounded-md text-xs hover:bg-green-600" onclick="event.preventDefault(); openPaymentModal(${penyewaanData.id});">
                <i class="fa-solid fa-cash-register"></i> Bayar Sekarang
            </button>
        `;
    }

    updateMejaStatusOnServer(mejaId, 'waktu_habis');
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
                const res = await fetch(`${window.routes.basePath}/penyewaan/${penyewaanId}/remove-service`, {
                    method: 'DELETE', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken }, body: JSON.stringify({ service_id: serviceId })
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
        const res = await fetch(window.routes.apiPenyewaanAktif);
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

                if (userRole === 'admin' || userRole === 'supervisor') {
                    deleteButtonHtml = `
                        <button type="button" class="px-3 py-1 bg-red-200 hover:bg-red-300 text-red-900 rounded-md text-xs" onclick="event.preventDefault(); confirmDeletePenyewaan(${penyewaanForThisMeja.id}, '${penyewaanForThisMeja.meja_nama}');">
                            <i class="fa-solid fa-trash"></i> Hapus
                        </button>
                    `;
                }

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
                } else if (isSepuasnya || isTimeUpBackend) {
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
                card.classList.remove('bg-green-100', 'bg-red-200');
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

const updateMejaStatusOnServer = (mejaId, newStatus) => {
    console.log(`Updating meja ${mejaId} status to ${newStatus} on server.`);
    // Implementasi AJAX ke backend untuk update status meja
};

const fetchPaymentDetails = async (penyewaanId) => {
    if (getEl('paymentModal').style.display === 'flex') return;

    const formPembayaran = getEl('formPembayaran');
    const submitBtn = formPembayaran.querySelector('button[type="submit"]');
    const qrisBtn = getEl('btn-pay-qris');
    const printBtn = getEl('btn-print-struk');

    submitBtn.disabled = true;
    qrisBtn.disabled = true;
    printBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memuat...';
    qrisBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memuat...';
    printBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memuat...';

    const p = currentActiveRentals[penyewaanId];
    if (!p) {
        Swal.fire('Error', 'Detail penyewaan tidak ditemukan.', 'error');
        closePaymentModal();
        return;
    }

    getEl('payment_penyewaan_id').value = penyewaanId;
    getEl('kode_kupon').value = '';

    const serviceDetailsUntukModalBayar = (p.service_detail || []).filter(s => (s.subtotal || 0) > 0);
    const originalIsSepuasnya = p.is_sepuasnya;

    const updatePaymentDisplayFromBackend = async (kuponCode = '') => {
        try {
            const url = `${window.routes.basePath}/penyewaan/${penyewaanId}/calculate-payment?${new URLSearchParams({ kode_kupon: kuponCode })}`;
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();

            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Gagal menghitung pembayaran dari backend.');
            }

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

            getEl('payment_mode_sepuasnya_info').style.display = originalIsSepuasnya || (p.paket_id && p.paket && (parseFloat(p.paket.isi_paket.durasi_jam || '1') === 0.0)) ? 'block' : 'none';
            getEl('payment_waktu_mulai').innerText = fmtFullDt(p.waktu_mulai);
            getEl('payment_waktu_selesai_terjadwal').innerText = p.waktu_selesai ? fmtFullDt(p.waktu_selesai) : 'N/A';
            getEl('payment_waktu_selesai_aktual').innerText = data.calculated_waktu_selesai ? fmtFullDt(data.calculated_waktu_selesai) : 'N/A';

            getEl('label_harga_sewa').innerText = p.paket_id ? 'Harga Paket' : 'Harga Per Jam';
            getEl('payment_harga_per_jam_or_paket').innerText = fmtRp(p.harga_per_jam);
            getEl('payment_durasi').innerText = originalIsSepuasnya || (p.paket_id && p.paket && (parseFloat(p.paket.isi_paket.durasi_jam || '1') === 0.0)) ? 'N/A (Main Sepuasnya)' : fmtDur(data.calculated_durasi_jam);


            getEl('payment_subtotal_main').innerText = fmtRp(data.subtotal_sewa);
            getEl('payment_total_service').innerText = fmtRp(data.total_service_tambahan);

            // Pastikan nilai diskon tidak null/undefined sebelum ditampilkan
            const diskonMemberAmount = data.diskon_member_amount !== undefined && data.diskon_member_amount !== null ? data.diskon_member_amount : 0;
            const diskonKuponAmount = data.diskon_kupon_amount !== undefined && data.diskon_kupon_amount !== null ? data.diskon_kupon_amount : 0;
            const diskonMemberPersen = data.diskon_member_persen !== undefined && data.diskon_member_persen !== null ? data.diskon_member_persen : 0;
            const diskonKuponPersen = data.diskon_kupon_persen !== undefined && data.diskon_kupon_persen !== null ? data.diskon_kupon_persen : 0;


            getEl('payment_diskon_member').innerText = `${fmtRp(diskonMemberAmount)} (${diskonMemberPersen}%)`;
            getEl('payment_diskon_kupon').innerText = `${fmtRp(diskonKuponAmount)} (${diskonKuponPersen}%)`;
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
            submitBtn.disabled = false;
            qrisBtn.disabled = false;
            printBtn.disabled = false;
            submitBtn.innerHTML = 'Bayar Sekarang';
            qrisBtn.innerHTML = 'Bayar dengan QRIS';
            printBtn.innerHTML = 'Cetak Struk';
        }
    };

    await updatePaymentDisplayFromBackend();

    getEl('kode_kupon').oninput = debounce(async function() {
        submitBtn.disabled = true; qrisBtn.disabled = true; printBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menghitung...';
        qrisBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menghitung...';
        printBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menghitung...';
        const kuponCode = this.value.trim();
        await updatePaymentDisplayFromBackend(kuponCode);
    }, 500);

    toggleModal('paymentModal', true);
};

const fetchAndRenderServicesForAdd = async () => {
    try {
        const res = await fetch(window.routes.apiServices);
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
            const res = await fetch(`${window.routes.apiMemberValidate}?kode_member=${encodeURIComponent(kodeMember)}`, {
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

            // Pastikan isiPaket.durasi_jam adalah angka yang valid
            const durasiPaket = parseFloat(isiPaket.durasi_jam);

            if (isNaN(durasiPaket) || durasiPaket === 0) { // Jika durasi 0 atau NaN
                isSepuasnyaCheckbox.checked = true;
                durasiJamWrapper.style.display = 'none';
                durasiJamInput.removeAttribute('required');
                durasiJamInput.value = ''; // Kosongkan nilai
            } else {
                isSepuasnyaCheckbox.checked = false;
                durasiJamWrapper.style.display = 'block';
                durasiJamInput.setAttribute('required', 'required');
                durasiJamInput.value = durasiPaket;
            }

            nonPaketOptionsDiv.style.display = 'none';
            isSepuasnyaCheckbox.disabled = true;
            durasiJamInput.disabled = true;

            let descHtml = [];

            if (isiPaket.harga_paket !== undefined && isiPaket.harga_paket !== null) descHtml.push(`<p><strong>Harga:</strong> ${fmtRp(isiPaket.harga_paket)}</p>`);
            if (isiPaket.durasi_jam !== undefined && isiPaket.durasi_jam !== null) {
                if (parseFloat(isiPaket.durasi_jam) > 0) descHtml.push(`<p><strong>Durasi:</strong> ${fmtDur(isiPaket.durasi_jam)}</p>`);
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

            getEl('formPesan').action = window.routes.pesanPaket;

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

        getEl('formPesan').action = window.routes.pesanDurasi;
    }
});

getEl('is_sepuasnya').addEventListener('change', function() {
    if (getEl('paket_id_select').value) return;
    const durasiWrapper = getEl('durasi_jam_wrapper');
    const durasiInput = getEl('durasi_jam');
    const paketSelect = getEl('paket_id_select');
    const paketDeskripsiPreview = getEl('paket_deskripsi_preview');

    if (this.checked) {
        durasiWrapper.style.display = 'none';
        durasiInput.removeAttribute('required');
        durasiInput.value = '';
        paketSelect.style.display = 'none';
        paketDeskripsiPreview.style.display = 'none';
        paketDeskripsiPreview.innerHTML = '';
        getEl('formPesan').action = window.routes.pesanSepuasnya;
    } else {
        durasiWrapper.style.display = 'block';
        durasiInput.setAttribute('required', 'required');
        paketSelect.style.display = 'block';
        populatePaketsDropdown(memberData.valid);
        getEl('formPesan').action = window.routes.pesanDurasi;
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
        const res = await fetch(form.action, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' },
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            Swal.fire('Berhasil!', data.message, 'success');
            closeModal();
            fetchAndRenderMejas();
        } else {
            let errorMessage = data.message || 'Terjadi kesalahan.';
            if (data.errors) {
                const errorList = Object.values(data.errors).map(err => `<li>${err.join(', ')}</li>`).join('');
                errorMessage += `<ul class="text-left mt-2 list-disc list-inside">${errorList}</ul>`;
            }
            Swal.fire('Gagal!', errorMessage, 'error');
        }
    } catch (error) {
        console.error('Error submitting form:', error);
        Swal.fire('Error!', 'Terjadi kesalahan jaringan atau server.', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Mulai';
    }
});


getEl('formAddDuration').addEventListener('submit', async (e) => {
    e.preventDefault(); const pId = getEl('add_duration_penyewaan_id').value; const addDur = getEl('additional_durasi_jam').value;
    const submitBtn = e.target.querySelector('button[type="submit"]'); submitBtn.disabled = true; submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menambah...';
    try {
        const res = await fetch(`${window.routes.basePath}/penyewaan/${pId}/add-duration`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken }, body: JSON.stringify({ additional_durasi_jam: addDur }) });
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
        const res = await fetch(`${window.routes.basePath}/penyewaan/${pId}/add-service`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken }, body: JSON.stringify({ services: selSrv }) });
        const data = await res.json();
        if (data.success) { Swal.fire('Berhasil!', data.message, 'success'); closeAddServiceModal(); fetchAndRenderMejas(); }
        else { Swal.fire('Gagal!', data.message || 'Terjadi kesalahan.', 'error'); }
    } catch (error) { Swal.fire('Error!', 'Terjadi kesalahan jaringan atau server saat menambah service.', 'error'); console.error('Error adding service:', error); }
    finally { submitBtn.disabled = false; submitBtn.innerHTML = 'Tambah Service'; }
});

getEl('formPembayaran').addEventListener('submit', async (e) => {
    e.preventDefault();
    const pId = getEl('payment_penyewaan_id').value;
    const kupon = getEl('kode_kupon').value;
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const qrisBtn = getEl('btn-pay-qris');
    const printBtn = getEl('btn-print-struk');

    submitBtn.disabled = true;
    qrisBtn.disabled = true;
    printBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';

    try {
        const res = await fetch(`${window.routes.basePath}/penyewaan/${pId}/bayar`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken },
            body: JSON.stringify({ kode_kupon: kupon })
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
        qrisBtn.disabled = false;
        printBtn.disabled = false;
        submitBtn.innerHTML = 'Bayar Sekarang';
    }
});

const handleQrisPayment = async () => {
    const penyewaanId = getEl('payment_penyewaan_id').value;
    const kuponCode = getEl('kode_kupon').value;
    const qrisButton = getEl('btn-pay-qris');
    const normalPayButton = document.querySelector('#formPembayaran button[type="submit"]');
    const printBtn = getEl('btn-print-struk');

    qrisButton.disabled = true;
    normalPayButton.disabled = true;
    printBtn.disabled = true;
    qrisButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses QRIS...';

    try {
        const res = await fetch(`${window.routes.basePath}/penyewaan/${penyewaanId}/bayar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
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
        normalPayButton.disabled = false;
        printBtn.disabled = false;
        qrisButton.innerHTML = 'Bayar dengan QRIS';
    }
};

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
        const res = await fetch(`${window.routes.basePath}/penyewaan/${penyewaanId}/delete`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken }
        });
        const data = await res.json();
        if (res.ok && data.success) {
            Swal.fire('Dihapus!', data.message, 'success');
            if (countdownIntervals[penyewaanId]) { clearInterval(countdownIntervals[penyewaanId]); delete countdownIntervals[penyewaanId]; }
            fetchAndRenderMejas();
        } else { Swal.fire('Gagal!', data.message || 'Terjadi kesalahan saat menghapus.', 'error'); }
    } catch (error) { console.error('Error deleting penyewaan:', error); Swal.fire('Error!', 'Terjadi kesalahan jaringan atau server.', 'error'); }
};

const printPaymentStruk = async () => {
    const penyewaanId = getEl('payment_penyewaan_id').value;
    const p = currentActiveRentals[penyewaanId];
    if (!p) {
        Swal.fire('Error', 'Detail penyewaan tidak ditemukan untuk struk.', 'error');
        return;
    }

    let paymentData;
    try {
        const url = `${window.routes.basePath}/penyewaan/${penyewaanId}/calculate-payment?${new URLSearchParams({ kode_kupon: getEl('kode_kupon').value.trim() })}`;
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();

        if (!res.ok || !data.success) {
            throw new Error(data.message || 'Gagal mengambil data pembayaran untuk struk.');
        }
        paymentData = data;
    } catch (error) {
        Swal.fire('Error', 'Gagal memuat data pembayaran untuk struk: ' + error.message, 'error');
        console.error('Error fetching payment details for print:', error);
        return;
    }

    const waktuSelesaiAktualStruk = paymentData.calculated_waktu_selesai ? fmtFullDt(paymentData.calculated_waktu_selesai) : 'N/A';
    const durasiAktualStruk = paymentData.calculated_durasi_jam;

    const diskonMemberAmount = paymentData.diskon_member_amount !== undefined && paymentData.diskon_member_amount !== null ? paymentData.diskon_member_amount : 0;
    const diskonKuponAmount = paymentData.diskon_kupon_amount !== undefined && paymentData.diskon_kupon_amount !== null ? paymentData.diskon_kupon_amount : 0;
    const diskonMemberPersen = paymentData.diskon_member_persen !== undefined && paymentData.diskon_member_persen !== null ? paymentData.diskon_member_persen : 0;
    const diskonKuponPersen = paymentData.diskon_kupon_persen !== undefined && paymentData.diskon_kupon_persen !== null ? paymentData.diskon_kupon_persen : 0;

    let strukContent = `
        <div class="text-center">
            <h3>TOKO XYZ</h3>
            <p>Jl. Raya No. 123, Kota Anda</p>
            <p>Telp: 0812-3456-7890</p>
            <p>---------------------------------</p>
            <p><strong>STRUK PEMBAYARAN</strong></p>
            <p>---------------------------------</p>
        </div>
        <p class="flex-container"><span class="flex-item-left">Tanggal:</span> <span class="flex-item-right">${fmtFullDt(getCalibratedNow())}</span></p>
        <p class="flex-container"><span class="flex-item-left">Meja:</span> <span class="flex-item-right">${p.meja_nama}</span></p>
        <p class="flex-container"><span class="flex-item-left">Penyewa:</span> <span class="flex-item-right">${p.nama_penyewa}</span></p>
    `;
    if (p.member_id) {
        strukContent += `<p class="flex-container"><span class="flex-item-left">Member:</span> <span class="flex-item-right">${p.member_nama} (${p.member_kode})</span></p>`;
    }
    strukContent += `
        <p class="separator"></p>
        <p class="flex-container"><span class="flex-item-left">Waktu Mulai:</span> <span class="flex-item-right">${fmtFullDt(p.waktu_mulai)}</span></p>
        <p class="flex-container"><span class="flex-item-left">Waktu Selesai:</span> <span class="flex-item-right">${waktuSelesaiAktualStruk}</span></p>
        <p class="flex-container"><span class="flex-item-left">Durasi:</span> <span class="flex-item-right">${p.is_sepuasnya ? 'Main Sepuasnya' : fmtDur(durasiAktualStruk)}</span></p>
    `;
    if (p.paket_id && p.nama_paket) {
        strukContent += `<p class="flex-container"><span class="flex-item-left">Paket:</span> <span class="flex-item-right">${p.nama_paket}</span></p>`;
        strukContent += `<p class="flex-container"><span class="flex-item-left">Harga Paket:</span> <span class="flex-item-right">${fmtRp(p.harga_per_jam)}</span></p>`;
    } else {
        strukContent += `<p class="flex-container"><span class="flex-item-left">Harga Per Jam:</span> <span class="flex-item-right">${fmtRp(p.harga_per_jam)}</span></p>`;
    }
    strukContent += `
        <p class="separator"></p>
        <p class="flex-container"><span class="flex-item-left">Subtotal Sewa:</span> <span class="flex-item-right">${fmtRp(paymentData.subtotal_sewa)}</span></p>
    `;

    const serviceDetailsUntukStruk = (p.service_detail || []).filter(s => (s.subtotal || 0) > 0);
    if (serviceDetailsUntukStruk.length > 0) {
        strukContent += `<p class="bold">Layanan Tambahan:</p>`;
        serviceDetailsUntukStruk.forEach(s => {
            strukContent += `<p class="service-item">- ${s.nama} (${s.jumlah}x): <span class="text-right">${fmtRp(s.subtotal)}</span></p>`;
        });
        strukContent += `<p class="flex-container"><span class="flex-item-left bold">Total Layanan:</span> <span class="flex-item-right bold">${fmtRp(paymentData.total_service_tambahan)}</span></p>`;
    } else {
        strukContent += `<p>Tidak ada layanan tambahan.</p>`;
    }

    strukContent += `
        <p class="separator"></p>
        <p class="flex-container"><span class="flex-item-left bold">Diskon Member:</span> <span class="flex-item-right bold text-red-500">-${fmtRp(diskonMemberAmount)} (${diskonMemberPersen}%)</span></p>
        <p class="flex-container"><span class="flex-item-left bold">Diskon Kupon:</span> <span class="flex-item-right bold text-red-500">-${fmtRp(diskonKuponAmount)} (${diskonKuponPersen}%)</span></p>
        <p class="flex-container"><span class="flex-item-left bold text-xl">TOTAL BAYAR:</span> <span class="flex-item-right bold text-xl">${fmtRp(paymentData.final_total_bayar)}</span></p>
        <p class="separator"></p>
        <div class="text-center">
            <p>Terima Kasih Atas Kunjungan Anda!</p>
            <p>---------------------------------</p>
            <p>Powered by Sistem Kasir V1.0</p>
        </div>
    `;

    let printWindow = window.open('', '', 'height=500,width=500');
    printWindow.document.write('<html><head><title>Struk Pembayaran</title>');
    printWindow.document.write('<style>');
    printWindow.document.write(`
        body {
            font-family: 'monospace', 'Courier New', monospace;
            font-size: 10px;
            line-height: 1.2;
            color: black;
            margin: 0;
            padding: 5px;
            box-sizing: border-box;
            width: 58mm;
        }
        h1, h2, h3 { text-align: center; margin: 0; padding: 0; font-size: 1.1em; font-weight: bold; }
        p, ul, li { margin: 0; padding: 0; font-size: 1em; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .separator { border-top: 1px dashed black; margin: 5px 0; }
        .flex-container { display: flex; justify-content: space-between; align-items: flex-start; }
        .flex-item-left { text-align: left; }
        .flex-item-right { text-align: right; }
        .service-item { display: flex; justify-content: space-between; }
    `);
    printWindow.document.write('</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(strukContent);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();
};

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

document.addEventListener('DOMContentLoaded', () => {
    // Inisialisasi variabel global dari window.*
    serverTime = window.serverTime;
    clientTimeAtLoad = new Date(); // Dapatkan waktu klien saat ini
    serverClientOffset = serverTime.getTime() - clientTimeAtLoad.getTime();
    userRole = window.userRole;
    allAvailablePakets = window.initialPakets; // Inisialisasi dari global variable

    getEl('formPesan').action = window.routes.pesanDurasi;
    populatePaketsDropdown(memberData.valid);
    fetchAndRenderMejas();
    setInterval(fetchAndRenderMejas, 5000);
});