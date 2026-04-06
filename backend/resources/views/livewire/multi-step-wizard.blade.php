<div>
  <div id="stepper1" class="bs-stepper"></div>
  <div id="stepper2" class="bs-stepper"></div>

  <h3 class="">Request Proforma</h3>

  
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div id="stepper3" class="bs-stepper gap-4 vertical">
                    <div class="border-right pr-2" role="tablist">
                        <div class="step" data-target="#test-vl-1">
                            <div class="step-trigger" role="tab" id="stepper3trigger1" aria-controls="test-vl-1">
                                <div class="bs-stepper-circle">1</div>
                                <div>
                                    <h5 class="mb-0 steper-title">Basic Information</h5>
                                    <p class="mb-0 steper-sub-title">1st Step</p>
                                </div>
                            </div>
                        </div>
                        <div class="step" data-target="#test-vl-2">
                            <div class="step-trigger" role="tab" id="stepper3trigger2" aria-controls="test-vl-2">
                                <div class="bs-stepper-circle">2</div>
                                <div>
                                    <h5 class="mb-0 steper-title">Car Specification</h5>
                                    <p class="mb-0 steper-sub-title">2nd Step</p>
                                </div>
                            </div>
                        </div>
                        <div class="step" data-target="#test-vl-3">
                            <div class="step-trigger" role="tab" id="stepper3trigger3" aria-controls="test-vl-3">
                                <div class="bs-stepper-circle">3</div>
                                <div>
                                    <h5 class="mb-0 steper-title">Required Spare Parts</h5>
                                    <p class="mb-0 steper-sub-title">3rd Step</p>
                                </div>
                            </div>
                        </div>
                        <div class="step" data-target="#test-vl-4">
                            <div class="step-trigger" role="tab" id="stepper3trigger4" aria-controls="test-vl-4">
                                <div class="bs-stepper-circle">4</div>
                                <div>
                                    <h5 class="mb-0 steper-title">Information for Garage</h5>
                                    <p class="mb-0 steper-sub-title">4th Step</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bs-stepper-content">
                        <form action="{{ route('insurance.create-file') }}" method="POST">
                            @csrf
                            <livewire:step-one-form />
                            <livewire:step-two-form />
                            <livewire:step-three-form />
                            <livewire:step-four-form />

                            <div id="repeater" class="mt-4">
                                <!-- Repeater items will be appended here -->
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const partsData = @json($parts); // Convert PHP array to JavaScript

        // Add new repeater item
        document.getElementById("add-repeater").addEventListener("click", function () {
            const repeater = document.getElementById("repeater");
            if (!repeater) return;

            let repeaterItem = document.createElement("div");
            repeaterItem.classList.add("repeater-item", "pt-2");

            let options = partsData.map(part => `<option value="${part.id}">${part.name}</option>`).join("");

            repeaterItem.innerHTML = `
                <div class="item-content row g-3">
                    <div class="col-12 col-lg-2">
                        <select class="form-select" name="parts_id[]" aria-label="Select Part">
                            ${options}
                        </select>
                    </div>
                
 <div class="col-12 col-lg-2">
                                                        <input name="parts[number][]" type="text" class="form-control" id="inputEmail1" placeholder="Part number" data-skip-name="true" data-name="email">
                                                    </div>
                                                    <div class="col-12 col-lg-2">
                                                        <input name="parts[grade][]" type="text" class="form-control" id="inputName1" placeholder="Grade" data-name="name">
                                                    </div>
                                                    <div class="col-12 col-lg-2">
                                                        <input name="parts[country][]" type="text" class="form-control" id="inputName1" placeholder="Country" data-name="name">
                                                    </div>
                                                    <div class="col-12 col-lg-1">
                                                        <input name="parts[qty][]" type="text" class="form-control" id="inputName1" placeholder="Qty" data-name="name">
                                                    </div>

                    <div class="col-12 col-lg-1">
                        <button type="button" class="remove-repeater btn btn-danger"><i class="bx bx-trash"></i></button>
                    </div>
                </div>
            `;

            repeater.appendChild(repeaterItem);
        });

        // Remove repeater item using event delegation
        document.getElementById("repeater").addEventListener("click", function (event) {
            if (event.target.closest(".remove-repeater")) {
                event.target.closest(".repeater-item").remove();
            }
        });
    });
</script>
</div>
