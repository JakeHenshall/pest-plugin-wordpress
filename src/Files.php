<?php

declare(strict_types=1);

namespace PestPluginWordPress;

class FileHelpers
{
    public static function fakeUpload(string $filename, string $content = 'test content', string $mimeType = 'text/plain'): array
    {
        // Sanitise filename to prevent path traversal
        $filename = basename($filename);
        
        if (empty($filename) || $filename === '.' || $filename === '..') {
            throw new \InvalidArgumentException('Invalid filename provided');
        }
        
        $uploadDir = wp_upload_dir();
        
        // Ensure upload directory exists
        if (!file_exists($uploadDir['path'])) {
            wp_mkdir_p($uploadDir['path']);
        }
        
        $filePath = $uploadDir['path'] . '/' . $filename;
        
        file_put_contents($filePath, $content);
        
        $attachment = [
            'post_mime_type' => $mimeType,
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit',
        ];
        
        $attachId = wp_insert_attachment($attachment, $filePath);
        
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $attachData = wp_generate_attachment_metadata($attachId, $filePath);
        wp_update_attachment_metadata($attachId, $attachData);
        
        return [
            'id' => $attachId,
            'path' => $filePath,
            'url' => wp_get_attachment_url($attachId),
        ];
    }
    
    public static function fakeImage(string $filename = 'test-image.jpg', int $width = 100, int $height = 100): array
    {
        // Sanitise filename to prevent path traversal
        $filename = basename($filename);
        
        if (empty($filename) || $filename === '.' || $filename === '..') {
            throw new \InvalidArgumentException('Invalid filename provided');
        }
        
        $uploadDir = wp_upload_dir();
        
        // Ensure upload directory exists
        if (!file_exists($uploadDir['path'])) {
            wp_mkdir_p($uploadDir['path']);
        }
        
        $filePath = $uploadDir['path'] . '/' . $filename;
        
        $image = imagecreatetruecolor($width, $height);
        $bgColor = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $bgColor);
        
        imagejpeg($image, $filePath);
        imagedestroy($image);
        
        $attachment = [
            'post_mime_type' => 'image/jpeg',
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit',
        ];
        
        $attachId = wp_insert_attachment($attachment, $filePath);
        
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $attachData = wp_generate_attachment_metadata($attachId, $filePath);
        wp_update_attachment_metadata($attachId, $attachData);
        
        return [
            'id' => $attachId,
            'path' => $filePath,
            'url' => wp_get_attachment_url($attachId),
            'width' => $width,
            'height' => $height,
        ];
    }
}

function fakeUpload(string $filename, string $content = 'test content', string $mimeType = 'text/plain'): array
{
    return FileHelpers::fakeUpload($filename, $content, $mimeType);
}

function fakeImage(string $filename = 'test-image.jpg', int $width = 100, int $height = 100): array
{
    return FileHelpers::fakeImage($filename, $width, $height);
}

function assertFileUploaded(int $attachmentId): void
{
    $file = get_attached_file($attachmentId);
    test()->assertNotFalse($file, "Attachment {$attachmentId} has no file");
    test()->assertFileExists($file, "File does not exist: {$file}");
}

function assertImageSize(int $attachmentId, string $size = 'full'): void
{
    $image = wp_get_attachment_image_src($attachmentId, $size);
    test()->assertNotFalse($image, "Image size '{$size}' does not exist for attachment {$attachmentId}");
}

