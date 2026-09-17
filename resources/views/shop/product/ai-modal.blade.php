<!-- AI Product Content Generator Modal with Glassmorphism UI -->
<div class="modal fade ai-glassmorphism-modal" id="aiGenerateModal" tabindex="-1" aria-labelledby="aiGenerateModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content ai-glass-card border-0 shadow-2xl rounded-4 overflow-hidden position-relative">
            
            <!-- Ambient Gradient Glow Orbs in Background for Glassmorphism depth -->
            <div class="ai-glass-orb ai-glass-orb-1"></div>
            <div class="ai-glass-orb ai-glass-orb-2"></div>

            <!-- Modal Header: Luxurious Frosted Glass Header -->
            <div class="modal-header ai-glass-header py-3 px-4 position-relative z-1">
                <div class="d-flex align-items-center gap-3">
                    <div class="ai-header-icon-badge shadow-sm">
                        <i class="bi bi-stars"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="modal-title fw-bold text-dark mb-0" id="aiGenerateModalLabel" style="font-size: 16.5px; letter-spacing: -0.2px;">
                                {{ __('AI Product Content Generator') }}
                            </h5>
                            <span class="ai-badge-gemini">
                                <span class="ai-pulse-dot"></span>
                                {{ __('Gemini AI Engine') }}
                            </span>
                        </div>
                        <p class="text-muted mb-0 small" style="font-size: 11.5px; margin-top: 2px;">
                            {{ __('Instantly generate conversion-optimized Short Descriptions & rich HTML content') }}
                        </p>
                    </div>
                </div>
                <button type="button" class="btn-close ai-glass-close-btn" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 position-relative z-1">
                
                <!-- Smart Assistant Tip Banner (Frosted Glass Pill Card) -->
                <div class="ai-glass-banner p-3 mb-3 d-flex gap-3 align-items-start">
                    <div class="ai-banner-icon-box flex-shrink-0">
                        <i class="bi bi-lightbulb-fill"></i>
                    </div>
                    <div style="font-size: 12.5px; line-height: 1.55; color: #334155;">
                        <span class="fw-bold text-dark">{{ __('Smart Content Assistant:') }}</span>
                        {{ __('Paste any product URL from Amazon, Flipkart, Myntra, or your supplier\'s site, or enter raw product keywords. AI will analyze the details and automatically craft an attractive Short Description and structured HTML Description for your store.') }}
                    </div>
                </div>

                <!-- Missing API Key Alert (Dynamically shown if key is missing) -->
                <div class="alert alert-warning border-0 bg-warning-subtle text-dark rounded-3 p-3 mb-3 d-none shadow-xs" id="ai_api_key_alert" style="font-size: 12.5px;">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                            <div>
                                <strong>{{ __('Google Gemini API Key Required:') }}</strong>
                                <span id="ai_api_key_alert_msg">{{ __('Please set your Gemini API key in Store Profile.') }}</span>
                            </div>
                        </div>
                        <a href="{{ route('shop.profile.edit') }}" target="_blank" class="btn btn-sm btn-dark px-3 py-1.5 rounded-pill shadow-xs" style="font-size: 11.5px; font-weight: 600;">
                            <i class="bi bi-gear me-1"></i>{{ __('Configure in Profile') }}
                        </a>
                    </div>
                </div>

                <!-- Input Form Section (Frosted Inner Glass Panel) -->
                <div class="ai-glass-inner-card p-3.5 mb-3">
                    
                    <!-- Reference URL Input -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small d-flex align-items-center justify-content-between mb-1.5">
                            <span class="d-flex align-items-center gap-1.5">
                                <i class="bi bi-link-45deg text-primary fs-5"></i>
                                <span>{{ __('Reference Product Website URL') }}</span>
                                <span class="text-muted fw-normal">({{ __('Amazon, Flipkart, Myntra, Supplier, etc.') }})</span>
                            </span>
                            <span class="badge bg-light-subtle text-secondary border px-2 py-0.5 rounded-pill" style="font-size: 10px; font-weight: 600;">{{ __('Optional') }}</span>
                        </label>
                        <div class="input-group ai-glass-input-group">
                            <span class="input-group-text ai-glass-addon"><i class="bi bi-globe2"></i></span>
                            <input type="url" class="form-control ai-glass-input" id="ai_input_url" 
                                placeholder="https://www.amazon.in/dp/... or https://www.flipkart.com/..." 
                                style="font-size: 13px;">
                        </div>
                        <small class="text-muted d-block mt-1 ps-1" style="font-size: 11px;">
                            <i class="bi bi-info-circle me-1"></i>{{ __('The AI will scrape title, features, specifications, and bullet points directly from the link.') }}
                        </small>
                    </div>

                    <!-- Keywords & Highlights -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small d-flex align-items-center justify-content-between mb-1.5">
                            <span class="d-flex align-items-center gap-1.5">
                                <i class="bi bi-card-text text-primary"></i>
                                <span>{{ __('Basic Information, Keywords & Highlights') }}</span>
                            </span>
                            <span class="badge bg-light-subtle text-secondary border px-2 py-0.5 rounded-pill" style="font-size: 10px; font-weight: 600;">{{ __('Optional if URL provided') }}</span>
                        </label>
                        <textarea class="form-control ai-glass-input" id="ai_input_keywords" rows="3" 
                            placeholder="{{ __('e.g. 100% Pure Cotton, breathable fabric, vibrant floral prints, machine washable, party wear for kids 2-8 years, durable stitching...') }}"
                            style="font-size: 13px;"></textarea>
                    </div>

                    <!-- Tone of Voice & Generate Action Button -->
                    <div class="row g-2.5 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small mb-1.5 d-flex align-items-center gap-1.5">
                                <i class="bi bi-palette text-primary"></i>
                                <span>{{ __('Tone & Style') }}</span>
                            </label>
                            <select class="form-select ai-glass-input" id="ai_input_tone" style="font-size: 12.5px; height: 40px;">
                                <option value="Professional, Engaging, and Benefit-Driven" selected>{{ __('Engaging & Benefit-Driven (Recommended)') }}</option>
                                <option value="Luxury, Elegant, and Premium">{{ __('Luxury & Premium') }}</option>
                                <option value="Playful, Trendy, and Vibrant">{{ __('Playful & Trendy') }}</option>
                                <option value="Direct, Concise, and Specification-Focused">{{ __('Concise & Spec-Focused') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn ai-btn-generate w-100 d-flex align-items-center justify-content-center gap-2" id="btn_run_ai_generate">
                                <i class="bi bi-stars"></i>
                                <span id="btn_run_ai_text">{{ __('Generate with Gemini AI') }}</span>
                                <div class="spinner-border spinner-border-sm text-light d-none" id="ai_spinner" role="status"></div>
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Error Notice (General) -->
                <div class="alert alert-danger border-0 bg-danger-subtle text-danger rounded-3 p-3 mb-3 d-none shadow-xs" id="ai_error_notice" style="font-size: 12.5px;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-octagon-fill fs-5 flex-shrink-0"></i>
                        <span id="ai_error_text"></span>
                    </div>
                </div>

                <!-- Result Preview Section (Frosted Glass Result Card) -->
                <div class="ai-glass-result-card p-3.5 d-none position-relative" id="ai_preview_card">
                    
                    <div class="d-flex align-items-center justify-content-between border-bottom pb-2.5 mb-3" style="border-color: rgba(226, 232, 240, 0.8) !important;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="ai-success-icon-box">
                                <i class="bi bi-check2-circle"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark m-0" style="font-size: 14.5px;">{{ __('Generated Content Preview') }}</h6>
                                <small class="text-muted" style="font-size: 11px;">{{ __('Review before applying to your product') }}</small>
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill d-flex align-items-center gap-1" style="font-size: 11px; font-weight: 600;">
                            <i class="bi bi-lightning-charge-fill text-warning"></i>{{ __('Ready to Apply') }}
                        </span>
                    </div>

                    <!-- Short Description Preview -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small d-flex align-items-center justify-content-between mb-1">
                            <span class="d-flex align-items-center gap-1.5">
                                <i class="bi bi-chat-left-quote-fill text-primary"></i>
                                <span>{{ __('Short Description') }}</span>
                            </span>
                            <span class="badge bg-light text-secondary border fw-medium" id="ai_short_desc_counter" style="font-size: 11px;">0 / 191</span>
                        </label>
                        <textarea class="form-control ai-glass-input" id="ai_preview_short_desc" rows="2" maxlength="191" placeholder="{{ __('Crisp summary strictly under 191 characters...') }}" style="font-size: 13px;"></textarea>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted" style="font-size: 11px;">{{ __('Strictly max 191 characters to match validation rules.') }}</small>
                            <small class="text-muted fw-normal" style="font-size: 11px;">{{ __('Editable before applying') }}</small>
                        </div>
                    </div>

                    <!-- Rich HTML Description Preview -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                            <label class="form-label fw-bold text-dark small m-0 d-flex align-items-center gap-1.5">
                                <i class="bi bi-file-earmark-richtext-fill text-primary"></i>
                                <span>{{ __('Rich HTML Description') }}</span>
                            </label>
                            <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none text-primary fw-semibold" id="btn_toggle_ai_raw_html" style="font-size: 11.5px;">
                                <i class="bi bi-code-slash me-1"></i><span>{{ __('View HTML Source') }}</span>
                            </button>
                        </div>
                        
                        <!-- Formatted Preview Box -->
                        <div class="ai-preview-rendered-box p-3 overflow-auto" id="ai_preview_rendered_html"></div>
                        
                        <!-- Raw HTML Textarea (Toggleable) -->
                        <textarea class="form-control font-monospace ai-glass-input d-none mt-2" id="ai_preview_raw_html" rows="6" style="font-size: 12px;"></textarea>
                    </div>

                    <!-- SEO & Search Engine Optimization Preview -->
                    <div class="border-top pt-3 mt-3" style="border-color: rgba(226, 232, 240, 0.8) !important;">
                        <div class="d-flex align-items-center gap-1.5 mb-2.5">
                            <i class="bi bi-search text-primary fs-6"></i>
                            <h6 class="fw-bold text-dark m-0" style="font-size: 13.5px;">{{ __('SEO & Search Engine Optimization') }}</h6>
                        </div>

                        <!-- Meta Title Preview -->
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small d-flex align-items-center justify-content-between mb-1">
                                <span class="d-flex align-items-center gap-1.5">
                                    <span>{{ __('Meta Title') }}</span>
                                </span>
                                <small class="text-muted fw-normal" style="font-size: 11px;">{{ __('Google search title') }}</small>
                            </label>
                            <input type="text" class="form-control ai-glass-input" id="ai_preview_meta_title" placeholder="{{ __('Meta Title') }}" style="font-size: 13px;">
                        </div>

                        <!-- Meta Description Preview -->
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small d-flex align-items-center justify-content-between mb-1">
                                <span class="d-flex align-items-center gap-1.5">
                                    <span>{{ __('Meta Description') }}</span>
                                </span>
                                <small class="text-muted fw-normal" style="font-size: 11px;">{{ __('Google search snippet') }}</small>
                            </label>
                            <textarea class="form-control ai-glass-input" id="ai_preview_meta_desc" rows="2" style="font-size: 13px;"></textarea>
                        </div>

                        <!-- SEO Meta Keywords Preview -->
                        <div class="mb-1" id="ai_preview_keywords_section">
                            <label class="form-label fw-bold text-dark small mb-1.5 d-flex align-items-center gap-1.5">
                                <i class="bi bi-tags-fill text-primary"></i>
                                <span>{{ __('Suggested SEO Meta Keywords') }}</span>
                            </label>
                            <div id="ai_preview_keywords_badges" class="d-flex flex-wrap gap-1.5 mb-1"></div>
                            <input type="hidden" id="ai_preview_keywords_input">
                        </div>
                    </div>

                    <!-- Delivery Dimensions Preview Section -->
                    <div class="border-top pt-3 mt-3" style="border-color: rgba(226, 232, 240, 0.8) !important;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-1.5">
                                <i class="bi bi-truck text-primary fs-6"></i>
                                <h6 class="fw-bold text-dark m-0" style="font-size: 13.5px;">{{ __('Estimated Delivery Dimensions') }}</h6>
                            </div>
                            <small class="text-muted" style="font-size: 11px;">{{ __('Applied directly to shipping package fields') }}</small>
                        </div>

                        <div class="row g-2">
                            <div class="col-6 col-sm-3">
                                <label class="form-label text-dark fw-semibold small mb-1" style="font-size: 11.5px;">{{ __('Length (cm)') }}</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" min="0" class="form-control ai-glass-input font-monospace fw-bold" id="ai_preview_length" placeholder="28">
                                    <span class="input-group-text bg-white" style="font-size: 10.5px;">cm</span>
                                </div>
                            </div>
                            <div class="col-6 col-sm-3">
                                <label class="form-label text-dark fw-semibold small mb-1" style="font-size: 11.5px;">{{ __('Width (cm)') }}</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" min="0" class="form-control ai-glass-input font-monospace fw-bold" id="ai_preview_width" placeholder="20">
                                    <span class="input-group-text bg-white" style="font-size: 10.5px;">cm</span>
                                </div>
                            </div>
                            <div class="col-6 col-sm-3">
                                <label class="form-label text-dark fw-semibold small mb-1" style="font-size: 11.5px;">{{ __('Height (cm)') }}</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" min="0" class="form-control ai-glass-input font-monospace fw-bold" id="ai_preview_height" placeholder="3">
                                    <span class="input-group-text bg-white" style="font-size: 10.5px;">cm</span>
                                </div>
                            </div>
                            <div class="col-6 col-sm-3">
                                <label class="form-label text-dark fw-semibold small mb-1" style="font-size: 11.5px;">{{ __('Weight (kg)') }}</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" min="0" class="form-control ai-glass-input font-monospace fw-bold" id="ai_preview_weight" placeholder="0.30">
                                    <span class="input-group-text bg-white" style="font-size: 10.5px;">kg</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Modal Footer (Frosted Glass Footer) -->
            <div class="modal-footer ai-glass-footer py-3 px-4 d-flex justify-content-between position-relative z-1">
                <button type="button" class="btn ai-btn-cancel px-3 py-1.5" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1 small"></i>{{ __('Close') }}
                </button>
                <button type="button" class="btn ai-btn-apply px-4 py-2 d-flex align-items-center gap-2 disabled" id="btn_apply_ai_content">
                    <i class="bi bi-check2-circle fs-6"></i>
                    <span>{{ __('Apply to Product Form') }}</span>
                </button>
            </div>

        </div>
    </div>
</div>

@push('css')
<style>
/* =========================================================
   GLASSMORPHISM AI MODAL STYLES (MATCHES THEME & HIGH-END UI)
   ========================================================= */

/* Backdrop overlay with blur */
.ai-glassmorphism-modal.modal.fade .modal-backdrop,
.modal-backdrop.show {
    backdrop-filter: blur(8px) !important;
    -webkit-backdrop-filter: blur(8px) !important;
    background-color: rgba(15, 23, 42, 0.45) !important;
}

/* Glassmorphism Main Modal Card */
.ai-glass-card {
    background: rgba(255, 255, 255, 0.88) !important;
    backdrop-filter: blur(24px) saturate(180%) !important;
    -webkit-backdrop-filter: blur(24px) saturate(180%) !important;
    border: 1px solid rgba(255, 255, 255, 0.8) !important;
    box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.18), 
                0 0 0 1px rgba(255, 255, 255, 0.5) inset,
                0 10px 25px -5px rgba(99, 102, 241, 0.08) !important;
    border-radius: 22px !important;
}

/* Ambient luminous glow orbs inside the card */
.ai-glass-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(55px);
    pointer-events: none;
    z-index: 0;
    opacity: 0.45;
}
.ai-glass-orb-1 {
    width: 220px;
    height: 220px;
    top: -40px;
    right: -30px;
    background: radial-gradient(circle, rgba(139, 92, 246, 0.5) 0%, rgba(217, 70, 239, 0.15) 70%, transparent 100%);
}
.ai-glass-orb-2 {
    width: 180px;
    height: 180px;
    bottom: 40px;
    left: -20px;
    background: radial-gradient(circle, rgba(59, 130, 246, 0.4) 0%, rgba(99, 102, 241, 0.1) 70%, transparent 100%);
}

