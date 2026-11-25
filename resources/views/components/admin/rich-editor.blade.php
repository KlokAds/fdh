{{-- Formatting editor bound to a hidden textarea (wire:model). Output is cleaned by App\Support\RichText on save. --}}
@props(['model', 'lang' => 'en', 'max' => 15000])
<div class="rte" data-rte wire:ignore.self lang="{{ $lang }}" data-upload="{{ route('admin.editor-image') }}">
    <textarea wire:model="{{ $model }}" class="rte-source" maxlength="{{ $max }}" hidden></textarea>
    <div wire:ignore>
        <div class="rte-toolbar" role="toolbar" aria-label="Formatting">
            <div class="rte-group">
                <button type="button" data-rte-block="p" title="Normal text" aria-label="Normal text"><span class="rte-txt">¶</span></button>
                <button type="button" data-rte-block="h2" title="Heading" aria-label="Heading"><span class="rte-txt">H2</span></button>
                <button type="button" data-rte-block="h3" title="Sub-heading" aria-label="Sub-heading"><span class="rte-txt">H3</span></button>
            </div>
            <div class="rte-group">
                <button type="button" data-rte-cmd="bold" title="Bold (Ctrl+B)" aria-label="Bold"><span class="rte-txt rte-b">B</span></button>
                <button type="button" data-rte-cmd="italic" title="Italic (Ctrl+I)" aria-label="Italic"><span class="rte-txt rte-i">I</span></button>
                <button type="button" data-rte-cmd="underline" title="Underline (Ctrl+U)" aria-label="Underline"><span class="rte-txt rte-u">U</span></button>
            </div>
            <div class="rte-group">
                <button type="button" data-rte-cmd="insertUnorderedList" title="Bullet list" aria-label="Bullet list"><x-icon name="list" /></button>
                <button type="button" data-rte-cmd="insertOrderedList" title="Numbered list" aria-label="Numbered list"><span class="rte-txt">1.</span></button>
                <button type="button" data-rte-block="blockquote" title="Quote" aria-label="Quote"><x-icon name="quote" /></button>
            </div>
            <div class="rte-group">
                <button type="button" data-rte-image title="Insert photo" aria-label="Insert photo"><x-icon name="image" /></button>
                <button type="button" data-rte-link title="Add link" aria-label="Add link"><x-icon name="external" /></button>
                <button type="button" data-rte-cmd="removeFormat" title="Clear formatting" aria-label="Clear formatting"><span class="rte-txt rte-clear">T</span></button>
            </div>
            <div class="rte-group rte-end">
                <button type="button" data-rte-cmd="undo" title="Undo (Ctrl+Z)" aria-label="Undo"><x-icon name="refresh" class="rte-undo" /></button>
                <button type="button" data-rte-cmd="redo" title="Redo (Ctrl+Y)" aria-label="Redo"><x-icon name="refresh" /></button>
            </div>
        </div>
        <div class="rte-linkbar" data-rte-linkbar hidden>
            <input type="url" placeholder="https://… or tel:01… or mailto:…" data-rte-url aria-label="Link address">
            <button type="button" class="btn btn-primary btn-sm" data-rte-link-apply>Apply</button>
            <button type="button" class="btn btn-ghost btn-sm" data-rte-link-remove>Remove link</button>
        </div>
        <input type="file" accept="image/jpeg,image/png,image/webp" data-rte-file hidden>
        <div class="rte-imgbar" data-rte-imgbar hidden>
            <span class="rte-imgbar-label">Photo</span>
            <div class="rte-group">
                <button type="button" data-rte-img-size="rt-sm" title="Small">S</button>
                <button type="button" data-rte-img-size="rt-md" title="Medium">M</button>
                <button type="button" data-rte-img-size="rt-lg" title="Large">L</button>
                <button type="button" data-rte-img-size="rt-full" title="Full width">Full</button>
            </div>
            <div class="rte-group">
                <button type="button" data-rte-img-align="rt-left" title="Left, text on the right"><x-icon name="align-left" /></button>
                <button type="button" data-rte-img-align="rt-center" title="Centre"><x-icon name="align-center" /></button>
                <button type="button" data-rte-img-align="rt-right" title="Right, text on the left"><x-icon name="align-right" /></button>
            </div>
            <button type="button" class="rte-img-remove" data-rte-img-remove title="Remove photo"><x-icon name="trash" /> Remove</button>
        </div>
        <div class="rte-uploading" data-rte-uploading hidden><span class="up-spin"></span> Uploading photo…</div>
        <div class="rte-area" contenteditable="true" role="textbox" aria-multiline="true" lang="{{ $lang }}" data-rte-area></div>
        <div class="rte-foot"><span data-rte-count>0</span> characters</div>
    </div>
</div>
