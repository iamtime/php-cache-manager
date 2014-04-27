<?php

class ChipVN_Cache_Adapter_Session extends ChipVN_Cache_Adapter_Abstract
{
    /**
     * Cache options.
     *
     * @var array
     */
    protected $options = array(
    );

    /**
     * Create ChipVN_Cache_Adapter_Session instance.
     *
     * @return void
     */
    public function __construct(array $options = array())
    {
        // Sure that session is initialized for saving somethings.
        if (!session_id()) {
            if (headers_sent()) {
                throw new Exception('Session is not initialized. Please sure that session_start(); was called at the top of the script.');
            }
            session_start();
        }
        parent::__construct($options);
    }

    /**
     * Set a cache entry.
     *
     * @param  strign       $key
     * @param  mixed        $value
     * @param  null|integer $expires In seconds
     * @return boolean
     */
    public function set($key, $value, $expires = null)
    {
        $key     = $this->getKeyGrouped($key);
        $expires = $expires ? $expires : $this->options['expires'];

        $_SESSION[$key] = array(
            'value'    => $value,
            'expires'  => time() + $expires,
        );

        return true;
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
        $key = $this->getKeyGrouped($key);

        if (isset($_SESSION[$key])) {
            $data = $_SESSION[$key];
            if ($data['expires'] >= time()) {
                return $data['value'];
            }
        }
        unset($_SESSION[$key]);

        return $default;
    }

    /**
     * Delete a cache entry.
     *
     * @param  string  $key
     * @return boolean
     */
    public function delete($key)
    {
        $key = $this->getKeyGrouped($key);
        unset($_SESSION[$key]);

        return true;
    }

    /**
     * Delete a group cache.
     *
     * @param  null|string $name Null to delete entries in current group
     * @return boolean
     */
    public function deleteGroup($name = null)
    {
        $group  = ($name === null ? $this->options['group'] : $name);
        $find   = ($group ? $this->getGroupIndex($group) . ':::' : '');
        $len    = strlen($find);

        foreach (array_keys($_SESSION) as $key) {
            if (substr($key, 0, $len) == $find
                && ($len == 0 && !strpos($key, ':::') || $len > 0 && strpos($key, ':::'))
            ) {
                unset($_SESSION[$key]);
            }
        }

        return true;
    }

    /**
     * Delete all cache entries with a prefix.
     * If $prefix is "null", the method will delete all entries use options[prefix].
     * If $group is not specified, options[group] will be used to execution.
     *
     * @param  string      $prefix
     * @param  null|string $group
     * @return boolean
     */
    public function deletePrefix($prefix = null, $group = null)
    {
        $prefix = ($prefix === null ? $this->options['prefix'] : $prefix);
        $group  = ($group === null ? $this->options['group'] : $group);
        $find   = ($group ? $this->getGroupIndex($group) . ':::' : '') . $prefix;
        $len    = strlen($find);

        foreach (array_keys($_SESSION) as $key) {
            if (substr($key, 0, $len) == $find) {
                unset($_SESSION[$key]);
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
        $_SESSION = array();

        return true;
    }

    /**
     * Run garbage collect.
     *
     * @return void
     */
    public function garbageCollect()
    {
        $this->runGarbageCollect($_SESSION);
        if (empty($_SESSION)) {
            $_SESSION = array();
        }
    }

    /**
     * Run garbage collect recusive.
     *
     * @param  string $directory
     * @return void
     */
    protected function runGarbageCollect(&$sessions)
    {
        foreach ($sessions as $key => &$session) {
            if (is_array($session)) {
                if (isset($session['expires'])) {
                    if ($session['expires'] < time()) {
                        unset($session[$key]);
                    }
                } else {
                    $this->runGarbageCollect($session);
                }
            }
        }
    }

    /**
     * Get cache key.
     *
     * @param  string $key
     * @return string
     */
    protected function getKeyGrouped($key)
    {
        $key = $this->sanitize($key);

        if ($group = $this->options['group']) {
            $index = $this->getGroupIndex($group);

            $key = $index . ':::' . $key;
        }

        return $key;
    }
}
