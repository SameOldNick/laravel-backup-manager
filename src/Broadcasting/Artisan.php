<?php

namespace SameOldNick\BackupManager\Broadcasting;

use SameOldNick\BackupManager\Broadcasting\Console\OutputRedirector;
use SameOldNick\BackupManager\Broadcasting\Notifiers\ProcessNotifier;
use Illuminate\Support\Facades\Artisan as ArtisanFacade;
use Symfony\Component\Process\Process;

/**
 * Pipes Artisan command to websocket.
 * TODO: Allow input piping.
 */
class Artisan
{
    protected ProcessNotifier $notifier;

    /**
     * Intitializes Artisan instance
     *
     * @param  ProcessNotifier  $notifier  Used to send process related notifications
     */
    public function __construct(
        public readonly string $channel,
        public readonly object $notifiable,
    ) {
        $this->notifier = new ProcessNotifier($channel, $notifiable, ProcessNotifier::DEFAULT_MAX_LENGTH);
    }

    /**
     * Calls Artisan command and routes output to notifier.
     *
     * @param  string  $command
     * @return int Error code
     */
    public function __invoke($command, array $parameters = [])
    {
        $this->notifier->begin();

        $errorCode = ArtisanFacade::call($command, $parameters, new OutputRedirector($this->notifier));

        $this->notifier->complete($errorCode);

        return $errorCode;
    }

    /**
     * Invokes artisan command.
     *
     * @param  string  $command
     * @return int Error code
     */
    public static function call(string $channel, object $notifiable, $command, array $parameters = [])
    {
        return call_user_func(new self($channel, $notifiable), $command, $parameters);
    }
}
