<?php

namespace App\Livewire\Admin\Concerns;

/**
 * Lets the image upload field drop a picked file before it is saved.
 * Components list their upload properties in $uploadFields; Content's keyed
 * "uploads.<setting>" array is handled too.
 */
trait DiscardsUploads
{
    public function discardUpload(string $field, ?int $index = null): void
    {
        if (str_starts_with($field, 'uploads.') && property_exists($this, 'uploads')) {
            unset($this->uploads[substr($field, 8)]);
            $this->resetErrorBag($field);

            return;
        }

        abort_unless(in_array($field, $this->uploadFields ?? [], true), 403);

        if ($index === null || ! is_array($this->{$field})) {
            $this->{$field} = is_array($this->{$field}) ? [] : null;
        } else {
            $files = $this->{$field};
            unset($files[$index]);
            $this->{$field} = array_values($files);
        }

        $this->resetErrorBag([$field, "{$field}.*"]);
    }
}
