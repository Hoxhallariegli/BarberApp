<?php

namespace App\Domain\BerberApp\DeviceToken\DTOs;

class DeviceTokenDTO
{
    public function __construct(
        public readonly mixed $user_id,
        public readonly mixed $fcm_token,
        public readonly mixed $device_type,
    ) {}
    public static function fromArray(array $data): self { return new self(
            user_id: $data['user_id'] ?? null,
            fcm_token: $data['fcm_token'] ?? null,
            device_type: $data['device_type'] ?? null,
        ); }
    public function toArray(): array { return [
            'user_id' => $this->user_id,
            'fcm_token' => $this->fcm_token,
            'device_type' => $this->device_type,
        ]; }
}