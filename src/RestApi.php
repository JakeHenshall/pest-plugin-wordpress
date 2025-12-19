<?php

declare(strict_types=1);

namespace PestPluginWordPress;

class RestApiHelpers
{
    public static function get(string $route, array $params = [], ?int $userId = null): RestApiResponse
    {
        return self::request('GET', $route, $params, $userId);
    }
    
    public static function post(string $route, array $data = [], ?int $userId = null): RestApiResponse
    {
        return self::request('POST', $route, $data, $userId);
    }
    
    public static function put(string $route, array $data = [], ?int $userId = null): RestApiResponse
    {
        return self::request('PUT', $route, $data, $userId);
    }
    
    public static function patch(string $route, array $data = [], ?int $userId = null): RestApiResponse
    {
        return self::request('PATCH', $route, $data, $userId);
    }
    
    public static function delete(string $route, array $params = [], ?int $userId = null): RestApiResponse
    {
        return self::request('DELETE', $route, $params, $userId);
    }
    
    private static function request(string $method, string $route, array $data = [], ?int $userId = null): RestApiResponse
    {
        $previousUserId = get_current_user_id();
        
        if ($userId !== null) {
            wp_set_current_user($userId);
        }
        
        $request = new \WP_REST_Request($method, $route);
        
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $request->set_body_params($data);
        } else {
            foreach ($data as $key => $value) {
                $request->set_param($key, $value);
            }
        }
        
        $response = rest_do_request($request);
        
        // Restore previous user
        wp_set_current_user($previousUserId);
        
        // Handle WP_Error responses
        if (is_wp_error($response)) {
            $response = new \WP_REST_Response([
                'code' => $response->get_error_code(),
                'message' => $response->get_error_message(),
                'data' => $response->get_error_data(),
            ], 500);
        }
        
        return new RestApiResponse($response);
    }
}

class RestApiResponse
{
    public function __construct(private \WP_REST_Response|\WP_HTTP_Response $response) {}
    
    public function assertStatus(int $status): self
    {
        test()->assertEquals($status, $this->response->get_status());
        return $this;
    }
    
    public function assertOk(): self
    {
        return $this->assertStatus(200);
    }
    
    public function assertCreated(): self
    {
        return $this->assertStatus(201);
    }
    
    public function assertNoContent(): self
    {
        return $this->assertStatus(204);
    }
    
    public function assertUnauthorized(): self
    {
        return $this->assertStatus(401);
    }
    
    public function assertForbidden(): self
    {
        return $this->assertStatus(403);
    }
    
    public function assertNotFound(): self
    {
        return $this->assertStatus(404);
    }
    
    public function assertJsonPath(string $path, mixed $expected): self
    {
        $data = $this->response->get_data();
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
    
    public function assertJsonStructure(array $structure): self
    {
        $data = $this->response->get_data();
        $this->validateStructure($structure, $data);
        return $this;
    }
    
    private function validateStructure(array $structure, array $data, string $path = ''): void
    {
        foreach ($structure as $key => $value) {
            if (is_array($value)) {
                test()->assertArrayHasKey($key, $data, "Missing key '{$key}' in path '{$path}'");
                $this->validateStructure($value, $data[$key], $path . '.' . $key);
            } else {
                test()->assertArrayHasKey($value, $data, "Missing key '{$value}' in path '{$path}'");
            }
        }
    }
    
    public function assertJsonCount(int $count, ?string $path = null): self
    {
        $data = $this->response->get_data();
        
        if ($path !== null) {
            $keys = explode('.', $path);
            foreach ($keys as $key) {
                $data = $data[$key];
            }
        }
        
        test()->assertCount($count, $data);
        return $this;
    }
    
    public function json(): array
    {
        $data = $this->response->get_data();
        
        if (!is_array($data)) {
            throw new \RuntimeException('Response data is not an array');
        }
        
        return $data;
    }
    
    public function getResponse(): \WP_REST_Response|\WP_HTTP_Response
    {
        return $this->response;
    }
}

function restGet(string $route, array $params = [], ?int $userId = null): RestApiResponse
{
    return RestApiHelpers::get($route, $params, $userId);
}

function restPost(string $route, array $data = [], ?int $userId = null): RestApiResponse
{
    return RestApiHelpers::post($route, $data, $userId);
}

function restPut(string $route, array $data = [], ?int $userId = null): RestApiResponse
{
    return RestApiHelpers::put($route, $data, $userId);
}

function restPatch(string $route, array $data = [], ?int $userId = null): RestApiResponse
{
    return RestApiHelpers::patch($route, $data, $userId);
}

function restDelete(string $route, array $params = [], ?int $userId = null): RestApiResponse
{
    return RestApiHelpers::delete($route, $params, $userId);
}

