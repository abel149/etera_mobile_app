<?php

namespace Tests\Feature;

use App\Models\{
    Cost,
    Commission,
    PaidUser,
    Proforma,
    ProformaApplication,
    ProformaInvoice,
    ProformaSelection,
    Transaction,
    User
};
use App\Services\ProformaVerificationService;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ProformaVerificationServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_generates_insurance_invoice_and_commissions()
    {
        // Arrange: base cost configuration
        $cost = Cost::create([
            'etera_chereta_cost' => 500,
            '1_proforma_cost'    => 100,
            '2_proforma_cost'    => 180,
            '3_proforma_cost'    => 250,
            '4_proforma_cost'    => 300,
            'insurance_proforma' => 400,
            'insured_cost'       => 0,
        ]);

        // Commission rates for shop, garage and operator
        $commission = Commission::create([
            'shopPay'     => 50,
            'garagePay'   => 75,
            'insurancePay'=> 0,
            'operatorPay' => 25,
        ]);

        // Users: acting admin (creator), poster (insurance), shop, garage and operator
        $admin    = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $poster   = User::factory()->create(['role' => User::ROLE_INSURANCE]);
        $shop     = User::factory()->create(['role' => User::ROLE_SHOP]);
        $garage   = User::factory()->create(['role' => User::ROLE_GARAGE]);
        $operator = User::factory()->create([
            'role'                => User::ROLE_OPERATOR,
            'commission_per_file' => 25,
        ]);

        // Authenticate as admin so Auth::id() is available for created_by
        $this->actingAs($admin);

        // Insurance-type proforma (3 shops, 3 garages)
        $proforma = Proforma::create([
            'poster_id'                 => $poster->id,
            'file_number'               => 'INS-TEST-001',
            'customer_name'             => 'Test Customer',
            'customer_phone_number'     => '+251900000000',
            'car_brand_id'              => 1,
            'model'                     => 'Test Model',
            'year'                      => '2024',
            'required_number_of_shops'  => 3,
            'required_number_of_garages'=> 3,
            'license_plate_number'      => 'ABC-123',
            'chassis_number'            => 'CHASSIS-TEST',
            'insured'                   => false,
            'status'                    => 'pending',
        ]);

        // Applications from shop and garage
        $shopApplication = ProformaApplication::create([
            'proforma_id'   => $proforma->id,
            'application_by'=> $shop->id,
            'from'          => 'shop',
            'amount'        => 1000,
        ]);

        $garageApplication = ProformaApplication::create([
            'proforma_id'   => $proforma->id,
            'application_by'=> $garage->id,
            'from'          => 'garage',
            'amount'        => 2000,
        ]);

        // Active operator selection for this proforma
        $selection = ProformaSelection::create([
            'proforma_id' => $proforma->id,
            'employee_id' => $operator->id,
            'active'      => true,
        ]);

        // Mock WalletService so we don't depend on wallet_balance column or real transactions
        $walletMock = Mockery::mock(WalletService::class);
        $this->app->instance(WalletService::class, $walletMock);

        // Expect two wallet transactions:
        // 1) Poster is charged for invoice
        // 2) Operator receives commission
        $walletMock->shouldReceive('processTransaction')
            ->twice()
            ->andReturn(new Transaction());

        // Act
        app(ProformaVerificationService::class)->verify($proforma->fresh());

        // Assert: invoice row created for this proforma as insurance type
        $this->assertDatabaseHas('proforma_invoices', [
            'proforma_id'     => $proforma->id,
            'type'            => 'insurance',
            'requested_count' => 6,
            'total_amount'    => (float) $cost->insurance_proforma,
        ]);

        // Assert: paid users (commission records) created for shop and garage
        $this->assertDatabaseHas('paid_users', [
            'user_id'     => $shop->id,
            'proforma_id' => $proforma->id,
            'amount'      => $commission->shopPay,
        ]);

        $this->assertDatabaseHas('paid_users', [
            'user_id'     => $garage->id,
            'proforma_id' => $proforma->id,
            'amount'      => $commission->garagePay,
        ]);

        // Assert: operator commission recorded on selection
        $this->assertDatabaseHas('proforma_selections', [
            'id'                => $selection->id,
            'commission_earned' => $commission->operatorPay,
        ]);

        // Assert: proforma marked as completed and verified
        $this->assertEquals('completed', $proforma->fresh()->status);
        $this->assertTrue((bool) $proforma->fresh()->verified);
    }
}

