{{-- Drawer für Issue hinzufügen --}}
<div class="drawer drawer-end z-50"
     x-data
     @keydown.escape.window="$wire.set('drawerOpen', false)">
    <input id="add-issue-drawer" type="checkbox" class="drawer-toggle" wire:model.live="drawerOpen" />
    <div class="drawer-side">
        <label for="add-issue-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
        <div class="bg-base-100 min-h-full w-[80vw] max-w-4xl p-6 shadow-xl">
            {{-- Header --}}
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold">Issue hinzufügen</h3>
                <label for="add-issue-drawer" class="btn btn-sm btn-ghost btn-circle">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </label>
            </div>

            {{-- Tabs --}}
            <div class="grid grid-cols-2 gap-2 mb-6">
                <button wire:click="switchTab('manual')"
                        class="btn {{ $drawerTab === 'manual' ? 'btn-primary' : 'btn-ghost border border-base-300' }} gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Manuell
                </button>
                <button wire:click="switchTab('jira')"
                        class="btn {{ $drawerTab === 'jira' ? 'btn-primary' : 'btn-ghost border border-base-300' }} gap-2">
                    {{-- Normal Icon --}}
                    <svg wire:loading.remove wire:target="switchTab('jira')" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M11.571 11.513H0a5.218 5.218 0 0 0 5.232 5.215h2.13v2.057A5.215 5.215 0 0 0 12.575 24V12.518a1.005 1.005 0 0 0-1.005-1.005zm5.723-5.756H5.736a5.215 5.215 0 0 0 5.215 5.214h2.129v2.058a5.218 5.218 0 0 0 5.215 5.214V6.758a1.001 1.001 0 0 0-1.001-1.001zM23.013 0H11.455a5.215 5.215 0 0 0 5.215 5.215h2.129v2.057A5.215 5.215 0 0 0 24 12.483V1.005A1.005 1.005 0 0 0 23.013 0z"/>
                    </svg>
                    {{-- Loading Spinner --}}
                    <span wire:loading wire:target="switchTab('jira')" class="loading loading-spinner loading-xs"></span>
                    Jira
                </button>
            </div>

            {{-- Manual Tab --}}
            @if($drawerTab === 'manual')
                @include('livewire.v2.partials._drawer-manual')
            @endif

            {{-- Jira Tab --}}
            @if($drawerTab === 'jira')
                @include('livewire.v2.partials._drawer-jira')
            @endif
        </div>
    </div>
</div>


