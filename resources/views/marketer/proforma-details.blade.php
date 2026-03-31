@extends('layouts.marketer')
@section('content')

    <style type="text/css">
        .player audio {
            width: 100%;
            border-radius: 6px;
            margin: 0;
            padding: 0;
            border: none;
        }

        /* Table Styles */
        .table-container {
            margin-top: 20px;
            overflow-y: hidden;
            overflow-x: auto;
            white-space: nowrap;
        }

        .basic-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #ddd;
            font-family: Arial, sans-serif;
        }

        .basic-table th,
        .basic-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            white-space: normal;
        }

        .basic-table th {
            background-color: #f4f4f4;
            color: #333;
            font-weight: bold;
            text-transform: uppercase;
        }

        .basic-table tr:hover {
            background-color: #f1f1f1;
        }
    </style>

    <div class="single-page-header" data-background-image="{{ asset('asset/images/banner-auto-insurance.jpg') }}">
        
        <div class="page-wrapper">
        <div class="page-content">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="single-page-header-inner">
                        <div class="left-side">
                            <div class="header-details">
                                <h5>File #: {{ $proforma->file_number ?? 'N/A' }}</h5>
                                <ul>
                                    <li><i class="icon-feather-credit-card"></i> Plate Number:
                                        {{ $proforma->license_plate_number ?? 'N/A' }}</li>
                                    <li><i class="icon-feather-settings"></i> Chassis: {{ $proforma->chassis_number ?? 'N/A' }}</li>
                                    <li><i class="icon-material-outline-directions-car"></i> Year:
                                        {{ $proforma->year ?? 'N/A' }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($proforma->images) && count($proforma->images) > 0)
        <div style="text-align: center; margin-bottom: 20px;">
            <p><strong>Proforma has {{ $proforma->images->count() }} image(s)</strong></p>
        </div>
    @endif

    <div class="container">
        <div class="row">
            <div class="col-xl-4 col-lg-4">
                <div class="sidebar-container">
                    <div class="sidebar-widget">
                        <div class="job-overview">
                            <div class="job-overview-headline">Proforma Summary</div>
                            <div class="job-overview-inner">
                                <ul>
                                    <li>
                                        <i class="icon-material-outline-directions-car"></i>
                                        <span>{{ $proforma->brand->name ?? 'N/A' }}</span>
                                        <h5>{{ $proforma->model ?? 'N/A' }}</h5>
                                    </li>
                                    <li>
                                        <i class="icon-material-outline-business"></i>
                                        <span>Posted By</span>
                                        <h5>{{ ucfirst($proforma->poster->role ?? 'N/A') }}</h5>
                                    </li>
                                    <li>
                                        <i class="icon-material-outline-access-time"></i>
                                        <span>Date Posted</span>
                                        <h5>{{ $proforma->created_at->diffForHumans() ?? 'N/A' }}</h5>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    @if (isset($proforma->audios) && count($proforma->audios))
                        <div>
                            <div class="job-overview margin-bottom-10">
                                <div class="job-overview-headline">Audio</div>
                            </div>
                            @foreach ($proforma->audios as $audio)
                                <div class="player">
                                    <audio controls>
                                        <source src="{{ $audio->url ?? '' }}" type="audio/mp3">
                                    </audio>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if ($proforma->voice_note_path ?? false)
                        <div>
                            <div class="job-overview margin-bottom-10">
                                <div class="job-overview-headline">Voice Note</div>
                            </div>
                            <div class="player">
                                <audio controls>
                                    <source src="{{ url('storage/' . $proforma->voice_note_path) }}" type="audio/webm">
                                    Your browser does not support the audio element.
                                </audio>
                            </div>
                        </div>
                    @endif
                    <br>

                    @if (isset($proforma->videos) && count($proforma->videos))
                        <div class="job-overview margin-bottom-10">
                            <div class="job-overview-headline">Video</div>
                        </div>
                        <div>
                            @foreach ($proforma->videos as $video)
                                <video controls style="width: 100%; height: auto; max-height: 300px;">
                                    <source src="{{ $video->url ?? '' }}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-xl-8 col-lg-8">
                <div class="table-container">
                    <table class="basic-table">
                        <thead>
                            <tr>
                                <th style="width: 1%;">#</th>
                                <th>Components</th>
                                <th>Part Name and Number</th>
                                <th>Grade</th>
                                <th>Condition</th>
                                <th>Country</th>
                                <th>Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($proforma->parts) && count($proforma->parts) > 0)
                                @foreach ($proforma->parts as $part)
                                    <tr>
                                        <td data-label="Index">{{ $loop->index + 1 }}</td>
                                        <td data-label="Component">{{ $part->component ?? 'N/A' }}</td>
                                        <td data-label="Part #">{{ $part->number ?? 'N/A' }}</td>
                                        <td data-label="Grade">{{ $part->grade ?? 'N/A' }}</td>
                                        <td data-label="Condition">{{ $part->condition ?? 'N/A' }}</td>
                                        <td data-label="Country">{{ $part->country ?? 'N/A' }}</td>
                                        <td data-label="Qty">{{ $part->quantity ?? 0 }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="7" class="text-center">
                                        No parts found for this proforma.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info margin-top-20">
                    <i class="icon-material-outline-info"></i>
                    This is a read-only view. Marketers cannot submit quotes for proformas.
                </div>

                <div class="margin-top-20">
                    <a href="/marketer/proformas" class="button ripple-effect">
                        <i class="icon-material-outline-arrow-back"></i> Back to Proforma List
                    </a>
                </div>
            </div>
        </div>
    </div>

@endsection
