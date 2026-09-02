<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;

class GstVerificationService
{
    public function verify($gstin)
    {
        try {
            // Razorpay GST Search scraping (example)
            $response = Http::get("https://razorpay.com/gst-number-search/" . $gstin);

            if ($response->failed()) {
                return ['success' => false, 'message' => 'Unable to fetch GST info'];
            }

            $html = $response->body();

            // Extract fields using regex
            preg_match('/GSTIN<\/td>\s*<td[^>]*>(.*?)<\/td>/', $html, $gstinMatch);
            preg_match('/Legal Name of Business<\/td>\s*<td[^>]*>(.*?)<\/td>/', $html, $legalNameMatch);
            preg_match('/GSTIN Status<\/td>\s*<td[^>]*>(.*?)<\/td>/', $html, $statusMatch);
            preg_match('/Constitution of Business<\/td>\s*<td[^>]*>(.*?)<\/td>/', $html, $constitutionMatch);
            preg_match('/Date of Registration<\/td>\s*<td[^>]*>(.*?)<\/td>/', $html, $regDateMatch);

            return [
                'success'      => true,
                'gstin'        => $gstinMatch[1] ?? '',
                'legal_name'   => $legalNameMatch[1] ?? '',
                'status'       => $statusMatch[1] ?? '',
                'constitution' => $constitutionMatch[1] ?? '',
                'reg_date'     => $regDateMatch[1] ?? '',
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}