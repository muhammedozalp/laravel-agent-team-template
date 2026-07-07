<x-filament-panels::page>
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ __('Automated items are probed weekly and on demand; the rest are toggled by you. Definitions: config/checklists.php.') }}
        </p>

        <x-filament::button wire:click="runProbes" icon="heroicon-o-play">
            {{ __('Run automated checks') }}
        </x-filament::button>
    </div>

    <x-filament::tabs>
        @foreach ($this->groups as $index => $group)
            <x-filament::tabs.item
                :alpine-active="'activeTab === \'' . $group['key'] . '\''"
                x-on:click="activeTab = '{{ $group['key'] }}'"
                :badge="$group['progress']"
                :badge-color="$group['complete'] ? 'success' : 'gray'"
            >
                {{ $group['label'] }}
            </x-filament::tabs.item>
        @endforeach
    </x-filament::tabs>

    <div x-data="{ activeTab: '{{ $this->groups[0]['key'] ?? '' }}' }">
        @foreach ($this->groups as $group)
            <div x-show="activeTab === '{{ $group['key'] }}'" x-cloak class="mt-4 space-y-2">
                @if (!empty($group['description']))
                    <p class="mb-3 text-sm text-gray-500 dark:text-gray-400">{{ $group['description'] }}</p>
                @endif

                @foreach ($group['items'] as $item)
                    @php($checked = $item['state']?->isChecked() ?? false)
                    <div
                        class="flex items-start gap-3 rounded-lg bg-white p-3 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                        @if ($item['auto'])
                            <x-filament::icon
                                :icon="$checked ? 'heroicon-s-check-circle' : ($item['state']?->last_run_at ? 'heroicon-s-x-circle' : 'heroicon-o-clock')"
                                @class(['mt-0.5 h-6 w-6 shrink-0', 'text-success-500' => $checked, 'text-danger-500' => !$checked && $item['state']?->last_run_at, 'text-gray-400' => !$item['state']?->last_run_at])
                            />
                        @else
                            <button
                                type="button"
                                wire:click="toggle('{{ $item['key'] }}')"
                                class="mt-0.5 shrink-0"
                                aria-label="{{ __('Toggle :item', ['item' => $item['label']]) }}"
                            >
                                <x-filament::icon
                                    :icon="$checked ? 'heroicon-s-check-circle' : 'heroicon-o-circle-stack'"
                                    @class(['h-6 w-6', 'text-success-500' => $checked, 'text-gray-300 hover:text-gray-500 dark:text-gray-600' => !$checked])
                                />
                            </button>
                        @endif

                        <div class="min-w-0 flex-1">
                            <p @class(['text-sm font-medium', 'text-gray-400 line-through dark:text-gray-500' => $checked && !$item['auto']])>
                                {{ $item['label'] }}
                                @if ($item['auto'])
                                    <x-filament::badge color="info" class="ms-1">{{ __('auto') }}</x-filament::badge>
                                @endif
                            </p>

                            @if (!empty($item['description']))
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $item['description'] }}</p>
                            @endif

                            @if ($item['auto'] && $item['state']?->last_run_at)
                                <p @class(['mt-0.5 text-xs', 'text-success-600' => $checked, 'text-danger-600' => !$checked])>
                                    {{ $item['state']->detail }} — {{ $item['state']->last_run_at->diffForHumans() }}
                                </p>
                            @elseif (!$item['auto'] && $checked)
                                <p class="mt-0.5 text-xs text-gray-400">
                                    {{ __(':name, :time', ['name' => $item['state']->checkedBy?->name ?? '—', 'time' => $item['state']->checked_at->diffForHumans()]) }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
