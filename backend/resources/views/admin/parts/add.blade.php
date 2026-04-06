{{-- @extends('layouts.admin')
@section('content')
<!--start page wrapper -->
        <div class="page-wrapper">
            <div class="page-content">              
                <div class="card">
                  <div class="card-body p-4">
                      <h5 class="card-title">Add Spare Parts</h5>
                      <hr/>
                       <form class="row g-3" action="{{route('add-part')}}" method="POST">
                        @csrf
                        @method('POST')
                                    <div class="col-md-12">
                                        <label for="input1" class="form-label">Name</label>
                                        <input name="name" type="text" class="form-control" id="input1" placeholder="Enter The Spare Part...">
                                        @error('name')
                                        <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                    
                                    <div class="pt-3">
                                        <hr/>
                                        <button type="submit" class="btn btn-primary radius-30 px-4" onclick="notification('Car Part Added Successfully')"> Add
                                        </button>
                                        &nbsp
                                        <a href="/admin/parts" type="button" class="btn btn-outline-secondary radius-30 px-3"> Cancel
                                        </a>
                                    </div>
                                </form>
                  </div>
              </div>


            </div>
        </div>
        <!--end page wrapper -->
@endsection --}}

@extends('layouts.admin')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">              
        <div class="card">
            <div class="card-body p-4">
                <h5 class="card-title">Add Spare Part</h5>
                <hr/>
                <form class="row g-3" action="{{ route('add-part') }}" method="POST">
                    @csrf
                    @method('POST')

                    <!-- Spare Part Name -->
                    <div class="col-md-12">
                        <label for="input1" class="form-label">Name</label>
                        <input name="name" type="text" class="form-control" id="input1" placeholder="Enter the Spare Part..." required>
                        @error('name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Component Type -->
                    <div class="col-md-12">
                        <label for="component" class="form-label">Component Type</label>
                        <select name="component" id="component" class="form-select" required>
                            <option value="">Select Component Type</option>
                            <option value="Body Parts (Inner)">Body Parts (Inner)</option>
                            <option value="Body Parts (Outer)">Body Parts (Outer)</option>
                            <option value="Mechanical Parts">Mechanical Parts</option>
                        </select>
                        @error('component')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Submit & Cancel -->
                    <div class="pt-3">
                        <hr/>
                        <button type="submit" class="btn btn-primary radius-30 px-4">
                            Add
                        </button>
                        &nbsp;
                        <a href="/admin/parts" type="button" class="btn btn-outline-secondary radius-30 px-3">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--end page wrapper -->
@endsection
