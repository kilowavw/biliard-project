@extends('compro.profile')

@section('content')
<div class="container mx-auto mt-16 px-4 text-center">

    <!-- Logo / Hiasan dengan Font Awesome -->
    <div class="flex justify-center items-center mb-4 space-x-6 text-green-400 text-4xl">
        <i class="fas fa-utensils"></i> <!-- Sendok & Garpu -->
        <i class="fas fa-hamburger"></i> <!-- Makanan -->
        <i class="fas fa-coffee"></i> <!-- Minuman -->
    </div>

    <!-- Decorative Line -->
    <div class="h-1 w-24 bg-green-400 mx-auto mb-4 rounded"></div>

    <h2 class="text-3xl md:text-4xl font-extrabold text-center text-green-400 mb-10 tracking-wide">Daftar Pelayanan</h2>

    <!-- Button Tambah Pelayanan -->
    <div class="text-center mb-8">
        <button 
            class="bg-green-400 text-gray-900 px-6 py-2 rounded-full hover:bg-green-300 transition shadow-md font-semibold uppercase tracking-wider"
            onclick="openModal('tambahModal')">
            Tambah Pelayanan
        </button>
    </div>

    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="bg-green-500 text-gray-900 px-5 py-2 rounded-lg mb-6 text-center shadow-md font-medium animate-fadeIn">
            {{ session('success') }}
        </div>
    @endif

    <!-- List per Kategori -->
    @foreach(['Makanan' => $makanan, 'Minuman' => $minuman, 'Rokok' => $rokok] as $kategori => $items)
    @php
        $gradient = match($kategori) {
            'Makanan' => 'from-green-400 to-green-600',
            'Minuman' => 'from-yellow-400 to-yellow-600',
            'Rokok'   => 'from-gray-500 to-gray-700',
        };
    @endphp

    <section class="category-section rounded-3xl p-8 mb-12 shadow-xl transition-all duration-500 transform hover:-translate-y-2 animate-section bg-gray-900">

        <!-- Icon kategori dengan gradient per menu -->
        <div class="flex justify-center items-center mb-6 space-x-8 text-5xl md:text-6xl">
            @if($kategori == 'Makanan')
                <i class="fas fa-utensils category-icon bg-clip-text text-transparent bg-gradient-to-r {{ $gradient }} animate-gradient-hover"></i>
            @elseif($kategori == 'Minuman')
                <i class="fas fa-coffee category-icon bg-clip-text text-transparent bg-gradient-to-r {{ $gradient }} animate-gradient-hover"></i>
            @elseif($kategori == 'Rokok')
                <i class="fas fa-smoking category-icon bg-clip-text text-transparent bg-gradient-to-r {{ $gradient }} animate-gradient-hover"></i>
            @endif
        </div>

        <h4 class="text-3xl md:text-4xl font-extrabold mb-8 text-green-400 border-b border-gray-700 pb-3 tracking-wide text-center">{{ $kategori }}</h4>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
            @foreach($items as $p)
            <div class="flex flex-col items-center text-center bg-gray-800 rounded-3xl p-6 shadow-md hover:shadow-xl transition duration-500 transform hover:-translate-y-2 hover:scale-105 animate-item">

                @if($p->gambar)
                    <img src="{{ asset('storage/'.$p->gambar) }}" alt="{{ $p->nama }}" class="h-28 w-28 md:h-32 md:w-32 rounded-full object-cover mb-4 border-2 border-green-400 shadow-lg transition-transform duration-500 hover:scale-110">
                @else
                    <div class="h-28 w-28 md:h-32 md:w-32 rounded-full bg-gray-700 flex items-center justify-center mb-4 shadow-lg border-2 border-gray-600">
                        <span class="text-gray-400 text-sm">No Image</span>
                    </div>
                @endif

                <h5 class="text-lg md:text-xl font-semibold text-green-400 truncate">{{ $p->nama }}</h5>
                <p class="text-gray-300 text-sm md:text-base mt-2">Rp {{ number_format($p->harga,0,',','.') }}</p>

                <div class="flex space-x-3 mt-4">
                    <button class="bg-yellow-400 text-gray-900 px-4 py-1 rounded-full hover:bg-yellow-300 text-sm font-medium transition shadow-sm"
                            onclick="openModal('editModal-{{ $p->id }}')">Edit</button>
                    <form action="{{ route('pelayanan.destroy', $p->id) }}" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="bg-red-500 text-white px-4 py-1 rounded-full hover:bg-red-400 text-sm font-medium transition shadow-sm" onclick="return confirm('Yakin hapus?')">Hapus</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endforeach
</div>

