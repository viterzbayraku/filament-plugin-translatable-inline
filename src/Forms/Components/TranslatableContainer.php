<?php

declare(strict_types=1);

namespace Mvenghaus\FilamentPluginTranslatableInline\Forms\Components;

use Filament\Forms\Components\Component;
use Filament\Forms\ComponentContainer;
use Illuminate\Support\Collection;

class TranslatableContainer extends Component
{
    protected string $view = 'filament-plugin-translatable-inline::forms.components.translatable-container';

    protected Component $baseComponent;

    protected bool $onlyMainIsRequired = false;
    protected array $requiredLocales = [];

    final public function __construct(array $schema = [])
    {
        $this->schema($schema);

        $this->baseComponent = collect($schema)->first();
        $this->statePath($this->baseComponent->getName());
    }

    public static function make(Component $component): static
    {
        $static = app(static::class, [
            'schema' => [$component]
        ]);
        $static->configure();

        return $static;
    }

    public function getName(): string
    {
        return $this->baseComponent->getName();
    }

    public function getLabel(): string
    {
        return $this->baseComponent->getLabel();
    }

    public function getChildComponentContainers(bool $withHidden = false): array
    {
        $locales = $this->getTranslatableLocales();

        $containers = [];

        $containers['main'] = ComponentContainer::make($this->getLivewire())
            ->parentComponent($this)
            ->components([
                $this->cloneComponent($this->baseComponent, $locales->first())
                    ->required($this->isLocaleRequired($locales->first()))
            ]);

        $containers['additional'] = ComponentContainer::make($this->getLivewire())
            ->parentComponent($this)
            ->components(
                $locales
                    ->filter(fn(string $locale, int $index) => $index !== 0)
                    ->map(
                        fn(string $locale): Component => $this->cloneComponent($this->baseComponent, $locale)
                            ->required($this->isLocaleRequired($locale))
                    )
                    ->all()
            );

        return $containers;
    }

    public function cloneComponent(Component $component, string $locale): Component
    {
        return $component
            ->getClone()
            ->meta('locale', $locale)
            ->label("{$component->getLabel()} ({$locale})")
            ->statePath($locale);
    }

    public function getTranslatableLocales(): Collection
    {
        $resourceLocales = null;
        if (method_exists($this->getLivewire(), 'getResource') &&
            method_exists($this->getLivewire()::getResource(), 'getTranslatableLocales')
        ) {
            $resourceLocales = $this->getLivewire()::getResource()::getTranslatableLocales();
        }

        return collect($resourceLocales ?? $this->getDefaultLocales());
    }

    protected function getDefaultLocales(): array
    {
        // Priority 1: Try to get locales from spatie/laravel-translatable config
        if (config('translatable.locales')) {
            return config('translatable.locales');
        }

        // Priority 2: Try to get locales from app config
        if (config('app.locales')) {
            return config('app.locales');
        }

        // Priority 3: Fallback to app locale
        return [config('app.locale', 'en')];
    }

    public function isLocaleStateEmpty(string $locale): bool
    {
        return empty($this->getState()[$locale]);
    }

    public function onlyMainLocaleRequired(): self
    {
        $this->onlyMainIsRequired = true;

        return $this;
    }

    public function requiredLocales(array $locales): self
    {
        $this->requiredLocales = $locales;

        return $this;
    }

    private function isLocaleRequired(string $locale): bool
    {
        if ($this->onlyMainIsRequired) {
            return ($locale === $this->getTranslatableLocales()->first());
        }

        if (in_array($locale, $this->requiredLocales)) {
            return true;
        }

        if (empty($this->requiredLocales) && $this->baseComponent->isRequired()) {
            return true;
        }

        return false;
    }
}
