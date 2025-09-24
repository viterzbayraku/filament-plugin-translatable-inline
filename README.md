# Filament Plugin - Translatable Inline

This is an addon to [Spatie Translatable](https://filamentphp.com/plugins/filament-spatie-translatable) that allows you to edit your translation directly below the field. 

This approach offers several advantages:

- Faster editing of your translations
- Detecting fields that can be translated is much easier to see
- You can quickly see which translations are missing

## Screenshots

![Screenshot](https://raw.githubusercontent.com/mvenghaus/filament-plugin-translatable-inline/main/docs/images/screenshot.png)

## Requirements

You need the latest version of Filament v3.

This package is based on:
- [Spatie Laravel Translatable](https://github.com/spatie/laravel-translatable)

> **_NOTE:_** As of version 3.1.0, this package no longer depends on the official `filament/spatie-laravel-translatable-plugin` due to compatibility issues with Filament v3. Instead, it works directly with the core `spatie/laravel-translatable` package. 

## Installation

Install the package via composer:

```bash
composer require mvenghaus/filament-plugin-translatable-inline:"^3.0"
```

### Configuration

You need to set up Spatie Laravel Translatable as described in their [documentation](https://github.com/spatie/laravel-translatable).

**Locale Configuration:**

This package will automatically detect locales from your configuration in the following priority:

1. Resource-specific locales (if you define `getTranslatableLocales()` in your resource)
2. Spatie translatable config (`config/translatable.php`)
3. App locales (`config/app.php` - `app.locales`)
4. Fallback to app locale (`config/app.php` - `app.locale`)

To configure your locales, you can:

**Option 1: Use Spatie's translatable config**
```php
// config/translatable.php
return [
    'locales' => ['en', 'es', 'fr', 'de'],
    // ... other config
];
```

**Option 2: Add to your app config**
```php
// config/app.php
return [
    'locale' => 'en',
    'locales' => ['en', 'es', 'fr', 'de'],
    // ... other config
];
```

**Usage:**

Make sure your Eloquent model uses the `Translatable` trait as described in the Spatie documentation.

Instead of having a locale switcher in a dropdown above, you add a container for each translatable field.

**Before**
```php
<?php

...

    public static function form(Form $form): Form
        {
            return $form
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->maxLength(255)
                        ->required()
                    ,

...
```

**After**
```php
<?php

...

use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

...

    public static function form(Form $form): Form
        {
            return $form
                ->schema([
                    TranslatableContainer::make(
                        Forms\Components\TextInput::make('title')
                            ->maxLength(255)
                            ->required()
                    )
                       ->onlyMainLocaleRequired() // optional
                       ->requiredLocales(['en', 'es']) // optional
                    ,

...
```

For each field that can be translated, simply repeat this process, and you'll be done.

> **_NOTE:_** You don't have to globally choose between inline or dropdown. Instead, you can choose an option on each page. For instance, it makes sense to have the dropdown in the list view and then use the inline version when editing.

### Options

#### onlyMainLocaleRequired

Sometimes you might want the field to be required, but only for the primary language. For example, if you set the TextInput to 'required,' it applies to all language variants. This is where this option comes into play. It removes the 'required' validation for all other languages except the primary one.

#### requireLocales

If you have more than one required locales you can pass an array to this method.

## Tipps & Hints

### Validation

If all of your locales are required and if your values do not pass the JS validation, then the variants will remain automatically expanded.

### afterStateUpdated

If you want to use "afterStateUpdated", you have to consider that the state path shifts by one level.
n addition, one must specify the locale which is located in the component's meta under the key "locale".

**Before**
```php
->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
```

**After**
```php
->afterStateUpdated(fn (Set $set, Component $component, ?string $state) => $set('../slug.' . $component->getMeta('locale'), Str::slug($state))),
```

### Empty translations

![Screenshot](https://raw.githubusercontent.com/mvenghaus/filament-plugin-translatable-inline/main/docs/images/screenshot.png)

As you can see in the screenshot, the "nl" is not filled and therefore not marked.

## Upgrading from v3.0.x

If you're upgrading from a previous version that used `filament/spatie-laravel-translatable-plugin`, you need to:

1. Remove the old plugin dependency:
   ```bash
   composer remove filament/spatie-laravel-translatable-plugin
   ```

2. Update this package:
   ```bash
   composer update mvenghaus/filament-plugin-translatable-inline
   ```

3. Configure your locales as described in the [Configuration](#configuration) section above.

4. Remove any Filament spatie translatable plugin configurations from your resource pages (traits, header actions, etc.) as they are no longer needed.

## Migration Notes for Filament v4

If you need Filament v4 support, consider using [lara-zeus/spatie-translatable](https://github.com/lara-zeus/spatie-translatable) which is designed for Filament v4.

# Contact
If you any questions or you find a bug, please [contact me via email](mailto:support@inklammern.de).