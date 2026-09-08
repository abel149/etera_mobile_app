@extends('layouts.authentication')
@section('title', 'Terms & Conditions — Etera')

@section('styles')
<style>
/* Widen card for T&C */
.etera-auth-card { max-width: 700px !important; padding: 2rem !important; }

/* Lang toggle */
.tc-lang-toggle { display:inline-flex; background:#f0f4f8; border-radius:50px; padding:3px; gap:3px; }
.tc-lang-btn {
    border: none; background: transparent; padding: 5px 18px; border-radius: 50px;
    font-size: 0.82rem; font-weight: 600; color: var(--etera-text-muted);
    cursor: pointer; transition: var(--etera-transition);
}
.tc-lang-btn.active { background: var(--etera-gradient); color: #fff; box-shadow: 0 2px 8px rgba(40,167,69,0.25); }

/* Scrollable T&C body */
.tc-scroll {
    background: #f8fafb;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.25rem 1.4rem;
    max-height: 340px;
    overflow-y: auto;
    font-size: 0.88rem;
    line-height: 1.78;
    color: #374151;
    scroll-behavior: smooth;
}
.tc-scroll::-webkit-scrollbar { width: 5px; }
.tc-scroll::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
.tc-scroll::-webkit-scrollbar-thumb { background: #28a745; border-radius: 10px; }
.tc-scroll ol { padding-left: 1.3rem; margin: 0; }
.tc-scroll li { margin-bottom: 0.85rem; }
.tc-scroll li strong { color: #1a1a2e; }
.tc-scroll hr { border-color: #e2e8f0; margin: 0.75rem 0; }

/* Amharic font friendlier line height */
[data-tc="am"] { line-height: 1.9; font-size: 0.87rem; }

/* Custom checkbox */
.tc-check-wrap {
    display: flex; align-items: flex-start; gap: 12px;
    background: linear-gradient(135deg, rgba(40,167,69,0.07), rgba(32,201,151,0.07));
    border: 1.5px solid rgba(40,167,69,0.25);
    border-radius: 12px; padding: 1rem 1.1rem; cursor: pointer;
}
.tc-check-wrap input[type="checkbox"] {
    width: 20px; height: 20px; accent-color: #28a745;
    margin-top: 2px; cursor: pointer; flex-shrink: 0;
}
.tc-check-wrap label { cursor: pointer; font-size: 0.92rem; color: var(--etera-text-soft); line-height: 1.5; }
.tc-check-wrap label strong { color: var(--etera-text); }

/* Error banner */
.tc-error {
    background: #fff5f5; border: 1.5px solid #fca5a5;
    border-radius: 10px; padding: 0.75rem 1rem;
    font-size: 0.88rem; color: #dc2626; margin-bottom: 1rem;
    display: flex; align-items: center; gap: 8px;
}

/* Scroll hint */
.tc-scroll-hint {
    font-size: 0.78rem; color: var(--etera-text-muted);
    text-align: center; margin-top: 4px; margin-bottom: 0.75rem;
}
</style>
@endsection

{{-- ─── LEFT BRANDING PANEL ─── --}}
@section('branding')
<div style="text-align:center; max-width:360px;">
    <div style="width:80px;height:80px;border-radius:50%;background:rgba(255,255,255,0.15);display:inline-flex;align-items:center;justify-content:center;margin-bottom:1.5rem;backdrop-filter:blur(10px);">
        <i class="bx bx-file-blank" style="font-size:2.5rem;color:#fff;"></i>
    </div>
    <h2 style="color:#fff;font-weight:800;font-size:1.85rem;margin-bottom:0.75rem;line-height:1.3;">
        Terms &amp;<br>Conditions
    </h2>
    <p style="color:rgba(255,255,255,0.80);font-size:1rem;line-height:1.7;margin-bottom:2rem;">
        Before you start using the Etera platform, please review and accept our Terms &amp; Conditions.
    </p>
    <div style="background:rgba(255,255,255,0.12);border-radius:14px;padding:1.1rem 1.3rem;text-align:left;backdrop-filter:blur(6px);">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:0.6rem;">
            <i class="bx bx-check-circle" style="color:#a5f3c0;font-size:1.1rem;"></i>
            <span style="color:#fff;font-size:0.88rem;">Your data stays private &amp; secure</span>
        </div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:0.6rem;">
            <i class="bx bx-check-circle" style="color:#a5f3c0;font-size:1.1rem;"></i>
            <span style="color:#fff;font-size:0.88rem;">One-time agreement per account</span>
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
            <i class="bx bx-check-circle" style="color:#a5f3c0;font-size:1.1rem;"></i>
            <span style="color:#fff;font-size:0.88rem;">Available in English &amp; Amharic</span>
        </div>
    </div>
</div>
@endsection

{{-- ─── RIGHT FORM PANEL (inside .etera-glass-card) ─── --}}
@section('content')

{{-- Header --}}
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:1.1rem;">
    <div>
        <h4 style="font-weight:800;color:var(--etera-text);margin:0 0 2px 0;font-size:1.2rem;">
            Terms &amp; Conditions
        </h4>
        <p style="color:var(--etera-text-muted);font-size:0.8rem;margin:0;">Effective Date: October 2025</p>
    </div>
    <div class="tc-lang-toggle" id="langToggle" role="tablist" aria-label="Terms language">
        <button type="button" class="tc-lang-btn active" data-tc-lang="en" aria-selected="true">EN</button>
        <button type="button" class="tc-lang-btn" data-tc-lang="am" aria-selected="false">አማ</button>
    </div>
</div>

@if($errors->any())
<div class="tc-error">
    <i class="bx bx-error-circle" style="font-size:1.1rem;flex-shrink:0;"></i>
    {{ $errors->first() }}
</div>
@endif

{{-- T&C Scrollable Content --}}
<div class="tc-scroll" id="tcScroll">

    {{-- ── ENGLISH ── --}}
    <div data-tc="en">
        <p><strong>Welcome to ETERA.</strong> By accessing or using our platform, you agree to be bound by the following Terms and Conditions. Please read them carefully.</p>
        <hr>
        <ol>
            <li><strong>Acceptance of Terms</strong><br>By using ETERA, you confirm that you have read, understood, and agreed to these Terms and Conditions. If you do not agree, you must not use the platform.</li>
            <li><strong>Eligibility</strong><br>You must be at least 18 years old or have legal parental/guardian consent to use ETERA. You represent that you have the authority to enter into this agreement.</li>
            <li><strong>Account Registration</strong><br>To access certain features, you may be required to register an account. You agree to provide accurate information and to keep your login credentials secure.</li>
            <li><strong>Use of the Platform</strong><br>You agree to use ETERA only for lawful purposes and in accordance with these Terms. You must not misuse the platform or attempt unauthorized access.</li>
            <li><strong>Product and Service Descriptions</strong><br>ETERA strives to provide accurate descriptions of products and services. However, we do not warrant that descriptions or other content are error-free, complete, or current.</li>
            <li><strong>Platform Role and Provider Responsibility</strong><br>ETERA acts solely as a facilitator of instant price quotes provided by spare part providers registered on our platform. We do not manufacture, stock, or sell any spare parts directly.<br><br>All products and services listed are offered by independent providers. ETERA is not responsible for the quality, condition, availability, or delivery of any parts sold, nor for any store's return, refund, or warranty policies. Any disputes or claims regarding a product must be resolved directly with the provider.</li>
            <li><strong>Orders and Availability</strong><br>All orders are subject to acceptance and availability. We reserve the right to refuse or cancel any order at our discretion.</li>
            <li><strong>Intellectual Property</strong><br>All content on ETERA, including logos, text, graphics, and software, is the property of ETERA or its licensors and is protected by applicable intellectual property laws.</li>
            <li><strong>User Content</strong><br>You may submit content (e.g., reviews, feedback). By doing so, you grant ETERA a non-exclusive, royalty-free license to use, reproduce, and display such content.</li>
            <li><strong>Prohibited Conduct</strong><br>You agree not to: violate any laws or regulations; infringe on intellectual property rights; transmit harmful or malicious code; use automated systems to access the platform.</li>
            <li><strong>Third-Party Links</strong><br>ETERA may contain links to third-party websites. We are not responsible for the content, policies, or practices of those sites.</li>
            <li><strong>Limitation of Liability</strong><br>ETERA is not liable for any indirect, incidental, or consequential damages arising from your use of the platform. Our total liability is limited to the amount paid by you for the relevant product or service.</li>
            <li><strong>Pricing and Payments</strong><br>All prices listed on ETERA are subject to change without prior notice. Payment must be made using one of the available payment methods indicated on the ETERA platform.</li>
            <li><strong>Refunds and Cancellations</strong><br>ETERA does not sell spare parts directly and is not responsible for refund or cancellation policies set by individual providers. Any requests must be directed to the spare part provider.</li>
            <li><strong>Privacy</strong><br>Your use of ETERA is also governed by our Privacy Policy. By using the platform, you consent to the collection and use of your information as described therein.</li>
            <li><strong>Changes to Terms</strong><br>ETERA reserves the right to update these Terms at any time. Continued use of the platform constitutes acceptance of the revised Terms.</li>
            <li><strong>Governing Law</strong><br>These Terms are governed by the laws of Ethiopia. Any disputes shall be resolved in accordance with the law.</li>
            <li><strong>Contact Us</strong><br>For questions or concerns, please reach out to us at the address provided on our platform.</li>
        </ol>
    </div>

    {{-- ── AMHARIC ── --}}
    <div data-tc="am" style="display:none;">
        <p><strong>የኢተራ ደንቦች እና ሁኔታዎች</strong></p>
        <p style="color:var(--etera-text-muted);font-size:0.82rem;">የሥራ መጀመሪያ ቀን፡ ጥቅምት 2018</p>
        <p>እንኳን ወደ ኢተራ በደህና መጡ። የእኛን ድህረገጽ ለመጠቀም በሚከተሉት ደንቦች እና ሁኔታዎች ላይ ተስማምተዋል። እባክዎ በጥንቃቄ ያንብቧቸው።</p>
        <hr>
        <ol>
            <li><strong>የደንቦቹ ተቀባይነት</strong><br>ኢተራን በመጠቀምዎ፣ እነዚህን ደንቦች እና ሁኔታዎች እንዳነበቡዋቸው፣ እንደተረዷቸው እና እንደተስማሙባቸው ያረጋግጣሉ። ካልተስማሙ፣ ድህረገጹን መጠቀም የለብዎትም።</li>
            <li><strong>ብቁነት</strong><br>ኢተራን ለመጠቀም ቢያንስ 18 ዓመት ወይም ከዚያ በላይ መሆን አለብዎት ወይም የሕግ ወላጅ/አሳዳጊ ፈቃድ ሊኖርዎት ይገባል።</li>
            <li><strong>መለያ ምዝገባ</strong><br>የተወሰኑ መረጃዎችን ለማግኘት፣ መለያ እንዲመዘግቡ ሊጠየቁ ይችላሉ። ትክክለኛ መረጃ ለማቅረብ እና የመግቢያ ማረጋገጫዎን ደህንነቱ በተጠበቀ ሁኔታ ለማቆየት ተስማምተዋል።</li>
            <li><strong>የ ድህረገጽ አጠቃቀም</strong><br>ኢተራን ለህጋዊ ዓላማዎች ብቻ እና በነዚህ ደንቦች መሠረት ለመጠቀም ተስማምተዋል። ድህረገጹን አላግባብ መጠቀም ወይም ያልተፈቀደ ተደራሽነት መሞከር የለብዎትም።</li>
            <li><strong>የምርት እና የአገልግሎት መግለጫዎች</strong><br>ኢተራ የምርቶችን እና አገልግሎቶችን ትክክለኛ መግለጫዎች ለማቅረብ ይጥራል። ነገር ግን፣ ዋስትና አንሰጥም።</li>
            <li><strong>የ ድህረገጹ ሚና እና የአቅራቢ ኃላፊነት</strong><br>ኢተራ በድህረገጻችን ላይ በተመዘገቡ መለዋወጫ አቅራቢዎች የሚሰጡ ፈጣን የዋጋ ጥቆማዎችን ለማቅረብ ብቻ ነው የሚሠራው። እኛ በቀጥታ ምንም አይነት መለዋወጫ አንሠራም፣ አናከማቸም ወይም አንሸጥም።<br><br>የተዘረዘሩት ሁሉም ምርቶች እና አገልግሎቶች የሚቀርቡት በገለልተኛ አቅራቢዎች ነው። ኢተራ ለሚሸጡ ማናቸውም ክፍሎች ጥራት፣ ሁኔታ፣ ተደራሽነት ወይም አቅርቦት ኃላፊነት አይወስድም።</li>
            <li><strong>ትዕዛዞች እና ተደራሽነት</strong><br>ሁሉም ትዕዛዞች ተቀባይነት እና ተደራሽነት ላይ የተመሰረቱ ናቸው። ማንኛውንም ትዕዛዝ አለመቀበል ወይም መሰረዝ መብታችን የተጠበቀ ነው።</li>
            <li><strong>የአእምሯዊ ንብረት መብት</strong><br>በኢተራ ላይ ያሉ ሁሉም ይዘቶች፣ አርማዎችን፣ ጽሑፎችን፣ ግራፊክሶችን እና ሶፍትዌሮችን ጨምሮ፣ የኢተራ ወይም የፈቃድ ሰጪዎቹ ንብረት ናቸው።</li>
            <li><strong>የተጠቃሚ ይዘት</strong><br>ይዘት ማስገባት ይችላሉ። ይህን በማድረግ፣ ኢተራ እንደዚህ አይነት ይዘትን ለመጠቀም ብቸኛ ያልሆነ፣ ከሮያሊቲ ነፃ የሆነ ፈቃድ ይሰጥዎታል።</li>
            <li><strong>የተከለከለ ባህሪ</strong><br>የሚከተሉትን ባለማድረግ ተስማምተዋል፡ ማንኛውንም ሕጎች ወይም ደንቦች መጣስ፤ በአእምሯዊ ንብረት መብቶች ላይ ጣልቃ መግባት፤ ጎጂ ወይም አደገኛ ኮድ ማስተላለፍ።</li>
            <li><strong>የሶስተኛ ወገን አገናኞች</strong><br>ኢተራ ወደ ሶስተኛ ወገን ድረ-ገጽ አገናኞችን ሊይዝ ይችላል። ለእነዚያ ጣቢያዎች ይዘት ወይም ተግባራት ኃላፊነት አንወስድም።</li>
            <li><strong>የኃላፊነት ውስንነት</strong><br>ኢተራ ድህረገጽን በመጠቀምዎ ምክንያት ለሚከሰቱ ቀጥተኛ ያልሆኑ ጉዳቶች ተጠያቂ አይደለም።</li>
            <li><strong>ዋጋ አሰጣጥ እና ክፍያዎች</strong><br>በኢተራ ላይ የተዘረዘሩ ሁሉም ዋጋዎች ያለቅድመ ማስታወቂያ ሊለወጡ ይችላሉ።</li>
            <li><strong>ተመላሽ ገንዘቦች እና ስረዛዎች</strong><br>ኢተራ በቀጥታ መለዋወጫዎችን አይሸጥም። ማንኛውም የተመላሽ ገንዘብ ጥያቄዎች ምርቱ ከተገዛበት መለዋወጫ አቅራቢ ማቅረብ አለባቸው።</li>
            <li><strong>ግላዊነት</strong><br>የኢተራ አጠቃቀምዎ በግላዊነት ፖሊሲያችንም ይተዳደራል። ድህረገጹን በመጠቀምዎ ተስማምተዋል።</li>
            <li><strong>የደንቦች ለውጦች</strong><br>ኢተራ እነዚህን ደንቦች በማንኛውም ጊዜ የማዘመን መብቱ የተጠበቀ ነው። ድህረገጹን መጠቀምዎን መቀጠል የተሻሻሉትን ደንቦች መቀበልን ይመሰክራል።</li>
            <li><strong>የበላይነት ሕግ</strong><br>እነዚህ ደንቦች የሚተዳደሩት በ ኢትዮጵያ ሕግ ነው።</li>
            <li><strong>ያግኙን</strong><br>ለጥያቄዎች ወይም አሳሳቢ ጉዳዮች፣ እባክዎን በ ድህረገጻችን ላይ በተቀመጠዉ አድራሻ ያግኙን።</li>
        </ol>
    </div>

</div>
<p class="tc-scroll-hint"><i class="bx bx-mouse"></i> Scroll to read all terms before agreeing</p>

{{-- Agreement Form --}}
<form method="POST" action="/terms-agree">
    @csrf

    <div class="tc-check-wrap" style="margin-bottom:1.1rem;" onclick="document.getElementById('agreedCheck').click()">
        <input type="checkbox" name="agreed" id="agreedCheck" value="1" onclick="event.stopPropagation()" {{ old('agreed') ? 'checked' : '' }}>
        <label for="agreedCheck" onclick="event.preventDefault()">
            I have read and agree to the <strong>Terms &amp; Conditions</strong> above.<br>
            <span style="font-size:0.8rem;color:var(--etera-text-muted);" id="tcCheckSubtext">በላዩ ላይ ያሉትን <strong>ደንቦችና ሁኔታዎች</strong> አንብቤ ተስማምቻለሁ።</span>
        </label>
    </div>

    <button type="submit" class="etera-btn etera-btn-primary etera-btn-block" style="margin-bottom:0.9rem;">
        <i class="bx bx-check-shield"></i>
        <span id="agreeBtnText">Agree &amp; Continue</span>
    </button>
</form>

<div style="text-align:center;">
    <form action="{{ route('logout') }}" method="POST" style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="submit" style="background:none;border:none;color:var(--etera-text-muted);font-size:0.82rem;cursor:pointer;padding:0;font-family:inherit;">
            <i class="bx bx-log-out"></i> Sign out instead
        </button>
    </form>
</div>

@endsection

@section('scripts')
<script>
(function () {
    const langKey = 'etera_terms_lang';

    function setLang(lang) {
        const safe = lang === 'am' ? 'am' : 'en';
        localStorage.setItem(langKey, safe);

        document.querySelectorAll('[data-tc]').forEach(function (el) {
            el.style.display = el.getAttribute('data-tc') === safe ? '' : 'none';
        });
        document.querySelectorAll('[data-tc-lang]').forEach(function (btn) {
            btn.classList.toggle('active', btn.getAttribute('data-tc-lang') === safe);
            btn.setAttribute('aria-selected', btn.getAttribute('data-tc-lang') === safe);
        });

        // Swap checkbox label language
        const sub = document.getElementById('tcCheckSubtext');
        const btn = document.getElementById('agreeBtnText');
        if (safe === 'am') {
            if (sub) sub.style.fontWeight = '600';
            if (btn) btn.textContent = 'ተስማምቼ ቀጥል';
        } else {
            if (sub) sub.style.fontWeight = '';
            if (btn) btn.textContent = 'Agree & Continue';
        }

        // Scroll back to top
        const scroll = document.getElementById('tcScroll');
        if (scroll) scroll.scrollTop = 0;
    }

    document.querySelectorAll('[data-tc-lang]').forEach(function (btn) {
        btn.addEventListener('click', function () { setLang(this.getAttribute('data-tc-lang')); });
    });

    setLang(localStorage.getItem(langKey) || 'en');
})();
</script>
@endsection
