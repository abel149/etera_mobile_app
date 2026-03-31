@extends('layouts.marketer')
@section('content')
<!--start page wrapper -->
        <div class="page-wrapper">
            <div class="page-content">              
                <div class="card">
                  
                  <div class="card-body p-4">
                      <h5 class="card-title">Add Insurance</h5>
                      <hr/>
                      @if ($errors->has('error'))
                          <div class="alert alert-danger">
                              {{ $errors->first('error') }}
                          </div>
                      @endif
                       <form class="row g-3" action="{{route('add-insurance.marketer')}}" method="POST">
                        @csrf
                        @method('POST')
                                    <div class="col-md-12">
                                        <label for="input1" class="form-label">Name</label>
                                        <input type="text" name="name" class="form-control" id="input1" placeholder="Insurance Name...">
                                    </div>
                                    @error('name')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    <div class="col-md-12">
                                        <label for="input7" class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" id="input7" placeholder="Email...">
                                    </div>
                                      @error('email')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                     <div class="col-md-12">
                                        <label for="input7" class="form-label">Phone Number</label>
                                        <input type="number" name="phone_number" class="form-control" id="input7" placeholder="251...">
                                    </div>
                                      @error('phone_number')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                    
                               
                                    <hr/>
                                    <div class="my-0">
                                        <button type="submit" class="btn btn-primary radius-30 px-4" onclick="notification('Insurance Added Successfully')"> Add
                                        </button>
                                        &nbsp
                                        <a href="/marketer/insurances" type="button" class="btn btn-outline-secondary radius-30 px-3"> Cancel
                                        </a>
                                    </div>
                                </form>
                  </div>
              </div>


            </div>
        </div>
        <!--end page wrapper -->
@endsection
