<?php

declare(strict_types=1);

namespace PestPluginWordPress;

class AjaxHelpers
{
    public static function call(string $action, array $data = [], bool $authenticated = false): AjaxResponse
    {
        // Store original state
        $originalPost = $_POST;
        $originalRequest = $_REQUEST;
        $originalUserId = get_current_user_id();
        
        $_POST['action'] = $action;
        $_REQUEST['action'] = $action;
        
        foreach ($data as $key => $value) {
            $_POST[$key] = $value;
            $_REQUEST[$key] = $value;
        }
        
        if ($authenticated) {
            if (!is_user_logged_in()) {
                actingAsAdmin();
            }
        }
        
        ob_start();
        
        try {
            if ($authenticated) {
                do_action("wp_ajax_{$action}");
            } else {
                do_action("wp_ajax_nopriv_{$action}");
            }
        } catch (\WPDieException $e) {
            // Expected for wp_die() calls
        }
        
        $output = ob_get_clean();
        
        // Restore original state
        $_POST = $originalPost;
        $_REQUEST = $originalRequest;
        wp_set_current_user($originalUserId);
        
        return new AjaxResponse($output);
    }
}

class AjaxResponse
{
    public function __construct(private string $output) {}
    
    public function assertSuccess(): self
    {
        $data = json_decode($this->output, true);
        test()->assertTrue($data['success'] ?? false, 'Ajax response was not successful');
        return $this;
    }
    
    public function assertFailed(): self
    {
        $data = json_decode($this->output, true);
        test()->assertFalse($data['success'] ?? true, 'Ajax response should have failed');
        return $this;
    }
    
    public function assertJson(): self
    {
        json_decode($this->output);
        test()->assertEquals(JSON_ERROR_NONE, json_last_error(), 'Response is not valid JSON');
        return $this;
    }
    
    public function assertJsonPath(string $path, mixed $expected): self
    {
        $data = json_decode($this->output, true);
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
    
    public function assertSee(string $text): self
    {
        test()->assertStringContainsString($text, $this->output);
        return $this;
    }
    
    public function json(): array
    {
        $decoded = json_decode($this->output, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Response is not valid JSON: ' . json_last_error_msg());
        }
        
        if (!is_array($decoded)) {
            throw new \RuntimeException('Response JSON is not an array');
        }
        
        return $decoded;
    }
    
    public function getOutput(): string
    {
        return $this->output;
    }
}

function callAjax(string $action, array $data = [], bool $authenticated = false): AjaxResponse
{
    return AjaxHelpers::call($action, $data, $authenticated);
}

