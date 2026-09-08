{{--
    Per-insurance letterhead dispatcher.

    Usage:
        @include('components.insurance-letterhead', ['poster' => $proforma->poster, 'section' => 'header'])
        @include('components.insurance-letterhead', ['poster' => $proforma->poster, 'section' => 'footer'])

    $poster  - the User model who posted the proforma (usually an 'insurance' role user).
    $section - 'header' or 'footer'.

    Matching is done on strtolower(trim($poster->name)). Add new insurance
    templates by creating a folder under components/insurance-letterheads/{slug}/
    with header.blade.php and footer.blade.php, then add a rule below.
--}}
@php
    // For insurance agents, use the parent insurance company's name for matching
    $matchUser = ($poster->role ?? '') === 'insurance_agent' ? $poster->parentInsurance : $poster;
    $posterName = strtolower(trim($matchUser->name ?? $poster->name ?? ''));

    $template = 'default';

    if (str_contains($posterName, 'africa insurance') || str_contains($posterName, 'africa')) {
        $template = 'africa-insurance';
    }

    $viewName = "components.insurance-letterheads.{$template}.{$section}";

    if (!\Illuminate\Support\Facades\View::exists($viewName)) {
        $viewName = "components.insurance-letterheads.default.{$section}";
    }
@endphp
@include($viewName)
