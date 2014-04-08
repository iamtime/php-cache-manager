<?php

abstract class ChipVN_Cache_Storage
{
    /**
     * Default value expires
     */
    const DEFAULT_EXPIRES = 900; // seconds

    /**
     * Cache options.
     *
     * @var array
     */
    protected $defaultOptions = array(
        'group'   => '',
        'prefix'  => '',
        'expires' => self::DEFAULT_EXPIRES,
    );

    /**
     * Create a storage instance.
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->setOptions($options);
    }

    /**
     * Cache options.
     *
     * @var array
     */
    protected $options = array();

    /**
     * Sanitize cache key.
     *
     * @param  string $id
     * @return string
     */
    protected function sanitize($id)
    {
        return $this->options['prefix'] . str_replace(array('/', '\\', ' '), '_', $id);
    }

    /**
     * Set cache options.
     *
     * @param  array $options
     * @return array
     */
    public function setOptions(array $options)
    {
        return $this->options = array_merge($this->defaultOptions, $this->options, $options);
    }

    /**
     * Get cache options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Get group index by name.
     *
     * @param  string $name
     * @return string
     */
    public function getGroupIndex($name)
    {
        return '__GROUP_' . $name;
    }
}
