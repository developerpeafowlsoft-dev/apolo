<!-- AI Image Studio Modal -->
<div class="modal fade" id="aiImageStudioModal" tabindex="-1" aria-labelledby="aiImageStudioModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="height: 88vh; max-height: 850px; display: flex; flex-direction: column; border-radius: 1.25rem; background: rgba(255, 255, 255, 0.96); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.6); overflow: hidden;">
            
            <!-- Modal Header -->
            <div class="modal-header border-bottom py-2.5 px-4 flex-shrink-0" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(168, 85, 247, 0.08) 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center shadow-xs text-white" style="background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); width: 38px; height: 38px;">
                        <i class="bi bi-camera-fill fs-5"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="modal-title fw-bold text-dark m-0" id="aiImageStudioModalLabel" style="letter-spacing: -0.3px; font-size: 16px;">
                                {{ __('AI Product Image Studio') }}
                            </h5>
                            <span class="badge rounded-pill text-white px-2.5 py-1 small fw-semibold shadow-xs" style="background: linear-gradient(135deg, #6366f1 0%, #ec4899 100%); font-size: 10px;">
                                <i class="bi bi-stars me-0.5"></i> {{ __('5 Variations') }}
                            </span>
                        </div>
                        <p class="text-muted small m-0" style="font-size: 12px;">
                            {{ __('Generate 5 professional studio photographs based on your reference image, background color & style keywords.') }}
                        </p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Top Gemini API Error / Warning Alert Banner -->
            <div id="ai_studio_api_error_banner" class="alert alert-danger mx-4 mt-2.5 mb-0 py-2 px-3 rounded-3 d-none align-items-start gap-2 shadow-xs flex-shrink-0" style="font-size: 12px; border-left: 4px solid #ef4444;">
                <i class="bi bi-exclamation-octagon-fill text-danger fs-6 flex-shrink-0 mt-0.5"></i>
                <div class="flex-grow-1" id="ai_studio_api_error_text"></div>
                <button type="button" class="btn-close btn-close-sm shadow-none ms-auto" style="font-size: 10px;" onclick="document.getElementById('ai_studio_api_error_banner').classList.add('d-none');"></button>
            </div>

            <!-- Modal Body (Fixed Dual-Pane Layout) -->
            <div class="modal-body p-3 flex-grow-1 overflow-hidden">
                <div class="row g-3 h-100">
                    
                    <!-- LEFT COLUMN: Controls & Input Configuration (Scrollable independently) -->
                    <div class="col-lg-4 h-100" style="display: flex; flex-direction: column;">
                        <div class="card border-0 shadow-2xs rounded-3 p-3 flex-grow-1 overflow-y-auto custom-scroll" style="background: rgba(248, 250, 252, 0.9); border: 1px solid rgba(226, 232, 240, 0.9);">
                            
                            <!-- 1. Reference Image Section -->
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <label class="form-label fw-bold text-dark small m-0 d-flex align-items-center gap-1.5" style="font-size: 12px;">
                                        <i class="bi bi-image text-primary"></i> {{ __('Reference Image') }}
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace" style="font-size: 9.5px;">{{ __('Required') }}</span>
                                    </label>
                                    <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-muted small" id="ai_reset_ref_btn" style="font-size: 11px; display: none;">
                                        <i class="bi bi-arrow-counterclockwise"></i> {{ __('Reset') }}
                                    </button>
                                </div>

                                <!-- Reference Image Preview & Dropzone -->
                                <div class="position-relative border-2 border-dashed rounded-3 p-2 text-center bg-white cursor-pointer hover-shadow transition-all" id="ai_ref_dropzone" style="border-color: #cbd5e1; min-height: 120px;">
                                    <div id="ai_ref_empty_state" class="py-2.5" style="display: none;">
                                        <div class="mb-1.5 text-muted">
                                            <i class="bi bi-cloud-arrow-up fs-3 text-primary"></i>
                                        </div>
                                        <span class="d-block fw-semibold text-dark small" style="font-size: 12px;">{{ __('Click or Drag Reference Photo') }}</span>
                                        <span class="text-muted font-monospace" style="font-size: 10.5px;">PNG, JPG, WEBP</span>
                                    </div>

                                    <div id="ai_ref_preview_wrapper" class="position-relative d-flex flex-column align-items-center justify-content-center">
                                        <img id="ai_ref_preview_img" src="{{ asset('default/upload.png') }}" alt="Reference Preview" class="img-fluid rounded-2 object-fit-contain shadow-xs" style="max-height: 110px; width: auto;">
                                        <div class="mt-1.5 d-flex gap-1.5">
                                            <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-0.5" id="ai_change_ref_btn" style="font-size: 10.5px;">
                                                <i class="bi bi-arrow-repeat me-1"></i>{{ __('Change Photo') }}
                                            </button>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill py-0.5 px-2 d-flex align-items-center" style="font-size: 9.5px;">
                                                <i class="bi bi-check-circle-fill me-1"></i>{{ __('Active') }}
                                            </span>
                                        </div>
                                    </div>

                                    <input type="file" id="ai_ref_file_input" accept="image/*" class="d-none">
                                </div>
                                <span class="text-muted d-block mt-1" style="font-size: 10.5px;">
                                    <i class="bi bi-info-circle me-1"></i>{{ __('AI maintains your product details and creates 5 commercial views.') }}
                                </span>
                            </div>

                            <!-- 2. Background Color Selection -->
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-1.5">
                                    <label class="form-label fw-bold text-dark small m-0 d-flex align-items-center gap-1.5" style="font-size: 12px;">
                                        <i class="bi bi-palette text-primary"></i> {{ __('Set Background Color') }}
                                    </label>
                                    <span class="badge bg-light text-dark font-monospace border" id="ai_selected_color_badge" style="font-size: 10.5px;">#FFFFFF</span>
                                </div>

                                <!-- Preset Color Swatches -->
                                <div class="d-flex flex-wrap gap-1.5 mb-2" id="ai_bg_swatches">
                                    <button type="button" class="color-swatch-btn active shadow-xs" data-color="#FFFFFF" title="Pure White Studio" style="background-color: #FFFFFF; border: 2px solid #6366f1; width: 26px; height: 26px; border-radius: 50%;"></button>
                                    <button type="button" class="color-swatch-btn shadow-xs" data-color="#F3F4F6" title="Studio Neutral Gray" style="background-color: #F3F4F6; border: 1px solid #cbd5e1; width: 26px; height: 26px; border-radius: 50%;"></button>
                                    <button type="button" class="color-swatch-btn shadow-xs" data-color="#FFFBEB" title="Warm Ivory Cream" style="background-color: #FFFBEB; border: 1px solid #cbd5e1; width: 26px; height: 26px; border-radius: 50%;"></button>
                                    <button type="button" class="color-swatch-btn shadow-xs" data-color="#FDF8F0" title="Warm Beige" style="background-color: #FDF8F0; border: 1px solid #cbd5e1; width: 26px; height: 26px; border-radius: 50%;"></button>
                                    <button type="button" class="color-swatch-btn shadow-xs" data-color="#E0F2FE" title="Pastel Sky Blue" style="background-color: #E0F2FE; border: 1px solid #cbd5e1; width: 26px; height: 26px; border-radius: 50%;"></button>
                                    <button type="button" class="color-swatch-btn shadow-xs" data-color="#FCE7F3" title="Soft Blush Pink" style="background-color: #FCE7F3; border: 1px solid #cbd5e1; width: 26px; height: 26px; border-radius: 50%;"></button>
                                    <button type="button" class="color-swatch-btn shadow-xs" data-color="#DCFCE7" title="Soft Sage Mint" style="background-color: #DCFCE7; border: 1px solid #cbd5e1; width: 26px; height: 26px; border-radius: 50%;"></button>
                                    <button type="button" class="color-swatch-btn shadow-xs" data-color="#1E293B" title="Slate Charcoal" style="background-color: #1E293B; border: 1px solid #cbd5e1; width: 26px; height: 26px; border-radius: 50%;"></button>
                                </div>

                                <!-- Custom Color Picker & Hex Input -->
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text p-1 bg-white">
                                        <input type="color" id="ai_custom_color_picker" value="#ffffff" class="form-control form-control-color border-0 p-0 cursor-pointer" style="width: 24px; height: 22px;" title="Choose custom color">
                                    </span>
                                    <input type="text" id="ai_custom_color_hex" value="#FFFFFF" class="form-control form-control-sm font-monospace text-uppercase" placeholder="#FFFFFF" maxlength="7" style="font-size: 11.5px;">
                                    <span class="input-group-text bg-light text-muted small" style="font-size: 10.5px;">{{ __('Hex') }}</span>
                                </div>
                            </div>

                            <!-- 3. 5 Ready-Made Product Photography Prompts -->
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-1.5">
                                    <label class="form-label fw-bold text-dark small m-0 d-flex align-items-center gap-1.5" style="font-size: 12px;">
                                        <i class="bi bi-camera-reels-fill text-primary"></i> {{ __('5 Ready-Made Studio Prompts') }}
                                    </label>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace" style="font-size: 9.5px;">{{ __('5 Styles Ready') }}</span>
                                </div>

                                <!-- Prompt Selector Pills -->
                                <div class="d-flex flex-wrap gap-1 mb-2" id="ai_ready_prompts_selector">
                                    <button type="button" class="btn btn-xs btn-outline-primary rounded-pill py-0.5 px-2 active ai-prompt-tab-btn" data-prompt-key="hero" style="font-size: 10px;">🌟 1. Hero</button>
                                    <button type="button" class="btn btn-xs btn-outline-primary rounded-pill py-0.5 px-2 ai-prompt-tab-btn" data-prompt-key="lifestyle" style="font-size: 10px;">🌿 2. Lifestyle</button>
                                    <button type="button" class="btn btn-xs btn-outline-primary rounded-pill py-0.5 px-2 ai-prompt-tab-btn" data-prompt-key="ingredient" style="font-size: 10px;">🧪 3. Ingredient</button>
                                    <button type="button" class="btn btn-xs btn-outline-primary rounded-pill py-0.5 px-2 ai-prompt-tab-btn" data-prompt-key="pedestal" style="font-size: 10px;">💎 4. Pedestal</button>
                                    <button type="button" class="btn btn-xs btn-outline-primary rounded-pill py-0.5 px-2 ai-prompt-tab-btn" data-prompt-key="social" style="font-size: 10px;">📱 5. Social</button>
                                </div>

                                <!-- Active Prompt Preview Card -->
                                <div class="card border rounded-2 p-2 bg-light-subtle shadow-2xs mb-2" id="ai_prompt_preview_card">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <strong class="text-dark small" id="ai_prompt_card_title" style="font-size: 11px;">1. Premium Studio Hero Shot</strong>
                                        <div class="d-flex gap-1">
                                            <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1.5 rounded" id="ai_copy_prompt_btn" title="{{ __('Copy prompt to clipboard') }}" style="font-size: 9.5px;">
                                                <i class="bi bi-clipboard me-1"></i>{{ __('Copy') }}
                                            </button>
                                            <button type="button" class="btn btn-xs btn-primary py-0 px-2 rounded" id="ai_insert_prompt_btn" title="{{ __('Insert prompt into box below to customize') }}" style="font-size: 9.5px;">
                                                <i class="bi bi-box-arrow-in-down me-1"></i>{{ __('Use / Edit') }}
                                            </button>
                                        </div>
                                    </div>
                                    <p class="text-muted small m-0" id="ai_prompt_card_desc" style="font-size: 10.5px; line-height: 1.3;">
                                        {{ __('Minimalist studio setup with clean background, soft diffused key light, rim lighting, contact shadow & reflections (500×500 px).') }}
                                    </p>
                                </div>
                            </div>

                            <!-- 4. Style / Keyword Prompts (Optional / Customizer) -->
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <label class="form-label fw-bold text-dark small m-0 d-flex align-items-center gap-1.5" style="font-size: 12px;">
                                        <i class="bi bi-sliders text-primary"></i> {{ __('Customize Prompt / Keywords') }}
                                        <span class="text-muted fw-normal" style="font-size: 10px;">({{ __('Optional') }})</span>
                                    </label>
                                    <button type="button" class="btn btn-link text-decoration-none p-0 text-muted small" id="ai_clear_keywords_btn" style="font-size: 10px;">
                                        <i class="bi bi-arrow-counterclockwise"></i> {{ __('Reset to All 5') }}
                                    </button>
                                </div>
                                <textarea id="ai_image_keywords" class="form-control form-control-sm rounded-2 shadow-none" rows="2" placeholder="{{ __('Leave blank to generate all 5 ready-made styles automatically, or insert/edit a prompt above...') }}" style="font-size: 11px; resize: none;"></textarea>
                                
                                <!-- Quick Keyword Suggestion Pills -->
                                <div class="d-flex flex-wrap gap-1 mt-1.5">
                                    <button type="button" class="btn btn-xs btn-light border rounded-pill py-0 px-2 text-muted small ai-keyword-pill" data-keyword="Studio Softbox Lighting" style="font-size: 10px;">+ Studio Lighting</button>
                                    <button type="button" class="btn btn-xs btn-light border rounded-pill py-0 px-2 text-muted small ai-keyword-pill" data-keyword="Clean Minimalist Aesthetic" style="font-size: 10px;">+ Minimalist</button>
                                    <button type="button" class="btn btn-xs btn-light border rounded-pill py-0 px-2 text-muted small ai-keyword-pill" data-keyword="Realistic Ground Contact Shadow" style="font-size: 10px;">+ Soft Shadow</button>
                                    <button type="button" class="btn btn-xs btn-light border rounded-pill py-0 px-2 text-muted small ai-keyword-pill" data-keyword="High-end Luxury Catalog" style="font-size: 10px;">+ Luxury</button>
                                    <button type="button" class="btn btn-xs btn-light border rounded-pill py-0 px-2 text-muted small ai-keyword-pill" data-keyword="Geometric Cylinder Podium Stand" style="font-size: 10px;">+ Podium</button>
                                </div>
                            </div>

                            <!-- 5. Generate Button -->
                            <button type="button" id="ai_generate_images_btn" class="btn text-white w-100 py-2 rounded-3 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2 mb-2 transition-all mt-auto" style="background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); font-size: 13px;">
                                <i class="bi bi-stars fs-6"></i>
                                <span>{{ __('Generate 5 AI Images') }}</span>
                            </button>

                            <!-- 6. Manual Add Section ("Also i can added manually also") -->
                            <div class="pt-2 border-top">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="text-muted small fw-semibold" style="font-size: 10.5px;">{{ __('Manual Add Option:') }}</span>
                                </div>
                                <button type="button" id="ai_trigger_manual_upload_btn" class="btn btn-outline-secondary btn-sm w-100 rounded-2 py-1.5 d-flex align-items-center justify-content-center gap-1.5" style="font-size: 11.5px;">
                                    <i class="bi bi-cloud-arrow-up"></i> {{ __('Add Photo Manually to Studio') }}
                                </button>
                                <input type="file" id="ai_manual_upload_input" accept="image/*" class="d-none">
                            </div>

                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Output Studio Showcase & Gallery Management (Fixed height with internal scroll) -->
                    <div class="col-lg-8 h-100" style="display: flex; flex-direction: column;">
                        <div class="card border-0 shadow-2xs rounded-3 p-3 bg-white h-100 d-flex flex-column overflow-hidden" style="border: 1px solid rgba(226, 232, 240, 0.9);">
                            
                            <!-- Studio Header & Bulk Actions (Fixed at top) -->
                            <div class="d-flex flex-wrap align-items-center justify-content-between pb-2 mb-2 border-bottom gap-2 flex-shrink-0">
                                <div>
                                    <h6 class="fw-bold text-dark m-0 d-flex align-items-center gap-1.5" style="font-size: 13.5px;">
                                        <i class="bi bi-grid-fill text-primary"></i> {{ __('Generated Product Photos') }}
                                        <span class="badge bg-light text-muted font-monospace border" id="ai_generated_count_badge">0 / 5</span>
                                    </h6>
                                    <span class="text-muted small" style="font-size: 11px;">{{ __('Review variations and apply them directly as Main Thumbnail or Gallery Images.') }}</span>
                                </div>

                                <!-- Bulk Action Buttons (Visible after generation) -->
                                <div class="d-flex align-items-center gap-1.5" id="ai_bulk_actions" style="display: none !important;">
                                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1" id="ai_add_all_to_gallery_btn" style="font-size: 11px;">
                                        <i class="bi bi-images text-purple"></i> {{ __('Add All to Gallery') }}
                                    </button>
                                    <button type="button" class="btn btn-xs btn-primary rounded-pill px-3 py-1 fw-bold d-inline-flex align-items-center gap-1 shadow-xs" id="ai_apply_main_and_gallery_btn" style="font-size: 11px; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); border: none;">
                                        <i class="bi bi-magic"></i> {{ __('Set #1 as Main & Rest to Gallery') }}
                                    </button>
                                </div>
                            </div>

                            <!-- Studio Content Area (Clean internal scroll, always starts at top) -->
                            <div class="flex-grow-1 overflow-y-auto custom-scroll position-relative" id="ai_studio_content_area" style="min-height: 0;">
                                
                                <!-- State 1: Initial Empty State (Visible by default) -->
                                <div id="ai_studio_empty_state" class="h-100 d-flex flex-column align-items-center justify-content-center p-3 text-center">
                                    <div class="rounded-circle p-2.5 mb-2 d-inline-flex align-items-center justify-content-center shadow-2xs" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.12) 0%, rgba(168, 85, 247, 0.12) 100%); width: 56px; height: 56px;">
                                        <i class="bi bi-camera2 fs-3 text-primary"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">{{ __('5-Perspective AI Studio Ready') }}</h6>
                                    <p class="text-muted small mb-3" style="max-width: 460px; font-size: 11.5px;">
                                        {{ __('Select your reference photo on the left, pick your background color, and click "Generate 5 AI Images" to render all 5 commercial photography variations:') }}
                                    </p>

                                    <!-- 5 Ready-made prompt showcase list -->
                                    <div class="row g-2 w-100 text-start mb-2.5" style="max-width: 580px;">
                                        <div class="col-12 col-md-6">
                                            <div class="p-2 border rounded-2 bg-light-subtle d-flex align-items-start gap-2 h-100 shadow-2xs">
                                                <span class="badge bg-primary-subtle text-primary rounded-pill px-1.5 py-0.5" style="font-size: 10px;">1</span>
                                                <div>
                                                    <strong class="d-block text-dark small" style="font-size: 11px;">🌟 {{ __('Premium Studio Hero Shot') }}</strong>
                                                    <span class="text-muted d-block" style="font-size: 10px; line-height: 1.2;">{{ __('Clean centered minimalist studio, soft diffused light, rim light & contact shadow.') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="p-2 border rounded-2 bg-light-subtle d-flex align-items-start gap-2 h-100 shadow-2xs">
                                                <span class="badge bg-success-subtle text-success rounded-pill px-1.5 py-0.5" style="font-size: 10px;">2</span>
                                                <div>
                                                    <strong class="d-block text-dark small" style="font-size: 11px;">🌿 {{ __('Lifestyle Product Scene') }}</strong>
                                                    <span class="text-muted d-block" style="font-size: 10px; line-height: 1.2;">{{ __('Tasteful contextual environment with warm natural lighting & shallow depth of field.') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="p-2 border rounded-2 bg-light-subtle d-flex align-items-start gap-2 h-100 shadow-2xs">
                                                <span class="badge bg-info-subtle text-info rounded-pill px-1.5 py-0.5" style="font-size: 10px;">3</span>
                                                <div>
                                                    <strong class="d-block text-dark small" style="font-size: 11px;">🧪 {{ __('Creative Ingredient & Feature') }}</strong>
                                                    <span class="text-muted d-block" style="font-size: 10px; line-height: 1.2;">{{ __('Surrounded with dynamic elements representing ingredients, fragrance, or features.') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="p-2 border rounded-2 bg-light-subtle d-flex align-items-start gap-2 h-100 shadow-2xs">
                                                <span class="badge bg-warning-subtle text-warning rounded-pill px-1.5 py-0.5" style="font-size: 10px;">4</span>
                                                <div>
                                                    <strong class="d-block text-dark small" style="font-size: 11px;">💎 {{ __('Luxury Pedestal & Reflection') }}</strong>
                                                    <span class="text-muted d-block" style="font-size: 10px; line-height: 1.2;">{{ __('Staged on marble/acrylic pedestal with soft spotlighting & atmospheric glow.') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="p-2 border rounded-2 bg-light-subtle d-flex align-items-start gap-2 shadow-2xs">
                                                <span class="badge bg-danger-subtle text-danger rounded-pill px-1.5 py-0.5" style="font-size: 10px;">5</span>
                                                <div>
                                                    <strong class="d-block text-dark small" style="font-size: 11px;">📱 {{ __('Social Media Advertising Shot') }}</strong>
                                                    <span class="text-muted d-block" style="font-size: 10px; line-height: 1.2;">{{ __('Contemporary geometric platforms, soft gradients & bold advertising lighting.') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-muted small font-monospace d-flex align-items-center gap-1" style="font-size: 10px;">
                                        <i class="bi bi-shield-check text-success"></i>
                                        <span>{{ __('Consistency rule enforced: Preserves original product branding, shape, colors, label & packaging.') }}</span>
                                    </div>
                                </div>

                                <!-- State 2: Loading & Generating State (Hidden by default using d-none) -->
                                <div id="ai_studio_loading_state" class="h-100 d-none flex-column align-items-center justify-content-center text-center p-3">
                                    <div class="spinner-grow text-primary mb-3" role="status" style="width: 2.75rem; height: 2.75rem;">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1" id="ai_loading_step_title" style="font-size: 14px;">{{ __('Rendering 5 Studio Variations...') }}</h6>
                                    <p class="text-muted small mb-3" id="ai_loading_step_desc" style="max-width: 380px; font-size: 12px;">
                                        {{ __('AI is analyzing your reference image and creating 5 studio perspectives with the chosen background color.') }}
                                    </p>
                                    
                                    <!-- Progress Shimmer Bar -->
                                    <div class="progress w-50" style="height: 6px; border-radius: 10px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 100%; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%) !important;"></div>
                                    </div>
                                </div>

                                <!-- State 3: Results Grid (Hidden by default using d-none) -->
                                <div id="ai_studio_results_grid" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-2.5 p-1 d-none">
                                    <!-- Dynamically rendered by JavaScript -->
                                </div>

                            </div>

                            <!-- Live Toast Feedback inside modal -->
                            <div id="ai_studio_toast" class="position-absolute bottom-0 start-50 translate-middle-x mb-3 alert alert-dark shadow-lg py-2 px-3 rounded-pill d-none align-items-center gap-2" style="z-index: 1050; font-size: 12px; background: rgba(15, 23, 42, 0.95); color: #fff; border: 1px solid rgba(255, 255, 255, 0.2);">
                                <i class="bi bi-check-circle-fill text-success" id="ai_toast_icon"></i>
                                <span id="ai_toast_message">Notification</span>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-top py-2 px-4 bg-light-subtle d-flex align-items-center justify-content-between flex-shrink-0">
                <span class="text-muted small" style="font-size: 11px;">
                    <i class="bi bi-shield-check text-success me-1"></i> {{ __('Selected photos are automatically converted and linked to your product form.') }}
                </span>
                <button type="button" class="btn btn-secondary btn-sm px-3 rounded-pill" data-bs-dismiss="modal" style="font-size: 12px;">
                    {{ __('Close Studio') }}
                </button>
            </div>

        </div>
    </div>
</div>

<!-- Image Lightbox Modal for High-Res Zoom -->
<div class="modal fade" id="aiImageZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 1065;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0 text-center">
            <div class="position-relative d-inline-block mx-auto">
                <img id="ai_zoom_img" src="" alt="High Res Preview" class="img-fluid rounded-3 shadow-2xl" style="max-height: 80vh; max-width: 90vw;">
                <button type="button" class="btn btn-dark btn-sm rounded-circle position-absolute top-0 end-0 m-2 shadow" data-bs-dismiss="modal" style="width: 32px; height: 32px; line-height: 28px;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .color-swatch-btn {
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
        cursor: pointer;
    }
    .color-swatch-btn:hover {
        transform: scale(1.15);
    }
    .color-swatch-btn.active {
        transform: scale(1.18);
        border: 2px solid #6366f1 !important;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3) !important;
    }
    .ai-keyword-pill {
        transition: all 0.15s ease;
        cursor: pointer;
    }
    .ai-keyword-pill:hover {
        background-color: #6366f1 !important;
        color: #ffffff !important;
        border-color: #6366f1 !important;
    }
    .ai-card-hover {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .ai-card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.12) !important;
    }
    .hover-shadow:hover {
        border-color: #6366f1 !important;
        background-color: rgba(99, 102, 241, 0.02) !important;
    }
    .custom-scroll::-webkit-scrollbar {
        width: 5px;
        height: 5px;
    }
    .custom-scroll::-webkit-scrollbar-track {
        background: rgba(0,0,0,0.03);
        border-radius: 4px;
    }
    .custom-scroll::-webkit-scrollbar-thumb {
        background: rgba(99, 102, 241, 0.25);
        border-radius: 4px;
    }
    .custom-scroll::-webkit-scrollbar-thumb:hover {
        background: rgba(99, 102, 241, 0.5);
    }
</style>

<script>
    (function() {
        // Global variables for AI Studio
        let selectedBgColor = '#FFFFFF';
        let currentRefFile = null;
        let generatedStudioImages = [];
        const defaultPlaceholderSrc = "{{ asset('default/upload.png') }}";

        // Utility: Convert Base64 data URL to File object synchronously in memory (NO CORS / Network errors)
        function dataURLtoFile(dataurl, filename) {
            if (!dataurl) return null;
            try {
                if (!dataurl.includes(',')) {
                    dataurl = 'data:image/png;base64,' + dataurl;
                }
                const arr = dataurl.split(',');
                const mimeMatch = arr[0].match(/:(.*?);/);
                const mime = mimeMatch ? mimeMatch[1] : 'image/png';
                const bstr = atob(arr[1]);
                let n = bstr.length;
                const u8arr = new Uint8Array(n);
                while (n--) {
                    u8arr[n] = bstr.charCodeAt(n);
                }
                return new File([u8arr], filename || 'ai-product-image.png', { type: mime });
            } catch (e) {
                console.error('Base64 to File conversion error:', e);
                return null;
            }
        }

        // Toast feedback inside modal
        function showStudioToast(message, isError = false) {
            const toast = document.getElementById('ai_studio_toast');
            const icon = document.getElementById('ai_toast_icon');
            const msgEl = document.getElementById('ai_toast_message');
            if (!toast) return;

            msgEl.textContent = message;
            if (isError) {
                icon.className = 'bi bi-exclamation-triangle-fill text-warning';
            } else {
                icon.className = 'bi bi-check-circle-fill text-success';
            }

            toast.classList.remove('d-none');
            toast.classList.add('d-flex');

            setTimeout(() => {
                toast.classList.remove('d-flex');
                toast.classList.add('d-none');
            }, 3500);
        }

        // Initialize when modal is shown
        const studioModalEl = document.getElementById('aiImageStudioModal');
        if (studioModalEl) {
            studioModalEl.addEventListener('show.bs.modal', function() {
                // Clear any previous error banner
                const errorBanner = document.getElementById('ai_studio_api_error_banner');
                if (errorBanner) errorBanner.classList.add('d-none');

                // Pre-populate reference image from main product thumbnail if available
                const formPreview = document.getElementById('preview');
                const refPreviewImg = document.getElementById('ai_ref_preview_img');
                const refPreviewWrapper = document.getElementById('ai_ref_preview_wrapper');
                const refEmptyState = document.getElementById('ai_ref_empty_state');
                const resetBtn = document.getElementById('ai_reset_ref_btn');

                if (!currentRefFile) {
                    if (formPreview && formPreview.src && !formPreview.src.includes('upload.png') && !formPreview.src.includes('placehold.co')) {
                        refPreviewImg.src = formPreview.src;
                        refPreviewWrapper.style.display = 'flex';
                        refEmptyState.style.display = 'none';
                        if (resetBtn) resetBtn.style.display = 'inline-block';
                    }
                }
            });
        }

        // Reference Image File Selection
        const refFileInput = document.getElementById('ai_ref_file_input');
        const refDropzone = document.getElementById('ai_ref_dropzone');
        const refPreviewImg = document.getElementById('ai_ref_preview_img');
        const refPreviewWrapper = document.getElementById('ai_ref_preview_wrapper');
        const refEmptyState = document.getElementById('ai_ref_empty_state');
        const changeRefBtn = document.getElementById('ai_change_ref_btn');
        const resetRefBtn = document.getElementById('ai_reset_ref_btn');

        if (changeRefBtn && refFileInput) {
            changeRefBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                refFileInput.click();
            });
        }

        if (refDropzone && refFileInput) {
            refDropzone.addEventListener('click', () => refFileInput.click());

            // Drag & Drop handlers
            ['dragenter', 'dragover'].forEach(eventName => {
                refDropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    refDropzone.style.borderColor = '#6366f1';
                    refDropzone.style.backgroundColor = 'rgba(99, 102, 241, 0.05)';
                });
            });

            ['dragleave', 'drop'].forEach(eventName => {
                refDropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    refDropzone.style.borderColor = '#cbd5e1';
                    refDropzone.style.backgroundColor = '#ffffff';
                });
            });

            refDropzone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                if (files && files[0] && files[0].type.startsWith('image/')) {
                    handleReferenceFile(files[0]);
                }
            });

            refFileInput.addEventListener('change', function(e) {
                if (this.files && this.files[0]) {
                    handleReferenceFile(this.files[0]);
                }
            });
        }

        function handleReferenceFile(file) {
            currentRefFile = file;
            const reader = new FileReader();
            reader.onload = function(e) {
                refPreviewImg.src = e.target.result;
                refPreviewWrapper.style.display = 'flex';
                refEmptyState.style.display = 'none';
                if (resetRefBtn) resetRefBtn.style.display = 'inline-block';
            };
            reader.readAsDataURL(file);
        }

        if (resetRefBtn) {
            resetRefBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                currentRefFile = null;
                if (refFileInput) refFileInput.value = '';
                const formPreview = document.getElementById('preview');
                if (formPreview && formPreview.src && !formPreview.src.includes('upload.png')) {
                    refPreviewImg.src = formPreview.src;
                } else {
                    refPreviewImg.src = defaultPlaceholderSrc;
                }
                resetRefBtn.style.display = 'none';
            });
        }

        // Background Color Swatches & Custom Pickers
        const colorSwatches = document.querySelectorAll('.color-swatch-btn');
        const customColorPicker = document.getElementById('ai_custom_color_picker');
        const customColorHex = document.getElementById('ai_custom_color_hex');
        const selectedColorBadge = document.getElementById('ai_selected_color_badge');

        function updateSelectedColor(hex, sourceElement = null) {
            selectedBgColor = hex.toUpperCase();
            if (customColorPicker) customColorPicker.value = hex;
            if (customColorHex) customColorHex.value = selectedBgColor;
            if (selectedColorBadge) selectedColorBadge.textContent = selectedBgColor;

            colorSwatches.forEach(btn => {
                if (btn.dataset.color.toUpperCase() === selectedBgColor) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
        }

        colorSwatches.forEach(btn => {
            btn.addEventListener('click', function() {
                updateSelectedColor(this.dataset.color, this);
            });
        });

        if (customColorPicker) {
            customColorPicker.addEventListener('input', function() {
                updateSelectedColor(this.value);
            });
        }

        if (customColorHex) {
            customColorHex.addEventListener('input', function() {
                let val = this.value.trim();
                if (!val.startsWith('#')) val = '#' + val;
                if (/^#[0-9A-F]{6}$/i.test(val)) {
                    updateSelectedColor(val);
                }
            });
        }

        // 5 Ready-Made Product Photography Prompts Dictionary
        const readyMadePrompts = {
            hero: {
                id: 1,
                title: "1. Premium Studio Hero Shot",
                short: "Centered minimalist studio setup with soft diffused light, rim lighting & contact shadow (500×500 px).",
                template: "Create a professional commercial product photography image based strictly on the uploaded reference photo of [PRODUCT NAME]. Preserve the exact product shape, proportions, colors, branding, label, logo, materials, packaging details, and design. Do not redesign or modify the product. Place it centered in a premium minimalist studio setup with a clean [BACKGROUND COLOR] background, soft diffused key light, subtle rim lighting, realistic contact shadow, and elegant reflections. Make the product look crisp, luxurious, photorealistic, and suitable for an e-commerce hero image. No extra text, no watermark, no duplicated product, no distorted logo. Square composition, final size 500 × 500 px. Treat the uploaded product photo as the primary visual reference; do not change the product itself—only change the environment, lighting, props, camera angle, and presentation."
            },
            lifestyle: {
                id: 2,
                title: "2. Lifestyle Product Scene",
                short: "High-end contextual lifestyle setting with warm natural lighting, props & shallow depth of field (500×500 px).",
                template: "Using the uploaded reference image of [PRODUCT NAME], create a high-end lifestyle product photograph. Keep the product 100% visually consistent with the reference, including packaging, label, logo, colors, dimensions, and materials. Place the product naturally in a tasteful environment related to [PRODUCT USE / DESCRIPTION], using complementary props such as [PROP IDEAS]. Use warm natural lighting, realistic shadows, shallow depth of field, clean styling, and an advertising-quality composition. The product must remain the main focus and fully readable. Avoid clutter, extra products, incorrect text, altered branding, or unrealistic proportions. Photorealistic, square 500 × 500 px. Treat the uploaded product photo as the primary visual reference; do not change the product itself—only change the environment, lighting, props, camera angle, and presentation."
            },
            ingredient: {
                id: 3,
                title: "3. Creative Ingredient / Feature Shot",
                short: "Surrounded with dynamic floating elements representing ingredients, fragrance, flavor, or features.",
                template: "Create a creative commercial product photography image using the uploaded [PRODUCT NAME] reference as the exact product source. Preserve all product details and branding accurately. Surround the product with visually attractive elements representing its main ingredients, fragrance, flavor, technology, or features: [INGREDIENTS / FEATURES]. Arrange the elements dynamically around the product without covering the logo or important packaging information. Use premium studio lighting, controlled highlights, realistic shadows, subtle floating elements where appropriate, and a polished advertising aesthetic. Keep the background [BACKGROUND STYLE/COLOR]. No fake text or changes to the product. Square image, 500 × 500 px. Treat the uploaded product photo as the primary visual reference; do not change the product itself—only change the environment, lighting, props, camera angle, and presentation."
            },
            pedestal: {
                id: 4,
                title: "4. Luxury Reflection / Pedestal Shot",
                short: "Positioned on an elegant marble or glass pedestal with studio backdrop, rim light & atmospheric glow.",
                template: "Generate a luxury product photography scene based on the uploaded reference photo of [PRODUCT NAME]. Reproduce the product exactly as shown, with no changes to its logo, label, color, packaging, proportions, or texture. Position it on an elegant [MARBLE / ACRYLIC / STONE / GLASS] pedestal with a sophisticated [COLOR] studio backdrop. Add soft spotlighting, premium rim light, subtle atmospheric glow, realistic reflections, and controlled shadows. Create a clean, modern, expensive beauty-advertising look with generous negative space. Product should be sharp and dominant in the frame. No watermark, no additional text, no distorted packaging. Output 500 × 500 px, 1:1 aspect ratio. Treat the uploaded product photo as the primary visual reference; do not change the product itself—only change the environment, lighting, props, camera angle, and presentation."
            },
            social: {
                id: 5,
                title: "5. Social Media Advertising Shot",
                short: "Scroll-stopping contemporary scene with geometric platforms, soft gradients & bold advertising lighting.",
                template: "Create a scroll-stopping social media product advertising image based on the uploaded reference photo of [PRODUCT NAME]. Maintain exact visual consistency with the original product—same branding, logo, packaging, shape, colors, label details, and materials. Build a bold contemporary scene using [BRAND COLORS], geometric platforms, soft gradients, tasteful props, realistic shadows, and dramatic but professional studio lighting. Make the product the clear focal point, with a polished premium commercial-photography finish. Leave some clean negative space for optional marketing copy, but do not generate any text inside the image. Avoid duplicates, distorted logos, altered packaging, or unrealistic objects. Square composition, 500 × 500 px. Treat the uploaded product photo as the primary visual reference; do not change the product itself—only change the environment, lighting, props, camera angle, and presentation."
            }
        };

        let activePromptKey = 'hero';
        const promptTabBtns = document.querySelectorAll('.ai-prompt-tab-btn');
        const promptCardTitle = document.getElementById('ai_prompt_card_title');
        const promptCardDesc = document.getElementById('ai_prompt_card_desc');
        const insertPromptBtn = document.getElementById('ai_insert_prompt_btn');
        const copyPromptBtn = document.getElementById('ai_copy_prompt_btn');
        const clearKeywordsBtn = document.getElementById('ai_clear_keywords_btn');
        const keywordsInput = document.getElementById('ai_image_keywords');

        function getActiveProductName() {
            const nameInput = document.querySelector('input[name="name"]');
            return (nameInput && nameInput.value.trim()) ? nameInput.value.trim() : 'Product';
        }

        function getPopulatedPrompt(key) {
            const item = readyMadePrompts[key] || readyMadePrompts['hero'];
            const pName = getActiveProductName();
            let text = item.template;
            text = text.replaceAll('[PRODUCT NAME]', pName);
            text = text.replaceAll('[BACKGROUND COLOR]', selectedBgColor || 'pure white');
            text = text.replaceAll('[BACKGROUND STYLE/COLOR]', (selectedBgColor || 'studio clean') + ' solid backdrop');
            text = text.replaceAll('[COLOR]', selectedBgColor || 'studio solid');
            return text;
        }

        function selectPromptTab(key) {
            activePromptKey = key;
            const item = readyMadePrompts[key];
            if (!item) return;

            promptTabBtns.forEach(btn => {
                if (btn.dataset.promptKey === key) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });

            if (promptCardTitle) promptCardTitle.textContent = item.title;
            if (promptCardDesc) promptCardDesc.textContent = item.short;
        }

        promptTabBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                selectPromptTab(this.dataset.promptKey);
            });
        });

        if (insertPromptBtn && keywordsInput) {
            insertPromptBtn.addEventListener('click', function() {
                const filled = getPopulatedPrompt(activePromptKey);
                keywordsInput.value = filled;
                keywordsInput.focus();
                keywordsInput.classList.add('border-primary');
                setTimeout(() => keywordsInput.classList.remove('border-primary'), 1000);
                showStudioToast('{{ __('Prompt loaded into box! You can customize bracketed parts.') }}');
            });
        }

        if (copyPromptBtn) {
            copyPromptBtn.addEventListener('click', function() {
                const filled = getPopulatedPrompt(activePromptKey);
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(filled).then(() => {
                        showStudioToast('{{ __('Prompt copied to clipboard!') }}');
                    });
                } else {
                    showStudioToast('{{ __('Clipboard unavailable.') }}', true);
                }
            });
        }

        if (clearKeywordsBtn && keywordsInput) {
            clearKeywordsBtn.addEventListener('click', function() {
                keywordsInput.value = '';
                showStudioToast('{{ __('Reset: Ready to render all 5 ready-made styles automatically!') }}');
            });
        }

        // Keyword suggestion pills
        const keywordPills = document.querySelectorAll('.ai-keyword-pill');
        keywordPills.forEach(pill => {
            pill.addEventListener('click', function() {
                const kw = this.dataset.keyword;
                if (!keywordsInput) return;
                const cur = keywordsInput.value.trim();
                if (cur) {
                    keywordsInput.value = cur + ', ' + kw;
                } else {
                    keywordsInput.value = kw;
                }
                this.classList.add('bg-primary', 'text-white');
                setTimeout(() => this.classList.remove('bg-primary', 'text-white'), 400);
            });
        });

        // Generate 5 AI Images handler
        const generateBtn = document.getElementById('ai_generate_images_btn');
        const studioContentArea = document.getElementById('ai_studio_content_area');
        const emptyState = document.getElementById('ai_studio_empty_state');
        const loadingState = document.getElementById('ai_studio_loading_state');
        const resultsGrid = document.getElementById('ai_studio_results_grid');
        const bulkActions = document.getElementById('ai_bulk_actions');
        const countBadge = document.getElementById('ai_generated_count_badge');
        const errorBanner = document.getElementById('ai_studio_api_error_banner');
        const errorText = document.getElementById('ai_studio_api_error_text');

        // Reset studio states whenever modal is opened
        const studioModal = document.getElementById('aiImageStudioModal');
        if (studioModal) {
            studioModal.addEventListener('shown.bs.modal', function() {
                if (!generatedStudioImages || generatedStudioImages.length === 0) {
                    if (emptyState) {
                        emptyState.classList.remove('d-none');
                        emptyState.classList.add('d-flex');
                    }
                    if (loadingState) {
                        loadingState.classList.add('d-none');
                        loadingState.classList.remove('d-flex');
                    }
                    if (resultsGrid) {
                        resultsGrid.classList.add('d-none');
                        resultsGrid.classList.remove('d-flex');
                    }
                }
            });
        }

        if (generateBtn) {
            generateBtn.addEventListener('click', async function() {
                // Clear previous errors
                if (errorBanner) errorBanner.classList.add('d-none');

                // Get product name from form
                const productName = getActiveProductName();

                // Check reference image
                const refImgSrc = refPreviewImg ? refPreviewImg.src : null;

                const formData = new FormData();
                formData.append('product_name', productName);
                formData.append('keywords', keywordsInput ? keywordsInput.value.trim() : '');
                formData.append('background_color', selectedBgColor);

                if (currentRefFile) {
                    formData.append('reference_image', currentRefFile);
                } else if (refImgSrc && !refImgSrc.includes('upload.png')) {
                    if (refImgSrc.startsWith('data:')) {
                        formData.append('reference_image_base64', refImgSrc);
                    } else {
                        formData.append('reference_image_url', refImgSrc);
                    }
                }

                // UI loading state (Cleanly toggle via d-none/d-flex)
                generateBtn.disabled = true;
                generateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> {{ __('Generating 5 Variations...') }}';
                if (emptyState) {
                    emptyState.classList.add('d-none');
                    emptyState.classList.remove('d-flex');
                }
                if (resultsGrid) {
                    resultsGrid.classList.add('d-none');
                    resultsGrid.classList.remove('d-flex');
                }
                if (loadingState) {
                    loadingState.classList.remove('d-none');
                    loadingState.classList.add('d-flex');
                }
                if (studioContentArea) studioContentArea.scrollTop = 0;

                try {
                    const response = await fetch("{{ route('shop.product.ai-generate-images') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json();

                    // Show Top Error / Warning Banner if Gemini returned an issue
                    if (data.gemini_error && errorBanner && errorText) {
                        errorText.innerHTML = `<strong>{{ __('Google Gemini API Note') }}:</strong> ${data.gemini_error}` +
                            (data.used_fallback ? `<div class="small mt-1 text-muted">{{ __('High-resolution studio fallback generator was automatically used to render your 5 images below.') }}</div>` : '');
                        errorBanner.classList.remove('d-none');
                        errorBanner.classList.add('d-flex');
                    }

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || data.gemini_error || '{{ __('Failed to generate images.') }}');
                    }

                    generatedStudioImages = data.images || [];
                    renderStudioGrid(generatedStudioImages);

                    if (bulkActions) bulkActions.style.setProperty('display', 'flex', 'important');
                    if (countBadge) countBadge.textContent = `${generatedStudioImages.length} / 5`;
                    if (studioContentArea) studioContentArea.scrollTop = 0;
                    showStudioToast(`{{ __('5 Studio variations rendered successfully!') }}`);

                } catch (err) {
                    console.error('Image generation error:', err);
                    if (errorBanner && errorText) {
                        errorText.innerHTML = `<strong>{{ __('Generation Failed') }}:</strong> ${err.message}`;
                        errorBanner.classList.remove('d-none');
                        errorBanner.classList.add('d-flex');
                    }
                    showStudioToast(err.message || '{{ __('Error during image generation.') }}', true);
                    if (emptyState && (!generatedStudioImages || generatedStudioImages.length === 0)) {
                        emptyState.classList.remove('d-none');
                        emptyState.classList.add('d-flex');
                    }
                } finally {
                    generateBtn.disabled = false;
                    generateBtn.innerHTML = '<i class="bi bi-stars fs-6 me-1.5"></i> {{ __('Generate 5 AI Images') }}';
                    if (loadingState) {
                        loadingState.classList.add('d-none');
                        loadingState.classList.remove('d-flex');
                    }
                }
            });
        }

        // Render Generated Images Grid
        function renderStudioGrid(images) {
            if (!resultsGrid) return;
            resultsGrid.innerHTML = '';

            images.forEach((img, index) => {
                const col = document.createElement('div');
                col.className = 'col';
                col.innerHTML = `
                    <div class="card h-100 border rounded-3 overflow-hidden shadow-2xs ai-card-hover position-relative bg-white" id="studio_card_${index}">
                        <div class="position-relative text-center d-flex align-items-center justify-content-center p-2" style="height: 165px; background-color: ${selectedBgColor};">
                            <img src="${img.base64 || img.url}" alt="${img.label}" class="w-100 h-100 object-fit-contain rounded-2 shadow-xs">
                            <span class="badge bg-dark bg-opacity-75 text-white position-absolute top-0 start-0 m-2 font-monospace" style="font-size: 9.5px;">
                                ${img.badge || ('#' + (index + 1))}
                            </span>
                            <button type="button" class="btn btn-sm btn-light rounded-circle position-absolute top-0 end-0 m-2 shadow-xs p-0 d-flex align-items-center justify-content-center border" onclick="window.openStudioZoom('${img.base64 || img.url}')" title="{{ __('Zoom Preview') }}" style="width: 26px; height: 26px;">
                                <i class="bi bi-arrows-fullscreen text-dark" style="font-size: 10px;"></i>
                            </button>
                        </div>
                        <div class="card-body p-2 d-flex flex-column justify-content-between">
                            <div class="mb-2">
                                <h6 class="fw-bold text-dark m-0 small" style="font-size: 11.5px;">${img.label || 'Perspective'}</h6>
                                <p class="text-muted small m-0" style="font-size: 10.5px; line-height: 1.2;">${img.description || ''}</p>
                            </div>
                            <div class="d-flex gap-1 mt-auto pt-1.5 border-top">
                                <button type="button" id="btn_set_main_${index}" class="btn btn-xs btn-outline-primary flex-grow-1 py-1 rounded-2 fw-semibold d-flex align-items-center justify-content-center gap-1 shadow-2xs" onclick="window.applyStudioImageAsMain(${index})" style="font-size: 10.5px;" title="{{ __('Set as Main Thumbnail') }}">
                                    <i class="bi bi-star-fill text-warning" style="font-size: 10px;"></i> <span>{{ __('Set Main') }}</span>
                                </button>
                                <button type="button" id="btn_add_gallery_${index}" class="btn btn-xs btn-outline-secondary flex-grow-1 py-1 rounded-2 fw-semibold d-flex align-items-center justify-content-center gap-1 shadow-2xs" onclick="window.applyStudioImageToGallery(${index})" style="font-size: 10.5px;" title="{{ __('Add to Gallery') }}">
                                    <i class="bi bi-plus-lg text-primary" style="font-size: 10px;"></i> <span>{{ __('+ Gallery') }}</span>
                                </button>
                                <a href="${img.base64 || img.url}" download="${img.filename || 'ai-product.png'}" class="btn btn-xs btn-light border py-1 px-1.5 rounded-2 text-muted shadow-2xs d-flex align-items-center" title="{{ __('Download Image') }}">
                                    <i class="bi bi-download" style="font-size: 10px;"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                `;
                resultsGrid.appendChild(col);
            });

            if (emptyState) {
                emptyState.classList.add('d-none');
                emptyState.classList.remove('d-flex');
            }
            if (loadingState) {
                loadingState.classList.add('d-none');
                loadingState.classList.remove('d-flex');
            }
            resultsGrid.classList.remove('d-none');
            resultsGrid.classList.add('d-flex');
        }

        // Manual Image Upload ("Also i can added manually also")
        const manualUploadBtn = document.getElementById('ai_trigger_manual_upload_btn');
        const manualUploadInput = document.getElementById('ai_manual_upload_input');

        if (manualUploadBtn && manualUploadInput) {
            manualUploadBtn.addEventListener('click', () => manualUploadInput.click());

            manualUploadInput.addEventListener('change', async function() {
                if (!this.files || !this.files[0]) return;

                const file = this.files[0];
                const uploadFormData = new FormData();
                uploadFormData.append('manual_image', file);

                manualUploadBtn.disabled = true;
                manualUploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> {{ __('Uploading...') }}';

                try {
                    const resp = await fetch("{{ route('shop.product.ai-upload-manual-image') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: uploadFormData
                    });

                    const resData = await resp.json();
                    if (!resp.ok || !resData.success) {
                        throw new Error(resData.message || '{{ __('Failed to upload image.') }}');
                    }

                    generatedStudioImages.push(resData.image);
                    renderStudioGrid(generatedStudioImages);

                    if (emptyState) emptyState.style.display = 'none';
                    if (bulkActions) bulkActions.style.setProperty('display', 'flex', 'important');
                    if (countBadge) countBadge.textContent = `${generatedStudioImages.length} Photos`;

                    showStudioToast('{{ __('Manual photo added to studio!') }}');

                } catch (err) {
                    console.error(err);
                    showStudioToast(err.message || '{{ __('Upload failed.') }}', true);
                } finally {
                    manualUploadBtn.disabled = false;
                    manualUploadBtn.innerHTML = '<i class="bi bi-cloud-arrow-up me-1"></i> {{ __('Add Photo Manually to Studio') }}';
                    manualUploadInput.value = '';
                }
            });
        }

        // Global functions exposed to window
        window.openStudioZoom = function(url) {
            const zoomImg = document.getElementById('ai_zoom_img');
            if (zoomImg) zoomImg.src = url;
            const zoomModal = new bootstrap.Modal(document.getElementById('aiImageZoomModal'));
            zoomModal.show();
        };

        // Set as Main Thumbnail in Form (Uses local Base64 conversion - NO CORS ERRORS)
        window.applyStudioImageAsMain = function(index) {
            try {
                const img = generatedStudioImages[index];
                if (!img) return;

                const file = dataURLtoFile(img.base64, img.filename || 'ai-main-thumbnail.png');
                if (!file) {
                    throw new Error('Could not convert image data.');
                }

                const dt = new DataTransfer();
                dt.items.add(file);

                const thumbInput = document.getElementById('thumbnail');
                if (thumbInput) {
                    thumbInput.files = dt.files;
                }

                const previewImg = document.getElementById('preview');
                if (previewImg) {
                    previewImg.src = img.base64 || img.url;
                }

                // Reset all main buttons
                generatedStudioImages.forEach((_, i) => {
                    const btn = document.getElementById(`btn_set_main_${i}`);
                    if (btn) {
                        btn.className = 'btn btn-xs btn-outline-primary flex-grow-1 py-1 rounded-2 fw-semibold d-flex align-items-center justify-content-center gap-1 shadow-2xs';
                        btn.innerHTML = '<i class="bi bi-star-fill text-warning" style="font-size: 10px;"></i> <span>{{ __('Set Main') }}</span>';
                    }
                });

                // Highlight clicked button as active
                const activeBtn = document.getElementById(`btn_set_main_${index}`);
                if (activeBtn) {
                    activeBtn.className = 'btn btn-xs btn-success flex-grow-1 py-1 rounded-2 fw-bold d-flex align-items-center justify-content-center gap-1 shadow-2xs text-white';
                    activeBtn.innerHTML = '<i class="bi bi-check2-circle" style="font-size: 11px;"></i> <span>{{ __('Main Set!') }}</span>';
                }

                showStudioToast('{{ __('Photo set as Main Product Thumbnail!') }}');
            } catch (err) {
                console.error(err);
                showStudioToast('{{ __('Could not assign image to thumbnail.') }}', true);
            }
        };

        // Add to Gallery Images in Form (Uses local Base64 conversion - NO CORS ERRORS)
        window.applyStudioImageToGallery = function(index) {
            try {
                const img = generatedStudioImages[index];
                if (!img) return;

                const file = dataURLtoFile(img.base64, img.filename || 'ai-gallery-image.png');
                if (!file) {
                    throw new Error('Could not convert gallery image data.');
                }

                if (typeof window.thumbnailCount === 'undefined') {
                    window.thumbnailCount = 100;
                }
                window.thumbnailCount++;

                const currentCount = window.thumbnailCount;
                const newThumbnailId = `additionThumbnail${currentCount}`;
                const newPreviewId = `preview${currentCount}`;
                const mainBoxId = `addition${currentCount}`;

                const newBox = document.createElement('div');
                newBox.id = mainBoxId;
                newBox.className = 'position-relative';

                newBox.innerHTML = `
                    <label for="${newThumbnailId}" class="additionThumbnail cursor-pointer border rounded-3 p-1 bg-white shadow-2xs d-block position-relative" style="width: 75px; height: 75px; overflow: visible !important;">
                        <img src="${img.base64 || img.url}" id="${newPreviewId}" alt="gallery image" class="w-100 h-100 object-fit-cover rounded-2">
                        <button onclick="event.preventDefault(); event.stopPropagation(); removeThumbnail('${mainBoxId}')" type="button" class="delete btn btn-danger btn-sm rounded-circle p-0 position-absolute" title="{{ __('Remove') }}" style="width: 22px; height: 22px; top: -7px; right: -7px; border: 2px solid #ffffff; box-shadow: 0 2px 5px rgba(0,0,0,0.25); z-index: 20; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-x-lg" style="font-size: 9px; line-height: 1;"></i>
                        </button>
                    </label>
                    <input id="${newThumbnailId}" accept="image/*" type="file" name="additionThumbnail[]" class="d-none">
                `;

                const dt = new DataTransfer();
                dt.items.add(file);
                const inputEl = newBox.querySelector('input');
                if (inputEl) {
                    inputEl.files = dt.files;
                }

                const additionalElements = document.getElementById('additionalElements');
                if (additionalElements) {
                    const placeholder = document.getElementById('addition');
                    if (placeholder) {
                        additionalElements.insertBefore(newBox, placeholder);
                    } else {
                        additionalElements.appendChild(newBox);
                    }
                }

                // Update button state on card
                const galleryBtn = document.getElementById(`btn_add_gallery_${index}`);
                if (galleryBtn) {
                    galleryBtn.className = 'btn btn-xs btn-primary flex-grow-1 py-1 rounded-2 fw-semibold d-flex align-items-center justify-content-center gap-1 shadow-2xs text-white';
                    galleryBtn.innerHTML = '<i class="bi bi-check2" style="font-size: 11px;"></i> <span>{{ __('Added!') }}</span>';
                }

                showStudioToast('{{ __('Photo added to Gallery Images!') }}');
            } catch (err) {
                console.error(err);
                showStudioToast('{{ __('Could not add image to gallery.') }}', true);
            }
        };

        // Bulk: Add All to Gallery
        const addAllBtn = document.getElementById('ai_add_all_to_gallery_btn');
        if (addAllBtn) {
            addAllBtn.addEventListener('click', function() {
                if (!generatedStudioImages || generatedStudioImages.length === 0) return;
                for (let i = 0; i < generatedStudioImages.length; i++) {
                    window.applyStudioImageToGallery(i);
                }
                showStudioToast('{{ __('All studio photos added to Gallery Images!') }}');
            });
        }

        // Bulk: Set #1 as Main & Rest to Gallery
        const applyMainAndRestBtn = document.getElementById('ai_apply_main_and_gallery_btn');
        if (applyMainAndRestBtn) {
            applyMainAndRestBtn.addEventListener('click', function() {
                if (!generatedStudioImages || generatedStudioImages.length === 0) return;

                // First is Main Thumbnail
                window.applyStudioImageAsMain(0);

                // Rest are Gallery Images
                for (let i = 1; i < generatedStudioImages.length; i++) {
                    window.applyStudioImageToGallery(i);
                }

                showStudioToast('{{ __('Main Thumbnail & Gallery Images populated successfully!') }}');
            });
        }

    })();
</script>
