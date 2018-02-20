<?php
/**
 * This Software is provided "as is" under the terms of the MIT license.
 *
 * @link https://opensource.org/licenses/MIT
 */

/**
 * Provides named constants for HTTP protocol status codes and their messages
 *
 * - Originally written for the Recess Framework http://www.recessframework.com
 * - Forked from https://gist.github.com/ewwink/f14474fd955801153c47
 * - Adapted for general use
 *
 * @author Kris Jordan
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://gist.github.com/aryelgois/e577aa73ebbe1463532ab8a584e3d16c
 * @version 0.1.0
 */
class HttpResponse
{
    // Informational 1xx
    const HTTP_CONTINUE = 100;
    const HTTP_SWITCHING_PROTOCOLS = 101;

    // Successful 2xx
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_ACCEPTED = 202;
    const HTTP_NONAUTHORITATIVE_INFORMATION = 203;
    const HTTP_NO_CONTENT = 204;
    const HTTP_RESET_CONTENT = 205;
    const HTTP_PARTIAL_CONTENT = 206;

    // Redirection 3xx
    const HTTP_MULTIPLE_CHOICES = 300;
    const HTTP_MOVED_PERMANENTLY = 301;
    const HTTP_FOUND = 302;
    const HTTP_SEE_OTHER = 303;
    const HTTP_NOT_MODIFIED = 304;
    const HTTP_USE_PROXY = 305;
    const HTTP_UNUSED = 306;
    const HTTP_TEMPORARY_REDIRECT = 307;

    const ERROR_CODES_START = 400;

    // Client Error 4xx
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_PAYMENT_REQUIRED = 402;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_NOT_ACCEPTABLE = 406;
    const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    const HTTP_REQUEST_TIMEOUT = 408;
    const HTTP_CONFLICT = 409;
    const HTTP_GONE = 410;
    const HTTP_LENGTH_REQUIRED = 411;
    const HTTP_PRECONDITION_FAILED = 412;
    const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
    const HTTP_REQUEST_URI_TOO_LONG = 414;
    const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const HTTP_EXPECTATION_FAILED = 417;

    // Server Error 5xx
    const HTTP_INTERNAL_SERVER_ERROR = 500;
    const HTTP_NOT_IMPLEMENTED = 501;
    const HTTP_BAD_GATEWAY = 502;
    const HTTP_SERVICE_UNAVAILABLE = 503;
    const HTTP_GATEWAY_TIMEOUT = 504;
    const HTTP_VERSION_NOT_SUPPORTED = 505;

    /**
     * Maps status codes to standard messages
     *
     * @var string[]
     */
    protected static $messages = [
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',

        // Successful 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',

        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',

        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
    ];

    /**
     * Returns HTTP header to be sent to client
     *
     * USAGE:
     *     header(HttpResponse::getHeader(503));
     *
     * @param int $code HTTP status code
     *
     * @return string
     */
    public static function getHeader(int $code)
    {
        return 'HTTP/1.1 ' . self::getMessage($code);
    }

    /**
     * Returns message for a HTTP status code
     *
     * You can format the output, similar as \date(). Valid characters are:
     * - 'c': HTTP status code
     * - 'm': Message
     *
     * Other characters are kept as is.
     *
     * EXAMPLES:
     * - 'c m'    DEFAULT FORMAT
     * - '[c] m'
     * - 'c: m'
     * - 'm (c)'
     *
     * @param int    $code   HTTP status code
     * @param string $format Message format
     *
     * @return string
     */
    public static function getMessage(int $code, string $format = null)
    {
        $fields = [
            'c' => $code,
            'm' => self::$messages[$code],
        ];

        return str_replace(array_keys($fields), $fields, $format ?? 'c m');
    }

    /**
     * Tells if a status code is a error code
     *
     * @param int $code HTTP status code
     *
     * @return boolean
     */
    public static function isError(int $code)
    {
        return $code >= self::ERROR_CODES_START;
    }

    /**
     * Checks if a HTTP response with given status code can have body
     *
     * @param int $code HTTP status code
     *
     * @return boolean
     */
    public static function canHaveBody(int $code)
    {
        // True if not in 100s
        // and not 204 NO CONTENT
        // and not 304 NOT MODIFIED
        return ($code < self::HTTP_CONTINUE || $code >= self::HTTP_OK)
            && $code != self::HTTP_NO_CONTENT
            && $code != self::HTTP_NOT_MODIFIED;
    }
}
