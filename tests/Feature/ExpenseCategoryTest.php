<?php

declare(strict_types=1);

use App\Livewire\ExpenseCategories\CreatePage;
use App\Livewire\ExpenseCategories\EditPage;
use App\Livewire\ExpenseCategories\IndexPage;
use App\Models\ExpenseCategory;
use App\Models\Permission;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    expenseCategoryPermissions($this->user, ['expenses.view', 'expenses.create', 'expenses.update']);
});

it('can view expense categories index', function () {
    $this->get(route('expense-categories.index'))->assertOk();
});

it('can create an expense category', function () {
    Livewire::test(CreatePage::class)
        ->set('name', 'Test Category')
        ->set('description', 'Test Description')
        ->set('is_active', true)
        ->call('save')
        ->assertRedirect(route('expense-categories.index'));

    $this->assertDatabaseHas('expense_categories', [
        'name' => 'Test Category',
        'description' => 'Test Description',
        'is_active' => true,
    ]);
});

it('can update an expense category', function () {
    $category = ExpenseCategory::query()->create([
        'name' => 'Original',
        'is_active' => true,
    ]);

    Livewire::test(EditPage::class, ['expenseCategory' => $category])
        ->set('name', 'Updated Name')
        ->set('description', 'Updated Desc')
        ->set('is_active', false)
        ->call('update')
        ->assertRedirect(route('expense-categories.index'));

    $this->assertDatabaseHas('expense_categories', [
        'id' => $category->id,
        'name' => 'Updated Name',
        'description' => 'Updated Desc',
        'is_active' => false,
    ]);
});

it('can delete an unused expense category', function () {
    $category = ExpenseCategory::query()->create([
        'name' => 'To Be Deleted',
        'is_active' => true,
    ]);

    Livewire::test(IndexPage::class)
        ->call('delete', $category->id);

    $this->assertDatabaseMissing('expense_categories', [
        'id' => $category->id,
    ]);
});

function expenseCategoryPermissions(User $user, array $permissions): void
{
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    foreach ($permissions as $permission) {
        Permission::query()->firstOrCreate([
            'name' => $permission,
            'guard_name' => 'web',
        ]);
    }

    $user->givePermissionTo($permissions);
}
