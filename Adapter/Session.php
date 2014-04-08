<?php

class ChipVN_Cache_Adapter_Session extends ChipVN_Cache_Storage implements ChipVN_Cache_Adapter_Interface
{
    /**
     * Cache options.
     *
     * @var array
     */
    protected $options = array(
        'cookie_expires' => 1800, // seconds
    );

    /**
     * Create ChipVN_Cache_Adapter_Session instance.
     * 
     * @return void
     */
    public function __construct()
    {
        // Sure that session is initialized for saving somethings.
        if (!session_id()) {
            if (headers_sent()) {
                throw new Exception('Session is not initialized. Please sure that session_start(); was called at the top of the script.');
            }
            session_name(md5(__CLASS__));
            session_start();
            session_set_cookie_params($this->options['cookie_expires'], '/'); // maximum lifetime
        }
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
        $session =& $this->getSession();

        $session[$key] = array(
            'value'    => $value,
            'expires'  => time() + $expires,
            'lifetime' => $expires,
        );
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
        $key     = $this->sanitize($key);
        $session =& $this->getSession();

        if (isset($session[$key])) {
            $data = $session[$key];
            if ($data['expires'] >= time()) {
                return $data['value'];
            }
        }
        unset($session[$key]);

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
        $key     = $this->sanitize($key);
        $session =& $this->getSession();

        unset($session[$key]);

        return true;
    }

    /**
     * Delete a group cache.
     *
     * @param  string  $key
     * @return boolean
     */
    public function deleteGroup($name)
    {
        unset($_SESSION[$this->getGroupIndex($name)]);

        return true;
    }

    /**
     * Delete all cache entries.
     *
     * @return boolean
     */
    public function flush()
    {
        unset($_SESSION);

        return true;
    }

    /**
     * Get session for cache
     *
     * @return array
     */
    protected function &getSession()
    {
        $session =& $_SESSION;

        if ($group = $this->options['group']) {
            $index = $this->getGroupIndex($group);
            if (!isset($_SESSION[$index])) {
                $_SESSION[$index] = array();
            }
            $session =& $_SESSION[$index];
        }

        return $session;
    }

    /**
     * Run garbage collect.
     *
     * @return void
     */
    public function garbageCollect()
    {
        $this->runGarbageCollect($_SESSION);
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
}
