<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaymentGateway::truncate();
        $paymentMethods = [
            [
                'title' => 'Stripe',
                'name' => 'stripe',
                'config' => json_encode([
                    'secret_key' => env('STRIPE_SECRET'),
                    'published_key' => env('STRIPE_PUBLISHED_KEY'),
                ]),
                'mode' => 'test',
                'alias' => 'Stripe',
                'is_active' => true,
            ],
            [
                'title' => 'PayPal',
                'name' => 'paypal',
                'config' => json_encode([
                    'client_id' => env('PAYPAL_CLIENT_ID'),
                    'client_secret' => env('PAYPAL_CLIENT_SECRET'),
                ]),
                'mode' => 'test',
                'alias' => 'PayPal',
                'is_active' => true,
            ],
            [
                'title' => 'Razorpay',
                'name' => 'razorpay',
                'config' => json_encode([
                    'key' => env('RAZORPAY_KEY'),
                    'secret' => env('RAZORPAY_SECRET'),
                ]),
                'mode' => 'test',
                'alias' => 'Razorpay',
                'is_active' => true,
            ],
            [
                'title' => 'Paystack',
                'name' => 'paystack',
                'config' => json_encode([
                    'public_key' => env('PAYSTACK_PUBLIC_KEY'),
                    'secret_key' => env('PAYSTACK_SECRET_KEY'),
                    'machant_email' => '',
                ]),
                'mode' => 'test',
                'alias' => 'PayStack',
                'is_active' => true,
            ],
            [
                'title' => 'aamarPay',
                'name' => 'aamarpay',
                'config' => json_encode([
                    'store_id' => env('AAMARPAY_STORE_ID'),
                    'signature_key' => env('AAMARPAY_SIGNATURE_KEY'),
                ]),
                'mode' => 'test',
                'alias' => 'AamarPay',
                'is_active' => true,
            ],
            [
                'title' => 'BKash',
                'name' => 'bKash',
                'config' => json_encode([
                    'username' => env('BKASH_USERNAME'),
                    'password' => env('BKASH_PASSWORD'),
                    'app_key' => env('BKASH_APP_KEY'),
                    'app_secret_key' => env('BKASH_APP_SECRET'),
                ]),
                'mode' => 'test',
                'alias' => 'Bkash',
                'is_active' => true,
            ],
            [
                'title' => 'PayTabs',
                'name' => 'paytabs',
                'config' => json_encode([
                    'base_url' => 'https://secure-global.paytabs.com',
                    'profile_id' => env('PAYTABS_PROFILE_ID'),
                    'server_key' => env('PAYTABS_SERVER_KEY'),
                    'currency' => 'USD',
                ]),
                'mode' => 'test',
                'alias' => 'PayTabs',
                'is_active' => true,
            ],
        ];

        PaymentGateway::insert($paymentMethods);
    }
}

// 'username' => '',
// 'password' => '',
// 'app_key' => '',
// 'app_secret_key' => ''

// rozorpay live key
// 'key' => '',
// 'secret' => '',
