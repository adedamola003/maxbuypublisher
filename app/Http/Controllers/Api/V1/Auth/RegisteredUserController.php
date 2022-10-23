<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\BaseController;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class RegisteredUserController extends BaseController
{
    /**
     * Handle an incoming registration request.
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $messages = [
            'password.regex' => 'Password must be more than 8 characters long, should contain at least 1 Uppercase, 1 Lowercase and  1 number',
        ];
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:12'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9]).{8,}$/'],
        ], $messages);

        if ($validator->fails()) return $this->sendError('Validation Error.', $validator->messages());
        $validated = $validator->validated();


        $user = User::create([
            'email' => $validated['email'],
            'name' => ucfirst($validated['name']),
            'password' => Hash::make($validated['password']),
        ]);
        $data['email'] = $user->email;
        $data['hasVerifiedEmail'] = false;
        $message = "Successfully Registered";


        event(new Registered($user));

        return $this->sendResponse($data, $message, Response::HTTP_CREATED);


    }
}
