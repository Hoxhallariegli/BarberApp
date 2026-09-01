<?php

namespace App\Domain\BerberApp\Booking\DTOs;

class BookingDTO
{
    public function __construct(
        public readonly mixed $customer_id,
        public readonly mixed $barber_id,
        public readonly mixed $service_id,
        public readonly mixed $appointment_datetime,
        public readonly mixed $customer_name = null,
        public readonly mixed $customer_phone = null,
        public readonly mixed $status = 'pending',
        public readonly mixed $reminder_enabled = true,
        public readonly mixed $reminder_minutes = 30,
        public readonly mixed $fcm_token = null,
    ) {}

    public static function fromArray(array $data): self { return new self(
            customer_id: $data['customer_id'] ?? null,
            barber_id: $data['barber_id'] ?? null,
            service_id: $data['service_id'] ?? null,
            appointment_datetime: $data['appointment_datetime'] ?? null,
            customer_name: $data['customer_name'] ?? null,
            customer_phone: $data['customer_phone'] ?? null,
            status: $data['status'] ?? 'pending',
            reminder_enabled: $data['reminder_enabled'] ?? true,
            reminder_minutes: $data['reminder_minutes'] ?? 30,
            fcm_token: $data['fcm_token'] ?? null,
        ); }

    public function toArray(): array { return [
            'customer_id' => $this->customer_id,
            'barber_id' => $this->barber_id,
            'service_id' => $this->service_id,
            'appointment_datetime' => $this->appointment_datetime,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'status' => $this->status,
            'reminder_enabled' => $this->reminder_enabled,
            'reminder_minutes' => $this->reminder_minutes,
            'fcm_token' => $this->fcm_token,
        ]; }
}
