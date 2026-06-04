<?php

namespace SameOldNick\BackupManager\Broadcasting\Notifiers;

use SameOldNick\BackupManager\Broadcasting\Notifications\ProcessOutputNotification;
use SameOldNick\BackupManager\Broadcasting\Notifications\ProcessStatusNotification;
use DateTimeInterface;
use Illuminate\Broadcasting\BroadcastException;

/**
 * Broadcasts events related to a process.
 */
class ProcessNotifier extends AbstractNotifier
{
    /**
     * Default maximum length of messages
     */
    const DEFAULT_MAX_LENGTH = 7000;

    private readonly ProcessOutput $output;

    /**
     * Initializes ProcessNotifier instance
     *
     * @param  string  $channel  Channel
     * @param  object  $notifiable  Who to route notifications to
     * @param  int  $maxLength  Maximum length of messages
     */
    public function __construct(
        string $channel,
        object $notifiable,
        public readonly int $maxLength,
    ) {
        parent::__construct($channel, $notifiable);

        $this->output = new ProcessOutput($this);
    }

    /**
     * Sends notification that process was started.
     *
     * @return void
     */
    public function begin(?DateTimeInterface $dateTime = null)
    {
        $this->notify(
            $this->notifiable,
            new ProcessStatusNotification($this->channel, ProcessStatusNotification::STATUS_BEGIN, $dateTime)
        );
    }

    /**
     * Sends notification that process completed.
     *
     * @param  int  $errorCode  Process error code
     * @return void
     */
    public function complete(int $errorCode, ?DateTimeInterface $dateTime = null)
    {
        $this->notify(
            $this->notifiable,
            new ProcessStatusNotification(
                $this->channel,
                ProcessStatusNotification::STATUS_COMPLETE,
                $dateTime,
                ['error_code' => $errorCode]
            )
        );
    }

    /**
     * Gets ProcessOutput instance used to send process output notifications.
     */
    public function getProcessOutput(): ProcessOutput
    {
        return $this->output;
    }

    /**
     * Sends notification with output from process.
     *
     * @return void
     */
    public function output(string $message, bool $newline, ?DateTimeInterface $dateTime = null)
    {
        try {
            $this->notify(
                $this->notifiable,
                new ProcessOutputNotification($this->channel, $message, $newline, $dateTime)
            );
        } catch (BroadcastException $ex) {
            if ($ex->getMessage() === 'Pusher error: Payload too large..') {
                // Split message into chunks
                // The max size is 10KB
                $this->outputAsChunks($message, $newline, $dateTime);
            } else {
                throw $ex;
            }
        }
    }

    /**
     * Outputs message in chunks
     *
     * @return void
     */
    public function outputAsChunks(string $message, bool $newline, ?DateTimeInterface $dateTime = null)
    {
        // Split message into chunks of max length
        $chunks = mb_str_split($message, $this->maxLength);
        $numChunks = count($chunks);

        for ($i = 0; $i < $numChunks; $i++) {
            $this->notify(
                $this->notifiable,
                new ProcessOutputNotification(
                    $this->channel,
                    $chunks[$i],
                    // Include newline in last chunk (if specified)
                    $newline && $i + 1 === $numChunks,
                    $dateTime
                )
            );
        }
    }

    /**
     * Creates a ProcessNotifier instance
     */
    public static function create(object $notifiable, string $channel, ?int $maxLength = null): static
    {
        $maxLength = $maxLength ?? static::DEFAULT_MAX_LENGTH;

        return new self($channel, $notifiable, $maxLength);
    }
}
