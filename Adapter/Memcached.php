<?php

class ChipVN_Cache_Adapter_Memcached extends ChipVN_Cache_Storage implements ChipVN_Cache_Adapter_Interface
{
    /**
     * Cache options.
     *
     * @var array
     */
    protected $options = array(
        'host' => '127.0.0.1',
        'port' => 11211
    );

    /**
     * Memcached instance.
     *
     * @var Memcached
     */
    protected $memcached;

    /**
     * Create new memcache instance
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);

        $this->memcached = new Memcached;
        $this->memcached->addServer($this->options['host'], $this->options['port']);
    }

    /**
     * Get Memcached instance.
     *
     * @return Memcached
     */
    public function getMemcached()
    {
        return $this->memcached;
    }

    /**
     * Set a cache entry.
     *
     * @param  strign       $key
     * @param  mixed        $value
     * @param  null|integer $expires In seconds
     * @return void
     */
    public function set($key, $value, $expires = null)
    {
        $key     = $this->sanitize($key);
        $expires = $expires ? $expires : $this->options['expires'];

        if ($group = $this->options['group']) {
            $index = $this->getGroupIndex($group);

            $key = $index . $key;
        }

        $this->memcached->set($key, $value, $expires);
    }

    /**
     * Get a cache entry.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $key = $this->sanitize($key);

        if ($group = $this->options['group']) {
            $index = $this->getGroupIndex($group);

            $key = $index . $key;
        }

        $data = $this->memcached->get($key, $value, $expires);

        if ($data == false && !in_array($key, $this->memcached->getAllKeys())) {
            return $default;
        }

        return $data;
    }

    /**
     * Delete a cache entry.
     *
     * @param  string  $name
     * @return boolean
     */
    public function delete($key)
    {
        $this->memcached->delete($key);
    }

    /**
     * Delete a group cache.
     *
     * @param  string  $name
     * @return boolean
     */
    public function deleteGroup($name)
    {
        $group = $this->getGroupIndex($name);

        foreach ($this->memcached->getAllKeys() as $key) {
            if (strpos($key, $group) === 0) {
                $this->delete($key);
            }
        }

        return true;
    }

    /**
     * Delete all cache entries.
     *
     * @return boolean
     */
    public function flush()
    {
        $this->memcached->flush();
    }

    /**
     * Run garbage collect.
     *
     * @return void
     */
    public function garbageCollect()
    {
    }
}
