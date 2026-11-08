<?php

namespace App\Logging;

use Monolog\Formatter\NormalizerFormatter;
use Monolog\LogRecord;

/**
 * Writes one JSON object per log line with the core fields promoted to
 * the top level (time, level, channel, message) and the record's context
 * merged in alongside them. This lets log consumers filter directly on
 * fields like requestId or status instead of digging into Monolog's
 * default nested {message, context} shape.
 */
class StructuredJsonFormatter extends NormalizerFormatter
{
    public function format(LogRecord $record): string
    {
        // Context first, core fields last: a stray context key can never
        // clobber time/level/channel/message.
        $context = $this->normalize($record->context);
        $extra = $this->normalize($record->extra);

        $payload = array_merge(
            is_array($context) ? $context : [],
            is_array($extra) ? $extra : [],
            [
                'time' => $this->formatDate($record->datetime),
                'level' => strtolower($record->level->name),
                'channel' => $record->channel,
                'message' => $record->message,
            ],
        );

        $encoded = json_encode(
            $payload,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE,
        );

        // Never emit a non-JSON line: fallback to {} if encoding fails
        // (e.g. unnormalizable recursion), keeping every line parseable.
        if ($encoded === false) {
            return json_encode(['message' => $record->message]) . "\n";
        }

        return $encoded . "\n";
    }
}
