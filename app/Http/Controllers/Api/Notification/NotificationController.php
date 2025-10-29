<?php

namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Kreait\Firebase\Factory;
use Exception;
use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Auth as FirebaseAuth;



class NotificationController extends Controller
{

    public function send(Request $request)
    {

       $factory = (new Factory)
                ->withServiceAccount(config('firebase.credentials'))
                ->withProjectId(config('firebase.project_id'));

        $messaging = $factory->createMessaging();

        $token = $request->token;
        $title = $request->title;
        $body = $request->body;

        $message = CloudMessage::withTarget('token', $token)
            ->withNotification(Notification::create($title, $body));

        $messaging->send($message);

        return response()->json(['success' => true]);
    }
}
