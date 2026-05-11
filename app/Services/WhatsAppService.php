<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send a WhatsApp message using Fonnte API.
     * 
     * @param string $target The destination phone number
     * @param string $message The message content
     * @return bool
     */
    public static function sendMessage($target, $message)
    {
        $token = env('FONNTE_TOKEN');
        
        if (!$token) {
            Log::warning('Fonnte token not set. WhatsApp message not sent.');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $token
            ])->post('https://api.fonnte.com/send', [
                'target' => $target,
                'message' => $message,
                'countryCode' => '62', // Default Indonesia
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('Fonnte API Error: ' . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error('WhatsApp Service Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Format a notification message for new permit requests.
     * 
     * @param \App\Models\Izin $izin
     * @return string
     */
    public static function formatIzinNotification($izin)
    {
        $santriName = $izin->user->name;
        $jenisIzin = $izin->jenis_izin;
        $tanggal = $izin->tanggal_mulai->format('d/m/Y') . ' s/d ' . $izin->tanggal_selesai->format('d/m/Y');
        $keterangan = $izin->keterangan;
        
        $msg = "*NOTIFIKASI IZIN BARU*\n\n";
        $msg .= "Ada pengajuan izin baru yang memerlukan konfirmasi:\n\n";
        $msg .= "👤 *Santri:* {$santriName}\n";
        $msg .= "📝 *Jenis:* {$jenisIzin}\n";
        if ($izin->waktu_sholat && $izin->waktu_sholat !== 'Full Day') {
            $msg .= "⏰ *Waktu:* {$izin->waktu_sholat}\n";
        }
        $msg .= "📅 *Tanggal:* {$tanggal}\n";
        $msg .= "📄 *Keterangan:* {$keterangan}\n\n";
        $msg .= "Silakan login ke sistem untuk memproses:\n";
        $msg .= url('/izin/manage');
        
        return $msg;
    }
}
