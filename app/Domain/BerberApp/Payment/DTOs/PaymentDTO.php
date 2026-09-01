<?php

namespace App\Domain\BerberApp\Payment\DTOs;

class PaymentDTO
{
    public function __construct(
        public readonly mixed $booking_id,
        public readonly mixed $amount,
        public readonly mixed $status,
    ) {}
    public static function fromArray(array $data): self { return new self(
            booking_id: $data['booking_id'] ?? null,
            amount: $data['amount'] ?? null,
            status: $data['status'] ?? null,
        ); }
    public function toArray(): array { return [
            'booking_id' => $this->booking_id,
            'amount' => $this->amount,
            'status' => $this->status,
        ]; }
}