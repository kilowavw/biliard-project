@extends('default')

@section('title', 'Dashboard Kasir')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4 text-black">Dashboard Kasir</h1>

    <div id="meja-container" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 text-black">
        <!-- Meja akan dimuat via AJAX/JS secara dinamis, tapi kita tetap perlu placeholder awal -->
        @foreach ($mejas as $meja)
        <div id="meja-card-{{ $meja->id }}" class="p-4 border rounded shadow @if($meja->status === 'dipakai') bg-red-100 @else bg-green-100 @endif">
            <h2 class="text-lg font-semibold">{{ $meja->nama_meja }}</h2>
            <p id="status-meja-{{ $meja->id }}">Status: {{ $meja->status }}</p>

            {{-- Button "Pesan" atau placeholder untuk penyewaan aktif --}}
            @if ($meja->status === 'kosong')
            <button id="btn-pesan-{{ $meja->id }}" onclick="openModal({{ $meja->id }})" class="mt-2 btn btn-primary">Pesan</button>
            @endif
            {{-- Div ini akan selalu ada untuk ditempati detail penyewaan atau dikosongkan --}}
            <div id="penyewaan-{{ $meja->id }}"></div>
        </div>
        @endforeach
    </div>
</div>

<!-- Modal Pemesanan -->
<div id="pesanModal" style="display:none" class="fixed inset-0 text-black bg-gray-600 bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
        <h2 class="text-xl font-bold mb-4">Pemesanan Meja</h2>
        <form id="formPesan" action="{{ route('kasir.pesan') }}" method="POST">
            @csrf
            <input type="hidden" name="meja_id" id="modal_meja_id">
            <div class="mb-3">
                <label for="nama_penyewa" class="block text-sm font-medium text-gray-700">Nama Penyewa</label>
                <input type="text" name="nama_penyewa" id="nama_penyewa" required class="mt-1 block w-full border rounded p-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mb-3">
                <label for="durasi_jam" class="block text-sm font-medium text-gray-700">Durasi (Jam, contoh: 1.5 untuk 1 jam 30 menit)</label>
                <input type="number" name="durasi_jam" id="durasi_jam" required min="0.01" step="0.01" class="mt-1 block w-full border rounded p-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex justify-end mt-4">
                <button type="button" onclick="closeModal()" class="btn btn-secondary mr-2 px-4 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Batal</button>
                <button type="submit" class="btn btn-primary px-4 py-2 rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Mulai</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pembayaran -->
<div id="paymentModal" style="display:none" class="fixed inset-0 text-black bg-gray-600 bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
        <h2 class="text-xl font-bold mb-4">Detail Pembayaran</h2>
        <form id="formPembayaran">
            @csrf
            <input type="hidden" name="penyewaan_id" id="payment_penyewaan_id">
            <p>Meja: <strong id="payment_meja_nama"></strong></p>
            <p>Penyewa: <strong id="payment_nama_penyewa"></strong></p>
            <p>Durasi Booking: <strong id="payment_durasi"></strong></p>
            <p>Waktu Mulai: <strong id="payment_waktu_mulai"></strong></p>
            <p>Waktu Selesai: <strong id="payment_waktu_selesai"></strong></p>
            <p>Harga Per Jam: <strong id="payment_harga_per_jam"></strong></p>
            <p>Subtotal Main: <strong id="payment_subtotal_main"></strong></p>
            <p>Total Service: <strong id="payment_total_service"></strong></p>
            {{-- Menampilkan detail service --}}
            <ul id="payment_service_detail" class="list-disc list-inside text-sm text-gray-600 mb-2"></ul>

            <div class="mb-3 mt-4">
                <label for="kode_kupon" class="block text-sm font-medium text-gray-700">Kode Kupon (Opsional)</label>
                <input type="text" name="kode_kupon" id="kode_kupon" class="mt-1 block w-full border rounded p-2 focus:ring-blue-500 focus:border-blue-500">
                <small class="text-gray-500">Biarkan kosong jika tidak ada kupon.</small>
            </div>
            <p class="text-lg font-bold">Diskon: <strong id="payment_diskon" class="text-red-500"></strong></p>
            <p class="text-xl font-bold">Total Pembayaran: <strong id="payment_total_final" class="text-green-600"></strong></p>
            <div class="flex justify-end mt-4">
                <button type="button" onclick="closePaymentModal()" class="btn btn-secondary mr-2 px-4 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Batal</button>
                <button type="submit" class="btn btn-primary px-4 py-2 rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">Bayar Sekarang</button>
            </div>
        </form>
    </div>
