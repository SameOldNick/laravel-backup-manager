<?php

namespace Tests\Unit\Packages\Backup;

use SameOldNick\BackupManager\Rules\RelativePath;
use SameOldNick\BackupManager\Tests\TestCase;

class RelativePathRuleTest extends TestCase
{
    public function test_relative_path_rule_accepts_nested_relative_path(): void
    {
        $validator = validator(
            ['root' => 'backups/local'],
            ['root' => [new RelativePath]],
        );

        $this->assertFalse($validator->fails());
    }

    public function test_relative_path_rule_rejects_absolute_path(): void
    {
        $validator = validator(
            ['root' => '/var/backups'],
            ['root' => [new RelativePath]],
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('root', $validator->errors()->toArray());
    }

    public function test_relative_path_rule_rejects_windows_absolute_path(): void
    {
        $validator = validator(
            ['root' => 'C:\\backups\\local'],
            ['root' => [new RelativePath]],
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('root', $validator->errors()->toArray());
    }

    public function test_relative_path_rule_rejects_traversal_segments(): void
    {
        $validator = validator(
            ['root' => '../backups/local'],
            ['root' => [new RelativePath]],
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('root', $validator->errors()->toArray());
    }
}
