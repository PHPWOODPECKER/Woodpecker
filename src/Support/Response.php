<?php
namespace Woodpecker\Helper;
/**
 * Class ResponseClass
 *
 * This class handles HTTP responses, allowing for setting status codes,
 * headers, and body content. It also supports JSON responses and redirection.
 */
class Response {
    private int $statusCode = 200; // Default status code is 200 OK
    private array $headers = []; // Array to hold response headers
    private string $body = ''; // Response body content

    /**
     * Set the HTTP status code for the response.
     *
     * @param int $code The status code to set
     * @return self
     * @throws WPException if the status code is not valid.
     */
    public function setStatusCode(int $code): self 
    {
        if ($code < 100 || $code > 599) {
            throw new WPException(" redirect => redirect =>Invalid HTTP status code: $code");
        }
        $this->statusCode = $code; // Assign the status code
        return $this; // Return the current instance
    }

    /**
     * Set a specific header for the response.
     *
     * @param string $name The name of the header
     * @param string $value The value of the header
     * @return self
     */
    public function setHeader(string $name, string $value): self 
    {
        // Validate header name and value to prevent header injection
        if (!$this->isHeaderValid($name, $value)) {
            throw new WPException(" redirect => redirect =>Invalid header: $name: $value");
        }
        $this->headers[$name] = $value; // Set the header in the array
        return $this; // Return the current instance
    }
    
    /**
     * Set multiple headers at once.
     *
     * @param array $headers An associative array of headers
     * @return self
     */
    public function headers(array $headers): self 
    {
        foreach ($headers as $header => $value) {
            $this->setHeader($header, $value); // Use setHeader to validate each header
        }
        
        return $this;
    }

    /**
     * Set the body content for the response.
     *
     * @param string $body The body content
     * @return self
     */
    public function setBody(string $body): self 
    {
        $this->body = $body; // Assign the body content
        return $this; // Return the current instance
    }

    /**
     * Set the response body as JSON encoded data.
     *
     * @param array $data The data to encode as JSON
     * @param int $status Optional HTTP status code for the JSON response
     * @return self
     * @throws \JsonException on JSON encoding errors
     */
    public function json(array $data, int $status = 200): self 
    {
        $this->setStatusCode($status); // Set status code if provided
        $this->setHeader('Content-Type', 'application/json'); // Set content type to JSON
        
        // Attempt to encode data to JSON; throw exception on failure
        $this->body = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
        return $this; // Return the current instance
    }

    /**
     * Send the HTTP response to the client.
     */
    public function send(): void 
    {
        http_response_code($this->statusCode); // Set the HTTP response code
        
        // Send  headers
        foreach ($this->headers as $name => $value) {
            header("$name: $value"); // Output each header
        }
        
        // Output the response body
        echo $this->body; 
    }
    
    /**
     * Download a file from a given URL and save it to a specified path.
     * 
     * @param string $url The URL to download the file from
     * @param string $path The path to save the downloaded file
     * @throws WPException if the file download fails
     */
    public function download(string $url, string $path): void 
    {
        $fileContent = @file_get_contents($url);
        
        // Check if the file contents were retrieved successfully
        if ($fileContent === false) {
            throw new WPException(" redirect =>Failed to download file from $url");
        }
        
        // Attempt to write the contents to the specified path
        if (file_put_contents($path, $fileContent) === false) {
            throw new WPException(" redirect =>Failed to save file to $path");
        }
    }

    /**
     * Validate header name and value to prevent header injection.
     *
     * @param string $name
     * @param string $value
     * @return bool
     */
    private function isHeaderValid(string $name, string $value): bool 
    {
        // Check for invalid characters in header name or value
        return preg_match('/^[a-zA-Z0-9-]+$/', $name) && !preg_match('/[\r\n]/', $value);
    }
}

function response(): Response
{
  return new Response();
}
