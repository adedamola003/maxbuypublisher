<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\V1\BaseController;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class AuthenticatedSessionController extends BaseController
{
    /**
     * Handle an incoming authentication request.
     *
     * @param LoginRequest $request
     * @return array|JsonResponse
     * @throws ValidationException
     */
    public function store(LoginRequest $request): JsonResponse|array
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($validator->fails()){
            return $this->sendError('Validation Error.', $validator->messages());
        }

        $validated = $validator->validated();

        if (Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']])) {
            $user = Auth::user();
            // Revoke all tokens...
            $user->tokens()->delete();
            // Create a new token...
            $loginToken = $user->createToken('user')->plainTextToken;

            //todo: send email notification
            //Notification::route('mail', $user->email)->notify((new LoginNotification($user->user_tag, now(), $requestArr))->delay(now()->addSeconds(5)));
            $data['accessToken'] = $loginToken;
            $data['name'] = $user->name;
            $data['email'] = $user->email;
            $message = 'Login successful';

            return $this->sendResponse($data, $message, ResponseAlias::HTTP_CREATED);

        }
        //failed login request
        return $this->sendError('Your email or password is incorrect', ['error' => ['Your email or password is incorrect']]);

    }

    /**
     * Destroy an authenticated session.
     *
     * @return JsonResponse
     */
    public function destroy(): JsonResponse
    {
        $user = Auth::user();
        if($user){
            $user->tokens()->delete();
            return $this->sendResponse([],  'Logged out successfully', ResponseAlias::HTTP_CREATED);
        }
        return $this->sendError('User not found', ['error' => ['User not found']]);
    }
}
