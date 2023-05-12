<?php

namespace App\View\Components;

use App\Models\Session;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\Component;

class EmptySessionWarning extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public Session $session)
    {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Factory
    {
        return view('components.empty-session-warning', [
            'editSessionUrl' => route('session.issues', ['inviteCode' => $this->session->invite_code]),
        ]);
    }
}
