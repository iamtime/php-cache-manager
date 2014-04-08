<?php

class ChipVN_Cache_Adapter_File extends ChipVN_Cache_Storage implements ChipVN_Cache_Adapter_Interface
{

    const FILE_EXTENSION = '.cache';

    /**
     * Cache options.
     *
     * @var array
     */
    protected $options = array(
        'cache_dir' => ''
    );

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        $cache_dir = realpath($options['cache_dir']) . DIRECTORY_SEPARATOR;

        if (!$cache_dir || empty($options['cache_dir'])) {
            throw new Exception(sprintf('"cache_dir" "%s" must be a directory.', $cache_dir));
        }
        if (!is_writable($cache_dir)) {
            throw new Exception(sprintf('Cache directory "%s" must be writeable.', $cache_dir));
        }

        return parent::setOptions($options);
    }

    /**
     * {@inheritdoc}
     */
    protected function sanitize($id)
    {
        return parent::sanitize($id) . self::FILE_EXTENSION;
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
        $key       = $this->sanitize($key);
        $expires   = $expires ? $expires : $this->options['expires'];
        $directory = $this->getDirectory(true);

        $data = serialize(array(
            'expires'  => $expires + time(),
            'lifetime' => $expires,
            'value'    => $value
        ));
        file_put_contents($directory . $key, $data);
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
        $key       = $this->sanitize($key);
        $directory = $this->getDirectory(true);

        if (file_exists($file = $directory . $key)) {
            $data = unserialize(file_get_contents($file));
            if ($data['expires'] >= time()) {
                return $data['value'];
            }
            unlink($file);
        }

        return $default;
    }

    /**
     * Delete a cache entry.
     *
     * @param  string  $name
     * @return boolean
     */
    public function delete($key)
    {
        $key       = $this->sanitize($key);
        $directory = $this->getDirectory(true);

        if (file_exists($file = $directory . $key)) {
            unlink($file);

            return true;
        }

        return false;
    }

    /**
     * Delete a group cache.
     *
     * @param  string  $name
     * @return boolean
     */
    public function deleteGroup($name)
    {
        $directory = $this->options['cache_dir'];
        $index     = $this->getGroupIndex($group);

        if (is_dir($directory . $index)) {
            $this->deleteDirectory($directory . $index);
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
        $this->deleteDirectory($this->options['cache_dir'], false);

        return true;
    }

    /**
     * Delete a directory.
     *
     * @param  string $directory
     * @return void
     */
    protected function deleteDirectory($directory, $removeSelf = false)
    {
        foreach (glob($dir . '/*') as $file) {
            is_dir($file) ? $this->deleteDirectory($file, true) : unlink($file);
        }
        if ($removeSelf) {
            rmdir($dir);
        }
    }

    /**
     * Get directory for cache file.
     *
     * @return string
     */
    protected function getDirectory($create = false)
    {
        $directory = $this->options['cache_dir'];

        if ($group = $this->options['group']) {
            $index = $this->getGroupIndex($group);
            $directory .= $index . DIRECTORY_SEPARATOR;
        }
        if ($create && !is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        return $directory;
    }

    /**
     * Run garbage collect.
     *
     * @return void
     */
    public function garbageCollect()
    {
        $this->runGarbageCollect(rtrim($this->options['cache_dir'], DIRECTORY_SEPARATOR));
    }

    /**
     * Run garbage collect recusive.
     *
     * @param  string $directory
     * @return void
     */
    protected function runGarbageCollect($directory)
    {
        $files = glob($directory . DIRECTORY_SEPARATOR . '*');
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->runGarbageCollect($file);
                if (!count(glob($file . DIRECTORY_SEPARATOR . '*' . self::FILE_EXTENSION))) {
                    rmdir($file);
                }
            } elseif (is_file($file)) {
                $data = unserialize(file_get_contents($file));
                if ($data['expires'] < time()) {
                    unlink($file);
                }
            }
        }
    }
}
