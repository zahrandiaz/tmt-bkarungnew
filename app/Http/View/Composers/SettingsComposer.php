<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsComposer
{
    /**
     * The settings repository implementation.
     *
     * @var array
     */
    protected $settings;

    /**
     * Create a new settings composer.
     *
     * @return void
     */
    public function __construct()
    {
        // Ambil pengaturan dari cache jika ada, jika tidak, ambil dari DB dan cache selamanya.
        $this->settings = Cache::rememberForever('app_settings', function () {
            return Setting::all()->pluck('value', 'key')->toArray();
        });
    }

    /**
     * Bind data to the view.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with('settings', $this->settings);
    }
}