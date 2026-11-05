@props([
    'title' => 'Aiuto',
    'text' => '',
    'placement' => 'top',
])

<button
    type="button"
    class="btn btn-link p-0 border-0 text-info-emphasis align-middle ui-help-trigger"
    data-ui="help-popover"
    data-bs-trigger="click focus"
    data-bs-placement="{{ $placement }}"
    data-bs-custom-class="ui-help-popover"
    data-bs-title="{{ $title }}"
    data-bs-content="{{ $text }}"
    aria-label="{{ $title }}"
>
    <i class="ri-question-line fs-16 align-middle"></i>
</button>
