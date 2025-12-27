<?php

declare(strict_types=1);

namespace CurtainCall\Data;

final class MemberData extends Data
{
    public function __construct(
        public readonly int $ID,
        public readonly ?string $firstName,
        public readonly ?string $lastName,
        public readonly ?string $fullName,
        public readonly ?string $role,
        public readonly ?string $type,
        public readonly int $order = 0,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            ID: $data['ID'],
            firstName: $data['first_name'] ?? $data['firstName'] ?? $data['name_first'] ?? $data['nameFirst'] ?? null,
            lastName: $data['last_name'] ?? $data['lastName'] ?? $data['name_last'] ?? $data['nameLast'] ?? null,
            fullName: $data['full_name'] ?? $data['fullName'] ?? null,
            role: $data['role'] ?? null,
            type: $data['type'] ?? null,
            order: $data['order'] ?? $data['custom_order'] ?? $data['customOrder'] ?? 0,
        );
    }

    public function toArray(): array
    {
        return [
            'ID' => $this->ID,
            'nameFirst' => $this->firstName,
            'nameLast' => $this->lastName,
            'fullName' => $this->fullName,
            'role' => $this->role,
            'type' => $this->type,
            'order' => $this->order,
        ];
    }
}
