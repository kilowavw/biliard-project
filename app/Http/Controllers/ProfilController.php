<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfilController extends Controller
{
    public function index()
    {
        // Data Company Profile
        $data = [
            'company' => [
                'nama' => 'CIMAHI BILLIARD CENTRE',
                'tagline' => 'Tempat terbaik untuk bermain & bersantai',
                'deskripsi' => 'PT Billiard Jaya adalah perusahaan yang bergerak di bidang pembuatan, penyewaan, dan distribusi meja billiard. 
                                Dengan pengalaman lebih dari 10 tahun, kami selalu mengutamakan kualitas, desain elegan, serta pelayanan profesional.',
            ],
            'layanan' => [
                [
                    'judul' => 'Pembuatan Meja Billiard',
                    'deskripsi' => 'Custom desain sesuai permintaan dengan bahan berkualitas.',
                    'image' => 'images/billiard_contoh1.png'
                ],
                [
                    'judul' => 'Sewa Meja Billiard',
                    'deskripsi' => 'Menyediakan paket penyewaan untuk event atau kebutuhan pribadi.',
                    'image' => 'images/billiard_contoh2.png'
                ],
                [
                    'judul' => 'Distribusi Peralatan',
                    'deskripsi' => 'Menjual aksesoris dan perlengkapan billiard berkualitas.',
                    'image' => 'images/billiard_contoh3.png'

                ],
            ],
            'images' => [
                    'images/slider1.png',
                    'images/slider2.png',
                    'images/slider3.png',
                    // 'images/slider4.png',

            ],
            'kontak' => [
                'email' => 'info@billiardjaya.com',
                'alamat' => 'Jl. Raya Billiard No. 10, Bandung',
                'telepon' => '+62 812-3456-7890',
            ],
            'harga' => [
                [
                    'judul' => 'Daftar Harga Perjam',
                    'deskripsi' => 'Non Paket',
                    'image' => 'images/non_paket.png'
                ],
                [
                    'judul' => 'Daftar Paket Siang',
                    'deskripsi' => 'Jam 10:00 s.d 17:00',
                    'image' => 'images/Promo_siang.png'
                ],
                [
                    'judul' => 'Daftar Paket Malam',
                    'deskripsi' => 'Jam 17:00 s.d 24:00',
                    'image' => 'images/Promo_malam.png'

                ],
            ]
        ];
        

        return view('compro/home', compact('data'));
    }
}

