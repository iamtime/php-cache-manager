<?php

interface ChipVN_Cache_Adapter_Interface
{
    /**
     * Set a cache entry.
     *
     * @param  strign       $key
     * @param  mixed        $value
     * @param  null|integer $expires In seconds
     * @return boolean
     */
    public function set($key, $value, $expires = null);

    /**
     * Get a cache entry.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Delete a cache entry.
     *
     * @param  string  $name
     * @return boolean
     */
    public function delete($key);

    /**
     * Delete a group cache.
     *
     * @param  string $name
     * @return boolean
     */
    public function deleteGroup($name);

    /**
     * Delete all cache entries.
     *
     * @return boolean
     */
    public function flush();

    /**
     * Run garbage collect.
     *
     * @return void
     */
    public function garbageCollect();

    /**
     * Get cache options.
     *
     * @return array
     */
    public function getOptions();

    /**
     * Set cache options
     *
     * @param array $options
     * @return void
     */
    public function setOptions(array $options);

    /**
     * Get group index by name.
     *
     * @param  string $name
     * @return string
     */
    public function getGroupIndex($name);
}
