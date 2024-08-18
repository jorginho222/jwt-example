<?php

function jsonResponse($data = [], $status = 200, $message = 'Ok', $errors = []): \Illuminate\Http\JsonResponse
{
    return response()->json(compact('data', 'status', 'message', 'errors'), $status);
}
