@extends('default')

@section('content')
<div class="container mx-auto p-4 md:p-6 bg-[#121212] text-white">
    <h1 class="text-3xl font-bold mb-6">Manajemen Paket</h1>

    <div id="ajax-response-message" style="display:none;" class="flex items-center p-4 mb-4 text-sm rounded-lg" role="alert">
    </div>

    <div class="flex justify-between items-center mb-4">
        <button data-modal-target="create-paket-modal" data-modal-toggle="create-paket-modal" class="block text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-500 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
            Tambah Paket Baru
        </button>
    </div>

    <div class="bg-[#1e1e1e] p-6 rounded-lg shadow-md overflow-x-auto">
        <table id="paketsTable" class="min-w-full leading-normal text-white">
            <thead>
                <tr class="bg-[#2a2a2a] text-gray-300 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">ID</th>
                    <th class="py-3 px-6 text-left">Nama Paket</th>
                    <th class="py-3 px-6 text-left">Detail Paket</th>
                    <th class="py-3 px-6 text-left">Aktif</th>
                    <th class="py-3 px-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-400 text-sm font-light">
                @foreach($pakets as $paket)
                <tr class="border-b border-gray-700 hover:bg-[#232323]">
                    <td class="py-3 px-6 text-left whitespace-nowrap">{{ $paket->id }}</td>
                    <td class="py-3 px-6 text-left">{{ $paket->nama_paket }}</td>
                    <td class="py-3 px-6 text-left">
                          @php
                                $isiPaket = $paket->isi_paket;
                                $desc = [];
                                if (isset($isiPaket['harga_paket'])) $desc[] = 'Harga: Rp' . number_format($isiPaket['harga_paket'], 0, ',', '.');
                                if (isset($isiPaket['durasi_jam']) && $isiPaket['durasi_jam'] > 0) $desc[] = 'Durasi: ' . $isiPaket['durasi_jam'] . ' jam';
                                elseif (isset($isiPaket['durasi_jam']) && $isiPaket['durasi_jam'] == 0) $desc[] = 'Durasi: Sepuasnya';
                                if (isset($isiPaket['deskripsi_tambahan']) && !empty($isiPaket['deskripsi_tambahan'])) $desc[] = $isiPaket['deskripsi_tambahan'];
                                if (isset($isiPaket['services']) && count($isiPaket['services']) > 0) {
                                    $serviceNames = collect($isiPaket['services'])->pluck('nama')->implode(', ');
                                    $desc[] = 'Service: ' . $serviceNames;
                                }
                            @endphp
                            {{ implode(', ', array_filter($desc)) ?: 'Tidak ada detail' }}
                    </td>
                    <td class="py-3 px-6 text-left">
                        @if($paket->aktif)
                            <span class="bg-green-600 text-white text-xs font-semibold px-2.5 py-0.5 rounded-full">Aktif</span>
                        @else
                            <span class="bg-red-600 text-white text-xs font-semibold px-2.5 py-0.5 rounded-full">Tidak Aktif</span>
                        @endif
                    </td>
                    <td class="py-3 px-6 text-center">
                        <div class="flex item-center justify-center space-x-2">
                            <form action="{{ route('admin.pakets.destroy', $paket->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus paket ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-full bg-red-700 hover:bg-red-800 flex items-center justify-center text-white" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $pakets->links() }}
        </div>
    </div>
</div>

