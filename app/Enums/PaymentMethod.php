<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'Cash Payment';
    case ONLINE = 'Online Payment';
    case STRIPE = 'Stripe';
    case PAYPAL = 'PayPal';
    case RAZORPAY = 'Razorpay';
    case PAYSTACK = 'PayStack';
    case AAMARPAY = 'Amarpay';
    case BKASH = 'Bkash';
    case PAYTABS = 'PayTabs';
    case PAYTM_CARD = 'Paytm Card';
    case PAYTM_UPI = 'Paytm UPI';
    case PHONEPE_CARD = 'PhonePe Card';
    case PHONEPE_UPI = 'PhonePe UPI';
    case MOCK_CARD = 'Mock Card';
    case MOCK_UPI = 'Mock UPI';
}
