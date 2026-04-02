<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Actions\Appearance\UpdateAppearanceAction;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class AppearancePage extends Component
{
    public string $theme_preference = 'system';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('settings.appearance.update'), 403);

        $this->theme_preference = auth()->user()->theme_preference ?? 'system';
    }

    public function setTheme(string $theme, UpdateAppearanceAction $action): void
    {
        abort_unless(auth()->user()?->can('settings.appearance.update'), 403);

        if (! in_array($theme, ['light', 'dark', 'system'])) {
            return;
        }

        $this->theme_preference = $theme;
        $action->handle(auth()->user(), ['theme_preference' => $theme]);
    }

    public function render(): View
    {
        return view('livewire.settings.appearance-page');
    }
}
