
@extends('layouts.sparepart')
@section('content')
<div class="container margin-top-45 margin-bottom-45">
    <div class="row">
        <div class="col-md-12">
            <style type="text/css">
                .pp {
                    /* Add styling if needed */
                }
                .fu {
                    /* Add styling if needed */
                }
                .ub {
                    /* Add styling if needed */
                }

                .avatar-wrapper {
                    position: relative;
                    display: inline-block;
                }

                .zoom-btn {
                    position: absolute;
                    bottom: 10px;
                    left: 50%;
                    transform: translateX(-50%);
                    background-color: var(--etera-teal);
                    color: white;
                    border: none;
                    padding: 5px 10px;
                    cursor: pointer;
                    font-size: 14px;
                    border-radius: 5px;
                    z-index: 10; /* Ensure buttons appear above the image */
                }

                .zoomable {
                    transition: transform 0.3s ease;
                }

                .zoomed {
                    transform: scale(1.5); /* Adjust the scale for zoom */
                }

                /* Fullscreen modal styling */
                .fullscreen-modal {
                    display: none;
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background-color: rgba(0, 0, 0, 0.8);
                    justify-content: center;
                    align-items: center;
                    z-index: 1000;
                }

                .fullscreen-modal img {
                    max-width: 100%;
                    max-height: 100%;
                    object-fit: contain;
                }

                .close-modal {
                    position: absolute;
                    top: 20px;
                    right: 20px;
                    color: white;
                    font-size: 30px;
                    background: none;
                    border: none;
                    cursor: pointer;
                    z-index: 1001;
                }
            </style>
            <link rel="stylesheet" href="{{asset('assets/plugins/select2/css/select2.min.css')}}" />
            <link rel="stylesheet" href="{{asset('assets/plugins/select2/css/select2-bootstrap-5-theme.min.css')}}" />

            <!-- Row -->
            <div class="row margin-bottom-45">
                <div class="col-xl-12">
                    <div class="counters-container">
                        <!-- Counter -->
                        <div class="single-counter">
                            <i class="icon-line-awesome-files-o"></i>
                            <div class="counter-inner">
                                <h3><span class="counter">{{\App\Models\ProformaApplication::where('application_by',auth()->id())->count()}}</span></h3>
                                <span class="counter-title">Total Applied Proformas</span>
                            </div>
                        </div>

                        <!-- Counter -->
                        <div class="single-counter">
                            <i class="icon-line-awesome-money"></i>
                            <div class="counter-inner">
                                <h3><span class="counter">{{auth()->user()->balance}}</span></h3>
                                <span class="counter-title">Total Gained Money</span>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
                @if(Session::has('success'))
                    <div class="headline">
                        <h3 class="text-success">{{Session::get('success')}}</h3>
                    </div>
                @endif
                {{-- <form action="{{route('update.profile',auth()->user()->id)}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <!-- Dashboard Box -->
                    <div class="col-xl-12">
                        <div class="dashboard-box margin-top-0">
                            <!-- Headline -->
                            <div class="headline">
                                <h3><i class="icon-material-outline-account-circle"></i> My Account</h3>
                            </div>
                            <div class="content with-padding padding-bottom-0">
                                <div class="row">
                                    <div class="col-auto">
                                        <div class="avatar-wrapper" data-tippy-placement="bottom" title="Change Stamp">
                                            @if(auth()->user()->stamp_image)
                                                <img class="profile-pic stamp-image" src="{{ asset('storage/' . auth()->user()->stamp_image) }}" alt="Stamp" />
                                            @else
                                                <img class="profile-pic stamp-image" src="{{asset('assets/images/stamp.png')}}" alt="No Stamp Here" />
                                            @endif
                                            <div class="upload-button"></div>
                                            <input class="file-upload" type="file" name="stamp_image" accept="image/*"/>
                                            <!-- Zoom Buttons for Stamp Image -->
                                            <button class="zoom-btn" type="button" onclick="openFullScreen('stamp-image')">Zoom</button>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="avatar-wrapper" data-tippy-placement="bottom" title="Change Business License">
                                            @if(auth()->user()->license_image)
                                                <img class="pp license-image" src="{{ asset('storage/' . auth()->user()->license_image) }}" alt="Business License" />
                                            @else
                                                <img class="pp license-image" src="{{asset('assets/images/license.png')}}" alt="No License Here" />
                                            @endif
                                            <div class="ub"></div>
                                            <input class="fu" type="file" name="license_image" accept="image/*"/>
                                            <!-- Zoom Buttons for License Image -->
                                            <button class="zoom-btn" type="button" onclick="openFullScreen('license-image')">Zoom</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="col">
                                    <div class="row">
                                        <div class="col-xl-6">
                                            <div class="submit-field">
                                                <h5>Name</h5>
                                                <input type="text" value="{{auth()->user()->name}}" name="name"  class="with-border">
                                                @error('name')
                                                    <span class="text-danger">{{$message}}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-xl-6">
                                            <div class="submit-field">
                                                <h5>Phone number</h5>
                                                <input value="{{auth()->user()->phone_number}}" type="text" name="phone_number" class="with-border" >
                                                @error('phone_number')
                                                    <span class="text-danger">{{$message}}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-xl-6">
                                            <div class="submit-field">
                                                <h5>Email</h5>
                                                <input value="{{auth()->user()->email}}" type="text" name="email" class="with-border" >
                                                @error('email')
                                                    <span class="text-danger">{{$message}}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        @if(auth()->user()->role == 'shop')
                                        <div class="col-xl-6">
                                            <div class="submit-field">
                                                <h5>Car To Serve</h5>
                                                <div class="keywords-container">
                                                    <select class="keyword-select" name="brands[]" multiple data-live-search="true" title="Select keywords">
                                                        @foreach(\App\Models\Brand::all() as $brand)
                                                            <option {{auth()->user()->brands()->where('brand_id',$brand->id)->exists() ? 'selected' : ''}} value="{{$brand->id}}">{{$brand->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        <div class="col-xl-6">
                                            <div class="submit-field">
                                                <h5>Tin Number</h5>
                                                <input value="{{auth()->user()->tin_number}}" type="text" name="tin_number" class="with-border" >
                                            </div>
                                        </div>
                                        <div class="col-xl-6">
                                            <div class="submit-field">
                                                <h5>Business License Proc. Number</h5>
                                                <input value="{{auth()->user()->business_license_number}}" type="text" name="business_license_number" class="with-border" >
                                            </div>
                                        </div>
                                        <div class="col-xl-6">
                                            <div class="submit-field">
                                                <h5>Business License Expiry Date</h5>
                                                <input value="{{auth()->user()->license_expire_date}}" type="date" name="license_expire_date" class="with-border" >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <!-- Dashboard Box -->
                    <div class="col-xl-12">
                        <div id="test1" class="dashboard-box">
                            <!-- Headline -->
                            <div class="headline">
                                <h3><i class="icon-material-outline-lock"></i> Password & Security</h3>
                            </div>

                            <div class="content with-padding">
                                <div class="row">
                                    <div class="col-xl-4">
                                        <div class="submit-field">
                                            <h5>Current Password</h5>
                                            <input type="password" class="with-border" name="current_password">
                                            @error('current_password')
                                                <span class="text-danger">{{$message}}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-xl-4">
                                        <div class="submit-field">
                                            <h5>New Password</h5>
                                            <input type="password" class="with-border" name="password">
                                            @error('password')
                                                <span class="text-danger">{{$message}}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-xl-4">
                                        <div class="submit-field">
                                            <h5>Repeat New Password</h5>
                                            <input type="password" class="with-border" name="password_confirmation">
                                            @error('password_confirmation')
                                                <span class="text-danger">{{$message}}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Button -->
                    <div class="col-xl-12">
                        <button type="submit" class="button radius-30 ripple-effect big margin-top-30">Save Changes</button>
                    </div>
                </form> --}}
    
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
</div>

<script>
    setTimeout(function() {
        document.querySelector('.alert-success')?.remove();
    }, 3000); // Hides after 3 seconds
</script>
@endif


                <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
					<!-- Dashboard Box -->
					<div class="col-xl-12">
						<div class="dashboard-box margin-top-0">
							<!-- Headline -->
							<div class="headline">
								<h3><i class="icon-material-outline-account-circle"></i> My Account</h3>
							</div>
							<div class="content with-padding padding-bottom-0">
								<div class="row">
									<div class="col-auto">
										<div class="avatar-wrapper" data-tippy-placement="bottom" title="Change Stamp">
											@if(auth()->user()->stamp_image)
												<img class="profile-pic stamp-image" src="{{ asset('storage/' . auth()->user()->stamp_image) }}" alt="Stamp" />
                                        
                                                @else
												<img class="profile-pic stamp-image" src="{{asset('assets/images/stamp.png')}}" alt="No Stamp Here" />
											@endif
											<div class="upload-button"></div>
											<input class="file-upload" type="file" name="stamp_image" accept="image/*"/>
											<!-- Zoom Buttons for Stamp Image -->
											<button class="zoom-btn" type="button" onclick="openFullScreen('stamp-image')">Zoom</button>
										</div>
									</div>
									<div class="col-auto">
										<div class="avatar-wrapper" data-tippy-placement="bottom" title="Change Business License">
											@if(auth()->user()->license_image)
												<img class="pp license-image" src="{{ asset('storage/' . auth()->user()->license_image) }}" alt="Business License" />
											@else
												<img class="pp license-image" src="{{asset('assets/images/license.png')}}" alt="No License Here" />
											@endif
											<div class="ub"></div>
											<input class="fu" type="file" name="license_image" accept="image/*"/>
											<!-- Zoom Buttons for License Image -->
											<button class="zoom-btn" type="button" onclick="openFullScreen('license-image')">Zoom</button>
										</div>
									</div>
								</div>
				
								<div class="col">
									<div class="row">
										<div class="col-xl-6">
											<div class="submit-field">
												<h5>Name</h5>
												<input type="text" value="{{auth()->user()->name}}" name="name"  class="with-border">
												@error('name')
													<span class="text-danger">{{$message}}</span>
												@enderror
											</div>
										</div>
				
										<div class="col-xl-6">
											<div class="submit-field">
												<h5>Phone number</h5>
												<input value="{{auth()->user()->phone_number}}" type="text" name="phone_number" class="with-border" >
												@error('phone_number')
													<span class="text-danger">{{$message}}</span>
												@enderror
											</div>
										</div>
				
										<div class="col-xl-6">
											<div class="submit-field">
												<h5>Email</h5>
												<input value="{{auth()->user()->email}}" type="text" name="email" class="with-border" >
												@error('email')
													<span class="text-danger">{{$message}}</span>
												@enderror
											</div>
										</div>
				
										<div class="col-xl-6">
											<div class="submit-field">
												<h5>Tin Number</h5>
												<input value="{{auth()->user()->tin_number}}" type="text" name="tin_number" class="with-border" >
											</div>
										</div>
										{{-- <div class="col-xl-6">
											<div class="submit-field">
												<h5>Business License Proc. Number</h5>
												<input value="{{auth()->user()->business_license_number}}" type="text" name="business_license_number" class="with-border" >
											</div>
										</div>
										<div class="col-xl-6">
											<div class="submit-field">
												<h5>Business License Expiry Date</h5>
												<input value="{{auth()->user()->license_expire_date}}" type="date" name="license_expire_date" class="with-border" >
											</div>
										</div> --}}
									</div>
								</div>
							</div>
						</div>
					</div>
					<br>
					<!-- Dashboard Box -->
					<div class="col-xl-12">
						<div id="test1" class="dashboard-box">
							<!-- Headline -->
							<div class="headline">
								<h3><i class="icon-material-outline-lock"></i> Password & Security</h3>
							</div>
				
							<div class="content with-padding">
								<div class="row">
									<div class="col-xl-4">
										<div class="submit-field">
											<h5>Current Password</h5>
											<input type="password" class="with-border" name="current_password">
											@error('current_password')
												<span class="text-danger">{{$message}}</span>
											@enderror
										</div>
									</div>
				
									<div class="col-xl-4">
										<div class="submit-field">
											<h5>New Password</h5>
											<input type="password" class="with-border" name="password">
											@error('password')
												<span class="text-danger">{{$message}}</span>
											@enderror
										</div>
									</div>
				
									<div class="col-xl-4">
										<div class="submit-field">
											<h5>Repeat New Password</h5>
											<input type="password" class="with-border" name="password_confirmation">
											@error('password_confirmation')
												<span class="text-danger">{{$message}}</span>
											@enderror
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- Button -->
					<div class="col-xl-12">
						<button type="submit" class="button radius-30 ripple-effect big margin-top-30">Save Changes</button>
					</div>
				</form>
				
            </div>
        </div>
    </div>
</div>

<!-- Fullscreen Modal -->
<div id="fullscreenModal" class="fullscreen-modal">
    <button class="close-modal" onclick="closeFullScreen()">×</button>
    <img id="fullscreenImage" src="" alt="Full Screen" />
</div>

<!-- Bank Accounts Section -->
<div class="col-xl-12">
    <div class="dashboard-box">
        <div class="headline d-flex justify-content-between align-items-center">
            <h3><i class="icon-material-outline-account-balance"></i> My Bank Accounts</h3>
            <button class="button ripple-effect" data-bs-toggle="modal" data-bs-target="#addBankModal">+ Add Account</button>
        </div>

        <div class="content with-padding">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Bank Name</th>
                            <th>Account Number</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(auth()->user()->bankAccounts as $account)
                        <tr>
                            <td>{{ $account->bank_name }}</td>
                            <td>{{ $account->account_number }}</td>
                            <td>
                                <button class="button small ripple-effect" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editBankModal" 
                                    data-id="{{ $account->id }}"
                                    data-bank="{{ $account->bank_name }}"
                                    data-account="{{ $account->account_number }}">
                                    Edit
                                </button>
                            </td>
                        </tr>
                        @endforeach

                        @if(auth()->user()->bankAccounts->isEmpty())
                        <tr>
                            <td colspan="3" class="text-center">No bank accounts added yet.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Bank Modal -->
<div class="modal fade" id="addBankModal" tabindex="-1" aria-labelledby="addBankModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('user.bank.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBankModalLabel">Add Bank Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Bank / Payment Provider</label>
                          <select name="bank_name" id="bankSelect" class="form-select @error('bank_name') is-invalid @enderror" required>
        <option value="">Select Your Bank or Telebirr</option>

<!-- State-Owned Banks -->
<option value="Commercial Bank of Ethiopia">Commercial Bank of Ethiopia (CBE)</option>
<option value="Development Bank of Ethiopia">Development Bank of Ethiopia (DBE)</option>

<!-- Private Banks -->
<option value="Awash Bank">Awash Bank</option>
<option value="Dashen Bank">Dashen Bank</option>
<option value="Bank of Abyssinia">Bank of Abyssinia</option>
<option value="Wegagen Bank">Wegagen Bank</option>
<option value="Nib International Bank">Nib International Bank</option>
<option value="Cooperative Bank of Oromia">Cooperative Bank of Oromia</option>
<option value="Hibret Bank">Hibret Bank</option>
<option value="Bunna Bank">Bunna Bank</option>
<option value="Berhan Bank">Berhan Bank</option>
<option value="Enat Bank">Enat Bank</option>
<option value="Lion Bank">Lion Bank</option>
<option value="Zemen Bank">Zemen Bank</option>
<option value="Addis International Bank">Addis International Bank</option>
<option value="Abay Bank">Abay Bank</option>
<option value="Oromia International Bank">Oromia International Bank</option>
<option value="Construction and Business Bank">Construction and Business Bank (CBB)</option>
<option value="Amhara Bank">Amhara Bank</option>
<option value="Tsehay Bank">Tsehay Bank</option>
<option value="Ahadu Bank">Ahadu Bank</option>
<option value="Tsedey Bank">Tsedey Bank</option>
<option value="Siinqee Bank">Siinqee Bank</option>
<option value="Gadaa Bank">Gadaa Bank</option>
<option value="Sidama Bank">Sidama Bank</option>
<option value="Shabelle Bank">Shabelle Bank</option>
<option value="ZamZam Bank">ZamZam Bank</option>
<option value="Hijra Bank">Hijra Bank</option>
<option value="Debub Global Bank">Debub Global Bank</option>
<option value="Global Bank Ethiopia">Global Bank Ethiopia</option>

<!-- Mobile Wallet -->
<option value="Telebirr">Telebirr (Mobile Wallet)</option>

    </select>

    @error('bank_name')
        <span class="text-danger">{{ $message }}</span>
    @enderror
                    </div>
                    <div class="mb-3">
                        <label>Account Number</label>
                        <input type="text" name="account_number" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="button ripple-effect">Add</button>
                    <button type="button" class="button gray ripple-effect" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Bank Modal -->
<div class="modal fade" id="editBankModal" tabindex="-1" aria-labelledby="editBankModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editBankForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editBankModalLabel">Edit Bank Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Bank Name</label>
                        <input type="text" name="bank_name" id="editBankName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Account Number</label>
                        <input type="text" name="account_number" id="editAccountNumber" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="button ripple-effect">Update</button>
                    <button type="button" class="button gray ripple-effect" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Fill edit modal with data
    var editModal = document.getElementById('editBankModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var bank = button.getAttribute('data-bank');
        var account = button.getAttribute('data-account');

        var form = document.getElementById('editBankForm');
        form.action = '/user/bank/' + id;
        document.getElementById('editBankName').value = bank;
        document.getElementById('editAccountNumber').value = account;
    });
</script>


<script>
    function openFullScreen(imageClass) {
        var imageSrc = document.querySelector('.' + imageClass).src;
        var modal = document.getElementById('fullscreenModal');
        var modalImage = document.getElementById('fullscreenImage');
        modalImage.src = imageSrc;
        modal.style.display = 'flex';
    }

    function closeFullScreen() {
        var modal = document.getElementById('fullscreenModal');
        modal.style.display = 'none';
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
@endsection
