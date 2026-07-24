<?php

use App\Helpers\NotificationHelper;

if (!function_exists('send_notification')) {
    /**
     * Send a notification to a user via database and email.
     *
     * @param object $user The user object (must have 'email' and 'id' properties).
     * @param string $message The notification message for the database.
     * @param string $subject The email subject.
     * @param string $bladeView The blade view for the email.
     * @param array $viewData The data to pass to the email view.
     * @param string|null $relatedModel The related model name (e.g., 'Ticket', 'Subscription').
     * @param int|string|null $relatedModelId The ID of the related model.
     * @return void
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
     * Get full public URL for a file/image using System Settings AWS_FILE_LOAD_BASE or S3 config.
     *
     * @param string|null $path
     * @return string|null
     */
    function get_file_url($path)
    {
        if (empty($path)) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
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
