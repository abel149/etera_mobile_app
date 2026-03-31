@extends('layouts.admin')
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
                <form action="" method="POST">
                    
    <input type="hidden" name="proforma" value="">

                    <div>
                        <div class="table-responsive lead-table">
                            <table class="table tables mb-5 align-middle">
                                <tbody>
                                    <tr>
                                        <td class="no-wrap"><b>Name</b>&nbsp;</td>
                                        <td class="proportional">...</td>
                                        <td class="no-wrap"><b>Year</b>&nbsp;</td>
                                        <td class="proportional">...</td>
                                    </tr>
                                    <tr>
                                        <td class="no-wrap"><b>Role</b>&nbsp;</td>
                                        <td class="proportional">...</td>
                                        <td class="no-wrap"><b>Phone Number</b>&nbsp;</td>
                                        <td class="proportional">...</td>
                                    </tr>
                                    <tr>
                                        <td class="no-wrap"><b>ID #</b>&nbsp;</td>
                                        <td class="proportional">...</td>
                                        <td class="no-wrap"><b>Needed Parts</b>&nbsp;</td>
                                        <td class="proportional"><span class="text-purple" style="cursor: pointer" data-bs-toggle="modal" data-bs-target="#exampleScrollableModal">... Parts</span></td>
                                    </tr>
                                    <tr>
                                        <td class="no-wrap"><b>Car</b>&nbsp;</td>
                                        <td class="proportional">...</td>
                                        <td class="no-wrap"><b>VIN Number</b>&nbsp;</td>
                                        <td class="proportional">...</td>
                                    </tr>
                                    <tr>
                                        <td class="no-wrap"><b>Model</b>&nbsp;</td>
                                        <td class="proportional">...</td>
                                        <td class="no-wrap">&nbsp;</td>
                                        <td class="proportional"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <h4 class="mb-3">Spare Part Shops To Apply</h4>
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <label class="mt-2 mb-1">Spare Part #1</label>
                                                        <div class="input-group">
                                                            <select class="form-select" id="single-select-field" data-placeholder="Select Spare Part Shop">
                                                                <option value="">Select Spare Part Shop</option>
                                                                    <option value="">...</option>
                                                            </select>
                                                            {{-- <span class="input-group-text">
                                                                @if($selectedInsuranceShop)
                                                                    <i class="bx bx-lock text-danger"></i>
                                                                @else
                                                                    <i class="bx bx-lock-open text-success"></i>
                                                                @endif
                                                            </span> --}}
                                                            <span class="input-group-text"><i class="bx bx-lock-open text-success"></i></span>
                                                        </div>
                                                        {{-- @error('selected_insurance_shop')
                                                        <span class="text-danger">{{$message}}</span>
                                                        @enderror --}}
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <label class="mt-2 mb-1">Spare Part #2</label>
                                                        <div class="input-group">
                                                            <select class="form-select" id="single-select-field" data-placeholder="Select Spare Part Shop">
                                                                <option value="">Select Spare Part Shop</option>
                                                                    <option value="">...</option>
                                                            </select>
                                                            {{-- <span class="input-group-text">
                                                                @if($selectedInsuranceShop)
                                                                    <i class="bx bx-lock text-danger"></i>
                                                                @else
                                                                    <i class="bx bx-lock-open text-success"></i>
                                                                @endif
                                                            </span> --}}
                                                            <span class="input-group-text"><i class="bx bx-lock-open text-success"></i></span>
                                                        </div>
                                                        {{-- @error('selected_insurance_shop')
                                                        <span class="text-danger">{{$message}}</span>
                                                        @enderror --}}
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <label class="mt-2 mb-1">Spare Part #3</label>
                                                        <div class="input-group">
                                                            <select class="form-select" id="single-select-field" data-placeholder="Select Spare Part Shop">
                                                                <option value="">Select Spare Part Shop</option>
                                                                    <option value="">...</option>
                                                            </select>
                                                            {{-- <span class="input-group-text">
                                                                @if($selectedInsuranceShop)
                                                                    <i class="bx bx-lock text-danger"></i>
                                                                @else
                                                                    <i class="bx bx-lock-open text-success"></i>
                                                                @endif
                                                            </span> --}}
                                                            <span class="input-group-text"><i class="bx bx-lock-open text-success"></i></span>
                                                        </div>
                                                        {{-- @error('selected_insurance_shop')
                                                        <span class="text-danger">{{$message}}</span>
                                                        @enderror --}}
                                                    </div>
                                                </div>
                                            </div>

                                    </div>
                                </div>

                                <div class="my-0">
                                    <button type="submit" class="btn btn-primary radius-30 px-4" onclick="notification('Proforma Posted')"> Post
                                    </button>
                                    &nbsp
                                    <a href="/garages" type="button" class="btn btn-outline-secondary radius-30 px-3"> Cancel
                                    </a>
                                </div>
                            </div>
                        </div>

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
                                                                    <th scope="col">Part Name and Number</th>
                                                                    <th scope="col">Grade</th>
                                                                    <th scope="col">Country</th>
                                                                    <th scope="col">Qty</th>
                                                                    <th scope="col" style="width: 20%;">Image</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <th scope="row" class="no-wrap">1</th>
                                                                    <td>Part</td>
                                                                    <td>91268</td>
                                                                    <td>C</td>
                                                                    <td>China</td>
                                                                    <td>3</td>
                                                                    <td><img src="assets/images/gallery/21.png" class="d-block w-100" alt="..." data-bs-container="body" data-bs-toggle="popover" data-bs-placement="right" data-bs-content="Vivamus sagittis lacus vel augue laoreet rutrum faucibus."></td>
                                                                </tr>
                                                                <tr>
                                                                    <th scope="row" class="no-wrap">2</th>
                                                                    <td>Part</td>
                                                                    <td>91268</td>
                                                                    <td>C</td>
                                                                    <td>China</td>
                                                                    <td>3</td>
                                                                    <td><img src="assets/images/gallery/21.png" class="d-block w-100" alt="..." data-bs-container="body" data-bs-toggle="popover" data-bs-placement="right" data-bs-content="Vivamus sagittis lacus vel augue laoreet rutrum faucibus."></td>
                                                                </tr>
                                                                <tr>
                                                                    <th scope="row" class="no-wrap">3</th>
                                                                    <td>Part</td>
                                                                    <td>91268</td>
                                                                    <td>C</td>
                                                                    <td>China</td>
                                                                    <td>3</td>
                                                                    <td><img src="assets/images/gallery/21.png" class="d-block w-100" alt="..." data-bs-container="body" data-bs-toggle="popover" data-bs-placement="right" data-bs-content="Vivamus sagittis lacus vel augue laoreet rutrum faucibus."></td>
                                                                </tr>
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
