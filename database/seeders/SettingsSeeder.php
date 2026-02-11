<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $settings = [
            // Tenant 1 - General Settings
            [
                'tenant_id'  => 1,
                'group'      => 'general',
                'name'       => 'site_title',
                'locked'     => false,
                'payload'    => json_encode('Misaf'),
                'created_at' => '2024-08-31 08:04:16',
                'updated_at' => '2024-08-31 08:37:42',
            ],
            [
                'tenant_id'  => 1,
                'group'      => 'general',
                'name'       => 'site_description',
                'locked'     => false,
                'payload'    => json_encode(''),
                'created_at' => '2024-08-31 08:04:16',
                'updated_at' => '2024-08-31 08:37:42',
            ],

            // Tenant 1 - PerfectMoney Settings
            [
                'tenant_id'  => 1,
                'group'      => 'perfectmoney',
                'name'       => 'usd_x_toman',
                'locked'     => false,
                'payload'    => json_encode(70400),
                'created_at' => '2024-08-31 08:04:16',
                'updated_at' => '2024-11-21 13:30:50',
            ],
            [
                'tenant_id'  => 1,
                'group'      => 'perfectmoney',
                'name'       => 'toman_x_usd',
                'locked'     => false,
                'payload'    => json_encode(71400),
                'created_at' => '2024-08-31 08:04:16',
                'updated_at' => '2024-11-21 13:30:50',
            ],
            [
                'tenant_id'  => 1,
                'group'      => 'perfectmoney',
                'name'       => 'withdrawal_limit_hours',
                'locked'     => false,
                'payload'    => json_encode(24),
                'created_at' => '2024-08-31 08:04:16',
                'updated_at' => '2024-11-21 13:30:50',
            ],
            [
                'tenant_id'  => 1,
                'group'      => 'perfectmoney',
                'name'       => 'withdrawal_limit_count',
                'locked'     => false,
                'payload'    => json_encode(1),
                'created_at' => '2024-08-31 08:04:16',
                'updated_at' => '2024-11-21 13:30:50',
            ],

            // Tenant 1 - CoinPayments Settings
            [
                'tenant_id'  => 1,
                'group'      => 'coinpayments',
                'name'       => 'usd_x_toman',
                'locked'     => false,
                'payload'    => json_encode(19778),
                'created_at' => '2024-08-31 08:04:16',
                'updated_at' => '2025-06-01 20:52:28',
            ],
            [
                'tenant_id'  => 1,
                'group'      => 'coinpayments',
                'name'       => 'toman_x_usd',
                'locked'     => false,
                'payload'    => json_encode(19329),
                'created_at' => '2024-08-31 08:04:16',
                'updated_at' => '2025-06-01 20:52:28',
            ],
            [
                'tenant_id'  => 1,
                'group'      => 'coinpayments',
                'name'       => 'crazy_bonus_deposit_min_amount',
                'locked'     => false,
                'payload'    => json_encode(1),
                'created_at' => '2024-08-31 08:04:16',
                'updated_at' => '2025-06-01 20:52:28',
            ],
            [
                'tenant_id'  => 1,
                'group'      => 'coinpayments',
                'name'       => 'crazy_bonus_deposit_amount',
                'locked'     => false,
                'payload'    => json_encode(1),
                'created_at' => '2024-08-31 08:04:16',
                'updated_at' => '2025-06-01 20:52:28',
            ],
            [
                'tenant_id'  => 1,
                'group'      => 'coinpayments',
                'name'       => 'crazy_bonus_deposit_status',
                'locked'     => false,
                'payload'    => json_encode(false),
                'created_at' => '2024-08-31 08:04:16',
                'updated_at' => '2025-06-01 20:52:28',
            ],
            [
                'tenant_id'  => 1,
                'group'      => 'coinpayments',
                'name'       => 'crazy_bonus_withdrawal_min_amount',
                'locked'     => false,
                'payload'    => json_encode(1),
                'created_at' => '2024-08-31 08:04:16',
                'updated_at' => '2025-06-01 20:52:28',
            ],
            [
                'tenant_id'  => 1,
                'group'      => 'coinpayments',
                'name'       => 'crazy_bonus_withdrawal_amount',
                'locked'     => false,
                'payload'    => json_encode(2),
                'created_at' => '2024-08-31 08:04:16',
                'updated_at' => '2025-06-01 20:52:28',
            ],
            [
                'tenant_id'  => 1,
                'group'      => 'coinpayments',
                'name'       => 'crazy_bonus_withdrawal_status',
                'locked'     => false,
                'payload'    => json_encode(false),
                'created_at' => '2024-08-31 08:04:16',
                'updated_at' => '2025-06-01 20:52:28',
            ],
            [
                'tenant_id'  => 1,
                'group'      => 'coinpayments',
                'name'       => 'withdrawal_limit_hours',
                'locked'     => false,
                'payload'    => json_encode(24),
                'created_at' => '2024-08-31 08:04:16',
                'updated_at' => '2025-06-01 20:52:28',
            ],
            [
                'tenant_id'  => 1,
                'group'      => 'coinpayments',
                'name'       => 'withdrawal_limit_count',
                'locked'     => false,
                'payload'    => json_encode(2),
                'created_at' => '2024-08-31 08:04:16',
                'updated_at' => '2025-06-01 20:52:28',
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                [
                    'tenant_id' => $setting['tenant_id'],
                    'group'     => $setting['group'],
                    'name'      => $setting['name'],
                ],
                $setting
            );
        }
    }
}
