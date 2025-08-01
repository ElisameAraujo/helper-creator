<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Enables Backup
    |--------------------------------------------------------------------------
    |
    | When activated the package will make backup of your current 'composer.json'
    | before register the customs helpers.
    | Default: true
    |
    */
    'backup_enabled' => false,

    /*
    |--------------------------------------------------------------------------
    | Merge Backup
    |--------------------------------------------------------------------------
    |
    | This setting allows packages found in the most recent backup 
    | within the 'require' key to be merged into the current 'composer.json'
    | file when the restore is done using the flag --dry-run.
    | Recommended: false
    |
    */
    'merge_enabled' => false,

    /*
    |--------------------------------------------------------------------------
    | Log Levels
    |--------------------------------------------------------------------------
    |
    | Sets the default log level for the package.
    | Default: debug
    |
    */
    'log_level' => 'debug',
];
