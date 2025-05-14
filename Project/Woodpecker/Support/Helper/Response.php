<?php
namespace Woodpecker\Support;

use Woodpecker\Facade;
/**
 * Class ResponseClass
 *
 * This class handles HTTP responses, allowing for setting status codes,
 * headers, and body content. It also supports JSON responses and responseion.
 */
class Response extends Facade {
    protected static int $statusCode = 200; //status code
    protected static array $headers = []; // headers
    protected static string $body = ''; //response body

    /**
     * Set the HTTP status code for the response.
     *
     * @param int $code The status code to set
     * @return self
     * @throws WPException if the status code is not valid.
     */
    public static function setStatusCode(int $code): self 
    {
        if ($code < 100 || $code > 599) {
            throw new WPException(" response => response =>Invalid HTTP status code: $code");
        }
        self::$statusCode = $code;
        return self::class; 
    }

    /**
     * Set a specific header for the response.
     *
     * @param string $name The name of the header
     * @param string $value The value of the header
     * @return self
     */
    public static function setHeader(string $name, string $value): self 
    {
        if (!self::isHeaderValid($name, $value)) {
            throw new WPException(" response => response =>Invalid header: $name: $value");
        }
        self::$headers[$name] = $value;
        return self::class;
    }
    
    /**
     * Set multiple headers at once.
     *
     * @param array $headers An associative array of headers
     * @return self
     */
    public static function headers(array $headers): self 
    {
        foreach ($headers as $header => $value) {
            self::setHeader($header, $value);
        }
        
        return self::class;
    }

    /**
     * Set the body content for the response.
     *
     * @param string $body The body content
     * @return self
     */
    public static function setBody(string $body): self 
    {
        self::$body = $body; 
        return self::class; 
    }

    /**
     * Set the response body as JSON encoded data.
     *
     * @param array $data The data to encode as JSON
     * @param int $status Optional HTTP status code for the JSON response
     * @return self
     * @throws \JsonException on JSON encoding errors
     */
    public static function json(array $data, int $status = 200): string 
    {
        self::setStatusCode($status); 
        self::setHeader('Content-Type', 'application/json');
        
        self::$body = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
        return self::$body;
    }

    /**
     * Send the HTTP response to the client.
     */
    public static function send(): void 
    {
        http_response_code(self::$statusCode);
        
        foreach (self::$headers as $name => $value) {
            header("$name: $value"); 
        }
        
        echo self::$body; 
    }

    /**
     * Validate header name and value to prevent header injection.
     *
     * @param string $name
     * @param string $value
     * @return bool
     */
    protected static function isHeaderValid(string $name, string $value): bool 
    {
        return preg_match('/^[a-zA-Z0-9-]+$/', $name) && !preg_match('/[\r\n]/', $value);
    }
}