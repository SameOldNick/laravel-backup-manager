<?php

namespace SameOldNick\BackupManager\Runners;

abstract class Runner
{
    protected readonly mixed $onStartedCallback;

    protected readonly mixed $onSuccessCallback;

    protected readonly mixed $onFailedCallback;

    protected readonly mixed $onCompletedCallback;

    /**
     * Runner constructor.
     *
     * @param  ?callable  $onStartedCallback  Optional callback to execute when the backup starts
     * @param  ?callable  $onSuccessCallback  Optional callback to execute when the backup succeeds
     * @param  ?callable  $onFailedCallback  Optional callback to execute when the backup fails
     * @param  ?callable  $onCompletedCallback  Optional callback to execute when the backup completes (regardless of success or failure)
     */
    public function __construct(
        ?callable $onStartedCallback = null,
        ?callable $onSuccessCallback = null,
        ?callable $onFailedCallback = null,
        ?callable $onCompletedCallback = null,
    ) {
        $this->onStartedCallback = $onStartedCallback;
        $this->onSuccessCallback = $onSuccessCallback;
        $this->onFailedCallback = $onFailedCallback;
        $this->onCompletedCallback = $onCompletedCallback;
    }

    /**
     * Executes the runner's main logic with the appropriate callbacks.
     *
     * @param  callable  $callback  The main logic to execute, which will be wrapped with the callbacks
     */
    protected function executeWithCallbacks(callable $callback): void
    {
        $this->callStartedCallback();

        try {
            call_user_func($callback);

            $this->callSuccessCallback();
        } catch (\Exception $e) {
            $this->callFailedCallback($e);

            throw $e;
        } finally {
            $this->callCompletedCallback();
        }
    }

    /**
     * Call the onStartedCallback if it is set.
     */
    public function callStartedCallback(): void
    {
        if ($this->onStartedCallback !== null) {
            call_user_func($this->onStartedCallback);
        }
    }

    /**
     * Call the onFailedCallback if it is set, passing the exception as an argument.
     *
     * @param  \Exception  $e  The exception that was thrown during execution
     */
    public function callFailedCallback(\Exception $e): void
    {
        if ($this->onFailedCallback !== null) {
            call_user_func($this->onFailedCallback, $e);
        }
    }

    /**
     * Call the onSuccessCallback if it is set.
     */
    public function callSuccessCallback(): void
    {
        if ($this->onSuccessCallback !== null) {
            call_user_func($this->onSuccessCallback);
        }
    }

    /**
     * Call the onCompletedCallback if it is set.
     */
    public function callCompletedCallback(): void
    {
        if ($this->onCompletedCallback !== null) {
            call_user_func($this->onCompletedCallback);
        }
    }
}
