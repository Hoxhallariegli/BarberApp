<?php

namespace App\Domain\BerberApp\Service\DTOs;

class ServiceDTO
{
    public function __construct(
        public readonly mixed $name,
        public readonly mixed $price,
        public readonly mixed $duration_minutes,
    ) {}
    public static function fromArray(array $data): self { return new self(
            name: $data['name'] ?? null,
            price: $data['price'] ?? null,
            duration_minutes: $data['duration_minutes'] ?? null,
        ); }
    public function toArray(): array { return [
            'name' => $this->name,
            'price' => $this->price,
            'duration_minutes' => $this->duration_minutes,
        ]; }
}
