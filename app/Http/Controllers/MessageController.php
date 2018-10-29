<?php

namespace App\Http\Controllers;

use App\Conversation;
use App\Message;
use Auth;
use Illuminate\Http\Request;
use LRedis;

class MessageController extends Controller
{
    public function store(Request $request)
    {
        $conversation = Conversation::findOrFail($request->conversation_id);
        if ($conversation->user1 == Auth::user()->id || $conversation->user2 == Auth::user()->id) {
            $message = new Message();
            $message->conversation_id = $request->conversation_id;
            $message->user_id = Auth::user()->id;
            $message->text = $request->text;
            $message->is_read = 0;
            $message->save();

            $receiver_id = ($conversation->user1 == Auth::user()->id) ? $conversation->user2 : $conversation->user1;
            $data = [
                'conversation_id'=> $request->conversation_id,
                'text'           => $request->text,
                'client_id'      => $receiver_id,
            ];
            $redis = LRedis::connection();
            $redis->publish('message', json_encode($data));

            return response()->json(true);
        }

        return response()->json('permission error');
    }
}