/* Header */
.ai-glass-header {
    background: rgba(255, 255, 255, 0.72) !important;
    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;
    border-bottom: 1px solid rgba(226, 232, 240, 0.75) !important;
}

/* Header Icon Badge with Glowing Gradient */
.ai-header-icon-badge {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #d946ef 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    box-shadow: 0 6px 18px rgba(139, 92, 246, 0.4);
    border: 1px solid rgba(255, 255, 255, 0.4);
}

/* Gemini Badge with Pulsing Status Dot */
.ai-badge-gemini {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(99, 102, 241, 0.1);
    color: #6366f1;
    border: 1px solid rgba(99, 102, 241, 0.22);
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 10px;
    letter-spacing: 0.2px;
}
.ai-pulse-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background-color: #6366f1;
    box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.7);
    animation: ai-pulse 2s infinite;
}
@keyframes ai-pulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(99, 102, 241, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(99, 102, 241, 0); }
}

/* Glass Close Button */
.ai-glass-close-btn {
    width: 32px;
    height: 32px;
    background-color: rgba(100, 116, 139, 0.08);
    border-radius: 50%;
    padding: 8px;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    opacity: 0.7;
}
.ai-glass-close-btn:hover {
    background-color: rgba(239, 68, 68, 0.12);
    color: #ef4444;
    opacity: 1;
    transform: rotate(90deg);
}

