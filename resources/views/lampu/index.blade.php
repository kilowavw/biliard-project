<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontrol Perangkat & Status Meja</title>
    <meta name="csrf-token" content="{{ csrf_token() }}"> {{-- Tambahkan CSRF Token --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .card {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto bg-white p-8 rounded-lg card">
        <h1 class="text-3xl font-bold text-center mb-8 text-gray-800">Sistem Kontrol Lampu Meja Biliar</h1>

        <!-- Bagian Kontrol Perintah Global -->
        <div class="mb-8 p-6 bg-gray-50 rounded-lg">
            <h2 class="text-2xl font-semibold mb-4 text-gray-700">Kirim Perintah Global ke Perangkat</h2>
            <div class="space-y-4">
                <div>
                    <label for="control_device_name" class="block text-gray-700 text-sm font-semibold mb-2">Pilih Perangkat</label>
                    <select id="control_device_name" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Perangkat --</option>
                        @foreach($devices as $device)
                            <option value="{{ $device->name }}">{{ $device->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <button type="button" data-command="RESET" class="btn-command bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150 ease-in-out">
                        RESET NodeMCU
                    </button>
                    <button type="button" data-command="NO_COMMAND" class="btn-command bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150 ease-in-out">
                        Hapus Perintah
                    </button>
                    <button type="button" data-command="UPDATE_FIRMWARE" class="btn-command bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150 ease-in-out">
                        UPDATE FW
                    </button>
                </div>

                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Kontrol LED Internal (GPIO2)</h3>
                    <div class="grid grid-cols-3 gap-3">
                        <button type="button" data-command="LED_ON" class="btn-command bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150 ease-in-out">
                            LED ON
                        </button>
                        <button type="button" data-command="LED_OFF" class="btn-command bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150 ease-in-out">
                            LED OFF
                        </button>
                        <button type="button" data-command="LED_BLINK" class="btn-command bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150 ease-in-out">
                            LED BLINK
                        </button>
                    </div>
                </div>

                <div id="responseMessage" class="mt-4 text-center text-sm font-medium hidden"></div>
            </div>
        </div>

        <!-- Bagian Status Perangkat -->
        <div class="mb-8 p-6 bg-gray-50 rounded-lg">
            <h2 class="text-2xl font-semibold mb-4 text-gray-700">Status Perangkat</h2>
            <div id="devicesStatusContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Status perangkat akan di-load di sini oleh JavaScript -->
            </div>
        </div>

        <!-- Bagian Status Meja -->
        <div class="p-6 bg-gray-50 rounded-lg">
            <h2 class="text-2xl font-semibold mb-4 text-gray-700">Status Lampu Meja Biliar</h2>
            <div id="mejaStatusContainer" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($meja as $item)
                    <div id="meja-{{ $item->id }}" class="p-4 border rounded-lg text-center card"
                        data-meja-id="{{ $item->id }}"
                        data-meja-status="{{ $item->status }}">
                        <p class="font-semibold text-lg text-gray-800">{{ $item->nama_meja }}</p>
                        <span class="text-sm font-medium">Status: </span>
                        <span class="meja-status-text font-bold">
                            @if($item->status == 'dipakai')
                                <span class="text-green-600">Dipakai (Lampu ON)</span>
                            @elseif($item->status == 'kosong')
                                <span class="text-gray-500">Kosong (Lampu OFF)</span>
                            @elseif($item->status == 'waktu_habis')
                                <span class="text-yellow-600">Waktu Habis (Lampu OFF)</span>
                            @else
                                <span class="text-gray-500">{{ ucfirst($item->status) }}</span>
                            @endif
                        </span>
                        <div class="mt-2">
                            <button data-meja-id="{{ $item->id }}" class="btn-toggle-meja bg-indigo-500 hover:bg-indigo-600 text-white text-xs font-bold py-1 px-2 rounded">
                                Toggle Lampu
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // URL API (Pastikan ini sesuai dengan routes/api.php Anda)
            const API_URL_KIRIM_PERINTAH = '{{ url('/api/kirim-perintah') }}';
            const API_URL_GET_DEVICE_STATUS = '{{ url('/api/get-device-status') }}';
            const API_URL_GET_MEJA_STATUSES = '{{ url('/api/get-perintah-dan-status-meja') }}';
            const API_URL_UPDATE_MEJA_STATUS_BASE = '{{ url('/api/meja') }}';

            const controlDeviceNameSelect = document.getElementById('control_device_name');
            const commandButtons = document.querySelectorAll('.btn-command');
            const responseMessage = document.getElementById('responseMessage');
            const devicesStatusContainer = document.getElementById('devicesStatusContainer');
            const mejaStatusContainer = document.getElementById('mejaStatusContainer');
            const toggleMejaButtons = document.querySelectorAll('.btn-toggle-meja');

            // Fungsi untuk mengirim perintah global
            function sendGlobalCommand(deviceName, command) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

                fetch(API_URL_KIRIM_PERINTAH, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ device_name: deviceName, command: command })
                })
                .then(response => response.json())
                .then(result => {
                    responseMessage.textContent = result.message;
                    responseMessage.classList.remove('hidden', 'text-red-500');
                    responseMessage.classList.add('text-green-500');
                    fetchDevicesStatus(); // Refresh status perangkat setelah mengirim perintah
                })
                .catch(error => {
                    responseMessage.textContent = 'Terjadi kesalahan saat mengirim perintah.';
                    responseMessage.classList.remove('hidden', 'text-green-500');
                    responseMessage.classList.add('text-red-500');
                    console.error('Error:', error);
                });
            }

            // Event listener untuk tombol perintah global
            commandButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const deviceName = controlDeviceNameSelect.value;
                    const command = this.dataset.command;

                    if (!deviceName) {
                        responseMessage.textContent = 'Harap pilih perangkat terlebih dahulu.';
                        responseMessage.classList.remove('hidden', 'text-green-500');
                        responseMessage.classList.add('text-red-500');
                        return;
                    }
                    sendGlobalCommand(deviceName, command);
                });
            });

            // --- Bagian Ambil Status Perangkat ---
            function fetchDevicesStatus() {
                fetch(API_URL_GET_DEVICE_STATUS, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success' && data.devices) {
                        devicesStatusContainer.innerHTML = ''; // Kosongkan container
                        data.devices.forEach(device => {
                            const isOnline = device.online;
                            const statusBgColor = isOnline ? 'bg-green-100' : 'bg-red-100';
                            const statusTextColor = isOnline ? 'text-green-800' : 'text-red-800';
                            const statusText = isOnline ? 'Online' : 'Offline';
                            const lastSeen = device.last_seen || 'Never'; 
                            const ipAddress = device.ip_address || 'Tidak diketahui';

                            const deviceCard = `
                                <div class="p-4 border rounded-lg ${isOnline ? 'border-green-300' : 'border-red-300'} ${statusBgColor} card">
                                    <p class="font-bold text-lg text-gray-800">${device.device_name}</p>
                                    <p class="text-sm">Status: <span class="font-semibold ${statusTextColor}">${statusText}</span></p>
                                    <p class="text-xs text-gray-600">IP: ${ipAddress}</p>
                                    <p class="text-xs text-gray-600">Terakhir terlihat: ${lastSeen}</p>
                                    <p class="text-xs text-gray-600">Perintah: ${device.command_status}</p>
                                </div>
                            `;
                            devicesStatusContainer.innerHTML += deviceCard;
                        });
                    } else {
                        devicesStatusContainer.innerHTML = '<p class="text-red-500">Gagal mengambil status perangkat.</p>';
                        console.error('Gagal mengambil status perangkat:', data.message || 'Unknown error');
                    }
                })
                .catch(error => {
                    console.error('Error fetching device status:', error);
                    devicesStatusContainer.innerHTML = '<p class="text-red-500">Gagal mengambil status perangkat karena masalah jaringan.</p>';
                });
            }

            // --- Bagian Ambil & Update Status Meja ---
            function fetchMejaStatuses() {
                // Untuk menampilkan status meja di UI, kita bisa mengambil dari endpoint yang sama
                // yang dipanggil oleh NodeMCU. Namun, untuk API ini, `device_name` dibutuhkan
                // oleh backend agar NodeMCU dapat diidentifikasi, meskipun dari UI kita hanya ingin status meja.
                // Jika ingin hanya mengambil data meja, buat endpoint terpisah di backend yang tidak butuh device_name.
                
                // Mengambil nama perangkat pertama dari dropdown sebagai placeholder
                const firstDeviceName = controlDeviceNameSelect.options.length > 1 ? controlDeviceNameSelect.options[1].value : '';

                fetch(`${API_URL_GET_MEJA_STATUSES}?device_name=${firstDeviceName}`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success' && data.meja_data) {
                        data.meja_data.forEach(mejaApi => {
                            const mejaCard = document.getElementById(`meja-${mejaApi.id}`);
                            if (mejaCard) {
                                const statusSpan = mejaCard.querySelector('.meja-status-text');
                                statusSpan.innerHTML = ''; 
                                
                                if (mejaApi.status === 'dipakai') {
                                    statusSpan.innerHTML = '<span class="text-green-600">Dipakai (Lampu ON)</span>';
                                    mejaCard.dataset.mejaStatus = 'dipakai'; // Update data-meja-status
                                } else if (mejaApi.status === 'kosong') {
                                    statusSpan.innerHTML = '<span class="text-gray-500">Kosong (Lampu OFF)</span>';
                                    mejaCard.dataset.mejaStatus = 'kosong';
                                } else if (mejaApi.status === 'waktu_habis') {
                                    statusSpan.innerHTML = '<span class="text-yellow-600">Waktu Habis (Lampu OFF)</span>';
                                    mejaCard.dataset.mejaStatus = 'waktu_habis';
                                } else {
                                    statusSpan.innerHTML = `<span class="text-gray-500">${mejaApi.status.charAt(0).toUpperCase() + mejaApi.status.slice(1)}</span>`;
                                    mejaCard.dataset.mejaStatus = mejaApi.status;
                                }
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching meja statuses:', error);
                });
            }

            // --- Bagian Toggle Lampu Meja Manual ---
            toggleMejaButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const mejaId = this.dataset.mejaId;
                    const mejaCard = document.getElementById(`meja-${mejaId}`);
                    let currentStatus = mejaCard.dataset.mejaStatus;
                    let newStatus;

                    if (currentStatus === 'dipakai') {
                        newStatus = 'kosong';
                    } else { // Jika kosong atau waktu_habis, anggap mau dihidupkan
                        newStatus = 'dipakai';
                    }

                    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

                    fetch(`${API_URL_UPDATE_MEJA_STATUS_BASE}/${mejaId}/update-status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ status: newStatus })
                    })
                    .then(response => response.json())
                    .then(result => {
                        console.log(`Meja ${mejaId} status updated to ${newStatus}:`, result.message);
                        fetchMejaStatuses(); // Refresh status meja setelah toggle
                        responseMessage.textContent = `Status Meja ${mejaId} diubah menjadi ${newStatus}.`;
                        responseMessage.classList.remove('hidden', 'text-red-500');
                        responseMessage.classList.add('text-green-500');
                    })
                    .catch(error => {
                        console.error('Error toggling meja status:', error);
                        responseMessage.textContent = 'Gagal mengubah status meja.';
                        responseMessage.classList.remove('hidden', 'text-green-500');
                        responseMessage.classList.add('text-red-500');
                    });
                });
            });


            // Panggil fungsi status perangkat setiap 5 detik
            setInterval(fetchDevicesStatus, 5000);
            fetchDevicesStatus(); // Panggil sekali saat halaman dimuat

            // Panggil fungsi status meja setiap 3 detik
            setInterval(fetchMejaStatuses, 3000);
            fetchMejaStatuses(); // Panggil sekali saat halaman dimuat
        });
    </script>
</body>
</html>