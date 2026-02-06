<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemNotification extends Notification
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
        // Jika yang dinotifikasi adalah User terdaftar, kirim ke Database (Lonceng)
        if ($notifiable instanceof User) {
            return ['database'];
        }

        // Jika Guest (AnonymousNotifiable), kirim via Email
        return ['mail'];
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

        // Tambahkan warna tombol berdasarkan tipe
        if ($this->type === 'success') {
            $mail->success(); // Warna Hijau
        } elseif ($this->type === 'error') {
            $mail->error(); // Warna Merah
        }

        // Jika URL bukan '#'
        if ($this->url && $this->url !== '#') {
            $mail->action('Lihat Detail Tiket', $this->url);
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
}
