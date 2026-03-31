@extends('layouts.sparepart')
@section('others')
class="current"
@endsection
@section('content')

<style type="text/css">
.player audio
{
  width: 100%;
  border-radius: 6px;
  margin: 0;
  padding: 0;
  border: none;
}
.gfg {
    padding-bottom: 10px;
    display: flex;
    justify-content: space-around;
    flex-direction: column;
    font-size: 24px;
    font-weight: 600;
    color: #01940b;
    align-items: center;
}

.video-container {
    max-width: 500px;
    max-height: 300px;
    position: relative;
    border: 1px;
    border-style: ridge;
    margin: 0 auto;
    background-color: black;
}

#video-thumbnail {
    position: absolute;
    width: 100%;
    height: 100%;
    object-fit: cover;
    cursor: pointer;
}

#video{
    width: 100%;
    height: 100%;
    border-radius: 10px;
}

img{
	width: 100%;
    height: 100%;
}

.controls {
    position: absolute;
    bottom: 0px;
    left: 0;
    right: 0;
    height: 40px;
    background-color: rgba(
        0,
        0,
        0,
        0.7
    );
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    opacity: 0;
    transition: opacity 0.3s ease-in-out;
}

.controls button {
    background-color: transparent;
    border: none;
    color: #fff;
    font-size: 20px;
    cursor: pointer;
    outline: none;
}

.left {
    position: relative;
    top: 1%;
    width: 70px;
    display: flex;
    justify-content: space-between;
}

.video-timer {
    position: relative;
    top: 5.2px;
    right: 6px;
    display: flex;
    flex-direction: row;
    color: #efefef;
    margin-left: 15px;
    font-size: 12px;
}

#separator {
    margin: 0 5px;
    font-size: 16px;
    font-family: "Open sans";
}

.right {
    position: relative;
    padding: 10px;
    top: 1.5px;
}

.fa-volume-up,
.fa-solid {
    font-size: small;
    padding: 5px;
    color: rgb(255, 255, 255);
}

button,
input {
    background-color: transparent;
    border: none;
    cursor: pointer;
    font-size: 20px;
}

.volume-container {
    display: flex;
    align-items: center;
}

#volume {
    position: relative;
    left: 5px;
    width: 50px;
    height: 3px;
}

#mute {
    cursor: pointer;
}

.playback-line {
    position: relative;
    top: 2.7px;
    height: 4px;
    background-color: #ddd;
    width: 40%;
    cursor: pointer;
}

.progress-bar {
    height: 100%;
    width: 0;
    background-color: #0078d4;
    transition: width 0.1s linear;
}
</style>
<!-- Titlebar
================================================== -->
<div class="single-page-header" data-background-image="asset/images/banner-auto-insurance.jpg">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="single-page-header-inner">
					<div class="left-side">
						<div class="header-image"><a href="single-company-profile.html"><img src="asset/images/company-logo-03a.png" alt=""></a></div>
						<div class="header-details">
							<h3>Business owner or garage ID</h3>
							<ul>
								<li><i class="icon-feather-credit-card"></i> Role: Business Owner</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>


