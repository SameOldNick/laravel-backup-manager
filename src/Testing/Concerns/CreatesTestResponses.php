<?php

namespace SameOldNick\BackupManager\Testing\Concerns;

trait CreatesTestResponses
{
    /**
     * Creates a test response array
     */
    protected function createTestResponse(string $id, array $data = []): array
    {
        return [
            'responder' => $this->getSourceResponder(),
            'id' => $id,
            'data' => $data,
        ];
    }

    /**
     * Gets the source responder
     */
    abstract protected function getSourceResponder(): string;
}
