<?php

namespace App\Http\Controllers;

use App\Models\Meja;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LampuController extends Controller
{
    public function index()
    {
        $meja = Meja::all();
        $devices = Device::all();
        return view('lampu.index', compact('meja', 'devices'));
    }

    public function kirimPerintah(Request $request)
    {
        $request->validate([
            'device_name' => 'required|string|exists:devices,name',
            // Tambahkan perintah LED ke daftar yang valid
            'command' => 'required|string|in:RESET,UPDATE_FIRMWARE,NO_COMMAND,LED_ON,LED_OFF,LED_BLINK',
        ]);

        $device = Device::where('name', $request->device_name)->first();

        if (!$device) {
            return response()->json(['status' => 'error', 'message' => 'Perangkat tidak ditemukan'], 404);
        }

        $device->update([
            'pending_command' => ($request->command === 'NO_COMMAND' ? null : $request->command),
            'command_sent_at' => now(),
            'command_executed_at' => null,
        ]);

        Log::info("Perintah '{$request->command}' berhasil dijadwalkan untuk perangkat: '{$request->device_name}'");

        return response()->json([
            'status' => 'success',
            'message' => 'Perintah berhasil dijadwalkan untuk perangkat.',
        ]);
    }

    public function getPerintahDanStatusMeja(Request $request)
    {
        $request->validate([
            'device_name' => 'required|string',
            'ip_address' => 'nullable|string',
            'command_executed' => 'nullable|string',
        ]);

        $device = Device::firstOrCreate(
            ['name' => $request->device_name],
            ['ip_address' => $request->ip_address, 'last_seen_at' => null] // jangan otomatis isi sekarang
        );
        

        $deviceDataToUpdate = [
            'ip_address' => $request->ip_address,
            'last_seen_at' => now(),
        ];

        if ($request->filled('command_executed') && $device->pending_command === $request->command_executed) {
            $deviceDataToUpdate['command_executed_at'] = now();
            $deviceDataToUpdate['pending_command'] = null;
            Log::info("Perangkat '{$request->device_name}' berhasil mengeksekusi perintah: '{$request->command_executed}'");
        }

        $device->update($deviceDataToUpdate);

        $mejaStatuses = Meja::all(['id', 'nama_meja', 'status'])
                           ->map(function ($meja) {
                               return [
                                   'id' => $meja->id,
                                   'nama_meja' => $meja->nama_meja,
                                   'status' => $meja->status,
                               ];
                           });

        $pendingCommand = null;
        if ($device->pending_command && !$device->command_executed_at) {
            $pendingCommand = $device->pending_command;
        }

        return response()->json([
            'status' => 'success',
            'meja_data' => $mejaStatuses,
            'global_command' => $pendingCommand,
        ]);
    }

    public function getDeviceStatus(Request $request)
    {
        $deviceName = $request->query('device_name');
        if ($deviceName) {
            $device = Device::where('name', $deviceName)->first();
            if ($device) {
                // Tentukan status online/offline berdasarkan last_seen_at
                $onlineThresholdSeconds = 10; // Perangkat dianggap online jika terlihat dalam 10 detik terakhir
                $isOnline = $device->last_seen_at && $device->last_seen_at->diffInSeconds(now()) < $onlineThresholdSeconds;

                $commandStatus = 'Tidak ada perintah';
                if ($device->pending_command) {
                    $commandStatus = 'Menunggu eksekusi: ' . $device->pending_command;
                    if ($device->command_executed_at && $device->command_sent_at && $device->command_executed_at->greaterThan($device->command_sent_at)) {
                        $commandStatus = 'Dieksekusi: ' . $device->pending_command . ' (' . $device->command_executed_at->diffForHumans() . ')';
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'device_name' => $device->name,
                    'online' => $isOnline,
                    'last_seen' => $device->last_seen_at ? $device->last_seen_at->diffForHumans() : 'Never', // Format yang lebih mudah dibaca
                    'ip_address' => $device->ip_address,
                    'command_status' => $commandStatus,
                    'current_pending_command' => $device->pending_command,
                ]);
            }
            return response()->json(['status' => 'error', 'message' => 'Perangkat tidak ditemukan'], 404);
        }

        $devices = Device::all()->map(function ($device) {
            $onlineThresholdSeconds = 10;
            if (!$device->last_seen_at) {
                $isOnline = 'never_seen';
            } elseif ($device->last_seen_at->greaterThan(now()->subSeconds($onlineThresholdSeconds))) {
                $isOnline = 'online';
            } else {
                $isOnline = 'offline';
            }
            
            
            $commandStatus = 'Tidak ada perintah';
            if ($device->pending_command) {
                $commandStatus = 'Menunggu eksekusi: ' . $device->pending_command;
                if ($device->command_executed_at && $device->command_sent_at && $device->command_executed_at->greaterThan($device->command_sent_at)) {
                    $commandStatus = 'Dieksekusi: ' . $device->pending_command . ' (' . $device->command_executed_at->diffForHumans() . ')';
                }
            }
            return [
                'device_name' => $device->name,
                'online' => $isOnline,
                'last_seen' => $device->last_seen_at ? $device->last_seen_at->diffForHumans() : 'Never',
                'ip_address' => $device->ip_address,
                'command_status' => $commandStatus,
                'current_pending_command' => $device->pending_command,
            ];
        });

        return response()->json(['status' => 'success', 'devices' => $devices]);
    }
}