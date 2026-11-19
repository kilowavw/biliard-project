<?php

namespace Tests\Feature;

use App\Models\HargaSetting;
use App\Models\Meja;
use App\Models\Paket;
use App\Models\Penyewaan;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class KasirBookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--force' => true]);
    }

    private function signInKasir(): User
    {
        $user = User::create([
            'name' => 'Test Kasir',
            'email' => 'kasir@example.test',
            'password' => bcrypt('secret'),
            'role' => 'kasir',
        ]);
        $this->actingAs($user);
        HargaSetting::create(['harga_per_jam' => 30000]);

        return $user;
    }

    private function meja(): Meja
    {
        return Meja::create(['nama_meja' => 'Test Table', 'status' => 'kosong']);
    }

    private function initiate(Meja $meja, array $data): array
    {
        $response = $this->postJson(route('kasir.initiatePemesanan'), array_merge([
            'meja_id' => $meja->id,
            'nama_penyewa' => 'Test Guest',
        ], $data));

        $response->assertOk()->assertJsonPath('success', true);

        return $response->json('pemesanan_data');
    }

    public function test_duration_and_unlimited_bookings_start_and_release_reservations(): void
    {
        $this->signInKasir();

        foreach ([['durasi', ['durasi_jam' => 1.5]], ['sepuasnya', []]] as [$type, $details]) {
            $meja = $this->meja();
            $booking = $this->initiate($meja, array_merge(['tipe_pesanan' => $type], $details));
            $this->assertNotNull($booking['reservation_token']);

            $this->postJson(route('kasir.confirmPemesanan'), $booking)
                ->assertOk()->assertJsonPath('success', true);

            $this->assertSame('dipakai', $meja->fresh()->status);
            $this->assertNull($meja->fresh()->reservation_token);
            $this->assertTrue(Penyewaan::where('meja_id', $meja->id)->where('status', 'berlangsung')->exists());
        }
    }

    public function test_package_booking_decrements_stock_once(): void
    {
        $this->signInKasir();
        $service = Service::create(['nama' => 'Soda', 'harga' => 5000, 'stok' => 5]);
        $paket = Paket::create([
            'nama_paket' => 'Test Package',
            'isi_paket' => ['durasi_jam' => 2, 'harga_paket' => 40000, 'services' => [[
                'id' => $service->id, 'nama' => $service->nama, 'jumlah' => 2,
            ]]],
            'aktif' => true,
        ]);
        $meja = $this->meja();
        $booking = $this->initiate($meja, ['tipe_pesanan' => 'paket', 'paket_id' => $paket->id]);

        $this->postJson(route('kasir.confirmPemesanan'), $booking)
            ->assertOk()->assertJsonPath('success', true);

        $this->postJson(route('kasir.confirmPemesanan'), $booking)
            ->assertOk()->assertJsonPath('idempotent', true);

        $this->assertSame(3, $service->fresh()->stok);
        $this->assertDatabaseHas('penyewaans', [
            'meja_id' => $meja->id,
            'paket_id' => $paket->id,
            'harga_per_jam' => 40000,
        ]);
    }

    public function test_cancelled_reservation_cannot_clear_an_active_booking(): void
    {
        $this->signInKasir();
        $meja = $this->meja();
        $booking = $this->initiate($meja, ['tipe_pesanan' => 'durasi', 'durasi_jam' => 1]);

        $this->postJson(route('kasir.confirmPemesanan'), $booking)->assertOk();
        $this->postJson(route('kasir.resetMejaStatus', $meja), ['reservation_token' => $booking['reservation_token']])
            ->assertStatus(409)->assertJsonPath('success', false);

        $this->assertSame('dipakai', $meja->fresh()->status);
    }

    public function test_booking_validation_requires_the_matching_choice_data(): void
    {
        $this->signInKasir();
        $meja = $this->meja();

        $this->postJson(route('kasir.initiatePemesanan'), [
            'meja_id' => $meja->id,
            'nama_penyewa' => 'Test Guest',
            'tipe_pesanan' => 'durasi',
        ])->assertUnprocessable()->assertJsonValidationErrors('durasi_jam');

        $this->assertSame('kosong', $meja->fresh()->status);
    }

    public function test_admin_login_redirects_to_cashier_dashboard(): void
    {
        User::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.test',
            'password' => bcrypt('secret'),
            'role' => 'admin',
        ]);

        $this->post('/login', ['email' => 'admin@example.test', 'password' => 'secret'])
            ->assertRedirect(route('dashboard.kasir'));
    }

    public function test_expired_reservation_is_released_without_changing_active_tables(): void
    {
        $this->signInKasir();
        $expired = $this->meja();
        $expired->update([
            'status' => 'dipakai',
            'reservation_token' => (string) Str::uuid(),
            'reservation_expires_at' => now()->subMinute(),
        ]);
        $active = $this->meja();
        $active->update([
            'status' => 'dipakai',
            'reservation_token' => (string) Str::uuid(),
            'reservation_expires_at' => now()->subMinute(),
        ]);
        Penyewaan::create([
            'meja_id' => $active->id,
            'nama_penyewa' => 'Active Guest',
            'durasi_jam' => 1,
            'harga_per_jam' => 30000,
            'waktu_mulai' => now()->subMinutes(30),
            'waktu_selesai' => now()->addMinutes(30),
            'status' => 'berlangsung',
        ]);

        $this->get(route('dashboard.kasir'))->assertOk();

        $this->assertSame('kosong', $expired->fresh()->status);
        $this->assertSame('dipakai', $active->fresh()->status);
    }
}
