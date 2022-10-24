<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Api\V1\BaseController;
use App\Models\Message;
use App\Models\Publisher;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Jobs\ProcessMessage;

class PublisherController extends BaseController
{
    public Message $message;
    public Topic $topic;
    public Publisher $publisher;
    /**
     * @var string[]
     */
    private array $header;

    public function __construct()
    {
        $this->message = new message();
        $this->topic = new Topic();
        $this->publisher = new Publisher();
        $this->header = ['Content-Type' => 'application/json', 'Accept' => 'application/json'];
    }

    /**
     * @throws ValidationException
     */
    public function createMessage(Request $request)
    {
        //validate requests
        $validator = Validator::make($request->all(), [
            'message' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'exists:topics,slug']
        ]);

        if ($validator->fails()){
            return $this->sendError('Validation Error.', $validator->messages());
        }
        $validated = $validator->validated();

        $topic = $this->topic::with('subscribers')->where('slug', $validated['slug'])->first();
        if(!$topic){
            return $this->sendError('Topic not found', [ 'error' => 'Topic not found, please try again']);
        }

        $thisMessage = $this->message::create([
            'slug' => generateSlug(),
            'topic_id' => $topic->id,
            'message' => $validated['message'],
            'receiver_count' => $topic->subscribers->count(),
        ]);

        foreach($topic->subscribers->where('status', 'active') as $subscriber){
            Processmessage::dispatch($this->publisher, $subscriber, $thisMessage->id, $validated['message'])->delay(now()->addSeconds(3));
            //Job to send message to subscriber
           // $this->dispatch(new SendNotification($subscriber, $thisMessage));
        }

        $data = [
            'status' => 'Message dispatched successfully',
            'receiver_count' => $topic->subscribers->count(),
            'message' => $validated['message'],
            'topic' => $topic->name
        ];

        return $this->sendResponse($data, 'Message dispatched successfully');
    }

    public function getAllMessages(): \Illuminate\Http\JsonResponse
    {
        $messages = $this->message::with('topic')->get();

        $data = [];
        foreach($messages as $message){
            $data[] = [
                'slug' => $message->slug,
                'message' => $message->message,
                'topic' => $message->topic->name,
                'receiver_count' => $message->receiver_count,
                'created_at' => $message->created_at,
            ];
        }

        return $this->sendResponse($data, 'Messages retrieved successfully');
    }

    public function getMessage($slug): \Illuminate\Http\JsonResponse
    {
        $message = $this->message::with('receivers.subscriber')->where('slug', $slug)->first();

        if(!$message){
            return $this->sendError('Message not found', [ 'error' => 'Message not found, please try again']);
        }

        $data = [
            'slug' => $message->slug,
            'message' => $message->message,
            'topic' => $message->topic->name,
            'receiver_count' => $message->receiver_count,
            'created_at' => $message->created_at,
        ];
        $data['receivers'] = [];

        foreach($message->receivers as $receiver){
            $data['receivers'][] = [
                'endpoint' => $receiver->subscriber->endpoint,
                'created_at' => $receiver->created_at,
            ];
        }

        return $this->sendResponse($data, 'Message retrieved successfully');
    }
}
