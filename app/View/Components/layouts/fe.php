<?php

namespace App\View\Components\layouts;

use Closure;
use App\Models\Table;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class fe extends Component
{
    public $cart;
    public $tables;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->cart = session('cart', []);
        $this->tables = Table::all();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.layouts.fe');
    }
}
