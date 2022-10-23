<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Api\V1\BaseController;
use App\Models\Topic;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TopicController extends BaseController
{
    private Topic $topic;

    public function __construct()
    {
       $this->topic = new Topic();
    }

    public function getTopics(): JsonResponse
    {
        $topics = $this->topic::select(['slug', 'name', 'description'])->get();
        return $this->sendResponse($topics, 'Topics retrieved successfully');
    }

    /**
     * @throws ValidationException
     * @throws Exception
     */
    public function addTopic(Request $request): JsonResponse
    {
        //validate requests
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'unique:topics'],
            'description' => ['required', 'string', 'max:255']
        ]);

        if ($validator->fails()){
            return $this->sendError('Validation Error.', $validator->messages());
        }
        $validated = $validator->validated();

        $topic = $this->topic::create([
            'slug' => generateSlug(),
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);
        if(!$topic){
            return $this->sendError('Error creating topic, please try again', [ 'error' => 'Error creating topic, please try again']);
        }
        $data = [
            'slug' => $topic->slug,
            'name' => $topic->name,
            'description' => $topic->description,
        ];
        return $this->sendResponse($data, 'Topic added successfully');
    }

}
