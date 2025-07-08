<x-modal wire:model="modalWarning">
    <div class="flex justify-center items-center">
            <div class="mb-5 rounded-lg w-full text-center">
            <x-icon name="c-shield-exclamation" class="w-16 h-16 text-red-700 mx-auto mb-4" />
            <p class="text-center">You must scan the QR code first!</p>
        </div>
    </div>
    <x-slot:actions>
        <x-button class="btn-primary" label="OK" @click="$wire.modalWarning = false" />
        {{-- <x-button label="Ya" class="btn-primary" type="submit" spinner="delete" /> --}}
    </x-slot:actions>
</x-modal>
