<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Error extends Component
{
    public function __construct(
        public readonly ?string $field = null,
        public readonly ?string $message = null
    ) {}

    public function render(): View
    {
        return view('components.error');
    }
}