<div id="create-paket-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-2xl max-h-full">
        <div class="relative bg-gray-800 rounded-lg shadow">
            <div class="flex items-center justify-between p-4 md:p-5 border-b border-gray-700 rounded-t">
                <h3 class="text-xl font-semibold text-white">Tambah Paket Baru</h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-700 hover:text-white rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center" data-modal-hide="create-paket-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/></svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <form id="create-paket-form" class="p-4 md:p-5" action="{{ route('admin.pakets.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="create_nama_paket" class="block mb-2 text-sm font-medium text-white">Nama Paket</label>
                        <input type="text" name="nama_paket" id="create_nama_paket" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Contoh: Paket Hemat" required="">
                        <p class="text-red-500 text-xs italic mt-2" id="create-nama_paket-error"></p>
                    </div>
                    <div>
                        <label for="create_harga_paket" class="block mb-2 text-sm font-medium text-white">Harga Paket</label>
                        <input type="number" name="harga_paket" id="create_harga_paket" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="0" required="" min="0">
                        <p class="text-red-500 text-xs italic mt-2" id="create-harga_paket-error"></p>
                    </div>
                    <div>
                        <label for="create_durasi_jam" class="block mb-2 text-sm font-medium text-white">Durasi (Jam)</label>
                        <input type="number" name="durasi_jam" id="create_durasi_jam" value="0" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="0" required="" min="0" step="0.01">
                        <p class="text-gray-400 text-xs mt-1">Durasi jam yang termasuk dalam paket. Isi 0 jika fleksibel.</p>
                        <p class="text-red-500 text-xs italic mt-2" id="create-durasi_jam-error"></p>
                    </div>
                    <div>
                        <label for="create_deskripsi_tambahan" class="block mb-2 text-sm font-medium text-white">Deskripsi Tambahan (Opsional)</label>
                        <textarea name="deskripsi_tambahan" id="create_deskripsi_tambahan" rows="4" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Misal: Paket termasuk air mineral, berlaku hari Senin-Jumat"></textarea>
                        <p class="text-red-500 text-xs italic mt-2" id="create-deskripsi_tambahan-error"></p>
                    </div>
                </div>

                <h4 class="font-semibold mt-4 mb-2 text-white">Service yang Termasuk dalam Paket:</h4>
                <div id="create_package_service_list" class="max-h-64 overflow-y-auto border border-gray-600 rounded p-2 mb-4">
                    @forelse($services as $service)
                        <div class="flex items-center justify-between py-1 border-b border-gray-700 last:border-b-0">
                            <span class="font-medium text-gray-300">{{ $service->nama }} (Rp {{ number_format($service->harga) }}) <small>(Stok: {{ $service->stok }})</small></span>
                            <input type="number" name="services[{{ $service->id }}][jumlah]" data-service-id="{{ $service->id }}"
                                   class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 w-20 p-1 text-center"
                                   value="0" min="0" step="1" max="{{ $service->stok }}">
                            <input type="hidden" name="services[{{ $service->id }}][id]" value="{{ $service->id }}">
                        </div>
                    @empty
                        <p class="text-gray-500">Tidak ada layanan tersedia.</p>
                    @endforelse
                </div>
                <p class="text-red-500 text-xs italic mt-2" id="create-services-error"></p>

                <div class="mb-4">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="aktif" value="1" id="create_aktif" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-800 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        <span class="ms-3 text-sm font-medium text-white">Aktif</span>
                    </label>
                    <p class="text-red-500 text-xs italic mt-2" id="create-aktif-error"></p>
                </div>

                <button type="submit" class="text-white inline-flex items-center bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-500 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                    <i class="fas fa-plus me-1 -ms-1"></i>
                    Tambah Paket
                </button>
            </form>
        </div>
    </div>
</div>



