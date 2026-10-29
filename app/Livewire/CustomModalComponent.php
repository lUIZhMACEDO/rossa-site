<?php

namespace App\Livewire;

use InvalidArgumentException;
use LivewireUI\Modal\ModalComponent;

abstract class CustomModalComponent extends ModalComponent
{
    public static function modalMaxWidthClass(): string
    {
        $maxWidth = static::modalMaxWidth();
        $maxWidths = [
            'sm' => 'modal-sm',
            'md' => '',
            'lg' => 'modal-lg',
            'xl' => 'modal-xl',
            '2xl' => 'modal-xxl',
        ];

        if (! array_key_exists($maxWidth, $maxWidths)) {
            throw new InvalidArgumentException(
                sprintf('Modal max width [%s] is invalid. The width must be one of the following [%s].',
                    $maxWidth, implode(', ', array_keys($maxWidths))),
            );
        }

        return $maxWidths[$maxWidth];
    }
}
