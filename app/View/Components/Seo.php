<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Seo extends Component
{
    public array $meta;

    public function __construct(array $meta = [])
    {
        $this->meta = $meta;
    }

    public function render()
    {
        return view('components.seo');
    }
}