</div>


<script>
    
    const serverTime = new Date("{{ $serverTime }}");
    const clientTimeAtLoad = new Date();
    
    const serverClientOffset = serverTime.getTime() - clientTimeAtLoad.getTime();

    
    function getCalibratedNow() {
        return new Date(new Date().getTime() + serverClientOffset);
    }

    
    const countdownIntervals = {};
    
    let currentActiveRentals = {};


    function openModal(mejaId) {
        document.getElementById('modal_meja_id').value = mejaId;
        document.getElementById('pesanModal').style.display = 'flex';
        
        document.getElementById('formPesan').reset();
    }

    function closeModal() {
        document.getElementById('pesanModal').style.display = 'none';
    }

    
    function openPaymentModal(penyewaanId) {
        
        if (document.getElementById('paymentModal').style.display === 'flex') {
            console.log('Payment modal already open.');
            return;
        }

        document.getElementById('payment_penyewaan_id').value = penyewaanId;
        document.getElementById('paymentModal').style.display = 'flex';
        
        document.getElementById('kode_kupon').value = '';
        fetchPaymentDetails(penyewaanId);
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').style.display = 'none';
        
        fetchAndRenderMejas();
    }

    function formatWaktu(datetimeStr) {
        const date = new Date(datetimeStr);
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${hours}:${minutes}`;
    }

    function formatFullDateTime(datetimeStr) {
        const date = new Date(datetimeStr);
        const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false, timeZoneName: 'short' };
        return date.toLocaleString('id-ID', options);
    }


    function formatDuration(decimalHours) {
        const totalMinutes = decimalHours * 60;
        const hours = Math.floor(totalMinutes / 60);
        const minutes = Math.round(totalMinutes % 60); 
        let result = '';
        if (hours > 0) result += `${hours} Jam `;
        if (minutes > 0) result += `${minutes} Menit`;
        if (hours === 0 && minutes === 0 && decimalHours > 0) return 'Kurang dari 1 Menit'; 
        if (hours === 0 && minutes === 0 && decimalHours === 0) return '0 Menit';
        return result.trim();
    }

    function formatRupiah(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    }

    /**
     * Memulai atau memperbarui countdown untuk sebuah penyewaan.
     * @param {HTMLElement} el Elemen SPAN untuk menampilkan countdown.
     * @param {string} waktuSelesaiStr Waktu selesai dalam format ISO8601 string.
     * @param {object} penyewaanData Objek data penyewaan lengkap.
     */
    function startCountdown(el, waktuSelesaiStr, penyewaanData) {
        const waktuSelesai = new Date(waktuSelesaiStr);
        const penyewaanId = penyewaanData.id;
        const mejaId = penyewaanData.meja_id;

        
        if (countdownIntervals[penyewaanId]) {
            clearInterval(countdownIntervals[penyewaanId]);
        }

        const interval = setInterval(() => {
            const now = getCalibratedNow(); 
            const distance = waktuSelesai.getTime() - now.getTime(); 

            if (distance <= 0) {
                clearInterval(interval);
                el.innerText = 'Waktu habis!';
                el.classList.add('text-red-700', 'font-bold');

                
                handleTimeUp(penyewaanData);

            } else {
                const h = String(Math.floor((distance / (1000 * 60 * 60)) % 24)).padStart(2, '0');
                const m = String(Math.floor((distance / (1000 * 60)) % 60)).padStart(2, '0');
                const s = String(Math.floor((distance / 1000) % 60)).padStart(2, '0');
                el.innerText = `${h}:${m}:${s}`;
            }
        }, 1000); 

        countdownIntervals[penyewaanId] = interval; 
    }

    /**
     * @param {object} penyewaanData Objek data penyewaan lengkap.
     */
    async function handleTimeUp(penyewaanData) {
        const penyewaanId = penyewaanData.id;
        const mejaId = penyewaanData.meja_id;
        console.log(`Waktu untuk penyewaan ID ${penyewaanId} di meja ${mejaId} habis atau tombol bayar diklik.`);

        
        const actionsContainer = document.querySelector(`#penyewaan-${mejaId} .flex-wrap`);
        if (actionsContainer) {
            actionsContainer.innerHTML = `
                <a href="#" class="btn btn-xs bg-green-200 hover:bg-green-300 text-green-900 px-3 py-1 rounded"
                   onclick="event.preventDefault(); openPaymentModal(${penyewaanId});">
                    💵 Bayar Sekarang
                </a>
            `;
        }

        
        try {
            
            
            const waktuSelesaiDB = new Date(penyewaanData.waktu_selesai);
            const nowCalibrated = getCalibratedNow();

            if (penyewaanData.status === 'berlangsung' && (waktuSelesaiDB > nowCalibrated)) {
                 console.log(`Mengupdate waktu_selesai penyewaan ${penyewaanId} di backend.`);
                 const response = await fetch(`{{ url('/kasir/penyewaan/') }}/${penyewaanId}/finish`, {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': '{{ csrf_token() }}',
                     },
                     body: JSON.stringify({})
                 });

                 if (!response.ok) {
                     const errorData = await response.json();
                     console.error('Gagal mengupdate waktu_selesai di backend:', errorData.message);
                 } else {
                     console.log('Waktu selesai di backend berhasil diupdate.');
                 }
            }


            
            openPaymentModal(penyewaanId);

        } catch (error) {
            console.error('Error saat mengirim request finishPenyewaan atau membuka modal:', error);
            alert('Terjadi kesalahan saat mengakhiri penyewaan atau memuat pembayaran.');
        }
    }


    /**
     * Memuat ulang semua data meja dan penyewaan aktif dan merender ulang.
     * Ini digunakan untuk polling dan setelah transaksi sukses.
     */
    async function fetchAndRenderMejas() {
        try {
            const response = await fetch('{{ route('kasir.api.penyewaanAktif') }}');
            const activeRentals = await response.json();
            currentActiveRentals = {}; 
            activeRentals.forEach(p => {
                currentActiveRentals[p.id] = p;
            });

            
            const mejaCards = document.querySelectorAll('[id^="meja-card-"]');

            
            mejaCards.forEach(card => {
                const mejaId = parseInt(card.id.replace('meja-card-', ''));
                const penyewaanForThisMeja = activeRentals.find(p => p.meja_id === mejaId);

                const statusMejaEl = card.querySelector(`#status-meja-${mejaId}`);
                let pesanBtnEl = card.querySelector(`#btn-pesan-${mejaId}`); 
                const penyewaanDivEl = card.querySelector(`#penyewaan-${mejaId}`);

                if (penyewaanForThisMeja) {
                    
                    card.classList.remove('bg-green-100');
                    card.classList.add('bg-red-100');
                    if (statusMejaEl) statusMejaEl.innerText = 'Status: dipakai';

                    
                    if (pesanBtnEl) pesanBtnEl.style.display = 'none';

                    const isTimeUp = (new Date(penyewaanForThisMeja.waktu_selesai)).getTime() - getCalibratedNow().getTime() <= 0;
                    let actionButtonsHtml = '';

                    
                    if (!isTimeUp && penyewaanForThisMeja.status === 'berlangsung') {
                         actionButtonsHtml = `
                            <a href="#" class="btn btn-xs bg-gray-200 hover:bg-gray-300 text-gray-800 px-3 py-1 rounded">
                                ➕ Tambah Waktu
                            </a>
                            <a href="#" class="btn btn-xs bg-yellow-200 hover:bg-yellow-300 text-yellow-900 px-3 py-1 rounded">
                                🍹 Tambah Service
                            </a>
                            <a href="#" class="btn btn-xs bg-green-200 hover:bg-green-300 text-green-900 px-3 py-1 rounded"
                               onclick="event.preventDefault(); handleTimeUp(currentActiveRentals[${penyewaanForThisMeja.id}]);">
                                💵 Bayar
                            </a>
                        `;
                    } else { 
                        actionButtonsHtml = `
                            <a href="#" class="btn btn-xs bg-green-200 hover:bg-green-300 text-green-900 px-3 py-1 rounded"
                               onclick="event.preventDefault(); openPaymentModal(${penyewaanForThisMeja.id});">
                                💵 Bayar Sekarang
                            </a>
                        `;
                    }

                    if (penyewaanDivEl) {
                        penyewaanDivEl.innerHTML = `
                            <div class="mt-3 bg-white rounded-lg shadow p-3 border border-red-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-700">
                                            👤 ${penyewaanForThisMeja.nama_penyewa}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            ⏰ Mulai: ${formatWaktu(penyewaanForThisMeja.waktu_mulai)} WIB
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            🕒 Durasi: ${formatDuration(penyewaanForThisMeja.durasi_jam)}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-block bg-red-100 text-red-700 text-xs font-semibold px-2 py-1 rounded">
                                            DIPAKAI
                                        </span>
                                        <p class="text-sm font-bold text-red-700 mt-1">
                                            ⏳ <span id="countdown-${penyewaanForThisMeja.id}">--:--:--</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    ${actionButtonsHtml}
                                </div>
                            </div>
                        `;
                        
                        const countdownEl = document.getElementById(`countdown-${penyewaanForThisMeja.id}`);
                        if (countdownEl) {
                            if (penyewaanForThisMeja.status === 'berlangsung' && !isTimeUp) {
                                startCountdown(countdownEl, penyewaanForThisMeja.waktu_selesai, penyewaanForThisMeja);
                            } else {
                                
                                countdownEl.innerText = 'Waktu habis!';
                                countdownEl.classList.add('text-red-700', 'font-bold');
                            }
                        }
                    }
                } else {
                    
                    card.classList.remove('bg-red-100');
                    card.classList.add('bg-green-100');
                    if (statusMejaEl) statusMejaEl.innerText = 'Status: kosong';
                    if (penyewaanDivEl) penyewaanDivEl.innerHTML = ''; 

                    
                    
                    let pesanBtnEl = card.querySelector(`#btn-pesan-${mejaId}`);
                    if (!pesanBtnEl) { 
                        pesanBtnEl = document.createElement('button');
                        pesanBtnEl.id = `btn-pesan-${mejaId}`; 
                        pesanBtnEl.className = 'mt-2 btn btn-primary'; 
                        pesanBtnEl.innerText = 'Pesan';
                        pesanBtnEl.onclick = () => openModal(mejaId); 
                        card.appendChild(pesanBtnEl); 
                    }
                    pesanBtnEl.style.display = 'block'; 

                    
                    for (const id in countdownIntervals) {
                        if (currentActiveRentals[id] && currentActiveRentals[id].meja_id === mejaId) {
                             clearInterval(countdownIntervals[id]);
                             delete countdownIntervals[id];
                        }
                    }
                }
            });
        } catch (error) {
            console.error('Error fetching and rendering mejas:', error);
        }
    }

    async function fetchPaymentDetails(penyewaanId) {
        const penyewaan = currentActiveRentals[penyewaanId];

        if (penyewaan) {
            const hargaPerJam = parseFloat(penyewaan.harga_per_jam || 0);
            const durasiBooking = parseFloat(penyewaan.durasi_jam || 0);
            const initialTotalService = parseFloat(penyewaan.total_service || 0);
            const serviceDetails = penyewaan.service_detail; 

            
            console.log('--- Debugging Payment Details ---');
            console.log('Penyewaan Object:', penyewaan);
            console.log('Harga Per Jam (parsed):', hargaPerJam);
            console.log('Durasi Booking (parsed):', durasiBooking);
            console.log('Initial Total Service (parsed):', initialTotalService);
            console.log('Service Details:', serviceDetails);

            
            const updateTotalDisplay = (diskonPersen = 0) => {
                
                const subtotalMain = durasiBooking * hargaPerJam;
                const currentDiskon = (subtotalMain * diskonPersen) / 100;
                const finalTotal = (subtotalMain - currentDiskon) + initialTotalService;

                
                console.log('--- Recalculating Total ---');
                console.log('Subtotal Main:', subtotalMain);
                console.log('Diskon Persen:', diskonPersen);
                console.log('Current Diskon (amount):', currentDiskon);
                console.log('Final Total (before format):', finalTotal); 

                
                document.getElementById('payment_subtotal_main').innerText = formatRupiah(subtotalMain);
                document.getElementById('payment_total_service').innerText = formatRupiah(initialTotalService);
                document.getElementById('payment_diskon').innerText = formatRupiah(currentDiskon);
                document.getElementById('payment_total_final').innerText = formatRupiah(finalTotal);
            };

            
            document.getElementById('payment_meja_nama').innerText = penyewaan.meja_nama;
            document.getElementById('payment_nama_penyewa').innerText = penyewaan.nama_penyewa;
            document.getElementById('payment_durasi').innerText = formatDuration(durasiBooking);
            document.getElementById('payment_waktu_mulai').innerText = formatFullDateTime(penyewaan.waktu_mulai);
            document.getElementById('payment_waktu_selesai').innerText = formatFullDateTime(penyewaan.waktu_selesai);
            document.getElementById('payment_harga_per_jam').innerText = formatRupiah(hargaPerJam);


            
            const serviceDetailListEl = document.getElementById('payment_service_detail');
            serviceDetailListEl.innerHTML = ''; 
            if (serviceDetails && serviceDetails.length > 0) {
                serviceDetails.forEach(service => {
                    const li = document.createElement('li');
                    li.innerText = `${service.nama} (${service.jumlah}x) - ${formatRupiah(service.subtotal)}`;
                    serviceDetailListEl.appendChild(li);
                });
            } else {
                const li = document.createElement('li');
                li.innerText = 'Tidak ada layanan tambahan.';
                serviceDetailListEl.appendChild(li);
            }


            updateTotalDisplay(); 
            document.getElementById('kode_kupon').oninput = debounce(async function() {
                const kuponCode = this.value.trim();
                let diskonPersen = 0;

                if (kuponCode) {
                    try {
                        const kuponRes = await fetch(`{{ route('api.kupon.validate') }}?code=${encodeURIComponent(kuponCode)}`);
                        if (kuponRes.ok) {
                            const kuponData = await kuponRes.json();
                            diskonPersen = kuponData.diskon_persen || 0;
                            alert(`Kupon "${kuponCode}" berhasil diterapkan! Diskon ${diskonPersen}%`);
                        } else {
                            const errorData = await kuponRes.json();
                            alert('Kupon tidak valid: ' + (errorData.message || 'Kode kupon tidak ditemukan atau kadaluarsa.'));
                            console.warn('Kupon tidak valid:', errorData);
                        }
                    } catch (e) {
                        alert('Error saat memvalidasi kupon. Coba lagi.');
                        console.error('Error validating kupon:', e);
                    }
                }
                updateTotalDisplay(diskonPersen); 
            }, 500); 

        } else {
            console.error('Penyewaan tidak ditemukan:', penyewaanId);
            alert('Detail penyewaan tidak ditemukan.');
            closePaymentModal();
        }
    }

    
    function debounce(func, delay) {
        let timeout;
        return function(...args) {
            const context = this;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), delay);
        };
    }


    
    document.getElementById('formPembayaran').addEventListener('submit', async function(event) {
        event.preventDefault();
        const penyewaanId = document.getElementById('payment_penyewaan_id').value;
        const kodeKupon = document.getElementById('kode_kupon').value;

        
        const submitBtn = event.target.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerText = 'Memproses...';

        try {
            const response = await fetch(`{{ url('/kasir/penyewaan/') }}/${penyewaanId}/bayar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ kode_kupon: kodeKupon })
            });

            const data = await response.json();
            if (response.ok) {
                alert(data.message + ' Total bayar: ' + formatRupiah(data.total_bayar));
                closePaymentModal();
                
                if (countdownIntervals[penyewaanId]) {
                    clearInterval(countdownIntervals[penyewaanId]);
                    delete countdownIntervals[penyewaanId];
                }
                fetchAndRenderMejas(); 
            } else {
                alert('Gagal memproses pembayaran: ' + (data.message || 'Terjadi kesalahan.'));
                console.error('Pembayaran gagal:', data);
            }
        } catch (error) {
            console.error('Error saat submit pembayaran:', error);
            alert('Terjadi kesalahan jaringan atau server saat memproses pembayaran.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerText = 'Bayar Sekarang';
        }
    });


    
    document.addEventListener('DOMContentLoaded', function() {
        fetchAndRenderMejas(); 

        
        setInterval(fetchAndRenderMejas, 5000); 
    });
</script>
@endsection