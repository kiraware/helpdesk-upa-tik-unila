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
        if (! method_exists($notification, 'toWhatsapp')) {
            return;
        }

        $messageData = $notification->toWhatsapp($notifiable);

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

        $cleanPhone = preg_replace('/[^0-9]/', '', $to);

        if (Str::startsWith($cleanPhone, '0')) {
            $cleanPhone = '62'.substr($cleanPhone, 1);
        }

        $formattedTo = '+'.$cleanPhone;
        $twilioTarget = 'whatsapp:'.$formattedTo;

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