<!-- Modal Tambah -->
<div id="tambahModal" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50">
    <div class="bg-gray-900 rounded-2xl p-6 w-80 md:w-96 shadow-xl animate-fadeIn border border-green-400">
        <h3 class="text-xl md:text-2xl font-semibold text-green-400 mb-5 text-center tracking-wide">Tambah Pelayanan</h3>
        <form action="{{ route('pelayanan.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="text" name="nama" placeholder="Nama" class="w-full p-2 rounded-lg bg-gray-800 text-white border border-gray-700 focus:border-green-400 focus:ring-1 focus:ring-green-400" required>
            <select name="kategori" class="w-full p-2 rounded-lg bg-gray-800 text-white border border-gray-700 focus:border-green-400 focus:ring-1 focus:ring-green-400" required>
                <option value="">Pilih Kategori</option>
                <option value="Makanan">Makanan</option>
                <option value="Minuman">Minuman</option>
                <option value="Rokok">Rokok</option>
            </select>
            <input type="number" name="harga" placeholder="Harga" class="w-full p-2 rounded-lg bg-gray-800 text-white border border-gray-700 focus:border-green-400 focus:ring-1 focus:ring-green-400" required>
            <input type="file" name="gambar" accept="image/*" class="w-full text-gray-200">
            <div class="flex justify-end space-x-3 mt-2">
                <button type="button" onclick="closeModal('tambahModal')" class="px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 transition font-medium">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-green-400 text-gray-900 hover:bg-green-300 transition font-semibold">Tambah</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
@foreach($pelayanans as $p)
<div id="editModal-{{ $p->id }}" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50">
    <div class="bg-gray-900 rounded-2xl p-6 w-80 md:w-96 shadow-xl animate-fadeIn border border-green-400">
        <h3 class="text-xl md:text-2xl font-semibold text-green-400 mb-5 text-center tracking-wide">Edit {{ $p->nama }}</h3>
        <form action="{{ route('pelayanan.update', $p->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf @method('PUT')
            <input type="text" name="nama" value="{{ $p->nama }}" class="w-full p-2 rounded-lg bg-gray-800 text-white border border-gray-700 focus:border-green-400 focus:ring-1 focus:ring-green-400" required>
            <select name="kategori" class="w-full p-2 rounded-lg bg-gray-800 text-white border border-gray-700 focus:border-green-400 focus:ring-1 focus:ring-green-400" required>
                <option value="Makanan" {{ $p->kategori == 'Makanan' ? 'selected' : '' }}>Makanan</option>
                <option value="Minuman" {{ $p->kategori == 'Minuman' ? 'selected' : '' }}>Minuman</option>
                <option value="Rokok" {{ $p->kategori == 'Rokok' ? 'selected' : '' }}>Rokok</option>
            </select>
            <input type="number" name="harga" value="{{ $p->harga }}" class="w-full p-2 rounded-lg bg-gray-800 text-white border border-gray-700 focus:border-green-400 focus:ring-1 focus:ring-green-400" required>
            <input type="file" name="gambar" accept="image/*" class="w-full text-gray-200">
            <div class="flex justify-end space-x-3 mt-2">
                <button type="button" onclick="closeModal('editModal-{{ $p->id }}')" class="px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 transition font-medium">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-yellow-400 text-gray-900 hover:bg-yellow-300 transition font-semibold">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endforeach

<script>
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}
function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}
document.querySelectorAll('.animate-item').forEach((card, index) => {
    card.style.animationDelay = `${index * 100}ms`;
</script>

<style>
@keyframes fadeIn {
    from {opacity: 0; transform: translateY(-20px);}
    to {opacity: 1; transform: translateY(0);}
}
@keyframes fadeSlideUp {
    0% {opacity: 0; transform: translateY(20px);}
    100% {opacity: 1; transform: translateY(0);}
}
/* Animasi masuk section */
@keyframes fadeSlideUpSection {
    0% {opacity: 0; transform: translateY(20px);}
    100% {opacity: 1; transform: translateY(0);}
}

/* Hover gradient animasi icon kategori */
@keyframes gradientShift {
    0% {background-position: 0%;}
    50% {background-position: 100%;}
    100% {background-position: 0%;}
}
.animate-gradient-hover {
    background-size: 200% 200%;
    animation: gradientShift 3s ease infinite;
}

.animate-section {
    animation: fadeSlideUpSection 0.5s ease forwards;
}

/* Animasi masuk item */
@keyframes fadeSlideUpItem {
    0% {opacity: 0; transform: translateY(10px);}
    100% {opacity: 1; transform: translateY(0);}
}

@keyframes fadeUp {
    to {opacity:1; transform: translateY(0);}
}

.animate-item {
    opacity: 0;
    transform: translateY(20px);
    animation: fadeUp 0.6s forwards;
}

/* Shadow section subtle */
.category-section {
    background-color: #111827;
    border-radius: 1rem;
    transition: all 0.5s ease;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
.category-section:hover {
    box-shadow: 0 15px 25px rgba(0, 128, 0, 0.15), 0 5px 15px rgba(0, 255, 128, 0.05);
}

/* Gradient hover ikon per kategori */
.category-icon {
    transition: all 0.4s ease;
}
.category-icon:hover {
    background: linear-gradient(135deg, #38b000, #00ffb8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    transform: translateY(-4px) scale(1.2);
}

</style>
@endsection
