<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use App\Models\Brand; 
use App\Models\BankAccount; 
use App\Models\UserReview;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // =====================
    // Role Constants
    // =====================
    public const ROLE_SUPERADMIN     = 'superadmin';
    public const ROLE_ADMIN          = 'admin';
    public const ROLE_MANAGER        = 'manager';
    public const ROLE_OPERATOR       = 'operator';
    public const ROLE_BUSINESS_OWNER = 'business_owner';
    public const ROLE_INSURANCE      = 'insurance';
    public const ROLE_SHOP           = 'shop';
    public const ROLE_GARAGE         = 'garage';
    public const ROLE_EMPLOYEE       = 'employee';
    public const ROLE_MARKETER       = 'marketer';
    public const ROLE_INDIVIDUAL     = 'individual';
    public const ROLE_Others         = 'others';
    // =====================
    // Boot Method (Auto-Generate store_id for Shop & Garage)
    // =====================
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
    if (in_array($user->role, [self::ROLE_SHOP, self::ROLE_GARAGE]) && !$user->store_id) {

        $prefix = $user->role === self::ROLE_GARAGE ? 'EG-' : 'ES-';
        $prefixLength = strlen($prefix);

        // SAFER: lock the table row to prevent duplicates during high traffic
        $latestStoreId = self::where('role', $user->role)
            ->whereNotNull('store_id')
            ->where('store_id', 'LIKE', $prefix . '%')
            ->lockForUpdate()   // prevents two users generating the same ID at same time
            ->orderBy('id', 'desc')
            ->value('store_id');

        if ($latestStoreId) {
            $numberPart = intval(substr($latestStoreId, $prefixLength)) + 1;
        } else {
            $numberPart = 1;
        }

        $user->store_id = $prefix . str_pad($numberPart, 4, '0', STR_PAD_LEFT);
    }
});

    }

    // =====================
    // Fillable & Hidden
    // =====================
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_new',
        'store_id',
        'approved',
        'registered_by',
        'license_expire_date',
        'phone_number',
        'location',
        'tin_number',
        'business_license_number',
        'license_image',
        'stamp_image',
        'latitude',
        'longitude',
        'balance',
        'billing_plan',
        'billing_cycle_start',
        'file_quota',
        'commission_per_file',
        'employee_type',
        'telegram_chat_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // =====================
    // Casts (Universally Compatible Syntax)
    // =====================
    protected $casts = [
        'email_verified_at'   => 'datetime',
        'license_expire_date' => 'datetime',
        'password'            => 'hashed', // Requires Laravel 10+. Use 'string' for older versions.
        'is_new'              => 'boolean',
        'approved'            => 'boolean', // ✅ CRITICAL: Ensures 0/1 becomes false/true
        'file_quota'          => 'integer',
        'commission_per_file'  => 'decimal:2',
        'billing_cycle_start'  => 'date',
    ];

    // =====================
    // Role Checking Methods
    // =====================
    public function isSuperAdmin()         { return $this->role === self::ROLE_SUPERADMIN; }
    public function isAdmin()              { return $this->role === self::ROLE_ADMIN; }
    public function isAdminOrSuperAdmin()  { return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPERADMIN]); }
    public function isManager()            { return $this->role === self::ROLE_MANAGER; }
    public function isOperator()           { return $this->role === self::ROLE_OPERATOR; }
    public function isInsurance()          { return $this->role === self::ROLE_INSURANCE; }
    public function isBusinessOwner()      { return $this->role === self::ROLE_BUSINESS_OWNER; }
    public function isGarage()             { return $this->role === self::ROLE_GARAGE; }
    public function isShop()               { return $this->role === self::ROLE_SHOP; }
    public function isEmployee()           { return $this->role === self::ROLE_EMPLOYEE; }
    public function isMarketer()           { return $this->role === self::ROLE_MARKETER; }
    public function isIndividual()         { return $this->role === self::ROLE_INDIVIDUAL; }
    public function isOthers()             { return $this->role === self::ROLE_Others; }

    // =====================
    // Permission Methods
    // =====================
    public function canDeleteUsers()  { return $this->isSuperAdmin(); }
    public function canEditUsers()    { return $this->isAdminOrSuperAdmin(); }
    public function canViewUsers()    { return $this->isAdminOrSuperAdmin(); }
    public function canApproveUsers() { return $this->isAdminOrSuperAdmin(); }

    // =====================
    // Relationships
    // =====================
    public function level()              { return $this->belongsTo(Level::class); }
    public function brands()             { return $this->belongsToMany(Brand::class, 'brand_users'); }
    public function proformas()          { return $this->hasMany(Proforma::class, 'poster_id'); }
    public function proformaSelections() { return $this->hasMany(ProformaSelection::class, 'employee_id'); }
    public function withdrawalRequests() { return $this->hasMany(WithdrawalRequest::class, 'from'); }
    public function partners()           { return $this->hasMany(Partner::class, 'insurance_id'); }
    public function myRegistrations()    { return $this->hasMany(User::class, 'registered_by'); }
    public function billingStatements()  { return $this->hasMany(BillingStatement::class, 'owner_id')->latest(); }
    public function inboxes()            { return $this->hasMany(ProformaInbox::class, 'user_id'); }
    public function myInbox()            { return $this->hasMany(Inbox::class, 'user_id')->latest(); }
    
    // Manager-Operator Relationships
    public function managedOperators()   { return $this->hasMany(EmployeeManager::class, 'manager_id'); }
    public function myManager()          { return $this->hasOne(EmployeeManager::class, 'employee_id'); }
    public function processedFiles()     { return $this->hasMany(PaidUser::class, 'processed_by'); }
    public function reviewedFiles()      { return $this->hasMany(PaidUser::class, 'reviewed_by'); }

    // =====================
    // Partner Helpers
    // =====================
    public function sparePartPartners()
    {
        if ($this->role !== self::ROLE_INSURANCE) return collect();
        return Partner::where('insurance_id', $this->id)
            ->whereHas('partner', fn($q) => $q->where('role', self::ROLE_SHOP))
            ->get()->pluck('partner');
    }

    public function garagePartners()
    {
        if ($this->role !== self::ROLE_INSURANCE) return collect();
        return Partner::where('insurance_id', $this->id)
            ->whereHas('partner', fn($q) => $q->where('role', self::ROLE_GARAGE))
            ->get()->pluck('partner');
    }

    // =====================
    // Approval Helpers
    // =====================
    public function isPendingApproval() { return $this->approved === false || $this->approved === null; }
    public function isApproved()        { return $this->approved === true; }

    public function getApprovalStatusText()
    {
        if ($this->approved === null) return 'Pending';
        return $this->approved ? 'Approved' : 'Rejected';
    }

    public function getApprovalStatusBadge()
    {
        if ($this->approved === null) return '<span class="badge bg-warning">Pending</span>';
        return $this->approved 
            ? '<span class="badge bg-success">Approved</span>' 
            : '<span class="badge bg-danger">Not Approved</span>';
    }

    // =====================
    // Proforma / Inbox Helpers
    // =====================
    public function isInMyInbox($proformaId)
    {
        return Inbox::where('user_id', $this->id)
            ->where('proforma_id', $proformaId)
            ->exists();
    }

    public function getInboxCount() { return $this->myInbox()->count(); }

    public function getReturnedFromAdminCount(): int
    {
        return Proforma::where('poster_id', $this->id)->where('status', 'returned')->count();
    }

    // =====================
    // Messages
    // =====================
    public function unReadMessages() { return 0; }
    
    public function getReceivedProformasCount()
    {
        return \App\Models\Proforma::where('poster_id', $this->id)
            ->where('status', 'completed')
            ->where('verified', true)
            ->count();
    }

    public function markReceivedProformasAsViewed()
    {
        // No-op: is_new column does not exist on proformas table
    }
    
    public function reviews()
    {
        return $this->hasMany(UserReview::class, 'user_id');
    }
    
    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class);
    }

    // =====================
    // Operator/Manager Helpers
    // =====================
    
    /**
     * Check if user can process files (is an operator with available quota)
     */
    public function canProcessFiles()
    {
        if (!$this->isOperator()) {
            return false;
        }
        
        return $this->getAvailableFileQuota() > 0;
    }
    
    /**
     * Check if user can review files (is a manager)
     */
    public function canReviewFiles()
    {
        return $this->isManager();
    }
    
    /**
     * Get available file quota for operator
     */
    public function getAvailableFileQuota()
    {
        if (!$this->isOperator()) {
            return 0;
        }
        
        $totalQuota = $this->file_quota ?? 0;
        $usedQuota = $this->proformaSelections()->where('active', true)->count();
        
        return max(0, $totalQuota - $usedQuota);
    }
    
    /**
     * Get total earned commissions for operator
     */
    public function getEarnedCommissions()
    {
        if (!$this->isOperator()) {
            return 0;
        }
        
        return $this->proformaSelections()->sum('commission_earned');
    }
    
    /**
     * Get pending commissions (not yet reviewed/approved)
     */
    public function getPendingCommissions()
    {
        if (!$this->isOperator()) {
            return 0;
        }
        
        return $this->processedFiles()
            ->where('status', 'pending_review')
            ->sum('amount');
    }
    
    /**
     * Get approved commissions (ready for payment)
     */
    public function getApprovedCommissions()
    {
        if (!$this->isOperator()) {
            return 0;
        }
        
        return $this->processedFiles()
            ->whereIn('status', ['approved', 'paid'])
            ->sum('amount');
    }
    
    /**
     * Get all operators managed by this manager
     */
    public function getOperators()
    {
        if (!$this->isManager()) {
            return collect();
        }
        
        return $this->managedOperators()->with('employee')->get()->pluck('employee');
    }
}
