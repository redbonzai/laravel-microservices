<?php

namespace Redbonzai\Logging;

use Monolog\Formatter\JsonFormatter;

class CustomJsonFormatter extends JsonFormatter
{
    public function format(array|\Monolog\LogRecord $record): string
    {
        // Check if the context has an exception and serialize it properly
        if (isset($record['context']['exception']) && $record['context']['exception'] instanceof \Exception) {
            $e = $record['context']['exception'];
            $record['context']['exception'] = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => explode("\n", $e->getTraceAsString()),
            ];
        }

        return parent::format($record);
    }
}
