<x-layouts.app title="Activity Logs">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Activity Logs</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Track and monitor all application activity and changes.</p>
    </div>

    <form action="{{ route('activity-logs.index') }}" method="GET" class="mb-6">
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search action, causer, subject, or event"
                    class="md:col-span-2 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white text-sm">
                <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-md hover:bg-gray-700 transition text-sm">Filter</button>
            </div>
        </div>
    </form>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Causer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Properties</th>
                    </tr>
                </thead>
                <tbody x-data class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($activities as $activity)
                        <tr x-data="{ expanded: false }" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $activity->created_at->format('M d, Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <span @class([
                                        'px-2.5 py-1 text-xs font-medium rounded-md',
                                        'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' => $activity->event === 'created',
                                        'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' => $activity->event === 'updated',
                                        'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' => $activity->event === 'deleted',
                                        'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300' => !in_array($activity->event, ['created', 'updated', 'deleted']),
                                    ])>
                                        {{ ucfirst($activity->event ?: 'system') }}
                                    </span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white truncate max-w-xs" title="{{ $activity->description }}">
                                        {{ $activity->description }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                @if($activity->subject_type)
                                    <div class="font-medium">{{ class_basename($activity->subject_type) }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">ID: {{ $activity->subject_id }}</div>
                                @else
                                    <span class="text-gray-500 dark:text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                @if($activity->causer)
                                    <div class="font-medium">{{ $activity->causer->name ?? 'System' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $activity->causer->email ?? '' }}</div>
                                @else
                                    <span class="text-gray-500 dark:text-gray-400">System</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                @if($activity->properties->isNotEmpty())
                                    <button @click="expanded = !expanded" class="inline-flex items-center space-x-1 text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-xs font-medium border-0 bg-transparent cursor-pointer p-0 focus:outline-none">
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
                                                <table class="min-w-full lg:w-3/4 xl:w-1/2 divide-y divide-gray-200 dark:divide-gray-700 m-0">
                                                    <thead class="bg-gray-100 dark:bg-gray-900/50">
                                                        <tr>
                                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Field</th>
                                                            @if(!empty($old))
                                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 w-1/3">Old Value</th>
                                                            @endif
                                                            @if(!empty($attributes))
                                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 border-l border-gray-200 dark:border-gray-700 w-1/3">New Value</th>
                                                            @endif
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                                        @foreach($allKeys as $key)
                                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                                <td class="px-4 py-2 text-sm font-medium text-gray-900 dark:text-gray-300">{{ $key }}</td>
                                                                @if(!empty($old))
                                                                    <td class="px-4 py-2 text-sm text-red-600 dark:text-red-400 line-through truncate max-w-[200px]" title="{{ isset($old[$key]) ? (is_array($old[$key]) ? json_encode($old[$key]) : (string) $old[$key]) : 'null' }}">
                                                                        @if(isset($old[$key]))
                                                                            {{ is_array($old[$key]) ? json_encode($old[$key]) : (string) $old[$key] }}
                                                                        @else
                                                                            <span class="text-gray-400 italic font-mono text-xs">null</span>
                                                                        @endif
                                                                    </td>
                                                                @endif
                                                                @if(!empty($attributes))
                                                                    <td class="px-4 py-2 text-sm text-green-600 dark:text-green-400 border-l border-gray-200 dark:border-gray-700 truncate max-w-[200px]" title="{{ isset($attributes[$key]) ? (is_array($attributes[$key]) ? json_encode($attributes[$key]) : (string) $attributes[$key]) : 'null' }}">
                                                                        @if(isset($attributes[$key]))
                                                                            {{ is_array($attributes[$key]) ? json_encode($attributes[$key]) : (string) $attributes[$key] }}
                                                                        @else
                                                                            <span class="text-gray-400 italic font-mono text-xs">null</span>
                                                                        @endif
                                                                    </td>
                                                                @endif
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <pre class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 p-3 rounded-md text-xs overflow-x-auto m-0 shadow-inner border border-gray-200 dark:border-gray-700">{{ json_encode($activity->properties, JSON_PRETTY_PRINT) }}</pre>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center space-y-3">
                                    <div class="h-12 w-12 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                        <i class="fas fa-history text-xl text-gray-400 dark:text-gray-500"></i>
                                    </div>
                                    <p class="text-gray-500 dark:text-gray-400 font-medium">No activity logs recorded yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($activities->hasPages())
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
                {{ $activities->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
