<?php

namespace Filament\Jetstream\Concerns;

use Closure;
use Filament\Actions\Action;
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

    public function apiTokenMenuItem(Panel $panel): Action
    {
        return Action::make('api_tokens')
            ->visible(fn (): bool => $this->hasApiTokensFeatures())
            ->label(fn () => $this->getApiMenuItemLabel())
            ->icon(fn () => $this->getApiMenuItemIcon())
            ->url(function () use ($panel) {
                return $this->getApiTokenUrl($panel);
            });
    }

    public function getApiTokenUrl(Panel $panel): ?string
    {
        return $panel->getUrl() . '/tokens';
    }

    public function validPermissions(array $permissions): array
    {
        return array_values(array_intersect($permissions, $this->getApiTokenPermissions()));
    }
}
