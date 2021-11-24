<?php

namespace CurtainCallWP\PostTypes\Traits;

use CurtainCallWP\PostTypes\CurtainCallPostMeta;

trait HasMeta
{
    /** @var array */
    protected $meta = [];
    /** @var array */
    protected $ccwp_meta_keys = [];

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
        return in_array($key, $this->ccwp_meta_keys);
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

        if (array_key_exists($key, $this->meta)) {
            return true;
        }

        return metadata_exists('post', $this->ID, $this->getMetaKey($key));
    }

    /**
     * @return $this
     */
    protected function loadMeta()
    {
        $this->meta = array_map(function($item){
            return $item[0] ?? null;
        }, $this->fetchAllMeta());

        return $this;
    }

    /**
     * @param string $key
     * @return false|mixed
     */
    protected function fetchMeta(string $key)
    {
        return get_post_meta($this->ID, $this->getMetaKey($key), true);
    }

    /**
     * @return array
     */
    protected function fetchAllMeta(): array
    {
        return get_post_meta($this->ID);
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getMeta(string $key)
    {
        $meta_key = $this->getMetaKey($key);

        if (array_key_exists($meta_key, $this->meta)) {
            return $this->meta[$meta_key];
        }

        $meta_value = $this->fetchMeta($key);

        $this->setMeta($key, $meta_value);

        return $meta_value;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    protected function setMeta(string $key, $value)
    {
        $this->meta[$this->getMetaKey($key)] = $value;

        return $this;
    }

    /**
     * @param  string $key
     * @param  mixed  $value
     * @return bool
     */
    public function updateMeta(string $key, $value): bool
    {
        if ($result = update_post_meta($this->ID, $this->getMetaKey($key), $value)) {
            $this->setMeta($key, $value);
        }

        return (bool) $result;
    }

    /**
     * Restricted to only updating ccwp postmeta fields
     * true  = something in the meta array was created or updated
     * false = nothing was created or updated. It doesn't mean something went wrong.
     *
     * @return bool
     */
    public function saveMeta(): bool
    {
        $updated = false;
        foreach ($this->meta as $key => $value) {
            if (preg_match('~^'.static::META_PREFIX.'([_a-zA-Z0-9]+)$~', $key)) {
                if (update_post_meta($this->ID, $key, sanitize_text_field((string)$value)) === true) {
                    $updated = true;
                }
            }
        }

        return $updated;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function deleteMeta(string $key): bool
    {
        if ($result = delete_post_meta($this->ID, $this->getMetaKey($key))) {
            $this->__unset($key);
        }
        return $result;
    }
}