<!-- Page Content
================================================== -->
<div class="container">
	<div class="row">

		<!-- Content -->
		<div class="col-xl-8 col-lg-8 content-right-offset">

			<div class="single-page-section">
				<div id="carouselExampleControls" class="carousel slide" data-bs-ride="carousel">
					<div class="carousel-inner">
						<div class="carousel-item active">
							<img src="assets/images/gallery/20.png" class="d-block w-100" alt="...">
						</div>
						<div class="carousel-item">
							<img src="assets/images/gallery/21.png" class="d-block w-100" alt="...">
						</div>
						<div class="carousel-item">
							<img src="assets/images/gallery/22.png" class="d-block w-100" alt="...">
						</div>
					</div>
					<a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-bs-slide="prev">	<span class="carousel-control-prev-icon" aria-hidden="true"></span>
						<span class="visually-hidden"></span>
					</a>
					<a class="carousel-control-next" href="#carouselExampleControls" role="button" data-bs-slide="next">	<span class="carousel-control-next-icon" aria-hidden="true"></span>
						<span class="visually-hidden"></span>
					</a>
				</div>
				<div class="margin-top-20" style="overflow-y: hidden; overflow-x: auto; white-space: nowrap;">
					<table class="basic-table">
						<tr>
							<th style="width: 1%;"></th>
							<th>Part Name</th>
							<th>Part Name and Number</th>
							<th>Grade</th>
							<th>Country</th>
							<th>Condition</th>
							<th>Qty</th>
							<th class="center" style="width: 17%;">Image</th>
							<th style="min-width: 100px;">Price</th>
						</tr>

						<tr>
							<td data-label="Column 1">1</td>
							<td data-label="Column 2">part 1</td>
							<td data-label="Column 3">21423</td>
							<td data-label="Column 3">C</td>
							<td data-label="Column 4">China</td>
							<td data-label="Column 4">New</td>
							<td data-label="Column 5">3</td>
							<td data-label="Column 6"><img src="assets/images/gallery/21.png" class="d-block w-100" alt="..." data-bs-container="body" data-bs-toggle="popover" data-bs-placement="right" data-bs-content="Vivamus sagittis lacus vel augue laoreet rutrum faucibus."></td>
							<td style="min-width: 100px;"><input type="text" name="price" class="with-border" placeholder="Price" value="" style="padding:5px"></td>
						</tr>

					</table>
				</div>


			</div>
		</div>


		<!-- Sidebar -->
		<div class="col-xl-4 col-lg-4">
			<div class="sidebar-container">

				<!-- Sidebar Widget -->
				<div class="sidebar-widget">
					<div class="job-overview">
						<div class="job-overview-headline">Proformsa Summary</div>
						<div class="job-overview-inner">
							<ul>
								<li>
									<i class="icon-material-outline-directions-car"></i>
									<span>Brand</span>
									<h5>Model</h5>
								</li>
								<li>
									<i class="icon-material-outline-settings"></i>
									<span>Spare Part Shop</span>
									<h5>3 remaining</h5>
								</li>
								<li>
									<i class="icon-material-outline-access-time"></i>
									<span>Date Posted</span>
									<h5>Time</h5>
								</li>
							</ul>
						</div>
					</div>
				</div>

				<div >
					<div class="job-overview margin-bottom-10">
						<div class="job-overview-headline">Audio</div>
					</div>
					<div class="player">
					    <audio controls>
					      <source src="asset/music/Diggity111.mp3" type="audio/mp3">
					    </audio>
					 </div>
				</div>
<br>
				<div class="job-overview margin-bottom-10">
						<div class="job-overview-headline">Video</div>
					</div>
	<div>
       <video id="video" controls >

          <!-- Video source -->
            <source src="asset/music/123.mp4" type="video/mp4">
        </video> 

        <!-- Controlers continer -->
        <div class="controls">

          <!-- Left controller div -->
            <div class="left">
                <button id="skipminus-10">
                    <i class="fa-solid fa-backward"></i>
                  </button>
                <button id="play-pause">
                    <i class="fa-solid fa-play"></i>
                  </button>
                <button id="skip-10">
                      <i class="fa-solid fa-forward"></i>
                  </button>
            </div>

      <!-- div for progress bar -->
            <div class="video-timer">
                <span id="current-time">00:00</span>
                <span id="separator">/</span>
                <span id="max-duration">00:00</span>
            </div>
            <div class="playback-line">
                <div class="progress-bar"></div>

            </div>

            <!-- Right controller div -->
            <div class="right">
                <div class="volume-container">
                    <div id="mute">
                        <i class="fas fa-volume-up"></i>
                    </div>
                    <input type="range"
                           id="volume"
                           min="0"
                           max="1"
                           step="0.01"
                           value="1">
                </div>
            </div>
        </div>
    </div>
				<div>
				    <form action="apply" method="POST">
                        @csrf
							<div>
								<div class="job-overview margin-bottom-10 margin-top-20">
									<div class="job-overview-headline">Total Price</div>
								</div>
								<div class="submit-field">
									<input type="text" name="amount" class="with-border" placeholder="Price" value="">
								</div>
							</div>
							<button type="submit" class="apply-now-button radius-30">Apply Now <i class="icon-material-outline-arrow-right-alt"></i></button>
                    </form>

				</div>

		</div>
	</div>

