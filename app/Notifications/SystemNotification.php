<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $title;

    public $message;

    public $url;

    public $type;

    /**
     * Create a new notification instance.
     */
    public function __construct($title, $message, $url = '#', $type = 'info')
    {
        $this->title = $title;
        $this->message = $message;
        $this->url = $url;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        // KONDISI 1: Penerima adalah User Terdaftar (Admin/Staff/User)
        if ($notifiable instanceof User) {
            // 1. Sistem (Lonceng)
            $channels[] = 'database';

            // 2. Email (Jika punya email)
            if (! empty($notifiable->email)) {
                $channels[] = 'mail';
            }

            // 3. WhatsApp (Jika punya nomor HP)
            if (! empty($notifiable->phone)) {
                $channels[] = WhatsAppChannel::class;
            }
        }
        // KONDISI 2: Penerima adalah Tamu / On-Demand Notification
        elseif ($notifiable instanceof AnonymousNotifiable) {
            // Tamu tidak punya akses login, jadi tidak pakai 'database'

            // Cek apakah route mail diset
            if (isset($notifiable->routes['mail'])) {
                $channels[] = 'mail';
            }

            // Cek apakah route whatsapp diset
            if (isset($notifiable->routes['whatsapp'])) {
                $channels[] = WhatsAppChannel::class;
            }
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('[Helpdesk] '.$this->title)
            ->greeting('Halo!')
            ->line($this->message);

        if ($this->type === 'success') {
            $mail->success();
        } elseif ($this->type === 'error') {
            $mail->error();
        }

        if ($this->url && $this->url !== '#') {
            $mail->action('Lihat Detail', $this->url);
        }

        return $mail->line('Terima kasih telah menggunakan layanan kami.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'type' => $this->type,
        ];
    }

    /**
     * Get the WhatsApp representation of the notification.
     */
    public function toWhatsapp($notifiable)
    {
        $text = "*[Helpdesk] {$this->title}*\n\n";
        $text .= "{$this->message}\n\n";

        if ($this->url && $this->url !== '#') {
            $text .= "Lihat detail: {$this->url}\n";
        }

        $text .= "\n_Pesan otomatis, jangan dibalas._";

        return ['text' => $text];
    }
}
