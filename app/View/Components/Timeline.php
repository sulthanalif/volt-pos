<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Timeline extends Component
{
    public $status;
    /**
     * Create a new component instance.
     */
    public function __construct($status = 'pending')
    {
        $this->status = $status;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.timeline', [
            'status' => $this->status,
        ]);
    }
}
