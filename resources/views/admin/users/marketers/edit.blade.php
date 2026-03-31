@extends('layouts.admin')

@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <h3 class="">Edit Marketer</h3>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('admin.users.marketers.update', $marketer->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Name</label>
                                        <input type="text" name="name" id="name" class="form-control" value="{{ $marketer->name }}" required>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="phone_number" class="form-label">Phone Number</label>
                                        <input type="text" name="phone_number" id="phone_number" class="form-control" value="{{ $marketer->phone_number }}" required>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" name="email" id="email" class="form-control" value="{{ $marketer->email }}" required>
                                    </div>
                                </div>



                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">Update Marketer</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!--end row-->
    </div>
</div>
</div>
@endsection
