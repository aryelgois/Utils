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
 * Constants are intended to be changed by children classes
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/utils
 */
class ReadOnly
{
    /**
     * List of acceptable keys and their types
     *
     * If empty, __construct() accepts any key and value
     *
     * The value can be a string or an array of PHP types
     *
     * @var mixed[]
     */
    const KEYS = [];

    /**
     * List of keys that may not be set during __construct()
     *
     * They can also be set as null
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
     *
     * @throws \DomainException          If key is invalid
     * @throws \InvalidArgumentException If value has invalid type
     */
    public function __construct(array $data)
    {
        $result = [];

        if (empty(static::KEYS)) {
            $result = $data;
        } else {
            $invalid = array_diff_key($data, static::KEYS);
            if (!empty($invalid)) {
                $message = 'Invalid key' . (count($invalid) > 1 ? 's' : '')
                    . ": '" . implode("', '", $invalid) . "'";
                throw new \DomainException($message);
            }
            foreach ($data as $key => $value) {
                $type = gettype($value);
                $expected = (array) static::KEYS[$key];
                if (!($type === 'NULL' && in_array($key, static::OPTIONAL))
                    && !in_array($type, $expected)
                ) {
                    throw new \InvalidArgumentException(sprintf(
                        "Key '%s' in Argument 1 passed to %s() must be of the type %s, %s given",
                        $key,
                        __METHOD__,
                        Format::naturalLanguageJoin($expected, 'or'),
                        $type
                    ));
                }
                $result[$key] = $value;
            }
        }

        $this->data = $result;
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
     * It includes missing optional keys as null
     *
     * @return mixed[]
     */
    public function dump()
    {
        return array_merge(
            array_fill_keys(static::OPTIONAL, null),
            $this->data
        );
    }
}
