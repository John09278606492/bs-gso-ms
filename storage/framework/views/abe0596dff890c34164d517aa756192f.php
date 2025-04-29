<?php
    $name = $getId();
    $isPrefixInline = $isPrefixInline();
    $isSuffixInline = $isSuffixInline();
    $prefixActions = $getPrefixActions();
    $prefixIcon = $getPrefixIcon();
    $prefixLabel = $getPrefixLabel();
    $suffixActions = $getSuffixActions();
    $suffixIcon = $getSuffixIcon();
    $suffixLabel = $getSuffixLabel();
    $statePath = $getStatePath();
?>

<?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => $getFieldWrapperView()] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\DynamicComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['field' => $field]); ?>
    <div
        x-ignore
        <?php if(\Filament\Support\Facades\FilamentView::hasSpaMode()): ?>
            ax-load="visible || event (ax-modal-opened)"
        <?php else: ?>
            ax-load
        <?php endif; ?>

        ax-load-src="<?php echo e(\Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('dateRangeComponent', 'malzariey/filament-daterangepicker-filter')); ?>"
        x-ref="container"
        x-data="dateRangeComponent({
                state: <?php if ((object) ($statePath) instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e($statePath->value()); ?>')<?php echo e($statePath->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e($statePath); ?>')<?php endif; ?>,
                name: <?php echo \Illuminate\Support\Js::from($name)->toHtml() ?>,
                alwaysShowCalendars: <?php echo \Illuminate\Support\Js::from($isAlwaysShowCalendar())->toHtml() ?>,
                autoApply: <?php echo \Illuminate\Support\Js::from($getAutoApply())->toHtml() ?>,
                linkedCalendars: <?php echo \Illuminate\Support\Js::from($getLinkedCalendars())->toHtml() ?>,
                singleCalendar: <?php echo \Illuminate\Support\Js::from($getSingleCalendar())->toHtml() ?>,
                autoUpdateInput: false,
                startDate: <?php echo \Illuminate\Support\Js::from($getStartDate())->toHtml() ?>,
                endDate: <?php echo \Illuminate\Support\Js::from($getEndDate())->toHtml() ?>,
                maxDate: <?php echo \Illuminate\Support\Js::from($getMaxDate())->toHtml() ?>,
                minDate: <?php echo \Illuminate\Support\Js::from($getMinDate())->toHtml() ?>,
                timePicker: <?php echo \Illuminate\Support\Js::from($getTimePicker())->toHtml() ?>,
                timePicker24: <?php echo \Illuminate\Support\Js::from($getTimePicker24())->toHtml() ?>,
                timePickerSecond: <?php echo \Illuminate\Support\Js::from($getTimePickerSecond())->toHtml() ?>,
                timePickerIncrement: <?php echo \Illuminate\Support\Js::from($getTimePickerIncrement())->toHtml() ?>,
                displayFormat: <?php echo \Illuminate\Support\Js::from($getDisplayFormat())->toHtml() ?>,
                disableCustomRange: <?php echo \Illuminate\Support\Js::from($getDisableCustomRange())->toHtml() ?>,
                opens: '<?php echo e($getOpens()); ?>',
                drops: '<?php echo e($getDrops()); ?>',
                applyLabel: '<?php echo __('filament-daterangepicker-filter::message.apply'); ?>',
                cancelLabel: '<?php echo __('filament-daterangepicker-filter::message.cancel'); ?>',
                fromLabel: '<?php echo __('filament-daterangepicker-filter::message.from'); ?>',
                toLabel: '<?php echo __('filament-daterangepicker-filter::message.to'); ?>',
                customRangeLabel: '<?php echo __('filament-daterangepicker-filter::message.custom'); ?>',
                january: '<?php echo __('filament-daterangepicker-filter::message.january'); ?>',
                february: '<?php echo __('filament-daterangepicker-filter::message.february'); ?>',
                march: '<?php echo __('filament-daterangepicker-filter::message.march'); ?>',
                april: '<?php echo __('filament-daterangepicker-filter::message.april'); ?>',
                may: '<?php echo __('filament-daterangepicker-filter::message.may'); ?>',
                june: '<?php echo __('filament-daterangepicker-filter::message.june'); ?>',
                july: '<?php echo __('filament-daterangepicker-filter::message.july'); ?>',
                august: '<?php echo __('filament-daterangepicker-filter::message.august'); ?>',
                september: '<?php echo __('filament-daterangepicker-filter::message.september'); ?>',
                october: '<?php echo __('filament-daterangepicker-filter::message.october'); ?>',
                november: '<?php echo __('filament-daterangepicker-filter::message.november'); ?>',
                december: '<?php echo __('filament-daterangepicker-filter::message.december'); ?>',
                sunday: '<?php echo __('filament-daterangepicker-filter::message.su'); ?>',
                monday: '<?php echo __('filament-daterangepicker-filter::message.mo'); ?>',
                tuesday: '<?php echo __('filament-daterangepicker-filter::message.tu'); ?>',
                wednesday: '<?php echo __('filament-daterangepicker-filter::message.we'); ?>',
                thursday: '<?php echo __('filament-daterangepicker-filter::message.th'); ?>',
                friday: '<?php echo __('filament-daterangepicker-filter::message.fr'); ?>',
                saturday: '<?php echo __('filament-daterangepicker-filter::message.sa'); ?>',
                firstDay: <?php echo \Illuminate\Support\Js::from($getFirstDayOfWeek())->toHtml() ?>,
                ranges: <?php echo \Illuminate\Support\Js::from($getRanges())->toHtml() ?>,
                maxSpan: <?php echo \Illuminate\Support\Js::from($getMaxSpan())->toHtml() ?>,
                disableRange : <?php echo \Illuminate\Support\Js::from($getDisableRanges())->toHtml() ?>,
                separator: <?php echo \Illuminate\Support\Js::from($getSeparator())->toHtml() ?>,
                useRangeLabels: <?php echo \Illuminate\Support\Js::from($getUseRangeLabels())->toHtml() ?>,
                disabledDates: <?php echo \Illuminate\Support\Js::from($getDisabledDates())->toHtml() ?>,
                handleValueChangeUsing: (value, name) => {
                    if (name == '<?php echo e($name); ?>') {
                        window.Livewire.find('<?php echo e($_instance->getId()); ?>').set('<?php echo $statePath; ?>', value);
                    }
                },
                showWeekNumbers: <?php echo \Illuminate\Support\Js::from($getShowWeekNumbers())->toHtml() ?>,
                showISOWeekNumbers: <?php echo \Illuminate\Support\Js::from($getShowISOWeekNumbers())->toHtml() ?>,
                weekLabel: '<?php echo __('filament-daterangepicker-filter::message.weekLabel'); ?>',
                showDropdowns: <?php echo \Illuminate\Support\Js::from($getShowDropdowns())->toHtml() ?>,
                minYear: <?php echo \Illuminate\Support\Js::from($getMinYear())->toHtml() ?>,
                maxYear: <?php echo \Illuminate\Support\Js::from($getMaxYear())->toHtml() ?>,
                timezone: <?php echo \Illuminate\Support\Js::from($getTimezone())->toHtml() ?>
            })"
        id="date-range-picker-<?php echo e($name); ?>"
        wire:key="date-range-picker-<?php echo e($name); ?>"
        x-on:keydown.esc="isOpen() && $event.stopPropagation()"

        <?php echo e($attributes->merge($getExtraAttributes() , escape: false)->class(['filament-forms-date-time-picker-component relative'])); ?>

        <?php echo e($getExtraAlpineAttributeBag()); ?>

    >
        <?php if (isset($component)) { $__componentOriginal505efd9768415fdb4543e8c564dad437 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal505efd9768415fdb4543e8c564dad437 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.input.wrapper','data' => ['disabled' => $isDisabled,'inlinePrefix' => $isPrefixInline,'inlineSuffix' => $isSuffixInline,'prefix' => $prefixLabel,'prefixActions' => $prefixActions,'prefixIcon' => $prefixIcon,'suffix' => $suffixLabel,'suffixActions' => $suffixActions,'suffixIcon' => $suffixIcon,'valid' => !$errors->has($statePath),'class' => 'fi-fo-text-input','attributes' => \Filament\Support\prepare_inherited_attributes($getExtraAttributeBag())->class([
                    'overflow-hidden',
                ])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::input.wrapper'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['disabled' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isDisabled),'inline-prefix' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isPrefixInline),'inline-suffix' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isSuffixInline),'prefix' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($prefixLabel),'prefix-actions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($prefixActions),'prefix-icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($prefixIcon),'suffix' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($suffixLabel),'suffix-actions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($suffixActions),'suffix-icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($suffixIcon),'valid' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(!$errors->has($statePath)),'class' => 'fi-fo-text-input','attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Filament\Support\prepare_inherited_attributes($getExtraAttributeBag())->class([
                    'overflow-hidden',
                ]))]); ?>
            <button
                x-ref="button"
                aria-label="<?php echo e($getPlaceholder()); ?>"
                dusk="filament.forms.<?php echo e($statePath); ?>.open"
                type="button"
                tabindex="-1"
                class="w-full"
            >
                <div
                    class="relative inline-block w-full bg-white dark:bg-white/5"
                    id="<?php echo e($name); ?>.container"
                    wire:key="<?php echo e($name); ?>.container"
                >
                    <?php if (isset($component)) { $__componentOriginal9ad6b66c56a2379ee0ba04e1e358c61e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ad6b66c56a2379ee0ba04e1e358c61e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.input.index','data' => ['xRef' => 'daterange','wire:key' => ''.e($this->id()).'.'.e($statePath).'.'.e($field::class).'.display-text','attributes' => \Filament\Support\prepare_inherited_attributes(
                                $getExtraInputAttributeBag(),
                            )->merge([
                                'id' => $getId(),
                                'name' => $name,
                                'disabled' => $isDisabled(),
                                'placeholder' => $getPlaceholder(),
                                'autofocus' => $isAutofocused(),
                                'required' => $isRequired(),
                                'readonly' => true,
                                'type' => 'text',
                            ], escape: false)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-ref' => 'daterange','wire:key' => ''.e($this->id()).'.'.e($statePath).'.'.e($field::class).'.display-text','attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Filament\Support\prepare_inherited_attributes(
                                $getExtraInputAttributeBag(),
                            )->merge([
                                'id' => $getId(),
                                'name' => $name,
                                'disabled' => $isDisabled(),
                                'placeholder' => $getPlaceholder(),
                                'autofocus' => $isAutofocused(),
                                'required' => $isRequired(),
                                'readonly' => true,
                                'type' => 'text',
                            ], escape: false))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ad6b66c56a2379ee0ba04e1e358c61e)): ?>
<?php $attributes = $__attributesOriginal9ad6b66c56a2379ee0ba04e1e358c61e; ?>
<?php unset($__attributesOriginal9ad6b66c56a2379ee0ba04e1e358c61e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ad6b66c56a2379ee0ba04e1e358c61e)): ?>
<?php $component = $__componentOriginal9ad6b66c56a2379ee0ba04e1e358c61e; ?>
<?php unset($__componentOriginal9ad6b66c56a2379ee0ba04e1e358c61e); ?>
<?php endif; ?>
                </div>
            </button>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal505efd9768415fdb4543e8c564dad437)): ?>
<?php $attributes = $__attributesOriginal505efd9768415fdb4543e8c564dad437; ?>
<?php unset($__attributesOriginal505efd9768415fdb4543e8c564dad437); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal505efd9768415fdb4543e8c564dad437)): ?>
<?php $component = $__componentOriginal505efd9768415fdb4543e8c564dad437; ?>
<?php unset($__componentOriginal505efd9768415fdb4543e8c564dad437); ?>
<?php endif; ?>
    </div>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\public_html - Copy\vendor\malzariey\filament-daterangepicker-filter\src\/../resources/views/date-range-picker.blade.php ENDPATH**/ ?>