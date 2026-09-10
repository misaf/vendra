<?php

declare(strict_types=1);

return [
    'reset_password' => [
        'action' => 'Passwort zurücksetzen',
        'expire' => 'Dieser Link zum Zurücksetzen des Passworts läuft in :count Minuten ab.',
        'greeting' => 'Hallo :user,',
        'line' => 'Sie erhalten diese E-Mail, weil wir eine Anfrage zum Zurücksetzen des Passworts für Ihr Konto erhalten haben.',
        'no_action' => 'Wenn Sie kein Zurücksetzen des Passworts angefordert haben, ist keine weitere Aktion erforderlich.',
        'subject' => 'Benachrichtigung zum Zurücksetzen des Passworts',
    ],
    'verify_email' => [
        'action' => 'E-Mail-Adresse verifizieren',
        'greeting' => 'Hallo :user,',
        'line' => 'Bitte klicken Sie auf die Schaltfläche unten, um Ihre E-Mail-Adresse zu verifizieren.',
        'no_action' => 'Wenn Sie kein Konto erstellt haben, ist keine weitere Aktion erforderlich.',
        'salutation' => 'Mit freundlichen Grüßen',
        'subject' => 'E-Mail-Adresse verifizieren',
    ],
];
