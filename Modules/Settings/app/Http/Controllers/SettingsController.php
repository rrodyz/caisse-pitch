<?php

namespace Modules\Settings\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:view-settings']);
    }

    public function index()
    {
        return view('settings::index');
    }
}
