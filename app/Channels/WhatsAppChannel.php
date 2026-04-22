<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WhatsAppChannel
{
    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification)
    {
        // 1. Ambil pesan dari method toWhatsapp di Notification Class
        if (! method_exists($notification, 'toWhatsapp')) {
            return;
        }

        $messageData = $notification->toWhatsapp($notifiable);

        // 2. Tentukan Nomor Tujuan
        $to = null;

        if (method_exists($notifiable, 'routeNotificationForWhatsapp')) {
            $to = $notifiable->routeNotificationForWhatsapp($notification);
        } elseif (isset($notifiable->phone)) {
            $to = $notifiable->phone;
        } elseif (isset($notifiable->routes[__CLASS__])) {
            $to = $notifiable->routes[__CLASS__];
        } elseif (isset($notifiable->routes['whatsapp'])) {
            $to = $notifiable->routes['whatsapp'];
        }

        if (! $to) {
            return;
        }

        // --- FORMAT NOMOR HP UNTUK TWILIO ---
        // Twilio mewajibkan format E.164 (misal: +628123456789)
        // Kita bersihkan dan format nomornya:

        // Hapus karakter selain angka
        $cleanPhone = preg_replace('/[^0-9]/', '', $to);

        // Jika diawali 0, ganti dengan 62 (asumsi Indonesia)
        if (Str::startsWith($cleanPhone, '0')) {
            $cleanPhone = '62'.substr($cleanPhone, 1);
        }

        // Jika belum ada prefix '+', tambahkan
        $formattedTo = '+'.$cleanPhone;

        // Prefix wajib Twilio
        $twilioTarget = 'whatsapp:'.$formattedTo;

        // 3. KIRIM KE API TWILIO
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $fromNumber = config('services.twilio.whatsapp_from');

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

        try {
            $response = Http::withBasicAuth($sid, $token)
                ->asForm()
                ->post($url, [
                    'From' => 'whatsapp:'.$fromNumber,
                    'To' => $twilioTarget,
                    'Body' => $messageData['text'],
                ]);

            if ($response->failed()) {
                \Log::error('Twilio Error: '.$response->body());
            }

        } catch (\Exception $e) {
            \Log::error('Gagal kirim WA (Twilio): '.$e->getMessage());
        }
    }
}
