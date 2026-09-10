<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InsufficientStockException extends Exception
{
    public function __construct(
        string $message = 'Insufficient stock for one or more items.',
        public readonly array $stockErrors = [],
        int $code = 422
    ) {
        parent::__construct($message, $code);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => $this->stockErrors ?: ['stock' => [$this->getMessage()]],
            'code' => $this->getCode(),
        ], $this->getCode());
    }
}
