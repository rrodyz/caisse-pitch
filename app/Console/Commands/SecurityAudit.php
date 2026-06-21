<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class SecurityAudit extends Command
{
    protected $signature   = 'security:audit';
    protected $description = 'Audit de sécurité de l\'application POS';

    public function handle(): int
    {
        $this->info('=== AUDIT DE SÉCURITÉ POS ===');
        $this->newLine();

        $issues = 0;
        $issues += $this->checkEnvironment();
        $issues += $this->checkUsersWithoutRoles();
        $issues += $this->checkOpenCashSessions();
        $issues += $this->checkPermissions();
        $issues += $this->checkStockAlerts();
        $issues += $this->checkUnpaidCredits();

        $this->newLine();
        if ($issues === 0) {
            $this->info('✓ Aucun problème détecté.');
        } else {
            $this->warn("⚠ {$issues} point(s) à vérifier.");
        }

        return $issues > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    private function checkEnvironment(): int
    {
        $this->line('<fg=blue>── Environnement</>');
        $issues = 0;

        if (app()->environment('production') && config('app.debug')) {
            $this->error('  [CRITIQUE] APP_DEBUG=true en production. Désactiver immédiatement.');
            $issues++;
        } else {
            $this->line('  ✓ APP_DEBUG = ' . (config('app.debug') ? 'true (dev)' : 'false'));
        }

        if (app()->environment('production') && ! str_starts_with(config('app.url'), 'https')) {
            $this->warn('  [WARN] APP_URL n\'utilise pas HTTPS.');
            $issues++;
        } else {
            $this->line('  ✓ APP_URL = ' . config('app.url'));
        }

        if (config('session.secure') !== true && app()->environment('production')) {
            $this->warn('  [WARN] SESSION_SECURE_COOKIE non activé en production.');
            $issues++;
        } else {
            $this->line('  ✓ Session secure = ' . (config('session.secure') ? 'true' : 'false (dev)'));
        }

        $this->line('  ✓ Session lifetime = ' . config('session.lifetime') . ' min');
        $this->line('  ✓ Session same_site = ' . config('session.same_site'));
        $this->newLine();
        return $issues;
    }

    private function checkUsersWithoutRoles(): int
    {
        $this->line('<fg=blue>── Utilisateurs</>');
        $issues = 0;

        $total = DB::table('users')->count();
        $withRoles = DB::table('model_has_roles')->distinct()->count('model_id');
        $noRole = $total - $withRoles;

        $this->line("  Utilisateurs : {$total} total, {$withRoles} avec rôle");

        if ($noRole > 0) {
            $this->warn("  [WARN] {$noRole} utilisateur(s) sans rôle assigné.");
            $issues++;
        } else {
            $this->line('  ✓ Tous les utilisateurs ont un rôle.');
        }

        $inactive = DB::table('users')->where('is_active', false)->count();
        $this->line("  Comptes inactifs : {$inactive}");

        $this->newLine();
        return $issues;
    }

    private function checkOpenCashSessions(): int
    {
        $this->line('<fg=blue>── Sessions de caisse</>');
        $issues = 0;

        $open = DB::table('cash_sessions')
            ->join('cash_registers', 'cash_registers.id', '=', 'cash_sessions.cash_register_id')
            ->where('cash_sessions.status', 'open')
            ->selectRaw('cash_registers.name, cash_sessions.opened_at')
            ->get();

        if ($open->isEmpty()) {
            $this->line('  ✓ Aucune session ouverte.');
        } else {
            foreach ($open as $s) {
                $hours = now()->diffInHours($s->opened_at);
                if ($hours > 24) {
                    $this->warn("  [WARN] Session ouverte depuis {$hours}h : {$s->name}");
                    $issues++;
                } else {
                    $this->line("  ✓ Session ouverte ({$hours}h) : {$s->name}");
                }
            }
        }

        $this->newLine();
        return $issues;
    }

    private function checkPermissions(): int
    {
        $this->line('<fg=blue>── Permissions</>');

        $permCount  = DB::table('permissions')->count();
        $rolesCount = DB::table('roles')->count();
        $this->line("  {$permCount} permissions, {$rolesCount} rôles définis.");
        $this->line('  ✓ Spatie Permission actif.');
        $this->newLine();
        return 0;
    }

    private function checkStockAlerts(): int
    {
        $this->line('<fg=blue>── Stock</>');
        $issues = 0;

        $alerts = DB::table('products')
            ->where('is_active', true)
            ->whereRaw('stock_quantity <= min_stock')
            ->count();

        if ($alerts > 0) {
            $this->warn("  [INFO] {$alerts} produit(s) en alerte stock (bas ou rupture).");
        } else {
            $this->line('  ✓ Aucune alerte stock.');
        }

        $this->newLine();
        return $issues;
    }

    private function checkUnpaidCredits(): int
    {
        $this->line('<fg=blue>── Crédits clients</>');

        $stats = DB::table('customers')
            ->where('is_active', true)
            ->where('current_credit', '>', 0)
            ->selectRaw('COUNT(*) as cnt, SUM(current_credit) as total')
            ->first();

        if ($stats && $stats->cnt > 0) {
            $this->warn("  [INFO] {$stats->cnt} client(s) avec encours : " . number_format($stats->total, 0) . ' FCFA.');
        } else {
            $this->line('  ✓ Aucun encours crédit.');
        }

        $this->newLine();
        return 0;
    }
}
