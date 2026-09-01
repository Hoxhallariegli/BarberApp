<?php

namespace App\Domain\BerberApp\Customer\DTOs;

class CustomerDTO
{
    public function __construct(
        public readonly mixed $name,
        public readonly mixed $phone,
        public readonly mixed $email,
        public readonly mixed $photo,
    ) {}
    public static function fromArray(array $data): self { return new self(
            name: $data['name'] ?? null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            photo: $data['photo'] ?? null,
        ); }
    public function toArray(): array { return [
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'photo' => $this->photo,
        ]; }
}