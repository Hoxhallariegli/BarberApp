<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;

class ApiResponse implements Responsable
{
    public function __construct(
        protected mixed $data = null,
        protected ?string $message = null,
        protected int $status = 200,
        protected array $headers = []
    ) {}

    public function toResponse($request): JsonResponse
    {
        return response()->json([
            'data' => $this->data,
            'message' => $this->message,
            'success' => $this->status >= 200 && $this->status < 300,
        ], $this->status, $this->headers);
    }
}
