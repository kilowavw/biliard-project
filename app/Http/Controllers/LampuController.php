<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LampuController extends Controller
{
    // ... Metode index tidak berubah
    public function index()
    {
        return view('lampu.index');
    }

    // Metode untuk menerima perintah dari web dan MENYIMPANNYA
    public function kirimPerintah(Request $request)
    {
        $request->validate(['perintah' => 'required|string']);

        $perintah = $request->input('perintah');

        // Simpan pesan ke file teks sederhana
        // Agar NodeMCU bisa mengambilnya nanti.
        file_put_contents(storage_path('app/perintah.txt'), $perintah);

        Log::info("Perintah baru diterima dan disimpan: '{$perintah}'");

        return response()->json([
            'status' => 'success',
            'message' => 'Perintah berhasil dikirim ke server dan siap diambil oleh perangkat',
        ]);
    }

    // Metode BARU untuk diambil oleh NodeMCU (polling)
    public function getPerintah()
    {
        // Ambil pesan dari file teks yang disimpan
        $perintah = file_exists(storage_path('app/perintah.txt'))
                    ? file_get_contents(storage_path('app/perintah.txt'))
                    : "Tidak ada perintah";

        return response()->json([
            'status' => 'success',
            'perintah' => $perintah,
        ]);
    }
}