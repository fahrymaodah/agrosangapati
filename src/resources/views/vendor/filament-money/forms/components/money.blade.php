@php
    use Filament\Forms\Components\TextInput\Actions\HidePasswordAction;
    use Filament\Forms\Components\TextInput\Actions\ShowPasswordAction;

    $fieldWrapperView = $getFieldWrapperView();
    $extraAttributeBag = $getExtraAttributeBag();
    $id = $getId();
    $isConcealed = $isConcealed();
    $isDisabled = $isDisabled();
    $statePath = $getStatePath();

    $inputAttributes = $getExtraInputAttributeBag()
        ->merge([
            'autofocus' => $isAutofocused(),
            'disabled' => $isDisabled,
            'id' => $id,
            'inputmode' => $getInputMode(),
            'max' => (! $isConcealed) ? $getMaxValue() : null,
            'maxlength' => (! $isConcealed) ? $getMaxLength() : null,
            'min' => (! $isConcealed) ? $getMinValue() : null,
            'minlength' => (! $isConcealed) ? $getMinLength() : null,
            'readonly' => $isReadOnly(),
            'required' => $isRequired() && (! $isConcealed),
            'type' => "text",
            $applyStateBindingModifiers('wire:model') => $statePath,
        ], escape: false)
        ->class([
            'w-full pr-10',
        ]);

@endphp

<x-dynamic-component
    :component="$fieldWrapperView"
    :field="$field"
    :inline-label-vertical-alignment="\Filament\Support\Enums\VerticalAlignment::Center"
>
    <x-filament::input.wrapper
        :disabled="$isDisabled"
        :valid="! $errors->has($statePath)"
        :attributes="
            \Filament\Support\prepare_inherited_attributes($extraAttributeBag)
                ->class(['fi-fo-text-input'])
        "
    >
        <div x-data="{ 
            state: $wire.$entangle('{{ $statePath }}'),
            isFocused: false,
            displayVal: '',
            formatCurrency(value) {
                if (!value && value !== 0) return '';
                let numValue = parseFloat(String(value).replace(/[^0-9.-]+/g, ''));
                if (isNaN(numValue)) return '';
                return new Intl.NumberFormat('id-ID', { 
                    style: 'currency', 
                    currency: 'IDR',
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }).format(numValue);
            },
            updateDisplay() {
                if (this.isFocused) {
                    this.displayVal = this.state || '';
                } else {
                    this.displayVal = this.formatCurrency(this.state);
                }
            },
            init() {
                this.updateDisplay();
                this.$watch('state', () => {
                    if (!this.isFocused) {
                        this.updateDisplay();
                    }
                });
            }
        }">
            <input {{ $inputAttributes->class(['fi-input']) }}
                type="text"
                x-model="displayVal"
                x-on:focus="isFocused = true; displayVal = state || ''"
                x-on:blur="isFocused = false; state = parseFloat(String(displayVal).replace(/[^0-9.-]+/g, '')) || 0; updateDisplay()">
        </div>
    </x-filament::input.wrapper>
</x-dynamic-component>