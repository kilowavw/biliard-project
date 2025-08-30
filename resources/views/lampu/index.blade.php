<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontrol Perangkat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .card {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg w-full max-w-md card">
        <h1 class="text-3xl font-bold text-center mb-6 text-gray-800">Kirim Perintah</h1>
        <div class="mt-4 text-center">
            <p class="text-gray-700 font-semibold">Status Perangkat Saat Ini:</p>
            <span id="deviceStatus" class="font-bold text-xl text-gray-500">Menghubungkan...</span>
        </div>
        <form id="perintahForm" action="{{ url('/api/kirim-perintah') }}" method="POST">
            @csrf
            <div class="mb-6">
                <label for="perintah" class="block text-gray-700 text-sm font-semibold mb-2">Masukkan Pesan/Perintah</label>
                <input type="text" name="perintah" id="perintah" placeholder="ON / OFF / RESET" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:shadow-outline">
            </div>
            
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150 ease-in-out">
                    Kirim Perintah
                </button>
            </div>
        </form>

        <div id="responseMessage" class="mt-4 text-center text-sm font-medium hidden"></div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('perintahForm');
            const responseMessage = document.getElementById('responseMessage');

            form.addEventListener('submit', function (event) {
                event.preventDefault(); // Mencegah form dari pengiriman standar dan halaman reload

                const formData = new FormData(form);
                const perintah = formData.get('perintah');

                // Siapkan data untuk dikirim dalam format JSON
                const data = {
                    perintah: perintah
                };

                // Kirim data ke API menggunakan Fetch API
                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    // Tampilkan pesan respons dari server
                    responseMessage.textContent = result.message;
                    responseMessage.classList.remove('hidden');
                    responseMessage.classList.add('text-green-500'); // Atur warna pesan sukses
                    form.reset(); // Bersihkan form
                })
                .catch(error => {
                    // Tangani kesalahan
                    responseMessage.textContent = 'Terjadi kesalahan saat mengirim perintah.';
                    responseMessage.classList.remove('hidden');
                    responseMessage.classList.remove('text-green-500');
                    responseMessage.classList.add('text-red-500'); // Atur warna pesan error
                    console.error('Error:', error);
                });
            });
        });

        function fetchDeviceStatus() {
        fetch('{{ url('/api/get-status') }}', {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            const statusText = document.getElementById('deviceStatus');
            statusText.textContent = data.perangkat_status;
            
            // Atur warna berdasarkan status
            if (data.perangkat_status === 'ON') {
                statusText.classList.remove('text-red-500');
                statusText.classList.add('text-green-500');
            } else if (data.perangkat_status === 'OFF') {
                statusText.classList.remove('text-green-500');
                statusText.classList.add('text-red-500');
            } else {
                statusText.classList.remove('text-green-500', 'text-red-500');
                statusText.classList.add('text-gray-500');
            }
        })
        .catch(error => {
            console.error('Error fetching device status:', error);
        });
    }

    // Panggil fungsi status setiap 3 detik
    setInterval(fetchDeviceStatus, 3000);

    // Panggil sekali saat halaman dimuat
    fetchDeviceStatus();
    </script>
</body>
</html>