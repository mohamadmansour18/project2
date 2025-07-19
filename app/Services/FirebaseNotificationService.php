<?php

namespace App\Services;

use App\Models\User;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;

class FirebaseNotificationService
{
    protected string $messagingUrl ;

    public function __construct()
    {
        $projectId = json_decode(file_get_contents(base_path(config('services.fcm.credentials_file'))) , true)['project_id'];
        $this->messagingUrl = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";
    }

    public function send(string $title , string $body , array $tokens): void
    {
        $accessToken = $this->getAccessToken();

        $payload = [];
        foreach ($tokens as $token)
        {
            $payload = [
                'message' => [
                    'token' => $token ,
                    'notification' => [
                        'title' => $title ,
                        'body' => $body ,
                    ],
                ]
            ];

            Http::withToken($accessToken)->post($this->messagingUrl , $payload);

        }


    }

    public function getAccessToken()
    {
        $scopes = ["https://www.googleapis.com/auth/firebase.messaging"];
        $credentials = new ServiceAccountCredentials(
            $scopes , base_path(config('services.fcm.credentials_file'))
        );

        return $credentials->fetchAuthToken()['access_token'];
    }
}
