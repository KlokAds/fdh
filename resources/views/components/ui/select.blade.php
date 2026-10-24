{{--
    Styled dropdown that drives a hidden native <select wire:model>, so Livewire binding,
    validation and keyboard focus behave exactly like a normal select.
--}}
@props(['model', 'options' => [], 'selected' => null, 'placeholder' => null, 'live' => false, 'small' => false, 'label' => null])
@php
    $choices = ($placeholder !== null ? ['' => $placeholder] : []) + $options;
    $current = (string) ($selected ?? '');
    $currentLabel = $choices[$current] ?? ($placeholder ?? (string) reset($choices));
@endphp
<div @class(['ui-select', 'ui-select-sm' => $small]) data-ui-select>
    <select {{ $live ? 'wire:model.live' : 'wire:model' }}="{{ $model }}" class="ui-select-native" tabindex="-1" aria-hidden="true">
        @foreach ($choices as $value => $text)
            <option value="{{ $value }}">{{ $text }}</option>
        @endforeach
    </select>
    <button type="button" class="ui-select-trigger" data-ui-select-toggle aria-haspopup="listbox" aria-expanded="false" @if ($label) aria-label="{{ $label }}" @endif>
        <span @class(['ui-select-value', 'is-placeholder' => $current === '' && $placeholder !== null])>{{ $currentLabel }}</span>
        <x-icon name="chevron" class="ui-select-chevron" />
    </button>
    <ul class="ui-select-list" role="listbox">
        @foreach ($choices as $value => $text)
            <li wire:key="opt-{{ $model }}-{{ $loop->index }}">
                <button type="button" role="option" class="ui-select-option" data-value="{{ $value }}" aria-selected="{{ (string) $value === $current ? 'true' : 'false' }}">
                    <span>{{ $text }}</span><x-icon name="check" class="ui-select-check" />
                </button>
            </li>
        @endforeach
    </ul>
</div>
