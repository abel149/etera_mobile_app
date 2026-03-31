<?php
namespace App\Livewire;

use App\Models\Brand;
use App\Models\User;
use App\Models\CarPart;
use Livewire\Component;
use App\Models\Proforma;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class ListOfProformasFromInsurances extends Component
{
    use WithPagination;
    
    public $chasisNumber;
    public $fileNumber;
    public $licenseNumber;
    public $sortBy = 'desc';
    public $selectedInsurances = [];
    public $selectedBrands = [];
    public $selectedGrades = [];
    public $selectedComponents = [];
    public $others = false;

    protected $queryString = [
        'selectedGrades' => ['except' => ''],
        'selectedComponents' => ['except' => ''],
        'selectedInsurances' => ['except' => ''],
        'selectedBrands' => ['except' => ''],
        'licenseNumber' => ['except' => ''],
        'chasisNumber' => ['except' => ''],
        'fileNumber' => ['except' => ''],
        'sortBy' => ['except' => 'desc'],
    ];

    protected $paginationTheme = 'bootstrap';

    /**
     * Resets pagination to page 1 when any filter property is updated.
     */
    public function updated($propertyName)
    {
        // Define all properties that should trigger a page reset
        $filterProperties = [
            'selectedInsurances',
            'selectedBrands',
            'selectedGrades',
            'selectedComponents',
            'others',
            'chasisNumber',
            'fileNumber',
            'licenseNumber',
        ];

        // Only reset page if one of the filter properties changed
        if (in_array($propertyName, $filterProperties)) {
            $this->resetPage(); 
        }
    }

    public function render()
    {
        $query = Proforma::query()
            ->with(['insurance', 'parts.brand']);

        // Apply filter by insurance user IDs
        if (!empty($this->selectedInsurances)) {
            $query->whereIn('insurance_id', $this->selectedInsurances);
        }

        // Apply filter by selected brands (checking nested relationship)
        if (!empty($this->selectedBrands)) {
            $query->whereHas('parts.brand', function ($q) {
                $q->whereIn('brands.id', $this->selectedBrands);
            });
        }

        // Apply filters by chassis, file, and license numbers
        if (!empty($this->chasisNumber)) {
            $query->where('chasis_number', 'like', "%{$this->chasisNumber}%");
        }
        if (!empty($this->fileNumber)) {
            $query->where('file_number', 'like', "%{$this->fileNumber}%\");
        }
        if (!empty($this->licenseNumber)) {
            $query->where('license_plate_number', 'like', "%{$this->licenseNumber}%\");
        }

        // Apply filter by selected grades
        // Filter out empty string and null values
        $selectedGrades = array_filter($this->selectedGrades, function($value) {
            return !empty($value) && $value !== '';
        });
        
        if (!empty($selectedGrades)) {
            $query->whereHas('parts', function ($q) use ($selectedGrades) {
                $q->whereIn('grade', $selectedGrades);
            });
        }
        
        // Apply filter by selected components/conditions (assuming 'condition' is the field)
        $selectedComponents = array_filter($this->selectedComponents, function($value) {
            return !empty($value) && $value !== '';
        });

        if (!empty($selectedComponents)) {
            $query->whereHas('parts', function ($q) use ($selectedComponents) {
                $q->whereIn('condition', $selectedComponents); // Assuming 'condition' is the correct field
            });
        }
        
        // Apply 'others' filter if checked
        if ($this->others) {
            // Adjust this logic based on what 'others' means in your context
            // Example: Proformas with a specific status or flag.
            // $query->where('is_other_flag', true); 
        }


        // Pagination and Sorting
        // Remove any existing orderBy before applying our sort
        $proformas = $query->reorder()->orderBy('created_at', $this->sortBy)->paginate(10);

        // Fetch support data for filters (outside the query scope)
        $insurances = User::where('role', 'insurance')->get();
        $brands = Brand::all();
        $grades = CarPart::join('proforma_part', 'car_parts.id', '=', 'proforma_part.car_part_id')
            ->select('proforma_part.grade')
            ->distinct()
            ->get();
        $components = DB::table('proforma_part')
            ->select('condition')
            ->distinct()
            ->get();


        // Return the view with all necessary data
        return view('livewire.list-of-proformas-from-insurances', [
            'proformas' => $proformas,
            'insurances' => $insurances,
            'brands' => $brands,
            'grades' => $grades,
            'components' => $components,
        ]);
    }
}
