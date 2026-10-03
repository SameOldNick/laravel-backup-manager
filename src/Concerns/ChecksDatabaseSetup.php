<?php

namespace SameOldNick\BackupManager\Concerns;

use Illuminate\Support\Facades\Schema;

trait ChecksDatabaseSetup
{
    /**
     * Checks if database has been setup by checking if the specified tables exist.
     */
    protected function isDatabaseSetup(array $tables): bool
    {
        try {
            foreach ($tables as $table) {
                if (! Schema::hasTable($table)) {
                    return false;
                }
            }

            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }
}
