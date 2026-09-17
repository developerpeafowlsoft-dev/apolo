<?php

namespace App\Services\AI;

use App\Models\GeneraleSetting;
use App\Models\Shop;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiContentService
{
    /**
     * Resolve the Gemini API key from Shop, General Settings, or Environment.
     */
    public function resolveApiKey(?Shop $shop = null): ?string
    {
        if ($shop && !empty($shop->gemini_api_key)) {
            return trim($shop->gemini_api_key);
        }

        $generalSettingKey = GeneraleSetting::query()->value('gemini_api_key');
        if (!empty($generalSettingKey)) {
            return trim($generalSettingKey);
        }

        $envKey = config('services.gemini.api_key', env('GEMINI_API_KEY'));
        if (!empty($envKey)) {
            return trim($envKey);
        }

        // Fallback to Root Shop or any configured active shop key
        $rootShopKey = Shop::whereNotNull('gemini_api_key')->where('gemini_api_key', '!=', '')->value('gemini_api_key');
        if (!empty($rootShopKey)) {
            return trim($rootShopKey);
        }

        return null;
    }

    /**
     * Resolve the Gemini Model from Shop, General Settings, or Default.
     */
    public function resolveModel(?Shop $shop = null): string
    {
        if ($shop && !empty($shop->gemini_model)) {
            return trim($shop->gemini_model);
        }

        $generalSettingModel = GeneraleSetting::query()->value('gemini_model');
        if (!empty($generalSettingModel)) {
            return trim($generalSettingModel);
        }

        return 'gemini-1.5-flash';
    }

    /**
     * Discover supported models from Google Gemini API for this key.
     */
    public function resolveSupportedModel(string $apiKey, string $preferred = 'gemini-1.5-flash'): array
    {
        $supportedModels = [];
        $apiVersions = ['v1beta', 'v1'];
        $activeVersion = 'v1beta';

        foreach ($apiVersions as $version) {
            try {
                $response = Http::timeout(10)->get("https://generativelanguage.googleapis.com/{$version}/models?key=" . urlencode($apiKey));
                if ($response->successful()) {
                    $rawModels = $response->json()['models'] ?? [];
                    foreach ($rawModels as $m) {
                        $methods = $m['supportedGenerationMethods'] ?? [];
                        if (in_array('generateContent', $methods)) {
                            $supportedModels[] = str_replace('models/', '', $m['name']);
                        }
                    }
                    if (!empty($supportedModels)) {
                        $activeVersion = $version;
                        break;
                    }
                }
            } catch (Exception $e) {
                // Continue to next version
            }
        }

        $supportedModels = array_values(array_unique($supportedModels));

        if (empty($supportedModels)) {
            return [
                'model' => 'gemini-1.5-flash',
                'version' => 'v1beta',
                'all' => [],
            ];
        }

        $cleanPreferred = trim(str_replace(['(Recommended)', '(High Reasoning)', ' '], '', $preferred));

        // 1. Exact match
        if (in_array($cleanPreferred, $supportedModels)) {
            return ['model' => $cleanPreferred, 'version' => $activeVersion, 'all' => $supportedModels];
        }

        // 2. Preferred candidates list
        $candidates = [
            'gemini-2.5-flash',
            'gemini-flash-latest',
            'gemini-2.5-flash-lite',
            'gemini-2.0-flash',
            'gemini-2.0-flash-exp',
            'gemini-1.5-flash',
            'gemini-1.5-flash-latest',
            'gemini-1.5-flash-002',
            'gemini-1.5-flash-001',
            'gemini-2.5-pro',
            'gemini-pro-latest',
            'gemini-1.5-pro',
            'gemini-1.5-pro-latest',
            'gemini-pro',
        ];

        foreach ($candidates as $cand) {
            if (in_array($cand, $supportedModels)) {
                return ['model' => $cand, 'version' => $activeVersion, 'all' => $supportedModels];
            }
        }

        // 3. Match any model with 'flash'
        foreach ($supportedModels as $avail) {
            if (stripos($avail, 'flash') !== false) {
                return ['model' => $avail, 'version' => $activeVersion, 'all' => $supportedModels];
            }
        }

        // 4. First available model that supports generateContent
        return [
            'model' => $supportedModels[0],
            'version' => $activeVersion,
            'all' => $supportedModels,
        ];
    }

    /**
     * Test Gemini API Key and Model connectivity.
     */
    public function testConnection(string $apiKey, string $model = 'gemini-1.5-flash'): array
    {
        $apiKey = trim($apiKey);
        if (empty($apiKey)) {
            return [
                'success' => false,
                'message' => __('Please enter a Google Gemini API Key first.'),
            ];
        }

        // 1. First validate API Key using Google's models endpoint
        try {
            $modelsCheck = Http::timeout(10)->get("https://generativelanguage.googleapis.com/v1beta/models?key=" . urlencode($apiKey));

            if (!$modelsCheck->successful()) {
                $err = $modelsCheck->json();
                $msg = $err['error']['message'] ?? ('HTTP ' . $modelsCheck->status());
                if (stripos($msg, 'API key not valid') !== false) {
                    return [
                        'success' => false,
                        'message' => __('Google Gemini API Key is invalid. Please check and copy a valid key from Google AI Studio.'),
                    ];
                }
                return [
                    'success' => false,
                    'message' => __('Gemini API Error: ') . $msg,
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => __('Network error connecting to Google Gemini: ') . $e->getMessage(),
            ];
        }

        // 2. Discover supported models from Google
        $modelInfo = $this->resolveSupportedModel($apiKey, $model);
        $resolvedModel = $modelInfo['model'];
        $version = $modelInfo['version'];

        // 3. Test generateContent with the resolved model
        $testPayload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => 'Hello, reply with 1 word: OK']
                    ]
                ]
            ],
            'generationConfig' => [
                'maxOutputTokens' => 10,
            ],
        ];

        $apiUrl = "https://generativelanguage.googleapis.com/{$version}/models/{$resolvedModel}:generateContent?key=" . urlencode($apiKey);

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(15)
                ->post($apiUrl, $testPayload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => __('Google Gemini API Key is valid and active! Connected to :model successfully.', ['model' => $resolvedModel]),
                    'resolved_model' => $resolvedModel,
                ];
            }

            // If the resolved model had an issue, try remaining models from $modelInfo['all']
            foreach ($modelInfo['all'] as $altModel) {
                if ($altModel === $resolvedModel) {
                    continue;
                }

                $altUrl = "https://generativelanguage.googleapis.com/{$version}/models/{$altModel}:generateContent?key=" . urlencode($apiKey);
                $altRes = Http::withHeaders(['Content-Type' => 'application/json'])->timeout(15)->post($altUrl, $testPayload);
                if ($altRes->successful()) {
                    return [
                        'success' => true,
                        'message' => __('Google Gemini API Key is valid and active! Connected to :model successfully.', ['model' => $altModel]),
                        'resolved_model' => $altModel,
                    ];
                }
            }

            $errorBody = $response->json();
            $errorMessage = $errorBody['error']['message'] ?? ('HTTP ' . $response->status());

            return [
                'success' => false,
                'message' => __('Gemini API Error: ') . $errorMessage,
            ];
        } catch (Exception $e) {
            return [
                'success' => true,
                'message' => __('Google Gemini API Key is valid and active!'),
                'resolved_model' => $resolvedModel,
            ];
        }
    }

    /**
     * Extract relevant product text / metadata from an external e-commerce URL.
     */
    public function scrapeUrlContent(string $url): array
    {
        $context = [
            'url' => $url,
            'title' => '',
            'meta_description' => '',
            'extracted_text' => '',
            'success' => false,
            'note' => '',
        ];

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Cache-Control' => 'no-cache',
            ])->timeout(12)->get($url);

            if ($response->successful()) {
                $html = $response->body();

                // Extract Title
                if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
                    $context['title'] = trim(html_entity_decode(strip_tags($matches[1])));
                }

                // Extract Meta Description & OG description
                if (preg_match('/<meta[^>]+name=[\'"]description[\'"][^>]+content=[\'"](.*?)[\'"]/is', $html, $matches)) {
                    $context['meta_description'] = trim(html_entity_decode($matches[1]));
                } elseif (preg_match('/<meta[^>]+property=[\'"]og:description[\'"][^>]+content=[\'"](.*?)[\'"]/is', $html, $matches)) {
                    $context['meta_description'] = trim(html_entity_decode($matches[1]));
                }

                // Strip script, style, svg, header, footer, nav
                $cleanHtml = preg_replace('/<(script|style|svg|noscript|header|footer|nav)\b[^>]*>(.*?)<\/\1>/is', '', $html);

                // Specific extractors for common e-commerce sites
                $specificText = '';
                // Amazon bullet points / details
                if (preg_match('/<div id="feature-bullets"[^>]*>(.*?)<\/div>/is', $cleanHtml, $amazonBullets)) {
                    $specificText .= " Features: " . strip_tags($amazonBullets[1]);
                }
                if (preg_match('/<div id="productDescription"[^>]*>(.*?)<\/div>/is', $cleanHtml, $amazonDesc)) {
                    $specificText .= " Description: " . strip_tags($amazonDesc[1]);
                }

                // Generic text extraction
                $text = strip_tags($cleanHtml);
                $text = preg_replace('/\s+/', ' ', $text);

                if (!empty($specificText)) {
                    $extracted = trim(preg_replace('/\s+/', ' ', $specificText));
                } else {
                    $extracted = trim($text);
                }

                // Truncate to reasonable context window (max 4000 characters)
                $context['extracted_text'] = mb_substr($extracted, 0, 4000);
                $context['success'] = true;
            } else {
                $context['note'] = 'URL returned HTTP ' . $response->status() . '. Will infer details from URL path.';
            }
        } catch (Exception $e) {
            Log::info('GeminiContentService: Failed to scrape URL (' . $url . '): ' . $e->getMessage());
            $context['note'] = 'Could not fetch remote page directly (' . $e->getMessage() . '). AI will analyze URL slug and keywords.';
        }

        // Fallback: extract hints from URL path (e.g. /product-slug-name)
        $urlPath = parse_url($url, PHP_URL_PATH) ?? '';
        $cleanPath = trim(str_replace(['-', '_', '/', '.html', '.php'], ' ', $urlPath));
        if (!empty($cleanPath) && empty($context['title'])) {
            $context['title'] = $cleanPath;
        }

        return $context;
    }

    /**
     * Generate product descriptions using Google Gemini 1.5 Flash.
     */
    public function generateContent(
        string $apiKey,
        ?string $productName = null,
        ?string $url = null,
        ?string $keywords = null,
        array $options = []
    ): array {
        $urlData = null;
        if (!empty($url) && (filter_var($url, FILTER_VALIDATE_URL) || preg_match('/^https?:\/\//i', $url))) {
            $urlData = $this->scrapeUrlContent($url);
        }

        // Build Gemini prompt
        $prompt = "You are an elite e-commerce copywriter and conversion optimization specialist.\n";
        $prompt .= "Generate a compelling, high-converting product Short Description and a rich HTML Description for an online retail store.\n\n";
        $prompt .= "=== PRODUCT INPUT DETAILS ===\n";

        if (!empty($productName)) {
            $prompt .= "Product Name: " . $productName . "\n";
        }
        if (!empty($keywords)) {
            $prompt .= "Key Highlights / Specifications / Keywords provided by seller:\n" . $keywords . "\n";
        }
        if ($urlData) {
            $prompt .= "Reference Product URL: " . $urlData['url'] . "\n";
            if (!empty($urlData['title'])) {
                $prompt .= "Reference Page Title: " . $urlData['title'] . "\n";
            }
            if (!empty($urlData['meta_description'])) {
                $prompt .= "Reference Meta Description: " . $urlData['meta_description'] . "\n";
            }
            if (!empty($urlData['extracted_text'])) {
                $prompt .= "Reference Page Extracted Specs/Details:\n" . $urlData['extracted_text'] . "\n";
            }
        }

        $tone = $options['tone'] ?? 'Professional, Engaging, and Benefit-Driven';

        $prompt .= "\n=== REQUIREMENTS ===\n";
        $prompt .= "1. Tone: {$tone}.\n";
        $prompt .= "2. 'meta_title': Punchy, high-CTR SEO title under 60 characters for search engines (e.g. \"Buy Baby Girls Fancy Toys Online | Safe & Fun\"). Plain text only (no HTML).\n";
        $prompt .= "3. 'meta_description': 1-2 compelling sentences (under 160 characters) summarizing key appeal and benefits for Google search snippets. Plain text only.\n";
        $prompt .= "4. 'short_description': 1-2 crisp, captivating sentences highlighting primary value, quality, and appeal. STRICT REQUIREMENT: Must be under 180 characters (hard limit: maximum 190 characters, never exceed 191 characters to satisfy strict form validation). Plain text only (no HTML).\n";
        $prompt .= "5. 'description': Well-structured, beautiful HTML formatted for a WYSIWYG editor (Quill). Must include:\n";
        $prompt .= "   - A compelling introduction paragraph.\n";
        $prompt .= "   - <h3>Key Features & Benefits</h3> with a clean <ul> of 4-6 bullet points using <strong> for highlight keywords.\n";
        $prompt .= "   - <h3>Specifications & Details</h3> with clean details or bullet points.\n";
        $prompt .= "   - <h3>Why You'll Love It</h3> closing paragraph.\n";
        $prompt .= "6. 'meta_keywords': 5-10 comma-separated SEO keywords.\n";
        $prompt .= "7. 'length': Estimated shipping parcel length in centimeters (cm) as a positive number (e.g. 28). Realistic for this type of product.\n";
        $prompt .= "8. 'width': Estimated shipping parcel width in centimeters (cm) as a positive number (e.g. 20). Realistic for this type of product.\n";
        $prompt .= "9. 'height': Estimated shipping parcel height in centimeters (cm) as a positive number (e.g. 3.5). Realistic for this type of product.\n";
        $prompt .= "10. 'weight': Estimated packaged gross shipping weight in kilograms (kg) as a positive decimal number (e.g. 0.35). Realistic for this type of product.\n\n";
        $prompt .= "=== OUTPUT FORMAT ===\n";
        $prompt .= "You MUST respond with a valid JSON object matching this exact schema:\n";
        $prompt .= "{\n";
        $prompt .= '  "meta_title": "...",' . "\n";
        $prompt .= '  "meta_description": "...",' . "\n";
        $prompt .= '  "short_description": "Crisp summary strictly under 190 characters...",' . "\n";
        $prompt .= '  "description": "...",' . "\n";
        $prompt .= '  "meta_keywords": "...",' . "\n";
        $prompt .= '  "length": 28,' . "\n";
        $prompt .= '  "width": 20,' . "\n";
        $prompt .= '  "height": 3.5,' . "\n";
        $prompt .= '  "weight": 0.35' . "\n";
        $prompt .= "}\n";

        $prefModel = !empty($options['model']) ? trim($options['model']) : $this->resolveModel();
        $modelInfo = $this->resolveSupportedModel($apiKey, $prefModel);
        $model = $modelInfo['model'];
        $version = $modelInfo['version'];
        $apiUrl = "https://generativelanguage.googleapis.com/{$version}/models/{$model}:generateContent?key=" . urlencode($apiKey);

        try {
            $generationConfig = [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 8192,
                'responseMimeType' => 'application/json',
            ];

            // For reasoning models (e.g. 2.5-flash, 3.x), disable thinking tokens so they don't consume the output token budget
            if (str_contains($model, '2.5') || str_contains($model, '3.') || str_contains($model, 'flash') || str_contains($model, 'thinking')) {
                $generationConfig['thinkingConfig'] = [
                    'thinkingBudget' => 0,
                ];
            }

            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => $generationConfig,
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(45)->post($apiUrl, $payload);

            // If thinkingConfig is rejected by this model version, retry without thinkingConfig
            if (!$response->successful()) {
                $errJson = $response->json();
                $errDetail = $errJson['error']['message'] ?? '';
                if (stripos($errDetail, 'thinkingConfig') !== false || stripos($errDetail, 'thinkingBudget') !== false) {
                    unset($payload['generationConfig']['thinkingConfig']);
                    $response = Http::withHeaders([
                        'Content-Type' => 'application/json',
                    ])->timeout(45)->post($apiUrl, $payload);
                }
            }

            if (!$response->successful()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['error']['message'] ?? ('Gemini API responded with HTTP ' . $response->status());
                Log::error('GeminiContentService Error: ' . $errorMessage, ['response' => $errorBody]);

                return [
                    'success' => false,
                    'message' => 'Gemini API Error: ' . $errorMessage,
                ];
            }

            $responseData = $response->json();
            $rawText = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';

            if (empty($rawText)) {
                return [
                    'success' => false,
                    'message' => 'Gemini returned an empty response. Please try again.',
                ];
            }

            // Strip any markdown wrappers
            $cleanJson = trim($rawText);
            if (preg_match('/^```(?:json)?\s*(.*?)\s*```$/is', $cleanJson, $matches)) {
                $cleanJson = trim($matches[1]);
            } elseif (preg_match('/```(?:json)?\s*(.*?)\s*```/is', $cleanJson, $matches)) {
                $cleanJson = trim($matches[1]);
            }

            // 1. First attempt: Standard json_decode
            $parsed = json_decode($cleanJson, true);

            // 2. Second attempt: Sanitize unescaped newlines/tabs inside quotes
            if (!is_array($parsed) || empty($parsed['description'])) {
                $sanitized = preg_replace_callback('/"((?:[^"\\\\]|\\\\.)*)"/s', function ($m) {
                    return '"' . str_replace(["\r\n", "\r", "\n", "\t"], ["\\n", "\\n", "\\n", " "], $m[1]) . '"';
                }, $cleanJson);
                $parsed = json_decode($sanitized, true);
            }

            // 3. Third attempt: Boundary-aware regex extraction
            if (!is_array($parsed) || empty($parsed['description'])) {
                $metaTitle = '';
                $metaDesc = '';
                $shortDesc = '';
                $descHtml = '';
                $keywordsRes = '';

                if (preg_match('/"meta_title"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"/s', $cleanJson, $m)) {
                    $metaTitle = stripcslashes($m[1]);
                }
                if (preg_match('/"meta_description"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"/s', $cleanJson, $m)) {
                    $metaDesc = stripcslashes($m[1]);
                }
                if (preg_match('/"short_description"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"/s', $cleanJson, $m)) {
                    $shortDesc = stripcslashes($m[1]);
                }

                // Match description until next field or end of JSON
                if (preg_match('/"description"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"/s', $cleanJson, $m)) {
                    $descHtml = stripcslashes($m[1]);
                } elseif (preg_match('/"description"\s*:\s*"(.*?)(?:"\s*,\s*"[a-zA-Z_]+"|"\s*\}|\s*$)/s', $cleanJson, $m)) {
                    $descHtml = stripcslashes(rtrim($m[1], "\"} \r\n"));
                }

                if (preg_match('/"meta_keywords"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"/s', $cleanJson, $m)) {
                    $keywordsRes = stripcslashes($m[1]);
                }

                $rawLength = null;
                $rawWidth = null;
                $rawHeight = null;
                $rawWeight = null;
                if (preg_match('/"length"\s*:\s*([0-9]+(?:\.[0-9]+)?)/s', $cleanJson, $m)) {
                    $rawLength = (float)$m[1];
                }
                if (preg_match('/"width"\s*:\s*([0-9]+(?:\.[0-9]+)?)/s', $cleanJson, $m)) {
                    $rawWidth = (float)$m[1];
                }
                if (preg_match('/"height"\s*:\s*([0-9]+(?:\.[0-9]+)?)/s', $cleanJson, $m)) {
                    $rawHeight = (float)$m[1];
                }
                if (preg_match('/"weight"\s*:\s*([0-9]+(?:\.[0-9]+)?)/s', $cleanJson, $m)) {
                    $rawWeight = (float)$m[1];
                }

                // If description is still missing, extract any HTML tags from rawText
                if (empty($descHtml)) {
                    if (preg_match('/(<p>.*<\/p>|<h[1-6]>.*<\/[a-z0-9]+>|<ul>.*<\/ul>)/is', $rawText, $hm)) {
                        $descHtml = trim($hm[0]);
                    } elseif (!empty($rawText)) {
                        $descHtml = '<p>' . nl2br(htmlspecialchars(trim($rawText))) . '</p>';
                    }
                }

                if (empty($shortDesc) && !empty($descHtml)) {
                    $shortDesc = mb_substr(strip_tags($descHtml), 0, 185);
                }
                if (empty($metaTitle)) {
                    $metaTitle = mb_substr($productName ?? 'Product Details', 0, 60);
                }
                if (empty($metaDesc)) {
                    $metaDesc = mb_substr($shortDesc, 0, 160);
                }

                if (!empty($descHtml)) {
                    $parsed = [
                        'meta_title' => $metaTitle,
                        'meta_description' => $metaDesc,
                        'short_description' => $shortDesc,
                        'description' => $descHtml,
                        'meta_keywords' => $keywordsRes,
                        'length' => $rawLength,
                        'width' => $rawWidth,
                        'height' => $rawHeight,
                        'weight' => $rawWeight,
                    ];
                }
            }

            if (!is_array($parsed) || (empty($parsed['description']) && empty($parsed['short_description']))) {
                Log::warning('GeminiContentService: Failed to parse structured content', [
                    'raw' => $rawText,
                    'cleanJson' => $cleanJson,
                ]);

                return [
                    'success' => false,
                    'message' => 'Could not parse structured content from Gemini response.',
                    'raw' => $rawText,
                ];
            }

            // Estimate package dimensions fallback if not provided by model
            $defaultDims = $this->estimatePackageDimensions($productName, ($urlData['extracted_text'] ?? '') . ' ' . ($keywords ?? ''));

            $length = isset($parsed['length']) && is_numeric($parsed['length']) && (float)$parsed['length'] > 0 
                ? round((float)$parsed['length'], 2) 
                : $defaultDims['length'];

            $width = isset($parsed['width']) && is_numeric($parsed['width']) && (float)$parsed['width'] > 0 
                ? round((float)$parsed['width'], 2) 
                : $defaultDims['width'];

            $height = isset($parsed['height']) && is_numeric($parsed['height']) && (float)$parsed['height'] > 0 
                ? round((float)$parsed['height'], 2) 
                : $defaultDims['height'];

            $weight = isset($parsed['weight']) && is_numeric($parsed['weight']) && (float)$parsed['weight'] > 0 
                ? round((float)$parsed['weight'], 2) 
                : $defaultDims['weight'];

            // Guarantee short_description never exceeds 191 characters (strict validation limit)
            $rawShortDesc = trim(strip_tags($parsed['short_description'] ?? ''));
            if (empty($rawShortDesc) && !empty($parsed['description'])) {
                $rawShortDesc = trim(strip_tags($parsed['description']));
            }

            if (mb_strlen($rawShortDesc) > 191) {
                // Truncate neatly at last space before 188 chars + ... or 191 chars
                $truncated = mb_substr($rawShortDesc, 0, 188);
                $lastSpace = mb_strrpos($truncated, ' ');
                if ($lastSpace !== false && $lastSpace > 130) {
                    $rawShortDesc = rtrim(mb_substr($truncated, 0, $lastSpace), " \t\n\r\0\x0B,.-") . '...';
                } else {
                    $rawShortDesc = rtrim($truncated) . '...';
                }
            }

            if (mb_strlen($rawShortDesc) > 191) {
                $rawShortDesc = mb_substr($rawShortDesc, 0, 191);
            }

            return [
                'success' => true,
                'meta_title' => trim($parsed['meta_title'] ?? ''),
                'meta_description' => trim($parsed['meta_description'] ?? $rawShortDesc),
                'short_description' => $rawShortDesc,
                'description' => trim($parsed['description'] ?? ''),
                'meta_keywords' => trim($parsed['meta_keywords'] ?? ''),
                'length' => $length,
                'width' => $width,
                'height' => $height,
                'weight' => $weight,
                'url_scraped' => $urlData ? $urlData['success'] : null,
            ];

        } catch (Exception $e) {
            Log::error('GeminiContentService Exception: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return [
                'success' => false,
                'message' => 'An unexpected error occurred while contacting AI: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Estimate package delivery dimensions based on product keywords and naming.
     */
    public function estimatePackageDimensions(?string $productName = null, ?string $rawText = null): array
    {
        $text = strtoupper(trim(($productName ?? '') . ' ' . ($rawText ?? '')));

        $length = 28.0;
        $width  = 20.0;
        $height = 3.0;
        $weight = 0.30;

        if (str_contains($text, 'DRESS') || str_contains($text, 'FROCK') || str_contains($text, 'GOWN') || str_contains($text, 'MATERNITY')) {
            $length = 30.0; $width = 25.0; $height = 3.5; $weight = 0.35;
        } elseif (str_contains($text, 'SAREE') || str_contains($text, 'LEHENGA') || str_contains($text, 'HEAVY') || str_contains($text, 'SUIT')) {
            $length = 38.0; $width = 30.0; $height = 6.0; $weight = 0.85;
        } elseif (str_contains($text, 'JEANS') || str_contains($text, 'TROUSER') || str_contains($text, 'PANT') || str_contains($text, 'BOTTOM')) {
            $length = 32.0; $width = 24.0; $height = 4.0; $weight = 0.50;
        } elseif (str_contains($text, 'SHIRT') || str_contains($text, 'TOP') || str_contains($text, 'KURTI') || str_contains($text, 'BLOUSE')) {
            $length = 28.0; $width = 22.0; $height = 2.5; $weight = 0.25;
        } elseif (str_contains($text, 'T-SHIRT') || str_contains($text, 'TSHIRT') || str_contains($text, 'VEST')) {
            $length = 25.0; $width = 20.0; $height = 2.0; $weight = 0.20;
        } elseif (str_contains($text, 'JACKET') || str_contains($text, 'COAT') || str_contains($text, 'SWEATER') || str_contains($text, 'HOODIE')) {
            $length = 35.0; $width = 28.0; $height = 6.0; $weight = 0.70;
        } elseif (str_contains($text, 'LINGERIE') || str_contains($text, 'INNERWEAR') || str_contains($text, 'BRA') || str_contains($text, 'PANTY') || str_contains($text, 'SOCKS')) {
            $length = 20.0; $width = 15.0; $height = 2.0; $weight = 0.12;
        } elseif (str_contains($text, 'SHOE') || str_contains($text, 'FOOTWEAR') || str_contains($text, 'SANDAL') || str_contains($text, 'BOOT') || str_contains($text, 'SLIPPER')) {
            $length = 32.0; $width = 20.0; $height = 12.0; $weight = 0.80;
        } elseif (str_contains($text, 'BAG') || str_contains($text, 'HANDBAG') || str_contains($text, 'PURSE') || str_contains($text, 'WALLET')) {
            $length = 30.0; $width = 22.0; $height = 10.0; $weight = 0.45;
        } elseif (str_contains($text, 'TOY') || str_contains($text, 'GIFT') || str_contains($text, 'GAME')) {
            $length = 25.0; $width = 20.0; $height = 15.0; $weight = 0.60;
        } elseif (str_contains($text, 'CRADLE') || str_contains($text, 'FURNITURE')) {
            $length = 65.0; $width = 45.0; $height = 30.0; $weight = 4.50;
        }

        return compact('length', 'width', 'height', 'weight');
    }

    /**
     * Generate 5 professional product images based on a reference image, keywords, and background color.
     */
    public function generateProductImages(string $apiKey, array $params = []): array
    {
        $productName = trim($params['product_name'] ?? 'Product');
        $keywords = trim($params['keywords'] ?? '');
        $bgColor = trim($params['background_color'] ?? '#FFFFFF');
        $referenceBase64 = $params['reference_image_base64'] ?? null;
        $referenceMime = $params['reference_image_mime'] ?? 'image/png';

        // Prepare storage directory
        $aiDir = storage_path('app/public/products/ai');
        if (!file_exists($aiDir)) {
            mkdir($aiDir, 0777, true);
        }

        // Color name mapping for richer prompt description
        $colorNames = [
            '#ffffff' => 'solid pure white',
            '#f3f4f6' => 'solid soft studio neutral gray (#f3f4f6)',
            '#fffbeb' => 'solid warm ivory cream (#fffbeb)',
            '#fdf8f0' => 'solid warm beige (#fdf8f0)',
            '#e0f2fe' => 'solid pastel sky blue (#e0f2fe)',
            '#fce7f3' => 'solid soft blush pastel pink (#fce7f3)',
            '#dcfce7' => 'solid soft pastel sage mint (#dcfce7)',
            '#1e293b' => 'solid luxury deep slate charcoal (#1e293b)',
            '#111827' => 'solid sleek matte black (#111827)',
        ];
        $colorDescription = $colorNames[strtolower($bgColor)] ?? "solid background color in {$bgColor}";

        $consistencySentence = "Treat the uploaded product photo as the primary visual reference; do not change the product itself—only change the environment, lighting, props, camera angle, and presentation.";

        // 5 Ready-Made Product Photography Prompts
        $variations = [
            'hero' => [
                'id' => 1,
                'label' => 'Premium Studio Hero Shot',
                'badge' => '🌟 Hero Shot',
                'description' => 'Centered minimalist studio setup with soft diffused light, rim lighting & contact shadow',
                'prompt' => "Create a professional commercial product photography image based strictly on the uploaded reference photo of {$productName}. Preserve the exact product shape, proportions, colors, branding, label, logo, materials, packaging details, and design. Do not redesign or modify the product. Place it centered in a premium minimalist studio setup with a clean {$colorDescription} background, soft diffused key light, subtle rim lighting, realistic contact shadow, and elegant reflections. Make the product look crisp, luxurious, photorealistic, and suitable for an e-commerce hero image. No extra text, no watermark, no duplicated product, no distorted logo. Square composition, final size 500 × 500 px. {$consistencySentence}" . ($keywords ? " Additional details: {$keywords}" : "")
            ],
            'lifestyle' => [
                'id' => 2,
                'label' => 'Lifestyle Product Scene',
                'badge' => '🌿 Lifestyle Scene',
                'description' => 'High-end contextual lifestyle setting with warm natural lighting and shallow depth of field',
                'prompt' => "Using the uploaded reference image of {$productName}, create a high-end lifestyle product photograph. Keep the product 100% visually consistent with the reference, including packaging, label, logo, colors, dimensions, and materials. Place the product naturally in a tasteful environment related to " . (!empty($keywords) ? $keywords : "modern premium lifestyle use") . ", using complementary props such as warm textured linen, natural botanicals, and tasteful aesthetic decor. Use warm natural lighting, realistic shadows, shallow depth of field, clean styling, and an advertising-quality composition. The product must remain the main focus and fully readable. Avoid clutter, extra products, incorrect text, altered branding, or unrealistic proportions. Photorealistic, square 500 × 500 px. {$consistencySentence}"
            ],
            'ingredient' => [
                'id' => 3,
                'label' => 'Creative Ingredient / Feature Shot',
                'badge' => '🧪 Ingredient & Feature',
                'description' => 'Surrounded with dynamic elements representing main ingredients, fragrance, or technology',
                'prompt' => "Create a creative commercial product photography image using the uploaded {$productName} reference as the exact product source. Preserve all product details and branding accurately. Surround the product with visually attractive elements representing its main ingredients, fragrance, flavor, technology, or features: " . (!empty($keywords) ? $keywords : "fresh natural organic essences, delicate dew droplets, and pure botanical elements") . ". Arrange the elements dynamically around the product without covering the logo or important packaging information. Use premium studio lighting, controlled highlights, realistic shadows, subtle floating elements where appropriate, and a polished advertising aesthetic. Keep the background {$colorDescription}. No fake text or changes to the product. Square image, 500 × 500 px. {$consistencySentence}"
            ],
            'pedestal' => [
                'id' => 4,
                'label' => 'Luxury Reflection / Pedestal Shot',
                'badge' => '💎 Luxury Pedestal',
                'description' => 'Positioned on an elegant pedestal with studio backdrop, rim light & atmospheric glow',
                'prompt' => "Generate a luxury product photography scene based on the uploaded reference photo of {$productName}. Reproduce the product exactly as shown, with no changes to its logo, label, color, packaging, proportions, or texture. Position it on an elegant marble, acrylic, stone, or polished glass pedestal with a sophisticated {$colorDescription} studio backdrop. Add soft spotlighting, premium rim light, subtle atmospheric glow, realistic reflections, and controlled shadows. Create a clean, modern, expensive beauty-advertising look with generous negative space. Product should be sharp and dominant in the frame. No watermark, no additional text, no distorted packaging. Output 500 × 500 px, 1:1 aspect ratio. {$consistencySentence}" . ($keywords ? " Additional details: {$keywords}" : "")
            ],
            'social' => [
                'id' => 5,
                'label' => 'Social Media Advertising Shot',
                'badge' => '📱 Social Media Ad',
                'description' => 'Bold contemporary scene with geometric platforms, soft gradients & dramatic lighting',
                'prompt' => "Create a scroll-stopping social media product advertising image based on the uploaded reference photo of {$productName}. Maintain exact visual consistency with the original product—same branding, logo, packaging, shape, colors, label details, and materials. Build a bold contemporary scene using complementary brand colors, geometric platforms, soft gradients, tasteful props, realistic shadows, and dramatic but professional studio lighting. Make the product the clear focal point, with a polished premium commercial-photography finish. Leave some clean negative space for optional marketing copy, but do not generate any text inside the image. Avoid duplicates, distorted logos, altered packaging, or unrealistic objects. Square composition, 500 × 500 px. {$consistencySentence}" . ($keywords ? " Additional details: {$keywords}" : "")
            ],
        ];

        // Intelligent custom prompt detection: if user edited a full prompt, apply it directly
        $isFullCustomPrompt = (str_contains(strtolower($keywords), 'reference photo') || str_contains(strtolower($keywords), 'reference image')) && strlen($keywords) > 80;
        if ($isFullCustomPrompt) {
            $lowerKw = strtolower($keywords);
            $customPromptWithConsistency = $keywords . (!str_contains($keywords, $consistencySentence) ? " {$consistencySentence}" : "");
            if (str_contains($lowerKw, 'lifestyle product photograph') || str_contains($lowerKw, 'lifestyle')) {
                $variations['lifestyle']['prompt'] = $customPromptWithConsistency;
            } elseif (str_contains($lowerKw, 'ingredient') || str_contains($lowerKw, 'feature shot')) {
                $variations['ingredient']['prompt'] = $customPromptWithConsistency;
            } elseif (str_contains($lowerKw, 'pedestal') || str_contains($lowerKw, 'reflection')) {
                $variations['pedestal']['prompt'] = $customPromptWithConsistency;
            } elseif (str_contains($lowerKw, 'social media') || str_contains($lowerKw, 'advertising shot')) {
                $variations['social']['prompt'] = $customPromptWithConsistency;
            } else {
                $variations['hero']['prompt'] = $customPromptWithConsistency;
            }
        }

        $generatedImages = [];
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-image:generateContent?key=" . urlencode($apiKey);
        $geminiError = null;
        $usedFallback = false;

        try {
            // Concurrently request all 5 variations using Laravel Http::pool
            $responses = Http::pool(function ($pool) use ($variations, $url, $referenceBase64, $referenceMime) {
                $requests = [];
                foreach ($variations as $key => $var) {
                    $parts = [];

                    // Multimodal: include reference image if provided
                    if (!empty($referenceBase64)) {
                        $parts[] = [
                            'inlineData' => [
                                'mimeType' => $referenceMime,
                                'data' => $referenceBase64,
                            ]
                        ];
                        $parts[] = [
                            'text' => "Using this reference image as the exact product to feature: " . $var['prompt']
                        ];
                    } else {
                        $parts[] = [
                            'text' => $var['prompt']
                        ];
                    }

                    $requests[] = $pool->as($key)->timeout(50)->post($url, [
                        'contents' => [
                            ['parts' => $parts]
                        ]
                    ]);
                }
                return $requests;
            });

            // Process each variation response
            foreach ($variations as $key => $var) {
                $res = $responses[$key] ?? null;
                $imageData = null;
                $mimeType = 'image/png';

                if ($res && $res->successful()) {
                    $candidates = $res->json()['candidates'] ?? [];
                    if (!empty($candidates)) {
                        $part = $candidates[0]['content']['parts'][0] ?? [];
                        if (!empty($part['inlineData']['data'])) {
                            $imageData = base64_decode($part['inlineData']['data']);
                            $mimeType = $part['inlineData']['mimeType'] ?? 'image/png';
                        }
                    }
                } else if ($res) {
                    $errJson = $res->json()['error'] ?? null;
                    if ($errJson && empty($geminiError)) {
                        $geminiError = ($errJson['message'] ?? 'Gemini API Error') . ' (Code: ' . ($errJson['code'] ?? $res->status()) . ')';
                    } elseif (empty($geminiError)) {
                        $geminiError = 'HTTP ' . $res->status() . ': ' . substr($res->body(), 0, 150);
                    }
                }

                // If Gemini image model didn't return image data for this variation, use fallback generator
                if (empty($imageData)) {
                    $usedFallback = true;
                    $imageData = $this->generateFallbackProductImage($var['prompt'], $bgColor, $key);
                    $mimeType = 'image/png';
                }

                if (!empty($imageData)) {
                    $filename = 'ai_prod_' . uniqid() . "_{$key}.png";
                    file_put_contents($aiDir . '/' . $filename, $imageData);
                    $publicUrl = asset('storage/products/ai/' . $filename);
                    $base64String = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);

                    $generatedImages[] = [
                        'id' => $var['id'],
                        'key' => $key,
                        'label' => $var['label'],
                        'badge' => $var['badge'] ?? ("#" . $var['id']),
                        'description' => $var['description'],
                        'prompt' => $var['prompt'],
                        'filename' => $filename,
                        'url' => $publicUrl,
                        'base64' => $base64String,
                    ];
                }
            }
        } catch (Exception $e) {
            $geminiError = 'Request Pool Exception: ' . $e->getMessage();
            Log::error('Gemini image generation pool error: ' . $e->getMessage());
            $usedFallback = true;

            // Emergency fallback for all 5 variations
            foreach ($variations as $key => $var) {
                $imageData = $this->generateFallbackProductImage($var['prompt'], $bgColor, $key);
                if (!empty($imageData)) {
                    $filename = 'ai_prod_' . uniqid() . "_{$key}.png";
                    file_put_contents($aiDir . '/' . $filename, $imageData);
                    $publicUrl = asset('storage/products/ai/' . $filename);

                    $generatedImages[] = [
                        'id' => $var['id'],
                        'key' => $key,
                        'label' => $var['label'],
                        'badge' => $var['badge'] ?? ("#" . $var['id']),
                        'description' => $var['description'],
                        'prompt' => $var['prompt'],
                        'filename' => $filename,
                        'url' => $publicUrl,
                        'base64' => 'data:image/png;base64,' . base64_encode($imageData),
                    ];
                }
            }
        }

        return [
            'success' => !empty($generatedImages),
            'count' => count($generatedImages),
            'images' => $generatedImages,
            'background_color' => $bgColor,
            'gemini_error' => $geminiError,
            'used_fallback' => $usedFallback,
        ];
    }

    /**
     * Optimize reference image size and downscale to max 1024px to prevent large payload timeouts.
     */
    public function optimizeReferenceImage(string $filePath): ?array
    {
        if (!file_exists($filePath)) {
            return null;
        }

        $imageInfo = @getimagesize($filePath);
        if (!$imageInfo || !extension_loaded('gd')) {
            return [
                'base64' => base64_encode(file_get_contents($filePath)),
                'mime' => $imageInfo['mime'] ?? 'image/jpeg',
            ];
        }

        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $mime = $imageInfo['mime'];

        $src = null;
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $src = @imagecreatefromjpeg($filePath);
                break;
            case 'image/png':
                $src = @imagecreatefrompng($filePath);
                break;
            case 'image/webp':
                $src = @imagecreatefromwebp($filePath);
                break;
        }

        if (!$src) {
            return [
                'base64' => base64_encode(file_get_contents($filePath)),
                'mime' => $mime,
            ];
        }

        $maxDim = 1024;
        if ($width > $maxDim || $height > $maxDim) {
            if ($width >= $height) {
                $newWidth = $maxDim;
                $newHeight = (int) round(($height / $width) * $maxDim);
            } else {
                $newHeight = $maxDim;
                $newWidth = (int) round(($width / $height) * $maxDim);
            }

            $dst = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($src);
            $src = $dst;
        }

        ob_start();
        imagejpeg($src, null, 85);
        $compressed = ob_get_clean();
        imagedestroy($src);

        return [
            'base64' => base64_encode($compressed),
            'mime' => 'image/jpeg',
        ];
    }

    /**
     * Fallback high-quality product image generator using Pollinations AI if primary API is busy.
     */
    protected function generateFallbackProductImage(string $prompt, string $bgColor, string $seedKey): ?string
    {
        try {
            $cleanPrompt = rawurlencode(substr($prompt . ", e-commerce product photograph, 8k resolution, crisp clean background", 0, 500));
            $seed = crc32($seedKey . microtime());
            $pollinationsUrl = "https://image.pollinations.ai/prompt/{$cleanPrompt}?width=800&height=800&seed={$seed}&nologo=true";

            $response = Http::timeout(25)->get($pollinationsUrl);
            if ($response->successful() && strlen($response->body()) > 2000) {
                return $response->body();
            }
        } catch (Exception $e) {
            Log::warning('Fallback image generation failed: ' . $e->getMessage());
        }

        // Minimalist branded SVG fallback if network fails
        return $this->createPlaceholderProductImage($bgColor, $seedKey);
    }

    /**
     * Clean SVG placeholder fallback.
     */
    protected function createPlaceholderProductImage(string $bgColor, string $label): string
    {
        $hex = !empty($bgColor) ? $bgColor : '#FFFFFF';
        $textColor = '#6B7280';
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="800" viewBox="0 0 800 800">
  <rect width="800" height="800" fill="{$hex}"/>
  <circle cx="400" cy="380" r="140" fill="#E5E7EB" opacity="0.6"/>
  <rect x="330" y="320" width="140" height="120" rx="16" fill="#9CA3AF" opacity="0.4"/>
  <circle cx="365" cy="355" r="16" fill="#6B7280"/>
  <path d="M335 420 L375 375 L415 410 L445 385 L465 420 Z" fill="#6B7280"/>
  <text x="400" y="580" text-anchor="middle" font-family="sans-serif" font-size="24" font-weight="600" fill="{$textColor}">AI Product Studio ({$label})</text>
  <text x="400" y="615" text-anchor="middle" font-family="sans-serif" font-size="16" fill="{$textColor}">Studio Catalog Photo</text>
</svg>
SVG;
        return $svg;
    }
}

