<?php

declare(strict_types=1);

namespace CurtainCall\Data;

final class ProductionData extends Data
{
    public function __construct(
        public readonly int|null $ID,
        public readonly string $name,
        public readonly string|null $dateStart,
        public readonly string|null $dateEnd,
        public readonly string|null $type,
        public readonly string|null $role,
        public readonly int $order = 0
    ) {
        //
    }

    public static function fromArray(array $data): self
    {
        return new self(
            ID: $data['ID'] ?? null,
            name: $data['name'],
            dateStart: $data['dateStart'] ?? $data['date_start'] ?? null,
            dateEnd: $data['dateEnd'] ?? $data['date_end'] ?? null,
            type: $data['type'] ?? null,
            role: $data['role'] ?? null,
            order: $data['order'] ?? 0
        );
    }

    public function toArray(): array
    {
        return [
            'ID' => $this->ID,
            'name' => $this->name,
            'dateStart' => $this->dateStart,
            'dateEnd' => $this->dateEnd,
            'type' => $this->type,
            'role' => $this->role,
            'order' => $this->order,
        ];
    }
}
