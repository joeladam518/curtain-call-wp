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

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        /** @var string $src */
        $src = $data['src'] ?? '';
        $width = isset($data['width']) && is_numeric($data['width']) ? (int) $data['width'] : null;
        $height = isset($data['height']) && is_numeric($data['height']) ? (int) $data['height'] : null;

        return new self(
            src: $src,
            width: $width,
            height: $height,
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
