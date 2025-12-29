<?php

declare(strict_types=1);

namespace CurtainCall\Data;

use CurtainCall\Models\CastAndCrew;

final class CastCrewData extends Data
{
    public function __construct(
        public readonly int|string|null $ID,
        public readonly ?string $firstName,
        public readonly ?string $lastName,
        public readonly ?string $fullName,
        public readonly ?string $role,
        public readonly ?string $type,
        public readonly ?int $order = 0,
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        /** @var int|null $id */
        $id = $data['ID'] ?? $data['Id'] ?? $data['id'] ?? null;
        /** @var string|null $firstName */
        $firstName = $data['firstName'] ?? $data['name_first'] ?? $data['nameFirst'] ?? $data['first_name'] ?? null;
        /** @var string|null $lastName */
        $lastName = $data['lastName'] ?? $data['name_last'] ?? $data['last_name'] ?? $data['nameLast'] ?? null;
        /** @var string|null $fullName */
        $fullName = $data['fullName'] ?? $data['full_name'] ?? trim($firstName . ' ' . $lastName);
        /** @var string|null $role */
        $role = $data['role'] ?? null;
        /** @var string|null $type */
        $type = $data['type'] ?? null;
        $order = (int) ($data['order'] ?? $data['custom_order'] ?? $data['customOrder'] ?? 0);

        return new self(
            ID: $id,
            firstName: $firstName,
            lastName: $lastName,
            fullName: $fullName,
            role: $role,
            type: $type,
            order: $order,
        );
    }

    public static function fromCastCrew(CastAndCrew $castCrew): self
    {
        $id = is_numeric($castCrew->ID) ? (int) $castCrew->ID : null;
        $order = (int) ($castCrew->ccwp_join->custom_order ?? 0);

        return new self(
            ID: $id,
            firstName: $castCrew->name_first ?: null,
            lastName: $castCrew->name_last ?: null,
            fullName: $castCrew->getFullName() ?: null,
            role: $castCrew->ccwp_join->role ?: null,
            type: $castCrew->ccwp_join->type ?: null,
            order: $order,
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