<div class="container margin-bottom-45">
	<div class="row">
		<div class="col-md-12">
			<h3 class="margin-bottom-10">Related</h3>
			<div class="listings-container compact-list-layout margin-top-35">

				<!-- Job Listing -->
				<a href="single-job-page.html" class="job-listing with-apply-button">

					<!-- Job Listing Details -->
					<div class="job-listing-details">

						<!-- Logo -->
						<div class="job-listing-company-logo">
							<img src="asset/images/company-logo-01.png" alt="">
						</div>

						<!-- Details -->
						<div class="job-listing-description">
							<h3 class="job-listing-title">Nib Insurance</h3>

							<!-- Job Listing Footer -->
							<div class="job-listing-footer">
								<ul>
									<li><i class="icon-material-outline-directions-car"></i> Toyota, Yaris </li>
									<li><i class="icon-material-outline-settings"></i> 3 Remaining </li>
									<li><i class="icon-material-outline-access-time"></i>2 days ago</li>
								</ul>
							</div>
						</div>

						<!-- Bookmark -->
						<span class="list-apply-button radius-30 ripple-effect">Apply Now</span>
					</div>
				</a>


				<!-- Job Listing -->
				<a href="single-job-page.html" class="job-listing with-apply-button">

					<!-- Job Listing Details -->
					<div class="job-listing-details">

						<!-- Logo -->
						<div class="job-listing-company-logo">
							<img src="asset/images/company-logo-01.png" alt="">
						</div>

						<!-- Details -->
						<div class="job-listing-description">
							<h3 class="job-listing-title">Nyala Insurance</h3>

							<!-- Job Listing Footer -->
							<div class="job-listing-footer">
								<ul>
									<li><i class="icon-material-outline-directions-car"></i> Toyota, Yaris </li>
									<li><i class="icon-material-outline-settings"></i> 3 Remaining </li>
									<li><i class="icon-material-outline-access-time"></i>2 days ago</li>
								</ul>
							</div>
						</div>

						<!-- Bookmark -->
						<span class="list-apply-button radius-30 ripple-effect">Apply Now</span>
					</div>
				</a>


				<!-- Job Listing -->
				<a href="single-job-page.html" class="job-listing with-apply-button">

					<!-- Job Listing Details -->
					<div class="job-listing-details">

						<!-- Logo -->
						<div class="job-listing-company-logo">
							<img src="asset/images/company-logo-01.png" alt="">
						</div>

						<!-- Details -->
						<div class="job-listing-description">
							<h3 class="job-listing-title">Awash Insurance</h3>

							<!-- Job Listing Footer -->
							<div class="job-listing-footer">
								<ul>
									<li><i class="icon-material-outline-directions-car"></i> Toyota, Yaris </li>
									<li><i class="icon-material-outline-settings"></i> 3 Remaining </li>
									<li><i class="icon-material-outline-access-time"></i>2 days ago</li>
								</ul>
							</div>
						</div>

						<!-- Bookmark -->
						<span class="list-apply-button radius-30 ripple-effect" onclick="notify('Price Code Submitted Successfully')">Apply Now</span>
					</div>
				</a>


				<!-- Job Listing -->
				<a href="single-job-page.html" class="job-listing with-apply-button">

					<!-- Job Listing Details -->
					<div class="job-listing-details">

						<!-- Logo -->
						<div class="job-listing-company-logo">
							<img src="asset/images/company-logo-01.png" alt="">
						</div>

						<!-- Details -->
						<div class="job-listing-description">
							<h3 class="job-listing-title">Nib Insurance</h3>

							<!-- Job Listing Footer -->
							<div class="job-listing-footer">
								<ul>
									<li><i class="icon-material-outline-directions-car"></i> Toyota, Yaris </li>
									<li><i class="icon-material-outline-settings"></i> 3 Remaining </li>
									<li><i class="icon-material-outline-access-time"></i>2 days ago</li>
								</ul>
							</div>
						</div>

						<!-- Bookmark -->
						<span class="list-apply-button radius-30 ripple-effect">Apply Now</span>
					</div>
				</a>


				<!-- Job Listing -->
				<a href="single-job-page.html" class="job-listing with-apply-button">

					<!-- Job Listing Details -->
					<div class="job-listing-details">

						<!-- Logo -->
						<div class="job-listing-company-logo">
							<img src="asset/images/company-logo-01.png" alt="">
						</div>

						<!-- Details -->
						<div class="job-listing-description">
							<h3 class="job-listing-title">Nile Insurance</h3>

							<!-- Job Listing Footer -->
							<div class="job-listing-footer">
								<ul>
									<li><i class="icon-material-outline-directions-car"></i> Toyota, Yaris </li>
									<li><i class="icon-material-outline-settings"></i> 3 Remaining </li>
									<li><i class="icon-material-outline-access-time"></i>2 days ago</li>
								</ul>
							</div>
						</div>

						<!-- Bookmark -->
						<span class="list-apply-button radius-30 ripple-effect">Apply Now</span>
					</div>
				</a>

			</div>
		</div>
	</div>
</div>
</div>
</div>
<script>
		$(function () {
			$('[data-bs-toggle="popover"]').popover();
			$('[data-bs-toggle="tooltip"]').tooltip();
		})
	</script>
<!-- Include Proforma Media Component -->
@include('components.proforma-media', ['proforma' => $proforma'])

@endsection
