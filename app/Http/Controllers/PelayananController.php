<?php

namespace App\Http\Controllers;

use App\Models\Pelayanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PelayananController extends Controller
{
    public function index()
    {
        $makanan = Pelayanan::where('kategori', 'Makanan')->get();
        $minuman = Pelayanan::where('kategori', 'Minuman')->get();
        $rokok   = Pelayanan::where('kategori', 'Rokok')->get();

        $pelayanans = Pelayanan::all();

        return view('compro.pelayanan', compact('makanan', 'minuman', 'rokok', 'pelayanans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'     => 'required|string|max:255',
            'kategori' => 'required|in:Makanan,Minuman,Rokok',
            'harga'    => 'required|numeric',
            'gambar'   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // validasi gambar
        ]);

        $data = $request->only('nama','kategori','harga');

        if($request->hasFile('gambar')){
            $file = $request->file('gambar');
            $path = $file->store('pelayanan', 'public'); // simpan di storage/app/public/pelayanan
            $data['gambar'] = $path;
        }

        Pelayanan::create($data);

        return redirect()->back()->with('success', $request->kategori.' berhasil ditambahkan');
    }

    public function update(Request $request, Pelayanan $pelayanan)
    {
        $request->validate([
            'nama'     => 'required|string|max:255',
            'kategori' => 'required|in:Makanan,Minuman,Rokok',
            'harga'    => 'required|numeric',
            'gambar'   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $data = $request->only('nama','kategori','harga');

        if($request->hasFile('gambar')){
            // hapus gambar lama jika ada
            if($pelayanan->gambar && Storage::disk('public')->exists($pelayanan->gambar)){
                Storage::disk('public')->delete($pelayanan->gambar);
            }
            $file = $request->file('gambar');
            $path = $file->store('pelayanan', 'public');
            $data['gambar'] = $path;
        }

        $pelayanan->update($data);

        return redirect()->back()->with('success', $request->kategori.' berhasil diperbarui');
    }

    public function destroy(Pelayanan $pelayanan)
    {
        $kategori = $pelayanan->kategori;

        // hapus gambar jika ada
        if($pelayanan->gambar && Storage::disk('public')->exists($pelayanan->gambar)){
            Storage::disk('public')->delete($pelayanan->gambar);
        }

        $pelayanan->delete();

        return redirect()->back()->with('success', $kategori.' berhasil dihapus');
    }
}
