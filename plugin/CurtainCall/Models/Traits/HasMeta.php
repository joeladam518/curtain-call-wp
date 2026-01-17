<?php

declare(strict_types=1);

namespace CurtainCall\Models\Traits;

use Illuminate\Support\Arr;

trait HasMeta
{
    /** @var array<string, mixed> */
    protected array $meta = [];
    /** @var list<string> */
    protected array $ccwp_meta = [];

    /**
     * @return array<string, list<string>>
     */
    protected function fetchAllMeta(): array
    {
        return get_post_meta($this->ID);
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function fetchMeta(string $key): mixed
    {
        return get_post_meta($this->ID, $this->getMetaKey($key), true) ?: null;
    }

    /**
     * @return list<string>
     */
    protected function getAllMeta(): array
    {
        $meta = $this->fetchAllMeta();

        return Arr::map($meta, static fn(array $item) => $item[0] ?? null);
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getMeta(string $key): mixed
    {
        $metaKey = $this->getMetaKey($key);

        if (array_key_exists($metaKey, $this->meta)) {
            return $this->meta[$metaKey];
        }

        $metaValue = $this->fetchMeta($key);

        $this->setMeta($key, $metaValue);

        return $metaValue;
    }

    /**
     * @param string $key
     * @return string
     */
    protected function getMetaKey(string $key): string
    {
        if ($this->isCCWPMeta($key)) {
            return static::META_PREFIX . $key;
        }

        return $key;
    }

    /**
     * @param string $key
     * @return boolean
     */
    protected function isCCWPMeta(string $key): bool
    {
        return in_array($key, $this->ccwp_meta, true);
    }

    /**
     * @param string $key
     * @return boolean
     */
    protected function isMetaAttribute(string $key): bool
    {
        if ($this->isCCWPMeta($key)) {
            return true;
        }

        return metadata_exists('post', $this->ID, $this->getMetaKey($key));
    }

    /**
     * @return $this
     */
    protected function loadMeta(): static
    {
        $this->meta = $this->getAllMeta();

        return $this;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    protected function setMeta(string $key, mixed $value): static
    {
        $this->meta[$this->getMetaKey($key)] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function deleteMeta(string $key): bool
    {
        $result = delete_post_meta($this->ID, $this->getMetaKey($key));

        if ($result) {
            $this->__unset($key);
        }

        return $result;
    }

    /**
     * Restricted to only updating ccwp postmeta fields
     * true = something in the meta array was created or updated
     * false = nothing was created or updated. It doesn't mean something went wrong.
     *
     * @return bool
     */
    public function saveMeta(): bool
    {
        $updated = false;
        foreach ($this->meta as $key => $value) {
            if (preg_match('~^' . static::META_PREFIX . '([_a-zA-Z0-9]+)$~', $key)) {
                if (update_post_meta($this->ID, $key, sanitize_text_field((string) $value)) === true) {
                    $updated = true;
                }
            }
        }

        return $updated;
    }

    /**
     * @param  string $key
     * @param  mixed  $value
     * @return bool
     */
    public function updateMeta(string $key, mixed $value): bool
    {
        $result = update_post_meta($this->ID, $this->getMetaKey($key), $value);

        if ($result) {
            $this->setMeta($key, $value);
        }

        return (bool) $result;
    }
}
