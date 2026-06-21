<?php

namespace Modules\Settings\app\Http\Livewire;

use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Settings\app\Models\Setting;

class SettingsForm extends Component
{
    use WithFileUploads;

    #[Rule('required|string|max:255')]
    public string $establishment_name = '';

    #[Rule('nullable|string|max:500')]
    public string $address = '';

    #[Rule('nullable|string|max:20')]
    public string $phone = '';

    #[Rule('nullable|email|max:255')]
    public string $email = '';

    #[Rule('required|string|max:10')]
    public string $currency = 'FCFA';

    #[Rule('required|string|size:3')]
    public string $currency_code = 'XOF';

    #[Rule('nullable|string|max:500')]
    public string $ticket_message = '';

    #[Rule('required|numeric|min:0|max:100')]
    public float $tax_rate = 0;

    #[Rule('required|string|max:20')]
    public string $ticket_number_prefix = 'TKT';

    #[Rule('required|integer|min:4|max:10')]
    public int $ticket_number_padding = 6;

    #[Rule('required|integer|min:0')]
    public int $stock_alert_threshold = 5;

    #[Rule('required|numeric|min:0|max:100')]
    public float $max_discount_percent = 10;

    #[Rule('nullable|numeric|min:0')]
    public ?float $supervisor_approval_threshold = null;

    public $logo = null;

    public function mount(): void
    {
        $settings = Setting::current();

        $this->establishment_name           = $settings->establishment_name;
        $this->address                      = $settings->address ?? '';
        $this->phone                        = $settings->phone ?? '';
        $this->email                        = $settings->email ?? '';
        $this->currency                     = $settings->currency;
        $this->currency_code                = $settings->currency_code;
        $this->ticket_message               = $settings->ticket_message ?? '';
        $this->tax_rate                     = $settings->tax_rate;
        $this->ticket_number_prefix         = $settings->ticket_number_prefix;
        $this->ticket_number_padding        = $settings->ticket_number_padding;
        $this->stock_alert_threshold        = $settings->stock_alert_threshold;
        $this->max_discount_percent         = $settings->max_discount_percent;
        $this->supervisor_approval_threshold = $settings->supervisor_approval_threshold;
    }

    public function save(): void
    {
        $this->authorize('edit-settings');

        $validated = $this->validate();

        $settings = Setting::current();
        $data     = array_filter($validated, fn ($v) => $v !== null && $v !== '');

        if ($this->logo) {
            $this->validate(['logo' => 'image|max:1024']);
            $data['logo'] = $this->logo->store('logos', 'public');
        }

        $settings->update($data);

        session()->flash('success', 'Paramètres enregistrés.');
    }

    public function render()
    {
        return view('settings::settings-form');
    }
}
