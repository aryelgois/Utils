<?php
/**
 * This Software is part of aryelgois/utils and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\Utils\Exceptions;

/**
 * A read-only object tries to change its data
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/utils
 */
class ReadOnlyException extends \LogicException
{
    public function __construct(string $class, Throwable $previous = null)
    {
        parent::__construct("Class $class is read-only", 0, $previous);
    }
}
