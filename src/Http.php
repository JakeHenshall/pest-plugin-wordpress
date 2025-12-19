<?php

declare(strict_types=1);

namespace PestPluginWordPress;

class HttpResponse
{
    public function __construct(
        private mixed $content,
        private int $status = 200,
        private array $headers = []
    ) {}
    
    public function assertStatus(int $status): self
    {
        test()->assertEquals($status, $this->status, "Expected status {$status}, got {$this->status}");
        return $this;
    }
    
    public function assertOk(): self
    {
        return $this->assertStatus(200);
    }
    
    public function assertNotFound(): self
    {
        return $this->assertStatus(404);
    }
    
    public function assertSee(string $value): self
    {
        test()->assertStringContainsString($value, (string) $this->content);
        return $this;
    }
    
    public function assertDontSee(string $value): self
    {
        test()->assertStringNotContainsString($value, (string) $this->content);
        return $this;
    }
    
    public function assertJson(): self
    {
        json_decode((string) $this->content);
        test()->assertEquals(JSON_ERROR_NONE, json_last_error(), 'Response is not valid JSON');
        return $this;
    }
    
    public function assertJsonPath(string $path, mixed $expected): self
    {
        $data = json_decode((string) $this->content, true);
        $keys = explode('.', $path);
        $value = $data;
        
        foreach ($keys as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                test()->fail("JSON path '{$path}' does not exist");
            }
            $value = $value[$key];
        }
        
        test()->assertEquals($expected, $value);
        return $this;
    }
    
    public function assertHeader(string $header, ?string $value = null): self
    {
        test()->assertArrayHasKey($header, $this->headers);
        
        if ($value !== null) {
            test()->assertEquals($value, $this->headers[$header]);
        }
        
        return $this;
    }
    
    public function getContent(): string
    {
        return (string) $this->content;
    }
    
    public function getStatus(): int
    {
        return $this->status;
    }
    
    public function json(): array
    {
        $decoded = json_decode((string) $this->content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Response is not valid JSON: ' . json_last_error_msg());
        }
        
        if (!is_array($decoded)) {
            throw new \RuntimeException('Response JSON is not an array');
        }
        
        return $decoded;
    }
}

function get(string $uri, array $headers = []): HttpResponse
{
    return makeRequest('GET', $uri, [], $headers);
}

function post(string $uri, array $data = [], array $headers = []): HttpResponse
{
    return makeRequest('POST', $uri, $data, $headers);
}

function put(string $uri, array $data = [], array $headers = []): HttpResponse
{
    return makeRequest('PUT', $uri, $data, $headers);
}

function patch(string $uri, array $data = [], array $headers = []): HttpResponse
{
    return makeRequest('PATCH', $uri, $data, $headers);
}

function delete(string $uri, array $data = [], array $headers = []): HttpResponse
{
    return makeRequest('DELETE', $uri, $data, $headers);
}

function from(string $referer): object
{
    return new class($referer) {
        public function __construct(private string $referer) {}
        
        public function get(string $uri, array $headers = []): HttpResponse
        {
            return \PestPluginWordPress\get($uri, array_merge(['HTTP_REFERER' => $this->referer], $headers));
        }
        
        public function post(string $uri, array $data = [], array $headers = []): HttpResponse
        {
            return \PestPluginWordPress\post($uri, $data, array_merge(['HTTP_REFERER' => $this->referer], $headers));
        }
    };
}

function makeRequest(string $method, string $uri, array $data = [], array $headers = []): HttpResponse
{
    global $wp;
    
    // Store original state
    $originalServer = $_SERVER;
    $originalGet = $_GET;
    $originalPost = $_POST;
    $originalRequest = $_REQUEST;
    
    // Apply headers to $_SERVER
    foreach ($headers as $key => $value) {
        $_SERVER[$key] = $value;
    }
    
    $_SERVER['REQUEST_METHOD'] = $method;
    
    // Parse URL and apply to globals
    $parsedUrl = parse_url($uri);
    $path = $parsedUrl['path'] ?? '/';
    
    $_SERVER['REQUEST_URI'] = $uri;
    $_SERVER['PATH_INFO'] = $path;
    
    if (isset($parsedUrl['query'])) {
        $_SERVER['QUERY_STRING'] = $parsedUrl['query'];
        parse_str($parsedUrl['query'], $queryParams);
        $_GET = array_merge($_GET, $queryParams);
        $_REQUEST = array_merge($_REQUEST, $queryParams);
    } else {
        $_SERVER['QUERY_STRING'] = '';
    }
    
    if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
        $_POST = $data;
        $_REQUEST = array_merge($_GET, $_POST);
    }
    
    // Capture output
    ob_start();
    
    $wp->parse_request();
    $wp->query_posts();
    $wp->main();
    
    $content = ob_get_clean();
    $status = http_response_code() ?: 200;
    
    // Parse any headers that were set
    $capturedHeaders = [];
    if (function_exists('headers_list')) {
        foreach (headers_list() as $header) {
            if (strpos($header, ':') !== false) {
                [$name, $value] = explode(':', $header, 2);
                $capturedHeaders[trim($name)] = trim($value);
            }
        }
        header_remove();
    }
    
    // Restore original state
    $_SERVER = $originalServer;
    $_GET = $originalGet;
    $_POST = $originalPost;
    $_REQUEST = $originalRequest;
    
    return new HttpResponse($content, $status, $capturedHeaders);
}

