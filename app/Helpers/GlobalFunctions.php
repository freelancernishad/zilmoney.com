<?php

use App\Helpers\NotificationHelper;
use Illuminate\Support\Facades\Storage;

if (!function_exists('send_notification')) {
    /**
     * Send a notification to a user via database and email.
     */
    function send_notification($user, $message, $subject, $bladeView, $viewData = [], $relatedModel = null, $relatedModelId = null)
    {
        try {
            NotificationHelper::sendUserNotification(
                $user,
                $message,
                $subject,
                $relatedModel,
                $relatedModelId,
                $bladeView,
                $viewData
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send global notification: " . $e->getMessage());
        }
    }
}

if (!function_exists('get_file_url')) {
    /**
     * Get full public URL for a file/image using System Settings AWS_FILE_LOAD_BASE, presigned S3 URLs, or local fallback.
     *
     * @param string|null $path
     * @return string|null
     */
    function get_file_url($path)
    {
        if (empty($path)) {
            return null;
        }

        // Handle AWS S3 URLs with temporary presigned URLs to bypass AWS S3 403 Forbidden blocks
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            if (str_contains($path, 'amazonaws.com')) {
                $s3Path = ltrim(parse_url($path, PHP_URL_PATH), '/');
                try {
                    return Storage::disk('s3')->temporaryUrl($s3Path, now()->addDays(7));
                } catch (\Exception $e) {
                    return asset('storage/' . $s3Path);
                }
            }
            return $path;
        }

        if (str_starts_with($path, 'shop/')) {
            try {
                return Storage::disk('s3')->temporaryUrl($path, now()->addDays(7));
            } catch (\Exception $e) {
                return asset('storage/' . $path);
            }
        }

        $baseLoadUrl = config('AWS_FILE_LOAD_BASE') 
            ?: config('filesystems.disks.s3.url') 
            ?: config('AWS_URL') 
            ?: env('AWS_URL');

        if ($baseLoadUrl) {
            return rtrim($baseLoadUrl, '/') . '/' . ltrim($path, '/');
        }

        $bucket = config('filesystems.disks.s3.bucket') ?: env('AWS_BUCKET');
        if ($bucket) {
            $region = config('filesystems.disks.s3.region') ?: env('AWS_DEFAULT_REGION', 'us-east-1');
            return "https://{$bucket}.s3.{$region}.amazonaws.com/" . ltrim($path, '/');
        }

        return asset('storage/' . ltrim(str_replace('storage/', '', $path), '/'));
    }
}

if (!function_exists('get_image_url')) {
    /**
     * Alias for get_file_url.
     *
     * @param string|null $path
     * @return string|null
     */
    function get_image_url($path)
    {
        return get_file_url($path);
    }
}
