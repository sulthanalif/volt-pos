<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Status extends Component
{
    public array $status;
    /**
     * Create a new component instance.
     */
    public function __construct(string $status)
    {
        switch ($status) {
            case 'approved':
                $this->status = [
                    'text' => 'Approved',
                    'class' => 'badge-success'
                ];
                break;
            case 'rejected':
                $this->status = [
                    'text' => 'Rejected',
                    'class' => 'badge-error'
                ];
                break;
            case 'shipped':
                $this->status = [
                    'text' => 'Shipped',
                    'class' => 'badge-info'
                ];
                break;
            case 'delivered':
                $this->status = [
                    'text' => 'Delivered',
                    'class' => 'badge-success'
                ];
                break;
            case 'success':
                $this->status = [
                    'text' => 'Success',
                    'class' => 'badge-success'
                ];
                break;
            case 'process':
                $this->status = [
                    'text' => 'Process',
                    'class' => 'badge-warning'
                ];
                break;
            case '1':
                $this->status = [
                    'text' => 'active',
                    'class' => 'badge-success'
                ];
                break;
            case '0':
                $this->status = [
                    'text' => 'inactive',
                    'class' => 'badge-error'
                ];
                break;
            case 'available':
                $this->status = [
                    'text' => 'Available',
                    'class' => 'badge-success'
                ];
                break;
            case 'unavailable':
                $this->status = [
                    'text' => 'Unavailable',
                    'class' => 'badge-error'
                ];
                break;
            case 'paid':
                $this->status = [
                    'text' => 'Paid',
                    'class' => 'badge-success'
                ];
                break;
            case 'unpaid':
                $this->status = [
                    'text' => 'Unpaid',
                    'class' => 'badge-error'
                ];
                break;
            default:
                $this->status = [
                    'text' => 'Pending',
                    'class' => 'badge-soft'
                ];
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.status', [
            'status' => $this->status
        ]);
    }
}
