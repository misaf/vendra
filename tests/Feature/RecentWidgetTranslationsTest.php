<?php

declare(strict_types=1);

it('labels table-list widgets as recent', function (string $locale, string $key, string $label): void {
    app()->setLocale($locale);

    expect(__($key))->toBe($label);
})->with([
    'activity log en' => ['en', 'vendra-activity-log::widgets.recent_activity_log_table', 'Recent Activity'],
    'activity log de' => ['de', 'vendra-activity-log::widgets.recent_activity_log_table', 'Letzte Aktivitäten'],
    'activity log fa' => ['fa', 'vendra-activity-log::widgets.recent_activity_log_table', 'فعالیت‌های اخیر'],
    'auth log en' => ['en', 'vendra-authify-log::widgets.recent_authify_log_table', 'Recent Auth Logs'],
    'auth log de' => ['de', 'vendra-authify-log::widgets.recent_authify_log_table', 'Letzte Auth-Protokolle'],
    'auth log fa' => ['fa', 'vendra-authify-log::widgets.recent_authify_log_table', 'گزارش‌های احراز هویت اخیر'],
    'multimedia en' => ['en', 'vendra-multimedia::widgets.recent_multimedia_table', 'Recent Media'],
    'multimedia de' => ['de', 'vendra-multimedia::widgets.recent_multimedia_table', 'Zuletzt hinzugefügte Medien'],
    'multimedia fa' => ['fa', 'vendra-multimedia::widgets.recent_multimedia_table', 'رسانه‌های اخیر'],
    'transaction en' => ['en', 'vendra-transaction::widgets.recent_transaction_table', 'Recent Transactions'],
    'transaction de' => ['de', 'vendra-transaction::widgets.recent_transaction_table', 'Letzte Transaktionen'],
    'transaction fa' => ['fa', 'vendra-transaction::widgets.recent_transaction_table', 'تراکنش‌های اخیر'],
    'transaction description en' => ['en', 'vendra-transaction::widgets.recent_transaction_table_description', 'Transaction · Recent entries'],
    'transaction description de' => ['de', 'vendra-transaction::widgets.recent_transaction_table_description', 'Transaktion · Letzte Einträge'],
    'transaction description fa' => ['fa', 'vendra-transaction::widgets.recent_transaction_table_description', 'تراکنش‌ها · موارد اخیر'],
    'users en' => ['en', 'vendra-user::navigation.recent_users', 'Recent Users'],
    'users de' => ['de', 'vendra-user::navigation.recent_users', 'Zuletzt registrierte Benutzer'],
    'users fa' => ['fa', 'vendra-user::navigation.recent_users', 'کاربران اخیر'],
]);
