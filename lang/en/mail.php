<?php

declare(strict_types=1);

return [
    'reset_password' => [
        'action' => 'Reset Password',
        'expire' => 'This password reset link will expire in :count minutes.',
        'greeting' => 'Dear :user,',
        'line' => 'You are receiving this email because we received a password reset request for your account.',
        'no_action' => 'If you did not request a password reset, no further action is required.',
        'subject' => 'Reset Password Notification',
    ],
    'verify_email' => [
        'action' => 'Verify Email Address',
        'greeting' => 'Hello :user,',
        'line' => 'Please click the button below to verify your email address.',
        'no_action' => 'If you did not create an account, no further action is required.',
        'salutation' => 'Best regards',
        'subject' => 'Verify Email Address',
    ],
];
