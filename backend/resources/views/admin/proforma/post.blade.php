@extends('layouts.admin')
@section('content')
<style type="text/css">
/* General styles for all table headers and cells */
.tables th, .tables td {
  text-align: left;
}

/* Class for columns that should not wrap content */
.no-wrap {
  white-space: nowrap;
  width: auto;
}

/* Class for columns with proportional width */
.proportional {
  width: 50%;
}

.media-container {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 20px;
}

.media-item {
  width: 150px;
  height: 150px;
  object-fit: cover;
  border: 1px solid #ddd;
  border-radius: 5px;
  cursor: pointer;
}

.part-image-link {
  display: block;
  text-align: center;
  color: #007bff;
  text-decoration: none;
  font-size: 14px;
}
.part-image-link:hover {
  text-decoration: underline;
}
</style>

<div class="page-wrapper">
  <div class="page-content">
    <h3>Proforma Details</h3>

    <div class="card">
      <div class="card-body">
        <form action="{{ route('proforma.store') }}" method="POST">
          @csrf
          <input type="hidden" name="proforma" value="{{ $proforma->id }}">

          <div class="table-responsive lead-table">
            <table class="table tables mb-5 align-middle">
              <tbody>
                <tr>
                  <td class="no-wrap"><b>Name</b></td>
                  <td class="proportional">{{ $proforma->poster->name ?? 'N/A' }}</td>
                  <td class="no-wrap"><b>License Plate</b></td>
                  <td class="proportional">{{ $proforma->license_plate_number ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <td class="no-wrap"><b>File #</b></td>
                  <td class="proportional">{{ $proforma->file_number ?? 'N/A' }}</td>
                  <td class="no-wrap"><b>Phone Number</b></td>
                  <td class="proportional">{{ $proforma->customer_phone_number ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <td class="no-wrap"><b>Agent Phone</b></td>
                  <td class="proportional">{{ $proforma->agent_phone_number ?? 'N/A' }}</td>
                  <td class="no-wrap"><b>Poster Phone</b></td>
                  <td class="proportional">{{ $proforma->poster->phone_number ?? 'N/A' }}</td>
                </tr>

                <tr>
                  <td class="no-wrap"><b>Car</b></td>
                  <td class="proportional">{{ $proforma->car_type ?? 'N/A' }} {{ $proforma->brand->name ?? 'N/A' }}</td>
                  <td class="no-wrap"><b>Needed Parts</b></td>
                  <td class="proportional">
                    <span class="text-purple" style="cursor: pointer"
                      data-bs-toggle="modal" data-bs-target="#partsModal">
                      {{ $proforma->parts->count() }} Parts
                    </span>
                    @php $progress = $proforma->partsPricingProgress(); @endphp
                    @if($progress['total'] > 0)
                    <span class="badge {{ $progress['filled'] >= $progress['total'] ? 'bg-success' : 'bg-warning' }} ms-2" style="font-size: 0.85rem;">
                      Parts Priced: {{ $progress['filled'] }}/{{ $progress['total'] }}
                    </span>
                    @endif
                  </td>
                </tr>
                <tr>
                  <td class="no-wrap"><b>Model</b></td>
                  <td class="proportional">{{ $proforma->model ?? 'N/A' }}</td>
                  <td class="no-wrap"><b>VIN Number</b></td>
                  <td class="proportional">{{ $proforma->chassis_number ?? 'N/A' }}</td>
                </tr>
                @if($proforma->call_customer)
                <tr>
                  <td class="no-wrap"><b>Call Customer</b></td>
                  <td class="proportional" colspan="3">
                    <span class="badge bg-warning text-dark"><i class="bx bx-phone-call me-1"></i>Call Customer</span>
                  </td>
                </tr>
                @endif
                <tr>
                  <td class="no-wrap"><b>Proforma Requested</b></td>
                  <td class="proportional">{{ $proforma->number_of_proformas ?? 'N/A' }}</td>

                  @if($proforma->selected())
                  <td class="no-wrap"><b>Proforma Selected By</b></td>
                  <td class="proportional">{{ $proforma->selectedBy()?->operator?->name ?? 'N/A' }}</td>
                  @endif
                </tr>

                
                <tr>
                  <td class="no-wrap"><b>Pending Proforma Inboxed To</b></td>
                  <td class="proportional">
                    <ul>
                @if($proforma->inboxes?->count())
                      @foreach($proforma->inboxes as $inbox)
                        <li>{{ $inbox->user?->name ?? 'N/A' }}</li>
                      @endforeach
                    </ul>
                  </td>
                   @endif
                  <td class="no-wrap"><b>Submitted Applications</b></td>
                  <td class="proportional">
                    <ul>
                      @foreach($proforma->applications as $application)
                        <li>{{ $application->applicationBy?->id }} - {{ $application->applicationBy?->name ?? 'N/A' }}</li>
                      @endforeach
                    </ul>
                  </td>
                </tr>
               
              </tbody>
            </table>
          </div>

          @if($proforma->isFromInsurance() && in_array($proforma->status, ['pending','opened','published']))
            <livewire:publish-proforma :proforma="$proforma" />
          @elseif($proforma->isFromOthers() && in_array($proforma->status, ['pending','opened','published']))
            <livewire:publish-proforma-from-others :proforma="$proforma" />
          @endif

          @if($proforma->status === 'pending')
          <div class="mt-3">
            <button type="button" class="btn btn-danger btn-sm"
                    data-bs-toggle="modal" data-bs-target="#rejectProformaModal">
              Reject
            </button>
          </div>
          @endif
        </form>

        @if($proforma->status === 'pending')
        <div class="modal fade" id="rejectProformaModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <form action="{{ route('proformas.reject', $proforma->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                  <h5 class="modal-title text-danger">Reject Proforma #{{ $proforma->file_number }}</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <p>To confirm rejection, type <strong>reject</strong> below:</p>
                  <input type="text" name="confirmation" class="form-control" placeholder="Type 'reject' here" required autocomplete="off">
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-danger">Reject</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        @endif
      </div>
    </div>

    {{-- Media Section --}}
    @if($proforma->media->count())
    <div class="mt-4">
      <h4>Proforma Media</h4>
      <div class="media-container">
        @foreach($proforma->media as $media)
          @php $extension = pathinfo($media->path, PATHINFO_EXTENSION); @endphp

          @if(in_array($extension, ['jpg','jpeg','png','gif']))
            <a href="{{ asset('storage/' . $media->path) }}" target="_blank">
              <img src="{{ asset('storage/' . $media->path) }}" class="media-item" alt="Proforma Image">
            </a>
          @elseif(in_array($extension, ['mp4','webm','ogg']))
            <video controls class="media-item">
              <source src="{{ asset('storage/' . $media->path) }}" type="video/{{ $extension }}">
            </video>
          @elseif(in_array($extension, ['mp3','wav']))
            <audio controls style="width:100%;">
              <source src="{{ asset('storage/' . $media->path) }}" type="audio/{{ $extension }}">
            </audio>
          @endif
        @endforeach
      </div>
    </div>
    @endif
  </div>
</div>

{{-- Parts Modal --}}
<div class="modal fade" id="partsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Parts Needed</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered mb-0">
          <thead>
            <tr>
              <th>No</th>
              <th>Part Name and Munber</th>
              <th>Grade</th>
              <th>Country</th>
              <th>Quantity</th>
              <th>Condition</th>
              <th>Photo</th>
            </tr>
          </thead>
          <tbody>
            @foreach($proforma->parts as $part)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $part->number ?? 'N/A' }}</td>
              <td>{{ $part->grade ?? 'N/A' }}</td>
              <td>{{ $part->country ?? 'N/A' }}</td>
              <td>{{ $part->quantity ?? 'N/A' }}</td>
              <td>{{ $part->condition ?? 'N/A' }}</td>
              <td>
                @if(!empty($part->photo) && ($part->photo) != '[]')
                    
                  <a href="{{ asset('storage/' . $part->photo) }}" target="_blank" class="part-image-link">View Image</a>
                @else
                  #N/A
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary radius-30" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection
