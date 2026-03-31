{{-- Telegram Connect Popup - Shows for authenticated users without telegram_chat_id --}}
@auth
@if(!auth()->user()->telegram_chat_id)
@php
    $telegramService = app(\App\Services\TelegramService::class);
    $tgBotUsername = config('services.telegram.bot_username', '');
    $tgConfigured = !empty($tgBotUsername);
    $tgLink = $tgConfigured ? $telegramService->generateStartLink(auth()->id()) : '#';
@endphp
@if($tgConfigured)
<!-- Telegram Connect Modal -->
<div class="modal fade" id="telegramConnectModal" tabindex="-1" aria-labelledby="telegramConnectLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content" style="border:none; border-radius:20px; overflow:hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
            <div class="modal-body p-0">
                <!-- Header gradient -->
                <div style="background: linear-gradient(135deg, #0088cc, #00aaee); padding: 28px 24px 20px; text-align:center;">
                    <div style="width:72px;height:72px;margin:0 auto 12px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;">
                        <i class="bx bxl-telegram" style="font-size:40px;color:#fff;"></i>
                    </div>
                    <h5 style="color:#fff;font-weight:700;margin:0 0 4px;">Connect Telegram</h5>
                    <p style="color:rgba(255,255,255,0.85);font-size:0.88rem;margin:0;">Get instant notifications on your phone</p>
                </div>

                <!-- Body -->
                <div style="padding: 24px;">
                    <p style="color:#555;font-size:0.9rem;text-align:center;margin-bottom:20px;line-height:1.6;">
                        Link your Telegram account to receive <strong>instant proforma notifications</strong> directly on your phone.
                    </p>

                    <!-- Steps -->
                    <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:12px;padding:14px 16px;margin-bottom:20px;">
                        <div style="font-size:0.75rem;font-weight:600;color:#0284c7;text-transform:uppercase;letter-spacing:0.03em;margin-bottom:8px;">
                            <i class="bx bx-info-circle" style="margin-right:4px;"></i>How it works
                        </div>
                        <div style="display:flex;align-items:flex-start;gap:8px;margin-bottom:6px;font-size:0.83rem;color:#475569;">
                            <span style="flex-shrink:0;width:20px;height:20px;border-radius:50%;background:linear-gradient(135deg,#0088cc,#00aaee);color:#fff;font-size:0.65rem;font-weight:700;display:flex;align-items:center;justify-content:center;">1</span>
                            <span>Tap <strong>"Open Telegram"</strong> below</span>
                        </div>
                        <div style="display:flex;align-items:flex-start;gap:8px;margin-bottom:6px;font-size:0.83rem;color:#475569;">
                            <span style="flex-shrink:0;width:20px;height:20px;border-radius:50%;background:linear-gradient(135deg,#0088cc,#00aaee);color:#fff;font-size:0.65rem;font-weight:700;display:flex;align-items:center;justify-content:center;">2</span>
                            <span>Press <strong>"Start"</strong> in the Telegram chat</span>
                        </div>
                        <div style="display:flex;align-items:flex-start;gap:8px;font-size:0.83rem;color:#475569;">
                            <span style="flex-shrink:0;width:20px;height:20px;border-radius:50%;background:linear-gradient(135deg,#0088cc,#00aaee);color:#fff;font-size:0.65rem;font-weight:700;display:flex;align-items:center;justify-content:center;">3</span>
                            <span>Done! You'll receive notifications instantly</span>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <a href="{{ $tgLink }}" target="_blank" 
                       style="display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:13px 20px;background:linear-gradient(135deg,#0088cc,#00aaee);color:#fff;border:none;border-radius:50px;font-size:0.95rem;font-weight:600;text-decoration:none;box-shadow:0 4px 18px rgba(0,136,204,0.3);transition:all 0.3s ease;margin-bottom:10px;"
                       onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 28px rgba(0,136,204,0.4)'"
                       onmouseout="this.style.transform='';this.style.boxShadow='0 4px 18px rgba(0,136,204,0.3)'">
                        <i class="bx bxl-telegram" style="font-size:1.2rem;"></i> Open Telegram & Connect
                    </a>
                    <button type="button" class="btn" data-bs-dismiss="modal"
                            style="display:flex;align-items:center;justify-content:center;width:100%;padding:11px 20px;background:transparent;color:#6b7280;border:2px solid #e5e7eb;border-radius:50px;font-size:0.9rem;font-weight:500;transition:all 0.3s ease;"
                            onmouseover="this.style.borderColor='#28a745';this.style.color='#28a745'"
                            onmouseout="this.style.borderColor='#e5e7eb';this.style.color='#6b7280'"
                            onclick="localStorage.setItem('tg_popup_dismissed_{{ auth()->id() }}', Date.now())">
                        Skip for Now
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Only show if not dismissed in the last 24 hours
    var dismissed = localStorage.getItem('tg_popup_dismissed_{{ auth()->id() }}');
    var oneDayMs = 24 * 60 * 60 * 1000;
    
    if (!dismissed || (Date.now() - parseInt(dismissed)) > oneDayMs) {
        setTimeout(function() {
            var modal = new bootstrap.Modal(document.getElementById('telegramConnectModal'));
            modal.show();
        }, 1500); // Small delay so the page loads first
    }
});
</script>
@endif
@endif
@endauth
