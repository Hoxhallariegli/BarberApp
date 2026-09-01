<?php

namespace App\Domain\BerberApp\Barber\DTOs;

class BarberDTO
{
    public function __construct(
        public readonly mixed $name,
        public readonly mixed $specialization,
        public readonly mixed $phone,
        public readonly mixed $commission_rate,
        public readonly mixed $photo,
    ) {}
    public static function fromArray(array $data): self { return new self(
            name: $data['name'] ?? null,
            specialization: $data['specialization'] ?? null,
            phone: $data['phone'] ?? null,
            commission_rate: $data['commission_rate'] ?? null,
            photo: $data['photo'] ?? null,
        ); }
    public function toArray(): array { return [
            'name' => $this->name,
            'specialization' => $this->specialization,
            'phone' => $this->phone,
            'commission_rate' => $this->commission_rate,
            'photo' => $this->photo,
        ]; }
}