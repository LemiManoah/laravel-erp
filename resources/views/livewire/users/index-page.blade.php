<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Users</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Manage staff accounts and assigned roles.</p>
        </div>
        <div class="flex items-center space-x-3">
            @can('viewAny', \App\Models\User::class)
                <a href="{{ route('roles.index') }}" class="rounded-md border border-gray-300 bg-gray-100 px-4 py-2 text-sm text-gray-700 transition hover:bg-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                    <i class="fas fa-users-cog mr-2"></i> Manage Roles
                </a>
            @endcan
            @can('create', \App\Models\User::class)
                <a href="{{ route('users.create') }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm text-white transition hover:bg-blue-700">
                    <i class="fas fa-user-plus mr-2"></i> Add User
                </a>
            @endcan
        </div>
    </div>

    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search user, email, or phone"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
            <select wire:model.live="role" class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">All roles</option>
                @foreach($roles as $roleName)
                    <option value="{{ $roleName }}">{{ $roleName }}</option>
                @endforeach
            </select>
            <select wire:model.live="status" class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">All statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <button type="button" wire:click="clearFilters" class="rounded-md bg-gray-900 px-4 py-2 text-sm text-white transition hover:bg-gray-700">
                Clear Filters
            </button>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Roles</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Last Login</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    @forelse($users as $user)
                        <tr wire:key="user-row-{{ $user->id }}">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $user->phone ?: 'N/A' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                <div class="flex flex-wrap gap-2">
                                    @forelse($user->roles as $roleItem)
                                        <span class="rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                            {{ $roleItem->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-gray-400">No roles</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span @class([
                                    'px-2 py-1 text-xs rounded-full font-medium',
                                    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' => $user->is_active,
                                    'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' => ! $user->is_active,
                                ])>
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $user->last_login_at?->format('M d, Y H:i') ?? 'Never' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                @can('update', $user)
                                    <a href="{{ route('users.edit', $user) }}"
                                        class="inline-flex items-center rounded-md border border-amber-200 px-2.5 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-50 dark:border-amber-800 dark:text-amber-300 dark:hover:bg-amber-900/30">
                                        Edit
                                    </a>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600">-</span>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
