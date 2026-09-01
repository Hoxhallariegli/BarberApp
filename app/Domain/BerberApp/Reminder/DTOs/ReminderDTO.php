<?php

namespace App\Domain\BerberApp\Reminder\DTOs;

class ReminderDTO
{
    public function __construct(
        public readonly mixed $booking_id,
        public readonly mixed $reminder_type,
        public readonly mixed $sent_at,
    ) {}
    public static function fromArray(array $data): self { return new self(
            booking_id: $data['booking_id'] ?? null,
            reminder_type: $data['reminder_type'] ?? null,
            sent_at: $data['sent_at'] ?? null,
        ); }
    public function toArray(): array { return [
            'booking_id' => $this->booking_id,
            'reminder_type' => $this->reminder_type,
            'sent_at' => $this->sent_at,
        ]; }
}