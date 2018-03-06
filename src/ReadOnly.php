<?php
/**
 * This Software is part of aryelgois/utils and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\Utils;

/**
 * A read-only data structure
 *
 * All data is passed to __construct() in an array and accessed as a normal
 * object
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/utils
 */
class ReadOnly
{
    /**
     * List of keys that may not be set during __construct()
     *
     * Intended to be used by children classes
     *
     * @var string[]
     */
    const OPTIONAL = [];

    /**
     * Stores object data
     *
     * @var mixed[]
     */
    protected $data;

    /**
     * Creates a new ReadOnly object
     *
     * @param array $data All data to be stored in the object
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Returns stored data
     *
     * @param string $key A valid key
     *
     * @return mixed
     *
     * @throws \DomainException If $key is invalid
     */
    public function __get(string $key)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        } elseif (!in_array($key, static::OPTIONAL)) {
            $message = static::class . " object does not have '$key'";
            throw new \DomainException($message);
        }
    }

    /**
     * Returns stored data
     *
     * @param string $key   Should be a valid key
     * @param mixed  $value Anything
     *
     * @throws \DomainException If $property is invalid
     */
    public function __set(string $key, $value)
    {
        throw new Exceptions\ReadOnlyException(static::class);
    }

    /**
     * Returns all stored data
     *
     * @return mixed[]
     */
    public function dump()
    {
        return $this->data;
    }
}
