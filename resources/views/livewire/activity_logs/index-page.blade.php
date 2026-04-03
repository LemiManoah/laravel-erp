<div>
    <x-ui.page-header title="Activity Logs" description="Track and monitor all application activity and changes." />

    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search action, causer, subject, or event"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white md:col-span-2"
            >
            <x-ui.action-link tag="button" type="button" wire:click="clearSearch" variant="secondary">
                Clear
            </x-ui.action-link>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Causer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Properties</th>
                    </tr>
                </thead>
                @forelse($activities as $activity)
                    <tbody x-data="{ expanded: false }" wire:key="activity-row-{{ $activity->id }}" class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                        <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $activity->created_at->format('M d, Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <span @class([
                                        'px-2.5 py-1 text-xs font-medium rounded-md',
                                        'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' => $activity->event === 'created',
                                        'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' => $activity->event === 'updated',
                                        'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' => $activity->event === 'deleted',
                                        'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300' => ! in_array($activity->event, ['created', 'updated', 'deleted']),
                                    ])>
                                        {{ ucfirst($activity->event ?: 'system') }}
                                    </span>
                                    <span class="max-w-xs truncate text-sm font-medium text-gray-900 dark:text-white" title="{{ $activity->description }}">
                                        {{ $activity->description }}
                                    </span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">
                                @if($activity->subject_type)
                                    <div class="font-medium">{{ class_basename($activity->subject_type) }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">ID: {{ $activity->subject_id }}</div>
                                @else
                                    <span class="text-gray-500 dark:text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">
                                @if($activity->causer)
                                    <div class="font-medium">{{ $activity->causer->name ?? 'System' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $activity->causer->email ?? '' }}</div>
                                @else
                                    <span class="text-gray-500 dark:text-gray-400">System</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                @if($activity->properties->isNotEmpty())
                                    <button @click="expanded = !expanded" class="inline-flex cursor-pointer items-center space-x-1 border-0 bg-transparent p-0 text-xs font-medium text-blue-600 hover:text-blue-800 focus:outline-none dark:text-blue-400 dark:hover:text-blue-300">
                                        <i class="fas" :class="expanded ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                        <span>View Changes</span>
                                    </button>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">No properties</span>
                                @endif
                            </td>
                        </tr>
                        @if($activity->properties->isNotEmpty())
                            <tr x-show="expanded" style="display: none;" class="bg-gray-50 dark:bg-gray-800/80">
                                <td colspan="5" class="px-6 py-4">
                                    <div class="text-sm">
                                        @php
                                            $attributes = (array) $activity->properties->get('attributes', []);
                                            $old = (array) $activity->properties->get('old', []);
                                            $allKeys = array_unique(array_merge(array_keys($attributes), array_keys($old)));
                                        @endphp

                                        @if(count($allKeys) > 0)
                                            <div class="overflow-hidden rounded-md border border-gray-200 dark:border-gray-700">
                                                <table class="m-0 min-w-full divide-y divide-gray-200 dark:divide-gray-700 lg:w-3/4 xl:w-1/2">
                                                    <thead class="bg-gray-100 dark:bg-gray-900/50">
                                                        <tr>
                                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Field</th>
                                                            @if(!empty($old))
                                                                <th class="w-1/3 px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Old Value</th>
                                                            @endif
                                                            @if(!empty($attributes))
                                                                <th class="w-1/3 border-l border-gray-200 px-4 py-2 text-left text-xs font-medium text-gray-500 dark:border-gray-700 dark:text-gray-400">New Value</th>
                                                            @endif
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                                        @foreach($allKeys as $key)
                                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                                <td class="px-4 py-2 text-sm font-medium text-gray-900 dark:text-gray-300">{{ $key }}</td>
                                                                @if(!empty($old))
                                                                    <td class="max-w-[200px] truncate px-4 py-2 text-sm text-red-600 line-through dark:text-red-400" title="{{ isset($old[$key]) ? (is_array($old[$key]) ? json_encode($old[$key]) : (string) $old[$key]) : 'null' }}">
                                                                        @if(isset($old[$key]))
                                                                            {{ is_array($old[$key]) ? json_encode($old[$key]) : (string) $old[$key] }}
                                                                        @else
                                                                            <span class="font-mono text-xs italic text-gray-400">null</span>
                                                                        @endif
                                                                    </td>
                                                                @endif
                                                                @if(!empty($attributes))
                                                                    <td class="max-w-[200px] truncate border-l border-gray-200 px-4 py-2 text-sm text-green-600 dark:border-gray-700 dark:text-green-400" title="{{ isset($attributes[$key]) ? (is_array($attributes[$key]) ? json_encode($attributes[$key]) : (string) $attributes[$key]) : 'null' }}">
                                                                        @if(isset($attributes[$key]))
                                                                            {{ is_array($attributes[$key]) ? json_encode($attributes[$key]) : (string) $attributes[$key] }}
                                                                        @else
                                                                            <span class="font-mono text-xs italic text-gray-400">null</span>
                                                                        @endif
                                                                    </td>
                                                                @endif
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <pre class="m-0 overflow-x-auto rounded-md border border-gray-200 bg-gray-100 p-3 text-xs text-gray-800 shadow-inner dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">{{ json_encode($activity->properties, JSON_PRETTY_PRINT) }}</pre>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                @empty
                    <tbody class="bg-white dark:bg-gray-800">
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center space-y-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                        <i class="fas fa-history text-xl text-gray-400 dark:text-gray-500"></i>
                                    </div>
                                    <p class="font-medium text-gray-500 dark:text-gray-400">No activity logs recorded yet.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                @endforelse
            </table>
        </div>
        @if($activities->hasPages())
            <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900">
                {{ $activities->links() }}
            </div>
        @endif
    </div>
</div>
