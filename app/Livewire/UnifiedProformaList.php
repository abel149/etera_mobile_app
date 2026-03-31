<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\User;
use App\Models\CarPart;
use App\Models\Proforma;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class UnifiedProformaList extends Component
{
    use WithPagination;
    
    public $chasisNumber;
    public $fileNumber;
    public $licenseNumber;
    public $sortBy = 'desc';
    public $selectedInsurances = [];
    public $selectedBrands = [];
    public $selectedGrades = [];
    public $selectedComponents = ['Body Parts'];
    public $proformaType = 'both'; // 'both', 'insurance', 'others'
    
    protected $queryString = ['selectedGrades', 'selectedComponents', 'licenseNumber', 'proformaType'];
    
    protected $paginationTheme = 'bootstrap';
    
    public function updated()
    {
        // This will trigger a re-render whenever any of the properties change
        $this->resetPage();
    }
    
    public function render()
    {
        // Start the query based on proforma type
        if ($this->proformaType === 'insurance') {
            $query = Proforma::fromInsurances()->where('status', 'published');
        } elseif ($this->proformaType === 'others') {
            $query = Proforma::fromOthers()->where('status', 'published');
        } else {
            // Both - combine insurance and others
            $query = Proforma::where('status', 'published')
                ->where(function($q) {
                    $q->whereHas('poster', function($posterQuery) {
                        $posterQuery->where('role', 'insurance');
                    })
                    ->orWhereHas('poster', function($posterQuery) {
                        $posterQuery->whereIn('role', ['business_owner', 'garage', 'shop']);
                    });
                });
        }
        
        // Part filtering removed - all requested parts must be displayed
        
        // Apply filter by selected insurances
        if (!empty($this->selectedInsurances)) {
            $query->whereIn('poster_id', $this->selectedInsurances);
        }
        
        // Apply filter by selected brands
        if (!empty($this->selectedBrands)) {
            $query->whereIn('car_brand_id', $this->selectedBrands);
        }
        
        // Apply filter by chassis number
        if (!empty($this->chasisNumber)) {
            $query->where('chassis_number', 'like', "%{$this->chasisNumber}%");
        }
        
        // Apply filter by file number
        if (!empty($this->fileNumber)) {
            $query->where('file_number', 'like', "%{$this->fileNumber}%");
        }
        
        // Apply filter by license number
        if (!empty($this->licenseNumber)) {
            $query->where('license_plate_number', 'like', "%{$this->licenseNumber}%");
        }
        
        // Pagination and Sorting
        $proformas = $query->orderBy('created_at', $this->sortBy)->paginate(10);
        
        // Return the view with all necessary data
        return view('livewire.unified-proforma-list', [
            'proformas' => $proformas,
            'insurances' => User::where('role', 'insurance')->get(),
            'brands' => Brand::all(),
            'grades' => CarPart::join('proforma_part', 'car_parts.id', '=', 'proforma_part.car_part_id')
                ->select('proforma_part.grade')
                ->distinct()
                ->get(),
            'components' => DB::table('proforma_part')
                ->select('condition')
                ->whereNotNull('condition')
                ->where('condition', '!=', '')
                ->distinct()
                ->get(),
            'allParts' => CarPart::select('name')->distinct()->orderBy('name')->get(),
        ]);
    }
}
