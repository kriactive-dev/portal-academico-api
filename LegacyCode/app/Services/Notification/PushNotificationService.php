<?php

namespace App\Services\Notification;


use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class PushNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(config('firebase.credentials'))
            ->withProjectId(config('firebase.project_id'));

        $this->messaging = $factory->createMessaging();
    }

    /**
     * Envia uma notificação push a um único dispositivo.
     */
    public function sendToDevice(string $token, string $title, string $body, array $data = []): void
    {
        $message = CloudMessage::withTarget('token', $token)
            ->withNotification(Notification::create($title, $body))
            ->withData($data);

        $this->messaging->send($message);
    }

    /**
     * Envia notificação para múltiplos dispositivos.
     */
    public function sendToDevices(array $tokens, string $title, string $body, array $data = []): void
    {
        $messages = [];

        foreach ($tokens as $token) {
            $messages[] = CloudMessage::withTarget('token', $token)
                ->withNotification(Notification::create($title, $body))
                ->withData($data);
        }

        $this->messaging->sendAll($messages);
    }
}





