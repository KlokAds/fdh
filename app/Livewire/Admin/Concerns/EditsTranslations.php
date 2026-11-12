<?php

namespace App\Livewire\Admin\Concerns;

use App\Support\RichText;
use Illuminate\Database\Eloquent\Model;

/**
 * Helpers for admin forms whose translatable fields are bound as form.field.en / form.field.bn.
 */
trait EditsTranslations
{
    /**
     * @return list<string>
     */
    protected function locales(): array
    {
        return array_keys(config('fdh.locales'));
    }

    /**
     * @param  list<string>  $fields
     * @return array<string, array<string, string>>
     */
    protected function blankTranslations(array $fields): array
    {
        $blank = array_fill_keys($this->locales(), '');

        return array_fill_keys($fields, $blank);
    }

    /**
     * @param  list<string>  $fields
     * @return array<string, array<string, string>>
     */
    protected function translationsFrom(Model $model, array $fields): array
    {
        $values = [];
        foreach ($fields as $field) {
            foreach ($this->locales() as $locale) {
                $values[$field][$locale] = (string) $model->getTranslation($field, $locale, false);
            }
        }

        return $values;
    }

    /**
     * Validation rules for a translatable field: English required (if $required), other languages optional.
     *
     * @return array<string, string>
     */
    protected function translationRules(string $field, bool $required, int $max): array
    {
        $rules = [];
        foreach ($this->locales() as $index => $locale) {
            $rules["form.{$field}.{$locale}"] = ($required && $index === 0 ? 'required' : 'nullable')."|string|max:{$max}";
        }

        return $rules;
    }

    /**
     * Drop empty translations so the model falls back to English.
     *
     * @param  array<string, string>  $values
     * @return array<string, string>
     */
    protected function cleanTranslations(array $values, bool $rich = false): array
    {
        $clean = $rich
            ? fn ($value): string => RichText::clean((string) $value)
            : fn ($value): string => trim(strip_tags((string) $value));

        return array_filter(array_map($clean, $values), fn (string $value): bool => $value !== '');
    }

    /**
     * @return array<string, string>
     */
    protected function translationAttributes(string $field, string $label): array
    {
        $attributes = [];
        foreach (config('fdh.locales') as $locale => $meta) {
            $attributes["form.{$field}.{$locale}"] = "{$label} ({$meta['name']})";
        }

        return $attributes;
    }
}
