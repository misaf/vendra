<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Filament Panels
    |--------------------------------------------------------------------------
    |
    | Here you may specify the Filament panels that the newsletter administration
    | UI is registered on. The newsletter navigation only appears within the
    | panels listed here. You may provide a single panel ID or an array of IDs
    | to mount the module across multiple panels.
    |
    | Supported: "admin" (string), ["admin", "vendor"] (array)
    |
    */

    'panels' => ['admin'],

    /*
    |--------------------------------------------------------------------------
    | Navigation Group
    |--------------------------------------------------------------------------
    |
    | This value determines the sidebar navigation group that the Newsletters
    | cluster is nested under. You may provide a translation key or a literal
    | label. When left empty, the module's default group is used.
    |
    */

    'navigation_group' => 'vendra-support::navigation.groups.Marketing',

    /*
    |--------------------------------------------------------------------------
    | Batch Chunk Size
    |--------------------------------------------------------------------------
    |
    | When a newsletter is sent, its subscribers are dispatched to the queue in
    | chunks of this size. Smaller chunks reduce memory pressure per job; larger
    | chunks reduce the number of dispatched jobs.
    |
    */

    'batch_chunk_size' => (int) env('NEWSLETTER_BATCH_CHUNK_SIZE', 100),

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    |
    | Configuration applied to the queued jobs that fan out and deliver a
    | newsletter. "email_timeout" applies to the per-recipient delivery job,
    | while "timeout" applies to the batch fan-out job.
    |
    */

    'queue' => [
        'connection' => env('NEWSLETTER_QUEUE_CONNECTION', ''),
        'name' => env('NEWSLETTER_QUEUE_NAME', 'default'),
        'tries' => (int) env('NEWSLETTER_QUEUE_TRIES', 3),
        'timeout' => (int) env('NEWSLETTER_QUEUE_TIMEOUT', 30),
        'email_timeout' => (int) env('NEWSLETTER_EMAIL_QUEUE_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Schedule
    |--------------------------------------------------------------------------
    |
    | The module registers its own scheduled task that dispatches due
    | newsletters for every tenant. Disable it here to schedule the
    | "vendra-newsletter:send-scheduled" command yourself, or adjust how often
    | it runs with a standard cron expression.
    |
    */

    'schedule' => [
        'enabled' => env('NEWSLETTER_SCHEDULE_ENABLED', true),
        'cron' => env('NEWSLETTER_SCHEDULE_CRON', '* * * * *'),
    ],

];
