<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LocalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $message;

    /**
     * @var string|null
     */
    public $actionUrl;

    /**
     * @var string|null
     */
    public $actionText;

    /**
     * @var string
     */
    public $type;

    /**
     * @var array|null
     */
    public $metadata;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        string $title,
        string $message,
        string $type = 'info',
        ?string $actionUrl = null,
        ?string $actionText = null,
        ?array $metadata = null
    ) {
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
        $this->actionUrl = $actionUrl;
        $this->actionText = $actionText;
        $this->metadata = $metadata;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->subject($this->title)
            ->line($this->message);

        if ($this->actionUrl && $this->actionText) {
            $mailMessage->action($this->actionText, $this->actionUrl);
        }

        return $mailMessage->line('Obrigado por usar nosso sistema!');
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'action_url' => $this->actionUrl,
            'action_text' => $this->actionText,
            'metadata' => $this->metadata,
            'created_at' => now(),
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
