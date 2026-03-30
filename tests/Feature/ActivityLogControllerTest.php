<?php

use App\Models\Permission;
use App\Models\User;
use function Pest\Laravel\actingAs;

test('activity logs index page is accessible with permission', function () {
    $user = User::factory()->create();
    $permission = Permission::firstOrCreate(['name' => 'activity-logs.view']);
    $user->givePermissionTo($permission);

    activity()
        ->causedBy($user)
        ->event('verified')
        ->log('System verification completed');

    $response = actingAs($user)->get(route('activity-logs.index'));

    $response->assertStatus(200);
    $response->assertSee('Activity Logs');
    $response->assertSee('System verification completed');
});

test('activity logs index page is forbidden without permission', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->get(route('activity-logs.index'));

    $response->assertStatus(403);
});
