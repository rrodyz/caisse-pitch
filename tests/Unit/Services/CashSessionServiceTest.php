<?php

namespace Tests\Unit\Services;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CashRegisters\app\Enums\CashSessionStatus;
use Modules\CashRegisters\app\Models\CashRegister;
use Modules\CashRegisters\app\Services\CashSessionService;
use RuntimeException;
use Tests\TestCase;

class CashSessionServiceTest extends TestCase
{
    use RefreshDatabase;

    private CashSessionService $service;
    private User $user;
    private CashRegister $register;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service  = new CashSessionService();
        $this->user     = User::factory()->create();
        $this->register = CashRegister::create(['name' => 'Caisse 1', 'is_active' => true]);
        $this->actingAs($this->user);
    }

    public function test_open_creates_session_with_correct_data(): void
    {
        $session = $this->service->open($this->register->id, 50000, 'Ouverture test');

        $this->assertSame(CashSessionStatus::Open, $session->status);
        $this->assertEquals(50000, $session->opening_amount);
        $this->assertSame($this->user->id, $session->opened_by);
        $this->assertNotNull($session->opened_at);
        $this->assertNull($session->closed_at);

        $this->assertDatabaseHas('cash_sessions', [
            'cash_register_id' => $this->register->id,
            'status'           => 'open',
            'opening_amount'   => 50000,
        ]);
    }

    public function test_double_open_throws_runtime_exception(): void
    {
        $this->service->open($this->register->id, 50000);

        $this->expectException(RuntimeException::class);
        $this->service->open($this->register->id, 30000);
    }

    public function test_close_calculates_expected_amount_and_gap(): void
    {
        $session = $this->service->open($this->register->id, 50000);

        $closed = $this->service->close($session, 52000, 'Clôture test');

        $this->assertSame(CashSessionStatus::Closed, $closed->status);
        $this->assertEquals(50000, $closed->expected_amount);  // no cash sales → expected = opening
        $this->assertEquals(52000, $closed->closing_amount);
        $this->assertEquals(2000, $closed->gap);               // surplus
        $this->assertNotNull($closed->closed_at);
        $this->assertSame($this->user->id, $closed->closed_by);
    }

    public function test_close_records_negative_gap_when_short(): void
    {
        $session = $this->service->open($this->register->id, 50000);

        $closed = $this->service->close($session, 47000);

        $this->assertEquals(-3000, $closed->gap);
    }

    public function test_close_already_closed_session_throws(): void
    {
        $session = $this->service->open($this->register->id, 50000);
        $closed  = $this->service->close($session, 50000);

        $this->expectException(RuntimeException::class);
        $this->service->close($closed, 50000);
    }
}
