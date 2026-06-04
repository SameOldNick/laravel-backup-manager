<?php

namespace SameOldNick\BackupManager\Concerns;

use Illuminate\Support\Str;

trait FormatsNotificationPayload
{
    protected const NTFY_MAX_BODY_LENGTH = 3000;

    protected const TRACE_PREVIEW_MAX_LENGTH = 1200;

    /**
     * Truncate a message for Ntfy payload, ensuring it does not exceed the maximum allowed length.
     *
     * @param  string  $message  The message to truncate
     * @return string The truncated message, with an indication if it was truncated
     */
    protected function truncateForNtfy(string $message): string
    {
        return Str::limit($message, self::NTFY_MAX_BODY_LENGTH, "\n...[truncated]");
    }

    /**
     * Format an exception into a summary suitable for inclusion in an Ntfy message payload, with truncation applied to prevent excessively long messages.
     *
     * @param  \Throwable  $exception  The exception to format
     * @return array{message: string, type: string, file: string, line: int, trace_preview: string, trace_frame_count: int}
     */
    protected function toExceptionSummary(\Throwable $exception): array
    {
        return [
            'message' => Str::limit($exception->getMessage(), 500),
            'type' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace_preview' => Str::limit($exception->getTraceAsString(), self::TRACE_PREVIEW_MAX_LENGTH),
            'trace_frame_count' => count($exception->getTrace()),
        ];
    }
}
