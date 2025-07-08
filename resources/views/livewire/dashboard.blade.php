<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Title;

new #[Title('Dashboard')] class extends Component {
    //
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Dashboard" separator progress-indicator>

    </x-header>

    <!-- TABLE  -->
    <x-card shadow>
        <div class="flex justify-center">
            Hallo
        </div>
    </x-card>
</div>