@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const getEl = (id) => document.getElementById(id);
        const hideModal = (id) => {
            const modalElement = getEl(id);
            if (modalElement) {
                const modalInstance = Flowbite.Modal.getInstance(modalElement);
                if (modalInstance) {
                    modalInstance.hide();
                }
            }
        };
        const showModal = (id) => {
            const modalElement = getEl(id);
            if (modalElement) {
                let modalInstance = Flowbite.Modal.getInstance(modalElement);
                if (!modalInstance) {
                    modalInstance = new Flowbite.Modal(modalElement, {});
                }
                modalInstance.show();
            }
        };

        const displayMessage = (type, message) => {
            const alertDiv = getEl('ajax-response-message');
            alertDiv.className = `flex items-center p-4 mb-4 text-sm rounded-lg ${type === 'success' ? 'text-green-800 bg-green-50 dark:bg-green-800 dark:text-green-400' : 'text-red-800 bg-red-50 dark:bg-red-800 dark:text-red-400'}`;
            alertDiv.innerHTML = `
                <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                </svg>
                <span class="sr-only">Info</span>
                <div class="ms-3 text-sm font-medium">
                    ${message}
                </div>
                <button type="button" class="ms-auto -mx-1.5 -my-1.5 ${type === 'success' ? 'bg-green-50 text-green-500 hover:bg-green-200 dark:bg-green-800 dark:text-green-400 dark:hover:bg-green-700' : 'bg-red-50 text-red-500 hover:bg-red-200 dark:bg-red-800 dark:text-red-400 dark:hover:bg-red-700'} rounded-lg focus:ring-2 focus:ring-400 p-1.5 inline-flex items-center justify-center h-8 w-8" onclick="this.parentElement.style.display='none';" aria-label="Close">
                    <span class="sr-only">Close</span>
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                </button>
            `;
            alertDiv.style.display = 'flex';
            setTimeout(() => alertDiv.style.display = 'none', 5000);
        };

        const clearErrors = (formId) => {
            document.querySelectorAll(`#${formId} p[id$="-error"]`).forEach(p => p.innerText = '');
            document.querySelectorAll(`#${formId} .border-red-500`).forEach(el => el.classList.remove('border-red-500'));
        };

        const displayErrors = (formId, errors) => {
            clearErrors(formId);
            for (const field in errors) {
                const errorElId = `${formId}-${field.replace(/\./g, '-')}-error`;
                const errorEl = getEl(errorElId);

                if (errorEl) {
                    errorEl.innerText = errors[field][0];
                }

                let inputEl = document.querySelector(`#${formId} [name="${field}"]`);
                if (!inputEl && field.includes('.')) {
                    const parts = field.split('.');
                    if (parts.length === 3 && parts[0] === 'services') {
                        inputEl = document.querySelector(`#${formId} input[name="services[${parts[1]}][${parts[2]}]"]`);
                    }
                }
                if (inputEl) {
                    inputEl.classList.add('border-red-500');
                }
            }
        };

        const handleFormSubmission = async (event, formId) => {
            event.preventDefault();
            clearErrors(formId);

            const form = getEl(formId);
            const formData = new FormData(form);
            const url = form.action;
            let method = form.method;

            const data = {};
            for (const [key, value] of formData.entries()) {
                if (key === 'aktif') {
                    data[key] = value === 'on' ? 1 : 0;
                }
                else if (key.startsWith('services[')) {
                    const match = key.match(/services\[(\d+)\]\[(id|jumlah)\]/);
                    if (match) {
                        const serviceId = match[1];
                        const prop = match[2];
                        if (!data.services) data.services = [];
                        let existingService = data.services.find(s => s.id == parseInt(serviceId));
                        if (!existingService) {
                            existingService = { id: parseInt(serviceId) };
                            data.services.push(existingService);
                        }
                        existingService[prop] = prop === 'jumlah' ? parseInt(value) : parseInt(value);
                    }
                }
                else if (key === '_method') {
                    method = value;
                }
                else if (key !== '_token') {
                    data[key] = value;
                }
            }
            if (form.querySelector('input[name="aktif"]') && !formData.has('aktif')) {
                data.aktif = 0;
            }

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnHtml = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                });

                const responseData = await response.json();

                if (response.ok) {
                    displayMessage('success', responseData.message);
                    hideModal(formId.includes('create') ? 'create-paket-modal' : 'edit-paket-modal');
                    location.reload();
                } else if (response.status === 422) {
                    displayErrors(formId, responseData.errors);
                    displayMessage('error', responseData.message || 'Validasi gagal, cek isian form.');
                } else {
                    displayMessage('error', responseData.message || 'Terjadi kesalahan saat memproses permintaan.');
                }
            } catch (error) {
                console.error('Fetch error:', error);
                displayMessage('error', 'Terjadi kesalahan jaringan atau server tidak merespons.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
            }
        };

        getEl('create-paket-form').addEventListener('submit', (e) => handleFormSubmission(e, 'create-paket-form'));
    });
</script>
@endpush