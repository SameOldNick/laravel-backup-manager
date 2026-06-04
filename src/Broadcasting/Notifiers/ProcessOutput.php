<?php

namespace SameOldNick\BackupManager\Broadcasting\Notifiers;

use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\Output;

class ProcessOutput extends Output
{
    /**
     * @var array<string, string>
     */
    private const TYPE_STYLE_MAP = [
        'error' => 'error',
        'warning' => 'warning',
        'warn' => 'warning',
        'info' => 'info',
        'notice' => 'comment',
        'success' => 'info',
        'debug' => 'comment',
    ];

    public function __construct(
        public readonly ProcessNotifier $notifier,
    ) {
        parent::__construct(Output::VERBOSITY_NORMAL, true, null);
    }

    /**
     * Writes error message
     */
    public function error(string $message, bool $newline = true): void
    {
        $this->doWrite($this->format('error', $message), $newline);
    }

    /**
     * Writes warning message
     */
    public function warn(string $message, bool $newline = true): void
    {
        $this->doWrite($this->format('warning', $message), $newline);
    }

    /**
     * Writes info message
     */
    public function info(string $message, bool $newline = true): void
    {
        $this->doWrite($this->format('info', $message), $newline);
    }

    /**
     * Writes success message
     */
    public function success(string $message, bool $newline = true): void
    {
        $this->doWrite($this->format('success', $message), $newline);
    }

    /**
     * Writes notice message
     */
    public function notice(string $message, bool $newline = true): void
    {
        $this->doWrite($this->format('notice', $message), $newline);
    }

    /**
     * Writes debug message
     */
    public function debug(string $message, bool $newline = true): void
    {
        $this->doWrite($this->format('debug', $message), $newline);
    }

    /**
     * Formats message based on its type
     */
    public function format(string $type, string $message): string
    {
        $this->registerWarningStyle();

        $style = self::TYPE_STYLE_MAP[$type] ?? null;

        if ($style === null) {
            return $message;
        }

        $escapedMessage = OutputFormatter::escape($message);

        return $this->getFormatter()->format("<{$style}>{$escapedMessage}</{$style}>");
    }

    /**
     * Writes message to the notifier without additional formatting.
     */
    protected function doWrite(string $message, bool $newline): void
    {
        $this->notifier->output($message, $newline);
    }

    /**
     * Registers custom style for warning messages if not already registered.
     */
    private function registerWarningStyle(): void
    {
        if (! $this->getFormatter()->hasStyle('warning')) {
            $this->getFormatter()->setStyle('warning', new OutputFormatterStyle('yellow'));
        }
    }
}
