@extends('layouts.employee')
@section('content')
<!--start page wrapper -->
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
</style>

<div class="page-wrapper">
    <div class="page-content">     
        <h3 class="">Proforma Details</h3>    

        <div class="card">
            <div class="card-body">
                <form action="{{route('proforma.store')}}" method="POST">
                    @csrf
    <input type="hidden" name="proforma" value="{{$proforma->id}}">

                    <div>
                        <div class="table-responsive lead-table">
                            <table class="table tables mb-5 align-middle">
                                <tbody>
                                    <tr>
                                        <td class="no-wrap"><b>Insurance</b>&nbsp;</td>
                                        <td class="proportional">{{ $proforma->insurance->name }}</td>
                                        <td class="no-wrap"><b>License Plate</b>&nbsp;</td>
                                        <td class="proportional">{{ $proforma->license_plate_number }}</td>
                                    </tr>
                                    <tr>
                                        <td class="no-wrap"><b>File #</b>&nbsp;</td>
                                        <td class="proportional">{{ $proforma->file_number }}</td>
                                        <td class="no-wrap"><b>Phone Number</b>&nbsp;</td>
                                        <td class="proportional">{{ $proforma->customer_phone_number }}</td>
                                    </tr>
                                    <tr>
                                        <td class="no-wrap"><b>Car</b>&nbsp;</td>
                                        <td class="proportional">{{ $proforma->brand->name }}</td>
                                        <td class="no-wrap"><b>Needed Parts</b>&nbsp;</td>
                                        <td class="proportional"><span class="text-purple" style="cursor: pointer" data-bs-toggle="modal" data-bs-target="#exampleScrollableModal">{{ $proforma->parts->count() }} Parts</span></td>
                                    </tr>
                                    <tr>
                                        <td class="no-wrap"><b>Model</b>&nbsp;</td>
                                        <td class="proportional">{{ $proforma->model }}</td>
                                        <td class="no-wrap"><b>Chassis Number</b>&nbsp;</td>
                                        <td class="proportional">{{ $proforma->chassis_number }}</td>
                                    </tr>
                                    <tr>
                                      <td class="no-wrap"><b>Proforma Requested</b>&nbsp;</td>
                                        <td class="proportional">{{ $proforma->number_of_proformas }}</td>
                                        
                                    @if($proforma->selected())
                                        <td class="no-wrap"><b>Proforma Selected By</b>&nbsp;</td>
                                        <td class="proportional">{{ $proforma->selectedBy()?->operator?->name }}</td>
                                    @endif
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        @if($proforma->isFromInsurance() && $proforma->status == "pending")
                        <livewire:publish-proforma :proforma="$proforma" />
                        @elseif($proforma->isFromOthers() && $proforma->status == "pending")
                        <livewire:publish-proforma-from-others :proforma="$proforma" />
                        @endif
                </form>
            </div>
        </div>
    </div>
</div>
<!--end page wrapper -->



<div class="modal fade" id="exampleScrollableModal" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Parts Needed</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <table class="table mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th scope="col"></th>
                                                                    <th scope="col">Part Name</th>
                                                                    <th scope="col">Part Number & Name</th>
                                                                    <th scope="col">Grade</th>
                                                                    <th scope="col">Country</th>
                                                                    <th scope="col">Qty</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>

                                                            @foreach($proforma->parts as $part)
                                                                <tr>
                                                                    <th scope="row" class="no-wrap">{{in_array($part->id, $proforma->parts->pluck('id')->toArray()) ? '✓' : ''}}</th>
                                                                    <td>{{$part->name}}</td>
                                                                    <td>{{$part->number}}</td>
                                                                    <td>{{$part->grade}}</td>
                                                                    <td>{{$part->country}}</td>
                                                                    <td>{{$part->quantity}}</td>
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