/* Tip Banner Card */
.ai-glass-banner {
    background: linear-gradient(135deg, rgba(238, 242, 255, 0.8) 0%, rgba(250, 245, 255, 0.8) 100%);
    border: 1px solid rgba(199, 210, 254, 0.7);
    border-radius: 14px;
    backdrop-filter: blur(8px);
    box-shadow: 0 3px 12px rgba(99, 102, 241, 0.04);
}
.ai-banner-icon-box {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: rgba(99, 102, 241, 0.15);
    color: #6366f1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

/* Inner Glass Card for Form Inputs */
.ai-glass-inner-card {
    background: rgba(255, 255, 255, 0.68);
    border: 1px solid rgba(255, 255, 255, 0.95);
    border-radius: 16px;
    box-shadow: 0 6px 20px rgba(15, 23, 42, 0.03), 0 0 0 1px rgba(226, 232, 240, 0.6) inset;
    backdrop-filter: blur(12px);
}

/* Glass Inputs & Textareas */
.ai-glass-input {
    background: rgba(255, 255, 255, 0.85) !important;
    border: 1.5px solid #cbd5e1 !important;
    border-radius: 10px !important;
    color: #1e293b !important;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
}
.ai-glass-input:focus {
    background: #ffffff !important;
    border-color: #6366f1 !important;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15) !important;
    outline: none !important;
}
.ai-glass-addon {
    background: rgba(248, 250, 252, 0.85) !important;
    border: 1.5px solid #cbd5e1 !important;
    border-right: none !important;
    border-radius: 10px 0 0 10px !important;
    color: #64748b !important;
}
.input-group > .ai-glass-input {
    border-radius: 0 10px 10px 0 !important;
}

/* Generate Button: Vibrant AI Gradient with Ambient Glow */
.ai-btn-generate {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #d946ef 100%) !important;
    color: #ffffff !important;
    border: none !important;
    border-radius: 10px !important;
    height: 40px;
    font-size: 13.5px;
    font-weight: 600;
    box-shadow: 0 4px 16px rgba(139, 92, 246, 0.35) !important;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
}
.ai-btn-generate:hover:not(:disabled) {
    transform: translateY(-1.5px);
    box-shadow: 0 8px 24px rgba(139, 92, 246, 0.48) !important;
    color: #ffffff !important;
}
.ai-btn-generate:active:not(:disabled) {
    transform: translateY(0);
}

/* Result Preview Glass Container */
.ai-glass-result-card {
    background: rgba(255, 255, 255, 0.82);
    border: 1.5px solid rgba(16, 185, 129, 0.35);
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(16, 185, 129, 0.08), 0 0 0 1px rgba(255, 255, 255, 0.9) inset;
    backdrop-filter: blur(14px);
    animation: ai-fade-in-up 0.4s ease-out;
}
@keyframes ai-fade-in-up {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

.ai-success-icon-box {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

/* Rendered HTML Box */
.ai-preview-rendered-box {
    background: rgba(255, 255, 255, 0.95);
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    max-height: 230px;
    font-size: 13px;
    line-height: 1.65;
    color: #334155;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.02);
}
.ai-preview-rendered-box h3,
.ai-preview-rendered-box h4 {
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
    margin-top: 10px;
    margin-bottom: 6px;
}
.ai-preview-rendered-box ul {
    padding-left: 18px;
    margin-bottom: 10px;
}
.ai-preview-rendered-box li {
    margin-bottom: 4px;
}

/* Glass Keywords Badges */
.ai-keyword-tag {
    background: rgba(99, 102, 241, 0.08);
    color: #4338ca;
    border: 1px solid rgba(99, 102, 241, 0.22);
    border-radius: 8px;
    font-size: 11.5px;
    padding: 4px 10px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.2s ease;
}
.ai-keyword-tag:hover {
    background: rgba(99, 102, 241, 0.15);
    transform: translateY(-1px);
}

/* Footer */
.ai-glass-footer {
    background: rgba(255, 255, 255, 0.75) !important;
    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;
    border-top: 1px solid rgba(226, 232, 240, 0.75) !important;
}

/* Cancel / Close button */
.ai-btn-cancel {
    background: rgba(241, 245, 249, 0.9) !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 10px !important;
    color: #475569 !important;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.2s ease;
}
.ai-btn-cancel:hover {
    background: #e2e8f0 !important;
    color: #1e293b !important;
}

/* Apply to Product Button: Fresh Emerald Gradient */
.ai-btn-apply {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
    border: none !important;
    color: #ffffff !important;
    border-radius: 10px !important;
    font-size: 13.5px;
    font-weight: 600;
    box-shadow: 0 4px 16px rgba(16, 185, 129, 0.35) !important;
    transition: all 0.25s ease;
}
.ai-btn-apply:hover:not(.disabled) {
    transform: translateY(-1.5px);
    box-shadow: 0 8px 22px rgba(16, 185, 129, 0.45) !important;
    color: #ffffff !important;
}
.ai-btn-apply.disabled {
    opacity: 0.55;
    cursor: not-allowed;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    
    // Open Modal button handler
    $(document).on('click', '#btn_open_ai_modal', function(e) {
        e.preventDefault();
        
        // Auto-fill product name as hint in keywords if keywords is empty
        var currentProductName = $('input[name="name"]').val();
        if (currentProductName && !$('#ai_input_keywords').val()) {
            $('#ai_input_keywords').attr('placeholder', 'e.g. Key details for: ' + currentProductName);
        }

        var modalEl = document.getElementById('aiGenerateModal');
        if (window.bootstrap && bootstrap.Modal) {
            var modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modalInstance.show();
        } else {
            $('#aiGenerateModal').modal('show');
        }
    });

    // Run AI Generation
    $('#btn_run_ai_generate').on('click', function() {
        var url = $('#ai_input_url').val().trim();
        var keywords = $('#ai_input_keywords').val().trim();
        var tone = $('#ai_input_tone').val();
        var productName = $('input[name="name"]').val();

        if (!url && !keywords && !productName) {
            showAiError("{{ __('Please enter a product URL, keywords/highlights, or a Product Name.') }}");
            return;
        }

        // Reset UI state
        $('#ai_error_notice').addClass('d-none');
        $('#ai_api_key_alert').addClass('d-none');
        $('#ai_spinner').removeClass('d-none');
        $('#btn_run_ai_text').text("{{ __('Generating with AI...') }}");
        $('#btn_run_ai_generate').prop('disabled', true);
        $('#btn_apply_ai_content').addClass('disabled');

        $.ajax({
            url: "{{ route('shop.product.ai-generate-content') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                url: url,
                keywords: keywords,
                tone: tone,
                product_name: productName
            },
            dataType: "json",
            timeout: 60000,
            success: function(response) {
                $('#ai_spinner').addClass('d-none');
                $('#btn_run_ai_text').text("{{ __('Regenerate Content') }}");
                $('#btn_run_ai_generate').prop('disabled', false);

                if (response.success) {
                    // Populate preview card
                    $('#ai_preview_meta_title').val(response.meta_title || '');
                    $('#ai_preview_meta_desc').val(response.meta_description || response.short_description || '');
                    var sDesc = (response.short_description || '').substring(0, 191);
                    $('#ai_preview_short_desc').val(sDesc);
                    updateAiShortDescCounter();
                    $('#ai_preview_rendered_html').html(response.description || '');
                    $('#ai_preview_raw_html').val(response.description || '');
                    $('#ai_preview_keywords_input').val(response.meta_keywords || '');

                    // Populate Delivery Dimensions
                    $('#ai_preview_length').val(response.length !== undefined && response.length !== null ? response.length : '');
                    $('#ai_preview_width').val(response.width !== undefined && response.width !== null ? response.width : '');
                    $('#ai_preview_height').val(response.height !== undefined && response.height !== null ? response.height : '');
                    $('#ai_preview_weight').val(response.weight !== undefined && response.weight !== null ? response.weight : '');

                    // Render keyword badges with glass tags
                    var badgesContainer = $('#ai_preview_keywords_badges').empty();
                    if (response.meta_keywords) {
                        var kwList = response.meta_keywords.split(',');
                        $.each(kwList, function(i, kw) {
                            var trimmed = $.trim(kw);
                            if (trimmed) {
                                badgesContainer.append('<span class="ai-keyword-tag"><i class="bi bi-tag text-primary"></i>' + trimmed + '</span>');
                            }
                        });
                        $('#ai_preview_keywords_section').removeClass('d-none');
                    } else {
                        $('#ai_preview_keywords_section').addClass('d-none');
                    }

                    // Show preview card and enable apply button
                    $('#ai_preview_card').removeClass('d-none');
                    $('#btn_apply_ai_content').removeClass('disabled');

                    // Scroll down to preview smoothly
                    $('#aiGenerateModal .modal-body').animate({
                        scrollTop: $('#ai_preview_card').offset().top
                    }, 400);

                } else {
                    showAiError(response.message || "{{ __('Failed to generate content.') }}");
                }
            },
            error: function(xhr) {
                $('#ai_spinner').addClass('d-none');
                $('#btn_run_ai_text').text("{{ __('Generate with Gemini AI') }}");
                $('#btn_run_ai_generate').prop('disabled', false);

                var errorMsg = "{{ __('An error occurred while generating content.') }}";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                    if (errorMsg.indexOf('API key is missing') !== -1 || errorMsg.indexOf('Gemini API key') !== -1) {
                        $('#ai_api_key_alert_msg').text(errorMsg);
                        $('#ai_api_key_alert').removeClass('d-none');
                        return;
                    }
                }
                showAiError(errorMsg);
            }
        });
    });

    function showAiError(msg) {
        $('#ai_error_text').text(msg);
        $('#ai_error_notice').removeClass('d-none');
    }

    function updateAiShortDescCounter() {
        var val = $('#ai_preview_short_desc').val() || '';
        var len = val.length;
        var badge = $('#ai_short_desc_counter');
        badge.text(len + ' / 191');
        if (len > 191) {
            badge.removeClass('bg-light text-secondary bg-warning text-dark').addClass('bg-danger text-white');
        } else if (len >= 180) {
            badge.removeClass('bg-light text-secondary bg-danger text-white').addClass('bg-warning text-dark');
        } else {
            badge.removeClass('bg-danger text-white bg-warning text-dark').addClass('bg-light text-secondary');
        }
    }

    $('#ai_preview_short_desc').on('input', updateAiShortDescCounter);

    // Toggle Raw HTML vs Formatted Preview
    $('#btn_toggle_ai_raw_html').on('click', function(e) {
        e.preventDefault();
        var rawBox = $('#ai_preview_raw_html');
        var renderedBox = $('#ai_preview_rendered_html');
        if (rawBox.hasClass('d-none')) {
            rawBox.removeClass('d-none').val(renderedBox.html());
            renderedBox.addClass('d-none');
            $(this).find('span').text("{{ __('View Formatted') }}");
        } else {
            renderedBox.removeClass('d-none').html(rawBox.val());
            rawBox.addClass('d-none');
            $(this).find('span').text("{{ __('View HTML Source') }}");
        }
    });

    // Apply to Product Form
    $('#btn_apply_ai_content').on('click', function() {
        var generatedMetaTitle = $('#ai_preview_meta_title').val();
        var generatedMetaDesc = $('#ai_preview_meta_desc').val();
        var generatedShortDesc = $('#ai_preview_short_desc').val();
        if (generatedShortDesc && generatedShortDesc.length > 191) {
            generatedShortDesc = generatedShortDesc.substring(0, 191);
        }
        var generatedDescription = $('#ai_preview_raw_html').hasClass('d-none') ? 
                                    $('#ai_preview_rendered_html').html() : 
                                    $('#ai_preview_raw_html').val();
        var generatedKeywords = $('#ai_preview_keywords_input').val();

        // 1. Set Meta Title in product form
        if (generatedMetaTitle) {
            var metaTitleElem = $('input[name="meta_title"]');
            if (metaTitleElem.length) {
                metaTitleElem.val(generatedMetaTitle);
            }
        }

        // 2. Set Meta Description in product form
        if (generatedMetaDesc) {
            var metaDescElem = $('textarea[name="meta_description"]');
            if (metaDescElem.length) {
                metaDescElem.val(generatedMetaDesc);
            }
        }

        // 3. Set Short Description
        $('textarea[name="short_description"]').val(generatedShortDesc);
        $('#edit_short_desc_counter').text(generatedShortDesc.length + ' / 191');
        $('#create_short_desc_counter').text(generatedShortDesc.length + ' / 191');

        // 4. Set Quill Editor content & hidden input
        if (typeof quill !== 'undefined' && quill) {
            quill.root.innerHTML = generatedDescription;
            if (typeof correctULTagFromQuill === 'function') {
                $('#description').val(correctULTagFromQuill(quill.root.innerHTML));
            } else {
                $('#description').val(quill.root.innerHTML);
            }
        } else {
            $('#description').val(generatedDescription);
        }

        // 5. Append Meta Keywords if tags select exists
        if (generatedKeywords && $('#tags').length) {
            var kwList = generatedKeywords.split(',');
            var currentTags = $('#tags').val() || [];
            $.each(kwList, function(i, kw) {
                var trimmed = $.trim(kw);
                if (trimmed && currentTags.indexOf(trimmed) === -1) {
                    var newOption = new Option(trimmed, trimmed, true, true);
                    $('#tags').append(newOption);
                    currentTags.push(trimmed);
                }
            });
            $('#tags').val(currentTags).trigger('change');
        }

        // 6. Set Delivery Dimensions in product form
        var genLength = $('#ai_preview_length').val();
        var genWidth  = $('#ai_preview_width').val();
        var genHeight = $('#ai_preview_height').val();
        var genWeight = $('#ai_preview_weight').val();

        if (genLength) {
            var lengthInput = document.getElementById('length') || document.querySelector('input[name="length"]');
            if (lengthInput) lengthInput.value = genLength;
        }
        if (genWidth) {
            var widthInput = document.getElementById('width') || document.querySelector('input[name="width"]');
            if (widthInput) widthInput.value = genWidth;
        }
        if (genHeight) {
            var heightInput = document.getElementById('height') || document.querySelector('input[name="height"]');
            if (heightInput) heightInput.value = genHeight;
        }
        if (genWeight) {
            var weightInput = document.getElementById('weight') || document.querySelector('input[name="weight"]');
            if (weightInput) weightInput.value = genWeight;
        }

        // Close modal
        var modalEl = document.getElementById('aiGenerateModal');
        if (window.bootstrap && bootstrap.Modal) {
            var modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) modalInstance.hide();
        } else {
            $('#aiGenerateModal').modal('hide');
        }

        // Display success alert / toastr
        if (typeof toastr !== 'undefined') {
            toastr.success("{{ __('AI Content, SEO & Delivery Dimensions applied!') }}");
        } else if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: "{{ __('Content Applied!') }}",
                text: "{{ __('Short Description, Rich Description, SEO, and Delivery Dimensions have been populated successfully.') }}",
                timer: 2200,
                showConfirmButton: false
            });
        }
    });

});
</script>
@endpush
