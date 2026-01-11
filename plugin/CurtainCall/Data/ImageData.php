<?php

declare(strict_types=1);

namespace CurtainCall\Data;

final class ImageData extends Data
{
    public function __construct(
        public readonly string $src,
        public readonly ?int $width,
        public readonly ?int $height,
    ) {
    }

    public static function fromArray(array $data): Data
    {
        return new self(
            src: $data['src'],
            width: (int) $data['width'] ?? null,
            height: (int) $data['height'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'src' => $this->src,
            'width' => $this->width,
            'height' => $this->height,
        ];
    }
}
