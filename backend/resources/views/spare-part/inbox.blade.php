@extends('layouts.sparepart')

@section('inbox')
    class="current"
@endsection

@section('content')
<div class="container margin-top-20 margin-bottom-45">
    <div class="row">
        <div class="col-xl-12 col-lg-12 content-left-offset">

            <div class="notify-box margin-top-15">
                <div class="switch-container">
                    <h3 class="page-title">Inboxed Proforma List</h3>
                </div>
                <div class="sort-by">
                    <span>Sort by:</span>
                    <select class="selectpicker hide-tick">
                        <option>Relevance</option>
                        <option>Newest</option>
                        <option>Oldest</option>
                        <option>Random</option>
                    </select>
                </div>
            </div>

            <div class="listings-container compact-list-layout margin-top-35">
                @php 
                    $user = auth()->user();
                    $userId = $user->id;
                    $userRole = $user->role;
                @endphp

                @foreach($user->myInbox as $proformaInbox)
                    @php 
                        $proforma = $proformaInbox->proforma;
                        $isInboxedUser = $proforma->inboxes->contains('user_id', $userId);
                        $totalApplications = $proforma->applications->count();
                        $hasApplied = $proforma->applications->contains('application_by', $userId);
                    @endphp

                    {{-- Always show items sent to the user's inbox --}}

                    <a href="{{ url('/proforma-details?proforma=' . $proforma->id) }}" class="job-listing with-apply-button">
                        <div class="job-listing-details">
                            <div class="job-listing-company-logo">
                                <img src="{{ asset('asset/images/company-logo-01.png') }}" alt="">
                            </div>

                            <div class="job-listing-description">
                                <h3 class="job-listing-title">{{ $proforma->poster->name }}</h3>

                                <div class="job-listing-footer">
                                    <ul>
                                        <li>
                                            <i class="icon-material-outline-directions-car"></i> 
                                            {{ $proforma->brand->name }}, {{ $proforma->model }}
                                        </li>
                                        <li>
                                            <i class="icon-material-outline-access-time"></i> 
                                            {{ $proforma->created_at->diffForHumans() }}
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <span class="list-apply-button radius-30 ripple-effect">Apply Now</span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="clearfix"></div>
            <div class="row">
                <div class="col-md-12">
                    {{-- {{$proformas->links()}} --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
