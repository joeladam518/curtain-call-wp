<?php

declare(strict_types=1);

namespace CurtainCall\Data;

use CurtainCall\Models\Production;

final class ProductionData extends Data
{
    public function __construct(
        public readonly int|string|null $id,
        public readonly ?string $name,
        public readonly ?string $dateStart,
        public readonly ?string $dateEnd,
        public readonly ?string $type,
        public readonly ?string $role,
        public readonly ?int $order = 0,
    ) {
    }

    public static function fromArray(array $data): self
    {
        /** @var int|null $id */
        $id = $data['ID'] ?? $data['Id'] ?? $data['id'] ?? null;
        /** @var string $name */
        $name = $data['name'] ?? $data['post_title'] ?? 'Untitled Production';
        /** @var string|null $dateStart */
        $dateStart = $data['dateStart'] ?? $data['date_start'] ?? null;
        /** @var string|null $dateEnd */
        $dateEnd = $data['dateEnd'] ?? $data['date_end'] ?? null;
        /** @var string|null $type */
        $type = $data['type'] ?? null;
        /** @var string|null $role */
        $role = $data['role'] ?? null;
        $order = (int) ($data['order'] ?? $data['custom_order'] ?? $data['customOrder'] ?? 0);

        return new self(
            id: $id,
            name: $name,
            dateStart: $dateStart,
            dateEnd: $dateEnd,
            type: is_string($type) ? $type : null,
            role: $role,
            order: $order,
        );
    }

    public static function fromProduction(Production $production): self
    {
        return new self(
            id: is_numeric($production->ID) ? (int) $production->ID : null,
            name: $production->name ?: $production->post_title ?: 'Untitled Production',
            dateStart: $production->date_start ?: null,
            dateEnd: $production->date_end ?: null,
            type: $production->getJoinType(),
            role: $production->getJoinRole(),
            order: $production->getJoinOrder(),
        );
    }

    public function toArray(): array
    {
        return [
            'ID' => $this->id,
            'name' => $this->name,
            'dateStart' => $this->dateStart,
            'dateEnd' => $this->dateEnd,
            'type' => $this->type,
            'role' => $this->role,
            'order' => $this->order,
        ];
    }
}
