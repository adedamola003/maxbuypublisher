<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Api\V1\BaseController;
use App\Models\Subscriber;
use App\Models\Topic;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SubscriberController extends BaseController
{
    private Subscriber $subscriber;
    private Topic $topic;

    public function __construct()
    {
        $this->subscriber = new Subscriber();
        $this->topic = new Topic();
    }

    /**
     * @throws Exception
     */
    public function addSubscriber(Request $request): JsonResponse
    {
        //validate requests
        $validator = Validator::make($request->all(), [
            'slug' => ['required', 'string', 'max:255', 'exists:topics,slug'],
            'endpoint' => ['required', 'url', 'max:255']
        ]);

        if ($validator->fails()){
            return $this->sendError('Validation Error.', $validator->messages());
        }
        $validated = $validator->validated();

        //get topic id
        $topic = $this->topic::where('slug', $validated['slug'])->first();
        if(!$topic){
            return $this->sendError('Topic not found', [ 'error' => 'Topic not found, please try again']);
        }
        //check if subscriber already exists
        $subscriber = $this->subscriber::where(['endpoint' => $validated['endpoint'], 'topic_id' => $topic->id])->exists();
        if($subscriber){
            return $this->sendError('Subscriber already exists', [ 'error' => 'Subscriber already exists']);
        }

        //create subscriber
        $thisSubscriber = $this->subscriber::create([
            'slug' => generateSlug(),
            'topic_id' => $topic->id,
            'endpoint' => $validated['endpoint'],
            'status' => 'active',
        ]);
        if(!$thisSubscriber){
            return $this->sendError('Error creating subscriber, please try again', [ 'error' => 'Error creating subscriber, please try again']);
        }

        $data = [
            'slug' => $thisSubscriber->slug,
            'topic' => $topic->name,
            'endpoint' => $thisSubscriber->endpoint,
            'status' => ucfirst($thisSubscriber->status)
        ];

        return $this->sendResponse($data, 'Subscriber added successfully');

    }

    public function getAllSubscribers(): JsonResponse
    {
        $subscribers = $this->subscriber::with('topic')->get();
        $data = [];
        foreach($subscribers as $subscriber){
            //select(['slug','topic.id', 'endpoint', 'status'])
            $data[] = [
                'slug' => $subscriber->slug,
                'topic' => $subscriber->topic->name,
                'endpoint' => $subscriber->endpoint,
                'status' => ucfirst($subscriber->status)
            ];
        }

        return $this->sendResponse($data, 'Subscribers retrieved successfully');
    }

    public function getSubscriber($slug): JsonResponse
    {
        $subscriber = $this->subscriber::with('topic')->where(['slug' => $slug])->first();
        if(!$subscriber){
            return $this->sendError('Subscriber not found', [ 'error' => 'Subscriber not found']);
        }
        $data = [
            'slug' => $subscriber->slug,
            'topic' => $subscriber->topic->name,
            'endpoint' => $subscriber->endpoint,
            'status' => ucfirst($subscriber->status),
            'created_at' => $subscriber->created_at,
        ];
        return $this->sendResponse($data, 'Subscriber retrieved successfully');
    }

    /**
     * @throws ValidationException
     */
    public function deactivateSubscriber(Request $request): JsonResponse
    {
        //validate requests
        $validator = Validator::make($request->all(), [
            'slug' => ['required', 'string', 'max:255', 'exists:subscribers']
        ]);

        if ($validator->fails()){
            return $this->sendError('Validation Error.', $validator->messages());
        }
        $validated = $validator->validated();

        $thisSubscriber = $this->subscriber::where(['slug' => $validated['slug']])->first();

        //check if subscriber is already deactivated
        if($thisSubscriber->status == 'inactive'){
            return $this->sendError('Subscriber already deactivated', [ 'error' => 'Subscriber already deactivated']);
        }

        //deactivate subscriber
        $thisSubscriber->update(['status' => 'inactive']);

        $data = [
            'name' => $thisSubscriber->name,
            'slug' => $thisSubscriber->slug,
            'endpoint' => $thisSubscriber->endpoint,
            'status' => ucfirst($thisSubscriber->status)
        ];

        return $this->sendResponse( $data, 'Subscriber deactivated successfully');

    }

    /**
     * @throws ValidationException
     */
    public function activateSubscriber(Request $request): JsonResponse
    {
        //validate requests
        $validator = Validator::make($request->all(), [
            'slug' => ['required', 'string', 'max:255', 'exists:subscribers']
        ]);

        if ($validator->fails()){
            return $this->sendError('Validation Error.', $validator->messages());
        }
        $validated = $validator->validated();

        $thisSubscriber = $this->subscriber::where(['slug' => $validated['slug']])->first();

        //check if subscriber is already deactivated
        if($thisSubscriber->status == 'active'){
            return $this->sendError('Subscriber is already active', [ 'error' => 'Subscriber is already active']);
        }

        //deactivate subscriber
        $thisSubscriber->update(['status' => 'active']);

        $data = [
            'slug' => $thisSubscriber->slug,
            'endpoint' => $thisSubscriber->endpoint,
            'status' => ucfirst($thisSubscriber->status)
        ];

        return $this->sendResponse( $data, 'Subscriber activated successfully');
    }
}
