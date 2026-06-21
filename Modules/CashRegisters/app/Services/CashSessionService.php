<?php

namespace Modules\CashRegisters\app\Services;

use Illuminate\Support\Facades\Auth;
use Modules\CashRegisters\app\Enums\CashSessionStatus;
use Modules\CashRegisters\app\Models\CashRegister;
use Modules\CashRegisters\app\Models\CashSession;
use RuntimeException;

class CashSessionService
{
    /**
     * Ouvre une nouvelle session de caisse.
     *
     * @throws RuntimeException si la caisse a déjà une session ouverte
     */
    public function open(int $registerId, float $openingAmount, string $notes = ''): CashSession
    {
        $register = CashRegister::findOrFail($registerId);

        if ($register->hasOpenSession()) {
            throw new RuntimeException("La caisse « {$register->name} » a déjà une session ouverte.");
        }

        return CashSession::create([
            'cash_register_id' => $registerId,
            'opened_by'        => Auth::id(),
            'status'           => CashSessionStatus::Open,
            'opening_amount'   => $openingAmount,
            'notes_opening'    => $notes ?: null,
            'opened_at'        => now(),
        ]);
    }

    /**
     * Clôture une session.
     * expected_amount = opening + total espèces encaissées (calculé via ventes liées).
     *
     * @throws RuntimeException si la session est déjà clôturée
     */
    public function close(CashSession $session, float $closingAmount, string $notes = ''): CashSession
    {
        if (! $session->isOpen()) {
            throw new RuntimeException('Cette session est déjà clôturée.');
        }

        // expected = opening + sum of cash sales for this session
        // Sales module linkage done in Step 11; for now expected = opening_amount
        $cashSales      = $this->getCashSalesTotal($session);
        $expectedAmount = $session->opening_amount + $cashSales;
        $gap            = $closingAmount - $expectedAmount;

        $session->update([
            'status'          => CashSessionStatus::Closed,
            'closed_by'       => Auth::id(),
            'closing_amount'  => $closingAmount,
            'expected_amount' => $expectedAmount,
            'gap'             => $gap,
            'notes_closing'   => $notes ?: null,
            'closed_at'       => now(),
        ]);

        return $session->fresh();
    }

    /**
     * Session actuellement ouverte pour un utilisateur (première session ouverte
     * sur une caisse active — à affiner si multi-caisse par user).
     */
    public function currentSession(): ?CashSession
    {
        return CashSession::open()
            ->whereHas('cashRegister', fn($q) => $q->where('is_active', true))
            ->latest('opened_at')
            ->first();
    }

    /**
     * Total espèces encaissées sur cette session (à compléter à l'étape 11).
     */
    private function getCashSalesTotal(CashSession $session): float
    {
        // Placeholder — Sales module will add sales linking in Step 11
        // SELECT SUM(total_amount) FROM sales WHERE cash_session_id = ? AND payment_mode = 'cash'
        if (class_exists(\Modules\Sales\app\Models\Sale::class)) {
            return (float) \Modules\Sales\app\Models\Sale::where('cash_session_id', $session->id)
                ->where('payment_mode', 'cash')
                ->where('status', 'completed')
                ->sum('total_amount');
        }
        return 0.0;
    }
}
