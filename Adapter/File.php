<?php

class ChipVN_Cache_Adapter_File extends ChipVN_Cache_Adapter_Abstract
{
    /**
     * Cache extension.
     */
    const FILE_EXTENSION = '.cache';

    /**
     * Cache options.
     *
     * @var array
     */
    protected $options = array(
        'cache_dir' => '',
        'extension' => self::FILE_EXTENSION,
    );

    /**
     * Create new cache instance
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        parent::__construct($options);

        if (!$dir = realpath($this->options['cache_dir'])) {
            throw new Exception(sprintf('Cache directory "%s" is not exists.', $this->options['cache_dir']));
        }
        $this->options['cache_dir'] = $dir.DIRECTORY_SEPARATOR;
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
        $file = $this->getFile($key, true);
        $expires = $expires ? $expires : $this->options['expires'];

        return file_put_contents($file, serialize($value), LOCK_EX) && touch($file, time() + $expires)
            ? true
            : $this->delete($key) && false;
    }

    /**
     * Determine if the key is exist or not.
     *
     * @param  string  $key
     * @return boolean
     */
    public function has($key)
    {
        $file = $this->getFile($key);

        return file_exists($file) && filemtime($file) > time()
            ? true
            : $this->delete($key) && false;
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
        return $this->has($key)
            ? unserialize(file_get_contents($this->getFile($key)))
            : $default;
    }

    /**
     * Delete a cache entry.
     *
     * @param  string  $name
     * @return boolean
     */
    public function delete($key)
    {
        return file_exists($file = $this->getFile($key)) && unlink($file);
    }

    /**
     * Delete all cache entries.
     *
     * @return boolean
     */
    public function flush()
    {
        $this->emptyDirectory(rtrim($this->options['cache_dir'], '\\/'));
    }

    /**
     * Run garbage collect.
     *
     * @return void
     */
    public function garbageCollect()
    {
        $this->runGarbageCollect(rtrim($this->options['cache_dir'], '\\/'));
    }

    /**
     * Run garbage collect recusive.
     *
     * @param  string $directory
     * @return void
     */
    protected function runGarbageCollect($directory)
    {
        foreach (glob($directory.'/*') as $item) {
            if (is_dir($item)) {
                $this->runGarbageCollect($item);
                !glob($item.'/*') && rmdir($item);
            } elseif (substr($item, -strlen($this->options['extension'])) == $this->options['extension']) {
                filemtime($item) < time() && unlink($item);
            }
        }
    }

    /**
     * Delete a directory.
     *
     * @param  string  $directory  Without endwish DIRECTORY_SEPARATOR
     * @param  boolean $selfDelete
     * @return void
     */
    protected function emptyDirectory($directory, $selfDelete = false)
    {
        foreach (glob($directory.'/*') as $item) {
            if (is_dir($item)) {
                $this->emptyDirectory($item, true);
            } elseif (substr($item, -strlen($this->options['extension'])) == $this->options['extension']) {
                unlink($item);
            }
        }
        if ($selfDelete) {
            rmdir($directory);
        }
    }

    /**
     * Gets file for cache.
     *
     * @param  string  $key
     * @param  boolean $preparePath
     * @return string
     */
    protected function getFile($key, $preparePath = false)
    {
        return $this->getPath($key, $preparePath).$this->sanitize($key).$this->options['extension'];
    }

    /**
     * Gets cache path for key.
     *
     * @param  string  $key
     * @param  boolean $preparePath
     * @return string
     */
    protected function getPath($key, $preparePath)
    {
        $tmp  = md5($key);
        $path = $this->options['cache_dir'].substr($tmp, 0, 2).DIRECTORY_SEPARATOR.substr($tmp, 2, 2).DIRECTORY_SEPARATOR;

        if ($preparePath && !is_dir($path)) {
            mkdir($path, 0755, true);
        }

        return $path;
    }
}
