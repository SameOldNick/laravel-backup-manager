<?php

namespace SameOldNick\BackupManager\DataTransferObjects;

class CreateBackupDestinationData
{
    const TYPE_LOCAL = 'local';

    const TYPE_FTP = 'ftp';

    const TYPE_SFTP = 'sftp';

    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly bool $enabled,
        public readonly ?string $host,
        public readonly ?int $port,
        public readonly ?string $username,
        public readonly ?string $password,
        public readonly ?string $privateKey,
        public readonly ?string $passphrase,
        public readonly ?string $root,
        public readonly ?array $extra,
    ) {
        //
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            type: $data['type'],
            enabled: $data['enabled'],
            host: $data['host'] ?? null,
            port: $data['port'] ?? null,
            username: $data['username'] ?? null,
            password: $data['password'] ?? null,
            privateKey: $data['private_key'] ?? null,
            passphrase: $data['passphrase'] ?? null,
            root: $data['root'] ?? null,
            extra: $data['extra'] ?? null,
        );
    }
}
