@extends('layouts.admin')
@section('content')
<!--start page wrapper -->
        <div class="page-wrapper">
            <div class="page-content">              
                <div class="card">
                  <div class="card-body p-4">
                      <h5 class="card-title">Edit Brand</h5>
                      <hr/>
                       <form class="row g-3" action="{{ route('brands.update', $brands->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                                    <div class="col-md-12">
                                        <label for="input1" class="form-label">Name</label>
                                        {{-- <input name="name" value="{{old('name')}}" type="text" class="form-control" id="input1" placeholder="Enter The Brand..."> --}}
                                  
                                        <input name="name" value="{{ old('name', $brands->name) }}" type="text" class="form-control" id="input1" placeholder="Enter The Brand...">

                                    </div>
                                    @error('name')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    <div class="pt-3">
                                        <hr/>
                                        <button type="submit" class="btn btn-primary radius-30 px-4" onclick="notification('Brand Updated Successfully')"> Update
                                        </button>
                                        &nbsp
                                        <a href="/admin/brands" type="submit" class="btn btn-outline-secondary radius-30 px-3"> Cancel
                                        </a>
                                    </div>
                                </form>
                  </div>
              </div>


            </div>
        </div>
        <!--end page wrapper -->
@endsection