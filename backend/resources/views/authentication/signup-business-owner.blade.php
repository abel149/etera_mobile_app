@extends('layouts.authentication')

@section('title', 'Sign Up — etera')

@section('branding')
    <img src="{{ asset('assets/images/transparent.svg') }}" class="etera-auth-logo" alt="etera">
    <h2 class="etera-heading etera-heading-lg" style="text-align:center; margin-bottom: 0.5rem;">
        Join etera Today
    </h2>
    <p class="etera-subtext" style="text-align:center; max-width: 360px; color: rgba(255,255,255,0.85);">
        Register as a customer and start sourcing auto parts across all brands.
    </p>

    @include('partials.brand-globe')
@endsection

@section('styles')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        /* Modal Overlay */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); display: none; justify-content: center; align-items: center; z-index: 9999; padding: 20px; }
        .modal-overlay.show { display: flex !important; }
        .modal-content { background: #fff; color: #1a1a2e; width: 90%; max-width: 650px; max-height: 90vh; overflow-y: auto; border-radius: 16px; padding: 25px; position: relative; animation: fadeIn 0.25s ease-in-out; border: 1px solid #c8e6c9; box-shadow: 0 8px 32px rgba(40,167,69,0.12); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px; }
        .modal-close { background: none; border: none; font-size: 28px; cursor: pointer; color: #6b7280; }
        .modal-close:hover { color: #1a1a2e; }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }

        .terms-header-left { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .terms-lang-toggle { display: inline-flex; gap: 6px; background: #f3f4f6; border-radius: 999px; padding: 4px; border: 1px solid #e5e7eb; }
        .terms-lang-btn { border: 0; background: transparent; padding: 6px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; color: #374151; cursor: pointer; }
        .terms-lang-btn.active { background: rgba(40,167,69,0.14); color: #1a1a2e; }

        .terms-meta { color: #6b7280; font-size: 12px; margin: 0 0 12px; }
        .terms-body { color: #111827; font-size: 13px; line-height: 1.55; }
        .terms-body h5 { font-size: 14px; margin: 14px 0 8px; }
        .terms-body ol { padding-left: 18px; margin: 0; }
        .terms-body li { margin: 8px 0; }
        .terms-body hr { border: 0; border-top: 1px solid #e5e7eb; margin: 14px 0; }
    </style>
@endsection

@section('content')

<div style="animation: etera-fade-in 0.6s ease-out">
    <div style="text-align: center; margin-bottom: 2rem;">
        <img src="{{ asset('assets/images/transparent.svg') }}" alt="etera" style="max-width: 120px; margin-bottom: 1rem;" class="d-xl-none">
        <h2 class="etera-heading" style="font-size: 1.5rem; margin-bottom: 0.5rem;">Create Your Account</h2>
        <p class="etera-subtext">Fill the form below to register as a <strong>Customer</strong>.</p>
    </div>

    <form id="businessRegisterForm" action="{{ route('register.business-owner') }}" method="POST" novalidate>
        @csrf
        <input type="hidden" name="role" value="others">

        {{-- Full Name --}}
        <div class="etera-input-group">
            <label>Full Name <span style="color:#dc3545">*</span></label>
            <input type="text"
                   class="etera-input {{ $errors->has('name') ? 'error' : '' }}"
                   name="name"
                   placeholder="Enter your full name"
                   value="{{ old('name') }}" required>
            @error('name')<div class="etera-error-text">{{ $message }}</div>@enderror
        </div>

        {{-- Email --}}
        <div class="etera-input-group">
            <label>Email Address <span style="color:var(--etera-text-muted); font-weight:400;">(optional)</span></label>
            <input type="email"
                   class="etera-input {{ $errors->has('email') ? 'error' : '' }}"
                   name="email"
                   placeholder="john@example.com"
                   value="{{ old('email') }}">
            @error('email')<div class="etera-error-text">{{ $message }}</div>@enderror
        </div>

        {{-- Phone Number --}}
        <div class="etera-input-group">
            <label>Phone Number <span style="color:#dc3545">*</span></label>
            <input type="tel"
                   class="etera-input {{ $errors->has('phone_number') ? 'error' : '' }}"
                   id="inputPhone"
                   name="phone_number"
                   placeholder="0912131415"
                   maxlength="10"
                   inputmode="numeric"
                   pattern="\d{10}"
                   value="{{ old('phone_number') }}" required>
            @error('phone_number')<div class="etera-error-text">{{ $message }}</div>@enderror
        </div>

        {{-- Password Row --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="etera-input-group">
                <label>Password (6 digits) <span style="color:#dc3545">*</span></label>
                <div class="etera-password-wrapper">
                    <input type="password"
                           id="password"
                           name="password"
                           class="etera-input {{ $errors->has('password') ? 'error' : '' }}"
                           placeholder="Enter 6-digit PIN"
                           maxlength="6"
                           inputmode="numeric"
                           autocomplete="new-password" required>
                    <button type="button" class="etera-password-toggle toggle-password" data-target="#password" tabindex="-1">
                        <i class='bx bx-hide'></i>
                    </button>
                </div>
                <div id="passwordError" class="etera-error-text" style="display:none;"></div>
                @error('password')<div class="etera-error-text">{{ $message }}</div>@enderror
            </div>

            <div class="etera-input-group">
                <label>Confirm Password <span style="color:#dc3545">*</span></label>
                <div class="etera-password-wrapper">
                    <input type="password"
                           id="password_confirmation"
                           name="password_confirmation"
                           class="etera-input"
                           placeholder="Confirm PIN"
                           maxlength="6"
                           inputmode="numeric"
                           autocomplete="new-password" required>
                    <button type="button" class="etera-password-toggle toggle-password" data-target="#password_confirmation" tabindex="-1">
                        <i class='bx bx-hide'></i>
                    </button>
                </div>
                <div id="confirmPasswordError" class="etera-error-text" style="display:none;"></div>
            </div>
        </div>

        {{-- Terms --}}
        <div style="margin-top: 0.5rem; margin-bottom: 1.25rem;">
            <label class="etera-toggle">
                <input type="checkbox" id="terms-check" name="terms" value="1" {{ old('terms') ? 'checked' : '' }} required>
                <span>I agree to the <a href="javascript:void(0);" id="openTermsModal" class="etera-link">Terms & Conditions</a></span>
            </label>
            @error('terms')<div class="etera-error-text" style="margin-top:4px;">{{ $message }}</div>@enderror
        </div>

        <button type="submit" id="submitBtn"
                class="etera-btn etera-btn-primary etera-btn-block etera-btn-lg">
            Sign Up
        </button>
    </form>

    <div style="text-align: center; margin-top: 1.5rem;">
        <p class="etera-subtext" style="font-size: 0.9rem;">
            Already have an account? <a href="/login" class="etera-link">Login here</a>
        </p>
    </div>

    <div class="etera-divider">or</div>

    <div style="text-align: center;">
        <p class="etera-subtext" style="font-size: 0.85rem;">
            <a href="{{ route('signup') }}" class="etera-link">Change role</a>
        </p>
    </div>
</div>

{{-- TERMS AND CONDITIONS MODAL --}}
<div id="termsModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <div class="terms-header-left">
                <h4 style="margin:0;">Terms and Conditions</h4>
                <div class="terms-lang-toggle" role="tablist" aria-label="Terms language">
                    <button type="button" class="terms-lang-btn" data-terms-lang="en">EN</button>
                    <button type="button" class="terms-lang-btn" data-terms-lang="am">አማ</button>
                </div>
            </div>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p class="terms-meta">Effective Date: October 2025</p>

            <div class="terms-body" data-terms-content="en">
                <p><strong>Welcome to ETERA.</strong> By accessing or using our platform, you agree to be bound by the following Terms and Conditions. Please read them carefully.</p>
                <hr>
                <ol>
                    <li><strong>Acceptance of Terms</strong><br>By using ETERA, you confirm that you have read, understood, and agreed to these Terms and Conditions. If you do not agree, you must not use the platform.</li>
                    <li><strong>Eligibility</strong><br>You must be at least 18 years old or have legal parental/guardian consent to use ETERA. You represent that you have the authority to enter into this agreement.</li>
                    <li><strong>Account Registration</strong><br>To access certain features, you may be required to register an account. You agree to provide accurate information and to keep your login credentials secure.</li>
                    <li><strong>Use of the Platform</strong><br>You agree to use ETERA only for lawful purposes and in accordance with these Terms. You must not misuse the platform or attempt unauthorized access.</li>
                    <li><strong>Product and Service Descriptions</strong><br>ETERA strives to provide accurate descriptions of products and services. However, we do not warrant that descriptions or other content are error-free, complete, or current.</li>
                    <li><strong>Platform Role and Provider Responsibility</strong><br>ETERA acts solely as a facilitator of instant price quotes provided by spare part providers registered on our platform. We do not manufacture, stock, or sell any spare parts directly.<br><br>All products and services listed are offered by independent providers. ETERA is not responsible for the quality, condition, availability, or delivery of any parts sold, nor for any store’s return, refund, or warranty policies. Any disputes or claims regarding a product must be resolved directly with the provider.</li>
                    <li><strong>Orders and Availability</strong><br>All orders are subject to acceptance and availability. We reserve the right to refuse or cancel any order at our discretion.</li>
                    <li><strong>Intellectual Property</strong><br>All content on ETERA, including logos, text, graphics, and software, is the property of ETERA or its licensors and is protected by applicable intellectual property laws.</li>
                    <li><strong>User Content</strong><br>You may submit content (e.g., reviews, feedback). By doing so, you grant ETERA a non-exclusive, royalty-free license to use, reproduce, and display such content.</li>
                    <li><strong>Prohibited Conduct</strong><br>You agree not to:<br>- Violate any laws or regulations<br>- Infringe on intellectual property rights<br>- Transmit harmful or malicious code<br>- Use automated systems to access the platform</li>
                    <li><strong>Third-Party Links</strong><br>ETERA may contain links to third-party websites. We are not responsible for the content, policies, or practices of those sites.</li>
                    <li><strong>Limitation of Liability</strong><br>ETERA is not liable for any indirect, incidental, or consequential damages arising from your use of the platform. Our total liability is limited to the amount paid by you for the relevant product or service.</li>
                    <li><strong>Pricing and Payments</strong><br>All prices listed on ETERA are subject to change without prior notice. The price applicable to any product or service will be the price in effect at the time your order is placed and will be clearly stated in your order confirmation email.<br><br>Payment must be made using one of the available payment methods indicated on the ETERA website. We reserve the right to modify accepted payment methods at any time without notice.</li>
                    <li><strong>Refunds and Cancellations</strong><br>ETERA does not sell spare parts directly and is not responsible for refund or cancellation policies set by individual providers. Any requests for refunds, exchanges, or cancellations must be directed to the spare part provider from whom the product was purchased.<br><br>We encourage users to review the provider’s return and refund policy before placing an order. ETERA does not mediate disputes related to refunds or cancellations but may assist in facilitating communication between users and providers when possible.</li>
                    <li><strong>Privacy</strong><br>Your use of ETERA is also governed by our Privacy Policy. By using the platform, you consent to the collection and use of your information as described therein.</li>
                    <li><strong>Changes to Terms</strong><br>ETERA reserves the right to update these Terms at any time. Changes will be effective upon posting. Continued use of the platform constitutes acceptance of the revised Terms.</li>
                    <li><strong>Governing Law</strong><br>These Terms are governed by the laws of Ethiopia. Any disputes shall be resolved in accordance with the law.</li>
                    <li><strong>Contact Us</strong><br>For questions or concerns, please reach out to us at the address provided on our website.</li>
                </ol>
            </div>

            <div class="terms-body" data-terms-content="am" style="display:none;">
                <p><strong>የኢተራ ደንቦች እና ሁኔታዎች</strong></p>
                <p class="terms-meta" style="margin-top:6px;">የሥራ መጀመሪያ ቀን፡ ጥቅምት 2018</p>
                <p>እንኳን ወደ ኢተራ በደህና መጡ። የእኛን ድህረገጽ ለመጠቀም በሚከተሉት ደንቦች እና ሁኔታዎች ላይ ተስማምተዋል። እባክዎ በጥንቃቄ ያንብቧቸው።</p>
                <hr>
                <ol>
                    <li><strong>የደንቦቹ ተቀባይነት</strong><br>ኢተራን በመጠቀምዎ፣ እነዚህን ደንቦች እና ሁኔታዎች እንዳነበቡዋቸው፣ እንደተረዷቸው እና እንደተስማሙባቸው ያረጋግጣሉ። ካልተስማሙ፣ ድህረገጹን መጠቀም የለብዎትም።</li>
                    <li><strong>ብቁነት</strong><br>ኢተራን ለመጠቀም ቢያንስ 18 ዓመት ወይም ከዚያ በላይ መሆን አለብዎት ወይም የሕግ ወላጅ/አሳዳጊ ፈቃድ ሊኖርዎት ይገባል። ወደዚህ ስምምነት ለመግባት ሥልጣን እንዳለዎት ይወክላሉ።</li>
                    <li><strong>መለያ ምዝገባ</strong><br>የተወሰኑ መረጃዎችን ለማግኘት፣ መለያ እንዲመዘግቡ ሊጠየቁ ይችላሉ። ትክክለኛ መረጃ ለማቅረብ እና የመግቢያ ማረጋገጫዎን ደህንነቱ በተጠበቀ ሁኔታ ለማቆየት ተስማምተዋል።</li>
                    <li><strong>የ ድህረገጽ አጠቃቀም</strong><br>ኢተራን ለህጋዊ ዓላማዎች ብቻ እና በነዚህ ደንቦች መሠረት ለመጠቀም ተስማምተዋል። ድህረገጹን አላግባብ መጠቀም ወይም ያልተፈቀደ ተደራሽነት መሞከር የለብዎትም።</li>
                    <li><strong>የምርት እና የአገልግሎት መግለጫዎች</strong><br>ኢተራ የምርቶችን እና አገልግሎቶችን ትክክለኛ መግለጫዎች ለማቅረብ ይጥራል። ነገር ግን፣ መግለጫዎቹ ወይም ሌላ ይዘቶች ከስህተት ነጻ ናቸው፣ የተሟሉ ናቸው ወይም ወቅታዊ ናቸው ብለን ዋስትና አንሰጥም።</li>
                    <li><strong>የ ድህረገጹ ሚና እና የአቅራቢ ኃላፊነት</strong><br>ኢተራ በድህረገጻችን ላይ በተመዘገቡ መለዋወጫ አቅራቢዎች የሚሰጡ ፈጣን የዋጋ ጥቆማዎችን ለማቅረብ ብቻ ነው የሚሠራው። እኛ በቀጥታ ምንም አይነት መለዋወጫ አንሠራም፣ አናከማቸም ወይም አንሸጥም።<br><br>የተዘረዘሩት ሁሉም ምርቶች እና አገልግሎቶች የሚቀርቡት በገለልተኛ አቅራቢዎች ነው። ኢተራ ለሚሸጡ ማናቸውም ክፍሎች ጥራት፣ ሁኔታ፣ ተደራሽነት ወይም አቅርቦት፣ እንዲሁም ለማንኛውም ሱቅ የመመለሻ፣ ተመላሽ ገንዘብ ወይም ዋስትና ፖሊሲዎች ኃላፊነት አይወስድም። ስለ ምርት የሚነሱ አለመግባባቶች ወይም የይገባኛል ጥያቄዎች በቀጥታ ከአቅራቢው ጋር መፈታት አለባቸው።</li>
                    <li><strong>ትዕዛዞች እና ተደራሽነት</strong><br>ሁሉም ትዕዛዞች ተቀባይነት እና ተደራሽነት ላይ የተመሰረቱ ናቸው። ማንኛውንም ትዕዛዝ በእኛ ውሳኔ አለመቀበል ወይም መሰረዝ መብታችን የተጠበቀ ነው።</li>
                    <li><strong>የአእምሯዊ ንብረት መብት</strong><br>በኢተራ ላይ ያሉ ሁሉም ይዘቶች፣ አርማዎችን፣ ጽሑፎችን፣ ግራፊክሶችን እና ሶፍትዌሮችን ጨምሮ፣ የኢተራ ወይም የፈቃድ ሰጪዎቹ ንብረት ናቸው እና በሚመለከታቸው የአእምሯዊ ንብረት ሕጎች የተጠበቁ ናቸው።</li>
                    <li><strong>የተጠቃሚ ይዘት</strong><br>ይዘት (ለምሳሌ፣ ግምገማዎች፣ ግብረመልሶች) ማስገባት ይችላሉ። ይህን በማድረግ፣ ኢተራ እንደዚህ አይነት ይዘትን ለመጠቀም፣ ለማባዛት እና ለማሳየት ብቸኛ ያልሆነ፣ ከሮያሊቲ ነፃ የሆነ ፈቃድ ይሰጥዎታል።</li>
                    <li><strong>የተከለከለ ባህሪ</strong><br>የሚከተሉትን ባለማድረግ ተስማምተዋል፡-<br>· ማንኛውንም ሕጎች ወይም ደንቦች መጣስ<br>· በአእምሯዊ ንብረት መብቶች ላይ ጣልቃ መግባት<br>· ጎጂ ወይም አደገኛ ኮድ ማስተላለፍ<br>· ድህረገጹን ለመድረስ አውቶሜትድ ሲስተሞችን መጠቀም</li>
                    <li><strong>የሶስተኛ ወገን አገናኞች</strong><br>ኢተራ ወደ ሶስተኛ ወገን ድረ-ገጽ አገናኞችን ሊይዝ ይችላል። ለእነዚያ ጣቢያዎች ይዘት፣ ፖሊሲዎች ወይም ተግባራት ኃላፊነት አንወስድም።</li>
                    <li><strong>የኃላፊነት ውስንነት</strong><br>ኢተራ ድህረገጽን በመጠቀምዎ ምክንያት ለሚከሰቱ ቀጥተኛ ያልሆኑ፣ ድንገተኛ ወይም ተከታይ ጉዳቶች ተጠያቂ አይደለም። አጠቃላይ ኃላፊነታችን ለሚመለከተው ምርት ወይም አገልግሎት በእርስዎ ለተከፈለው መጠን ብቻ የተወሰነ ነው።</li>
                    <li><strong>ዋጋ አሰጣጥ እና ክፍያዎች</strong><br>በኢተራ ላይ የተዘረዘሩ ሁሉም ዋጋዎች ያለቅድመ ማስታወቂያ ሊለወጡ ይችላሉ። በማንኛውም ምርት ወይም አገልግሎት ላይ የሚተገበረው ዋጋ ትዕዛዝዎን ባስቀመጡበት ጊዜ የነበረው ዋጋ ሲሆን የትዕዛዝ ማረጋገጫ በቴሌግራምዎ ላይ በግልጽ ይገለጻል።<br><br>ክፍያ በኢተራ ድረ-ገጽ ላይ ከተጠቆሙት ክፍያ ዘዴዎች ውስጥ አንዱን በመጠቀም ክፍያ መፈጸም አለበት። ያለማስታወቂያ ተቀባይነት ያላቸውን የክፍያ ዘዴዎች የመቀየር መብታችን የተጠበቀ ነው።</li>
                    <li><strong>ተመላሽ ገንዘቦች እና ስረዛዎች</strong><br>ኢተራ በቀጥታ መለዋወጫዎችን አይሸጥም እና በግለሰብ አቅራቢዎች ለተዘጋጁ የተመላሽ ገንዘብ ወይም ስረዛ ፖሊሲዎች ኃላፊነት አይወስድም። ማንኛውም የተመላሽ ገንዘብ፣ ልውውጥ ወይም ስረዛ ጥያቄዎች ምርቱ ከተገዛበት መለዋወጫ አቅራቢ ማቅረብ አለባቸው።<br><br>ተጠቃሚዎች ትዕዛዝ ከማስቀመጣቸው በፊት የአቅራቢውን የመመለሻ እና ተመላሽ ገንዘብ ፖሊሲ እንዲገመግሙ እናበረታታለን። ኢተራ ከተመላሽ ገንዘቦች ወይም ስረዛዎች ጋር በተያያዙ አለመግባባቶች ውስጥ ጣልቃ አይገባም ነገር ግን በሚቻልበት ጊዜ በተጠቃሚዎች እና አቅራቢዎች መካከል ግንኙነትን በማመቻቸት ሊረዳ ይችላል።</li>
                    <li><strong>ግላዊነት</strong><br>የኢተራ አጠቃቀምዎ በግላዊነት ፖሊሲያችንም ይተዳደራል። ድህረገጹን በመጠቀምዎ፣ እንደተገለጸው መረጃዎን ለመሰብሰብ እና ለመጠቀም ተስማምተዋል።</li>
                    <li><strong>የደንቦች ለውጦች</strong><br>ኢተራ እነዚህን ደንቦች በማንኛውም ጊዜ የማዘመን መብቱ የተጠበቀ ነው። ለውጦች በሚለጠፉበት ጊዜ ሥራ ላይ ይውላሉ። ድህረገጹን መጠቀምዎን መቀጠል የተሻሻሉትን ደንቦች መቀበልን ይመሰክራል።</li>
                    <li><strong>የበላይነት ሕግ</strong><br>እነዚህ ደንቦች የሚተዳደሩት በ ኢትዮጵያ ሕግ  ነው። ማንኛውም አለመግባባቶች በ ሕጉ መሠረት መፈታት አለባቸው።</li>
                    <li><strong>ያግኙን</strong><br>ለጥያቄዎች ወይም አሳሳቢ ጉዳዮች፣ እባክዎን በ ድህረገጻችን ላይ በተቀመጠዉ አድራሻ ያግኙን።</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<script>
(function($){
    $(function(){
        // Modal Logic
        const $termsModal = $('#termsModal');
        $('#openTermsModal').on('click', function(e){ e.preventDefault(); $termsModal.addClass('show'); $('body').css('overflow', 'hidden'); });
        $('.modal-close').on('click', function(){ $termsModal.removeClass('show'); $('body').css('overflow', ''); });
        $termsModal.on('click', function(e){ if (e.target === this) { $termsModal.removeClass('show'); $('body').css('overflow', ''); } });

        // Terms language toggle (persist in localStorage)
        const langKey = 'etera_terms_lang';
        function setTermsLang(lang) {
            const safe = (lang === 'am') ? 'am' : 'en';
            localStorage.setItem(langKey, safe);
            $('[data-terms-content="en"]').toggle(safe === 'en');
            $('[data-terms-content="am"]').toggle(safe === 'am');
            $('.terms-lang-btn').removeClass('active');
            $('.terms-lang-btn[data-terms-lang="' + safe + '"]').addClass('active');
        }
        $('.terms-lang-btn').on('click', function(){ setTermsLang($(this).data('terms-lang')); });
        setTermsLang(localStorage.getItem(langKey) || 'en');

        // Password — digits only
        const weakPins = ["123456","111111","000000","654321","222222","333333","444444","555555","112233","121212","123123","987654","101010","246824","121314"];
        function isWeakPattern(pin) {
            if (weakPins.includes(pin)) return true;
            if (/^(\d)\1{5}$/.test(pin)) return true;
            if ("0123456789012345".indexOf(pin) !== -1) return true;
            if ("987654321098765".indexOf(pin) !== -1) return true;
            if (/^(\d)(\d)\1\2\1\2$/.test(pin)) return true;
            return false;
        }
        $('#password, #password_confirmation').on('input', function(){ $(this).val($(this).val().replace(/\D/g, '').slice(0,6)); });

        // Toggle show/hide
        $(document).on('click', '.toggle-password', function(e){
            e.preventDefault();
            const $input = $($(this).data('target'));
            const $icon = $(this).find('i');
            if ($input.attr('type') === 'password') { $input.attr('type', 'text'); $icon.removeClass('bx-hide').addClass('bx-show'); }
            else { $input.attr('type', 'password'); $icon.removeClass('bx-show').addClass('bx-hide'); }
        });

        // Password validation
        $('#password').on('blur input', function(){
            const val = $(this).val(), $err = $('#passwordError');
            $err.hide(); $(this).removeClass('error');
            if (!val.length) return;
            if (!/^\d{6}$/.test(val)) { $err.text('Password must be exactly 6 digits.').show(); $(this).addClass('error'); return; }
            if (isWeakPattern(val)) { $err.text('This PIN is too common. Choose a stronger one.').show(); $(this).addClass('error'); return; }
            $('#password_confirmation').trigger('input');
        });
        $('#password_confirmation').on('input blur', function(){
            const p = $('#password').val(), c = $(this).val(), $err = $('#confirmPasswordError');
            $err.hide(); $(this).removeClass('error');
            if (!c.length) return;
            if (!/^\d{6}$/.test(c)) { $err.text('Must be 6 digits.').show(); $(this).addClass('error'); return; }
            if (p !== c) { $err.text('Passwords do not match.').show(); $(this).addClass('error'); return; }
        });

        // Phone input: allow user preferred format (no auto-prefix)

        // Helpers
        function showErr($el, msg) { $el.addClass('error'); let $e = $el.siblings('.js-err'); if (!$e.length) { $e = $('<div class="etera-error-text js-err"></div>'); $el.after($e); } $e.text(msg).show(); }
        function clearErr($el) { $el.removeClass('error'); $el.siblings('.js-err').hide(); }

        // Phone input messages
        $('#inputPhone').on('input', function(){
            const digits = (this.value || '').replace(/\D/g, '');
            if (digits.length > 10) {
                showErr($(this), 'You reached 10 digits.');
                return;
            }
            if (digits.length >= 2 && !digits.startsWith('09')) {
                showErr($(this), 'Phone number should start with 09.');
                return;
            }
            clearErr($(this));
        });

        // Blur validation
        $('input[name="name"]').on('blur', function(){ $(this).val().trim() === '' ? showErr($(this), 'Full name is required.') : clearErr($(this)); });
        $('input[name="email"]').on('blur', function(){ const v = $(this).val().trim(); if (v && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) showErr($(this), 'Please enter a valid email.'); else clearErr($(this)); });
        $('#inputPhone').on('blur', function(){
            const v = (this.value || '').trim();
            const digits = (v || '').replace(/\D/g, '');
            if (!digits) { showErr($(this), 'Phone is required.'); return; }
            if (digits.length !== 10) { showErr($(this), 'Phone number must be 10 digits.'); return; }
            if (!digits.startsWith('09')) { showErr($(this), 'Phone number should start with 09.'); return; }
            clearErr($(this));
        });

        // Form submit
        $('#businessRegisterForm').on('submit', function(e){
            let err = false;
            const $n = $('input[name="name"]'); if ($n.val().trim() === '') { showErr($n, 'Full name is required.'); err = true; } else clearErr($n);
            const $em = $('input[name="email"]'), ev = $em.val().trim(); if (ev && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(ev)) { showErr($em, 'Please enter a valid email.'); err = true; } else clearErr($em);
            const $ph = $('#inputPhone'); if (!$ph.val().trim()) { showErr($ph, 'Phone is required.'); err = true; } else clearErr($ph);
            $('#password').trigger('blur'); $('#password_confirmation').trigger('blur');
            if ($('#passwordError').is(':visible') || $('#confirmPasswordError').is(':visible')) err = true;
            if ($('#password').val().trim() === '') { $('#passwordError').text('Password is required.').show(); $('#password').addClass('error'); err = true; }
            if (!$('#terms-check').is(':checked')) { let $te = $('#terms-check').closest('.etera-toggle').siblings('.js-terms-err'); if (!$te.length) { $te = $('<div class="etera-error-text js-terms-err"></div>'); $('#terms-check').closest('div').append($te); } $te.text('You must accept the Terms & Conditions.').show(); err = true; } else { $('#terms-check').closest('div').find('.js-terms-err').hide(); }
            if (err) { e.preventDefault(); const $first = $('.error, .js-err:visible, .js-terms-err:visible').first(); if ($first.length) { $('html, body').animate({ scrollTop: $first.offset().top - 100 }, 300); $first.focus(); } return false; }
            $('#submitBtn').prop('disabled', true).text('Processing...');
        });
    });
})(jQuery);
</script>

@endsection
