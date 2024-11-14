<?php

namespace Filament\Jetstream\Concerns;

use Closure;
use Filament\Facades\Filament;
use Filament\Navigation\MenuItem;
use Filament\Panel;

trait HasApiTokensFeatures
{
    public Closure | bool $hasApiFeature = false;

    public Closure | array | null $apiTokenPermissions = [];

    public ?string $apiMenuItemLabel = null;

    public ?string $apiMenuItemIcon = null;

    public function hasApiTokensFeatures(): bool
    {
        return $this->evaluate($this->hasApiFeature) === true;
    }

    public function apiTokens(Closure | bool $condition = true, Closure | array | null $permissions = null, ?string $menuItemLabel = null, ?string $menuItemIcon = null): static
    {
        $this->hasApiFeature = $condition;

        $this->apiMenuItemLabel = $menuItemLabel;

        $this->apiMenuItemIcon = $menuItemIcon;

        $this->apiTokenPermissions = $permissions;

        return $this;
    }

    public function getApiMenuItemLabel(): string
    {
        return $this->evaluate($this->apiMenuItemLabel) ?? __('filament-jetstream::default.menu_item.api_tokens.label');
    }

    public function getApiMenuItemIcon(): string
    {
        return $this->evaluate($this->apiMenuItemIcon) ?? 'heroicon-o-key';
    }

    public function getApiTokenPermissions(): array
    {
        $permissions = $this->evaluate($this->apiTokenPermissions) ?? ['create', 'read', 'update', 'delete'];

        return array_combine($permissions, $permissions);
    }

    public function apiTokenMenuItem(Panel $panel): MenuItem
    {
        return MenuItem::make()
            ->visible(fn (): bool => $this->hasApiTokensFeatures())
            ->label(fn () => $this->getApiMenuItemLabel())
            ->icon(fn () => $this->getApiMenuItemIcon())
            ->url(function () use ($panel) {
                return $this->getApiTokenUrl($panel);
            });
    }

    public function getApiTokenUrl(Panel $panel): ?string
    {
        if (!$panel->hasTenancy()) {
            return $panel->route('api-tokens');
        }

        if ($tenant = Filament::getTenant()) {
            return $panel->route('api-tokens', ['tenant' => $tenant->id]);
        }

        return null;
    }

    public function validPermissions(array $permissions): array
    {
        return array_values(array_intersect($permissions, $this->getApiTokenPermissions()));
    }
}
