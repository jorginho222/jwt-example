<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    public function update(UpdateUserRequest $request): JsonResponse
    {
        auth()->user()->update($request->validated());

        $user = UserResource::make(auth()->user()->fresh());

        return jsonResponse(compact('user'));
    }
}
