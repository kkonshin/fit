<?php

declare(strict_types=1);

namespace App\MoonShine\Fields;

use Closure;
use MoonShine\UI\Fields\Number;
use function is_string;

class FloatNumber extends Number
{
    public function __construct(
        Closure|string|null $label = null,
        ?string $column = null,
        ?Closure $formatted = null,
    ) {
        parent::__construct($label, $column, $formatted);

        $this->customAttributes([
            'lang' => 'ru',
            'inputmode' => 'decimal',
        ]);

        $this->changePreview(
            static fn (mixed $value): mixed => self::normalizeDecimalSeparator($value),
        );
    }

    /**
     * @param mixed $old
     * @return mixed
     */
    protected function resolveOldValue(mixed $old): mixed
    {
        return self::normalizeDecimalSeparator(parent::resolveOldValue($old));
    }

    /**
     * @return mixed
     */
    protected function resolveValue(): mixed
    {
        return self::normalizeDecimalSeparator(parent::resolveValue());
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private static function normalizeDecimalSeparator(mixed $value): mixed
    {
        return is_string($value)
            ? str_replace(',', '.', $value)
            : $value;
    }
}
