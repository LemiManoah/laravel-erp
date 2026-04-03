<div>
    <x-ui.page-header title="Roles & Permissions" description="Manage user roles and the permissions assigned to them.">
        <x-slot:actions>
            @can('roles.create')
                <x-ui.action-link href="{{ route('roles.create') }}" variant="primary">
                    <i class="fas fa-plus mr-2"></i> Create Role
                </x-ui.action-link>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="w-1/3 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Role Name</th>
                        <th class="w-1/3 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Permissions Count</th>
                        <th class="w-1/3 px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($roles as $role)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $role->name }}</span>
                                @if($role->name === 'Admin')
                                    <span class="ml-2 inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/30 dark:text-red-300">Protected</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 dark:bg-blue-900/30 dark:text-blue-300 dark:ring-blue-500/30">
                                    {{ $role->permissions_count }} Permissions
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    @can('roles.update')
                                        <x-ui.action-link href="{{ route('roles.edit', $role) }}" variant="warning">
                                            Edit
                                        </x-ui.action-link>
                                    @endcan
                                    @if($role->name !== 'Admin')
                                        @can('roles.delete')
                                            <x-ui.action-link tag="button" type="button" wire:click="delete({{ $role->id }})" wire:confirm="Delete role '{{ $role->name }}'? This cannot be undone." variant="danger">
                                                Delete
                                            </x-ui.action-link>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No roles found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($roles->hasPages())
            <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900">
                {{ $roles->links() }}
            </div>
        @endif
    </div>
</div>
