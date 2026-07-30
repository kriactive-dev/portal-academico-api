<?php

namespace App\Services\Notification;

use App\Models\UserProfile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class PushNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $credentialsPath = config('firebase.credentials');

        if (!file_exists($credentialsPath)) {
            $storagePath = storage_path('app/firebase/firebase-credentials.json');
            if (file_exists($storagePath)) {
                $credentialsPath = $storagePath;
            }
        }

        $factory = (new Factory)
            ->withServiceAccount($credentialsPath)
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
        if (empty($tokens)) {
            return;
        }

        $messages = [];

        foreach ($tokens as $token) {
            $messages[] = CloudMessage::withTarget('token', $token)
                ->withNotification(Notification::create($title, $body))
                ->withData($data);
        }

        $this->messaging->sendAll($messages);
    }

    /**
     * Envia notificação para um tópico FCM.
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = []): void
    {
        $message = CloudMessage::withTarget('topic', $topic)
            ->withNotification(Notification::create($title, $body))
            ->withData($data);

        $this->messaging->send($message);

        Log::info('Push enviado para tópico', [
            'topic' => $topic,
            'title' => $title,
        ]);
    }

    /**
     * Inscreve um ou mais tokens num tópico.
     *
     * @param  string|array<int, string>  $tokens
     */
    public function subscribeToTopic(string $topic, string|array $tokens): void
    {
        $tokenList = is_array($tokens) ? array_values(array_filter($tokens)) : [$tokens];

        if (empty($tokenList)) {
            return;
        }

        $this->messaging->subscribeToTopic($topic, $tokenList);

        Log::info('Tokens inscritos no tópico', [
            'topic' => $topic,
            'token_count' => count($tokenList),
        ]);
    }

    /**
     * Remove um ou mais tokens de um tópico.
     *
     * @param  string|array<int, string>  $tokens
     */
    public function unsubscribeFromTopic(string $topic, string|array $tokens): void
    {
        $tokenList = is_array($tokens) ? array_values(array_filter($tokens)) : [$tokens];

        if (empty($tokenList)) {
            return;
        }

        $this->messaging->unsubscribeFromTopic($topic, $tokenList);

        Log::info('Tokens removidos do tópico', [
            'topic' => $topic,
            'token_count' => count($tokenList),
        ]);
    }

    /**
     * Inscreve o token em vários tópicos.
     *
     * @param  array<int, string>  $topics
     * @return array<int, string>
     */
    public function subscribeToTopics(string $token, array $topics): array
    {
        $subscribed = [];

        foreach (array_unique(array_filter($topics)) as $topic) {
            try {
                $this->subscribeToTopic($topic, $token);
                $subscribed[] = $topic;
            } catch (\Throwable $e) {
                Log::error('Erro ao inscrever token no tópico', [
                    'topic' => $topic,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $subscribed;
    }

    /**
     * Remove o token de vários tópicos.
     *
     * @param  array<int, string>  $topics
     */
    public function unsubscribeFromTopics(string $token, array $topics): void
    {
        foreach (array_unique(array_filter($topics)) as $topic) {
            try {
                $this->unsubscribeFromTopic($topic, $token);
            } catch (\Throwable $e) {
                Log::error('Erro ao remover token do tópico', [
                    'topic' => $topic,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function topicFaculdade(string $faculdade): string
    {
        return 'faculdade_' . Str::slug($faculdade);
    }

    public function topicCurso(string $course): string
    {
        return 'curso_' . Str::slug($course);
    }

    public function topicAno(string|int $year): string
    {
        return 'ano_' . $year;
    }

    /**
     * Constrói os tópicos a partir do perfil do aluno.
     * geral + faculdade + curso + ano curricular.
     *
     * @return array<int, string>
     */
    public function topicsFromProfile(?UserProfile $profile): array
    {
        $topics = ['geral'];

        if (!$profile) {
            return $topics;
        }

        if (!empty($profile->faculdade)) {
            $topics[] = $this->topicFaculdade($profile->faculdade);
        }

        if (!empty($profile->course)) {
            $topics[] = $this->topicCurso($profile->course);
        }

        if ($profile->academic_year !== null && $profile->academic_year !== '') {
            $topics[] = $this->topicAno($profile->academic_year);
        }

        return array_values(array_unique($topics));
    }

    /**
     * Resolve o tópico alvo de uma publicação (mais específico primeiro).
     * curso > ano > faculdade (university_name) > geral
     */
    public function topicFromPublication(
        ?string $courseName = null,
        ?string $year = null,
        ?string $universityName = null
    ): string {
        if (!empty($courseName)) {
            return $this->topicCurso($courseName);
        }

        if ($year !== null && $year !== '') {
            return $this->topicAno($year);
        }

        if (!empty($universityName)) {
            return $this->topicFaculdade($universityName);
        }

        return 'geral';
    }
}
