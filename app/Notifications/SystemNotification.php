<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public string $url = '#',
        public string $type = 'info',
        public array $channels = ['database']
    ) {}

    public function via(object $notifiable): array
    {
        return $this->channels;
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

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'type' => $this->type,
        ];
    }

    public function toWhatsapp($notifiable): array
    {
        return [
            'text' => "*[HELPDESK UPA TIK]* {$this->title}\n\n"
                ."{$this->message}\n\n"
                ."📍 Cek detail:\n{$this->url}",
        ];
    }
}
