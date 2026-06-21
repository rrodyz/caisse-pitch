<?php

namespace Modules\Users\app\Http\Livewire;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityLogViewer extends Component
{
    use WithPagination;

    public string $search     = '';
    public string $filterAction = '';
    public string $filterUser   = '';
    public string $filterModel  = '';
    public string $dateFrom     = '';
    public string $dateTo       = '';

    public function updatedSearch(): void    { $this->resetPage(); }
    public function updatedFilterAction(): void { $this->resetPage(); }
    public function updatedFilterUser(): void   { $this->resetPage(); }
    public function updatedFilterModel(): void  { $this->resetPage(); }

    public function render()
    {
        $this->authorize('view-activity-logs');

        $query = ActivityLog::with('user')
            ->when($this->filterAction, fn($q) => $q->where('action', $this->filterAction))
            ->when($this->filterUser,   fn($q) => $q->where('user_id', $this->filterUser))
            ->when($this->filterModel,  fn($q) => $q->where('subject_type', 'like', "%{$this->filterModel}%"))
            ->when($this->dateFrom,     fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,       fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('description', 'like', "%{$this->search}%")
                        ->orWhere('subject_type', 'like', "%{$this->search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(30);

        $users  = DB::table('users')
            ->select('id', DB::raw("CONCAT(first_name, ' ', last_name) as name"))
            ->orderBy('first_name')
            ->get();

        $models = ActivityLog::select('subject_type')
            ->whereNotNull('subject_type')
            ->distinct()
            ->pluck('subject_type')
            ->map(fn($t) => ['value' => $t, 'label' => class_basename($t)])
            ->sortBy('label')
            ->values();

        return view('users::livewire.activity-log-viewer', compact('query', 'users', 'models'));
    }
}
