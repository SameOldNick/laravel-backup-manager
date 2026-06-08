<?php

namespace SameOldNick\BackupManager\DataTransferObjects;

class UpdateBackupDestinationData
{
    const AUTH_TYPE_PASSWORD = 'password';

    const AUTH_TYPE_KEY = 'key';

    public function __construct(
        public readonly ?bool $enabled,
        public readonly ?string $name,
        public readonly ?string $slug,
        public readonly ?string $host,
        public readonly ?int $port,
        public readonly ?string $authType,
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
            enabled: $data['enabled'] ?? null,
            name: $data['name'] ?? null,
            slug: $data['slug'] ?? null,
            host: $data['host'] ?? null,
            port: $data['port'] ?? null,
            authType: $data['auth_type'] ?? null,
            username: $data['username'] ?? null,
            password: $data['password'] ?? null,
            privateKey: $data['private_key'] ?? null,
            passphrase: $data['passphrase'] ?? null,
            root: $data['root'] ?? null,
            extra: $data['extra'] ?? null,
        );
    }
}
