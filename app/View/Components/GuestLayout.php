<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\Component;


class GuestLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View|Factory
    {
        return view('layouts.guest');
    }
}
