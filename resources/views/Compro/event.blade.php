@extends('compro.profile')

@section('content')
<style>
  body {
    background: linear-gradient(120deg, #0f172a, #1e293b, #0f172a);
    background-size: 300% 300%;
    animation: gradientBG 15s ease infinite;
  }
  @keyframes gradientBG {
    0% {background-position: 0% 50%;}
    50% {background-position: 100% 50%;}
    100% {background-position: 0% 50%;}
  }
  /* Animasi fade-up halus */
@keyframes fadeUp {
    0% { opacity: 0; transform: translateY(20px); }
    100% { opacity: 1; transform: translateY(0); }
}
.animate-fade-up {
    animation: fadeUp 0.8s ease forwards;
}
</style>
<div class="text-white font-sans">
  <section class="relative bg-cover bg-center bg-no-repeat" 
         style="background-image: url('/images/slider1.png'); margin-top:0; padding-top:6rem; padding-bottom:6rem;">
    <!-- Overlay biar lebih terang -->
    <div class="absolute inset-0 bg-black bg-opacity-40"></div>

    <div class="relative text-center text-white px-6 max-w-4xl mx-auto">
        <h2 class="text-4xl md:text-5xl font-bold mb-4 opacity-0 translate-y-8 animate-fade-up" 
            style="font-family: 'Bungee', Arial, sans-serif; font-weight: 400;" 
            data-aos="fade-up">
            Event Spesial Kami
        </h2>

        <p class="mt-6 text-xl md:text-2xl font-sans font-bold text-gray-100 tracking-normal leading-relaxed drop-shadow-lg opacity-0 translate-x-[40px]
            animate-slideFromRight delay-200"
           data-aos="fade-up" data-aos-delay="200">
            Ikuti berbagai event seru dan turnamen billiard dengan hadiah menarik.
        </p>

        <a href="javascript:void(0)" onclick="openCreate()"
           class="mt-6 inline-block px-8 py-3 bg-gradient-to-r from-green-500 via-green-600 to-green-500 
                  text-white font-semibold rounded-xl shadow-lg transition-all duration-500 transform 
                  hover:scale-105 hover:shadow-2xl hover:from-green-600 hover:via-green-700 hover:to-green-600">
            + Tambah Event
        </a>
    </div>
</section>



<!-- Event Cards Premium + Scroll Animation -->
<section id="event-list" 
    class="mt-10 grid sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10 justify-center max-w-6xl mx-auto px-6 pb-20">
    @foreach($events as $event)
    <div data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}"
         class="bg-gray-900 rounded-2xl border border-gray-700 overflow-hidden flex flex-col transition transform hover:scale-[1.02] hover:shadow-lg group relative mx-auto w-full max-w-md">
        
        <!-- Gambar Event -->
        @if($event->gambar)
        <div class="w-full h-60 overflow-hidden rounded-t-2xl relative">
            <img src="{{ asset('storage/' . $event->gambar) }}" 
                 alt="Event" 
                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
        </div>
        @endif

        <!-- Isi Card -->
        <div class="p-6 flex flex-col flex-grow space-y-4 relative">

            <!-- Judul -->
            <h3 class="text-2xl font-bold text-green-400 line-clamp-2 transition-all duration-300 group-hover:drop-shadow-md">
                {{ $event->judul }}
            </h3>

             <!-- Deskripsi -->
            <p class="text-gray-100 text-base font-semibold leading-relaxed text-justify 
                line-clamp-3 group-hover:line-clamp-none transition-all duration-500 whitespace-pre-line">
                {{ $event->deskripsi }}
            </p>


            <!-- Info Meta -->
            <div class="flex flex-col gap-2 text-gray-300 text-sm">
                <div class="flex items-center gap-2">
                    <i class="fas fa-calendar-days text-green-400 w-5"></i>
                    <div>
                        <p class="font-semibold">Tanggal</p>
                        <p class="text-gray-200">
                            {{ \Carbon\Carbon::parse($event->tanggal_mulai)->translatedFormat('d F Y') }}
                            @if($event->tanggal_selesai)
                                - {{ \Carbon\Carbon::parse($event->tanggal_selesai)->translatedFormat('d F Y') }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fas fa-location-dot text-green-400 w-5"></i>
                    <div>
                        <p class="font-semibold">Tempat</p>
                        <p class="text-gray-200">{{ $event->lokasi ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="mt-auto flex justify-end items-center gap-3 pt-3 border-t border-gray-700 opacity-0 group-hover:opacity-100 translate-y-2 group-hover:translate-y-0 transition-all duration-500">
                <button onclick="openEdit({{ $event->id }}, 
                    '{{ addslashes($event->judul) }}', 
                    `{{ addslashes($event->deskripsi) }}`,
                    '{{ $event->tanggal_mulai }}', 
                    '{{ $event->tanggal_selesai }}', 
                    '{{ addslashes($event->lokasi ?? '') }}'
                    )" 
                    class="text-yellow-300 hover:text-yellow-100 text-sm font-medium transition">
                    <i class="fas fa-edit"></i>
                </button>

                <button onclick="openDelete({{ $event->id }})" 
                        class="text-red-400 hover:text-red-200 text-sm font-medium transition">
                    <i class="fas fa-trash"></i>
                </button>

                <button onclick="showDetail(
                    '{{ addslashes($event->judul) }}',
                    `{{ addslashes($event->deskripsi) }}`,
                    '{{ asset('storage/' . $event->gambar) }}',
                    '{{ $event->tanggal_mulai }}',
                    '{{ $event->tanggal_selesai ?? '' }}',
                    '{{ addslashes($event->lokasi ?? '') }}'
                    )"
                    class="px-4 py-2 bg-green-500 hover:bg-green-600 rounded-xl text-white text-sm font-semibold transition shadow-md hover:shadow-lg">
                    Selengkapnya
                </button>
            </div>

        </div>
    </div>
    @endforeach
</section>




  <!-- Detail Event -->
  <section id="event-detail" class="hidden max-w-5xl mx-auto px-6 pb-20">
    <div class="bg-gray-900/95 backdrop-blur-xl rounded-3xl shadow-2xl flex flex-col md:flex-row overflow-hidden border border-gray-700">
      
      <!-- Gambar Event -->
      <div class="md:w-1/2 w-full h-64 md:h-auto overflow-hidden">
        <img id="detail-img" src="" alt="Event Detail" 
             class="w-full h-full object-cover rounded-t-3xl md:rounded-l-3xl md:rounded-t-none">
      </div>
      
      <!-- Informasi Event -->
      <div class="p-8 flex flex-col justify-start space-y-7 md:w-1/2">

        <!-- Judul Event -->
        <h3 id="detail-title" 
            class="text-3xl md:text-4xl font-extrabold text-green-400 border-b border-green-400 pb-3 tracking-wide"></h3>

        <!-- Tanggal Event -->
        <div class="space-y-2">
          <div class="flex items-center gap-2 text-gray-300 text-sm">
            <i class="fas fa-calendar-alt text-green-400"></i>
            <span class="font-semibold">Tanggal Event</span>
          </div>
          <div class="bg-gray-800/70 rounded-xl px-4 py-3 shadow-inner">
              <p id="detail-date" class="text-gray-200 text-base leading-relaxed"></p>
          </div>
        </div>

        <!-- Lokasi Event -->
        <div class="space-y-2">
          <div class="flex items-center gap-2 text-gray-300 text-sm">
            <i class="fas fa-location-dot text-green-400"></i>
            <span class="font-semibold">Lokasi</span>
          </div>
          <div class="bg-gray-800/70 rounded-xl px-4 py-3 shadow-inner">
            <p id="detail-location" class="text-gray-200 text-base leading-relaxed"></p>
          </div>
        </div>

        <!-- Deskripsi di Detail -->
        <div class="space-y-3">
          <div class="flex items-center gap-2 text-gray-300 text-sm">
              <div class="w-9 h-9 flex items-center justify-center rounded-full bg-green-500/20 text-green-400">
                <i class="fas fa-pen-nib"></i>
              </div>
              <span class="font-semibold text-white text-base">Deskripsi</span>
          </div>

          <div class="bg-gray-800/80 border-l-4 border-green-500 rounded-r-xl px-5 py-4 shadow-md max-h-72 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-600 scrollbar-track-gray-800">
              <p id="detail-description" class="text-gray-200 text-base leading-relaxed text-justify whitespace-pre-line"></p>
          </div>
        </div>

        <!-- Tombol Kembali -->
        <div class="pt-2">
          <button onclick="backToList()" 
                  class="px-6 py-2.5 bg-green-500 hover:bg-green-600 rounded-xl text-white font-semibold transition-all duration-200 shadow-md hover:shadow-lg">
            ← Kembali ke Daftar
          </button>
        </div>
      </div>
    </div>
  </section>
</div>


<!-- Modal Create -->
<div id="createModal" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50">
  <div class="bg-gray-800 p-6 rounded-2xl w-full max-w-lg relative">
    <button onclick="closeCreate()" class="absolute top-3 right-3 text-white text-xl">&times;</button>
    <h3 class="text-2xl text-green-400 mb-4">Tambah Event</h3>
    <form id="createForm" method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data">
      @csrf
      <input type="text" name="judul" placeholder="Judul Event" class="w-full mb-3 p-2 rounded bg-gray-700 text-white" required>
      <textarea name="deskripsi" placeholder="Deskripsi" class="w-full mb-3 p-2 rounded bg-gray-700 text-white" required></textarea>
      <input type="date" name="tanggal_mulai" class="w-full mb-3 p-2 rounded bg-gray-700 text-white" required>
      <input type="date" name="tanggal_selesai" class="w-full mb-3 p-2 rounded bg-gray-700 text-white">
      <input type="text" name="lokasi" placeholder="Lokasi Event" class="w-full mb-3 p-2 rounded bg-gray-700 text-white">
      <input type="file" name="gambar" class="w-full mb-3 text-white">
      <button type="submit" class="bg-green-500 hover:bg-green-600 px-4 py-2 rounded text-white font-semibold">Tambah</button>
    </form>
  </div>
</div>

<!-- Modal Edit -->
<div id="editModal" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50">
  <div class="bg-gray-800 p-6 rounded-2xl w-full max-w-lg relative">
    <button onclick="closeEdit()" class="absolute top-3 right-3 text-white text-xl">&times;</button>
    <h3 class="text-2xl text-yellow-400 mb-4">Edit Event</h3>
    <form id="editForm" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')
      <input type="text" name="judul" id="editJudul" placeholder="Judul Event" class="w-full mb-3 p-2 rounded bg-gray-700 text-white" required>
      <textarea name="deskripsi" id="editDeskripsi" placeholder="Deskripsi" class="w-full mb-3 p-2 rounded bg-gray-700 text-white" required></textarea>
      <input type="date" name="tanggal_mulai" id="editMulai" class="w-full mb-3 p-2 rounded bg-gray-700 text-white" required>
      <input type="date" name="tanggal_selesai" id="editSelesai" class="w-full mb-3 p-2 rounded bg-gray-700 text-white">
      <input type="text" name="lokasi" id="editLokasi" placeholder="Lokasi Event" class="w-full mb-3 p-2 rounded bg-gray-700 text-white">
      <input type="file" name="gambar" id="editGambar" class="w-full mb-3 text-white">
      <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 px-4 py-2 rounded text-white font-semibold">Update</button>
    </form>
  </div>
</div>

<!-- Modal Delete -->
<div id="deleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50">
  <div class="bg-gray-800 p-6 rounded-2xl w-full max-w-md text-center relative">
    <button onclick="closeDelete()" class="absolute top-3 right-3 text-white text-xl">&times;</button>
    <h3 class="text-2xl text-red-400 mb-4">Hapus Event</h3>
    <p class="text-gray-300 mb-6">Yakin ingin menghapus event ini?</p>
    <form id="deleteForm" method="POST">
      @csrf
      @method('DELETE')
      <button type="submit" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded text-white font-semibold">Hapus</button>
      <button type="button" onclick="closeDelete()" class="bg-gray-500 hover:bg-gray-600 px-4 py-2 rounded text-white font-semibold ml-2">Batal</button>
    </form>
  </div>
</div>

<script>
  function openCreate() { document.getElementById('createModal').classList.remove('hidden'); }
  function closeCreate() { document.getElementById('createModal').classList.add('hidden'); }

  function openEdit(id, judul, deskripsi, mulai, selesai, lokasi) {
      document.getElementById('editModal').classList.remove('hidden');
      document.getElementById('editForm').action = '/events/' + id;
      document.getElementById('editJudul').value = judul;
      document.getElementById('editDeskripsi').value = deskripsi;
      document.getElementById('editMulai').value = mulai;
      document.getElementById('editSelesai').value = selesai ?? '';
      document.getElementById('editLokasi').value = lokasi ?? '';
  }
  function closeEdit() { document.getElementById('editModal').classList.add('hidden'); }

  function openDelete(id) {
      document.getElementById('deleteModal').classList.remove('hidden');
      document.getElementById('deleteForm').action = '/events/' + id;
  }
  function closeDelete() { document.getElementById('deleteModal').classList.add('hidden'); }

  function showDetail(title, desc, img, tanggalMulai, tanggalSelesai, lokasi) {
    document.getElementById('event-list').classList.add('hidden');
    document.getElementById('event-detail').classList.remove('hidden');

    document.getElementById('detail-title').innerText = title;
    document.getElementById('detail-description').innerText = desc;
    document.getElementById('detail-img').src = img;
    
    document.getElementById('detail-date').innerText = tanggalMulai + ' - ' + (tanggalSelesai ?? '-');
    document.getElementById('detail-location').innerText = lokasi ?? '-';
  }

  function backToList() {
    document.getElementById('event-detail').classList.add('hidden');
    document.getElementById('event-list').classList.remove('hidden');
  }
</script>
@endsection