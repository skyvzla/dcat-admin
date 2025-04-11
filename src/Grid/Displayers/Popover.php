<?php

namespace Dcat\Admin\Grid\Displayers;

use Dcat\Admin\Admin;
use Illuminate\Contracts\Support\Renderable;

class Popover extends AbstractDisplayer
{
    protected string $style = '';

    protected static $js = [
        '@bootstrap-popover'
    ];

    protected string $placement = 'top';
    protected string $trigger = 'hover';

    public function placement(string $placement): static
    {
        $this->placement = $placement;

        return $this;
    }

    public function trigger(string $trigger): static
    {
        $this->trigger = $trigger;

        return $this;
    }

    public function style(string $style): static
    {
        $this->style = $style;

        return $this;
    }

    public function display($callback = null)
    {
        if (is_null($callback)) return $this->value;

        if ($callback instanceof \Closure) {
            $html = $callback->call($this->row, $this);

            if ($html instanceof Renderable) {
                $html = $html->render();
            }

            Admin::script("$('[data-toggle=\"popover\"]').popover()");

            return <<<HTML
<a href="javascript:void(0);" style="{$this->style}" data-html="true" data-toggle="popover" data-trigger="{$this->trigger}" data-placement="{$this->placement}" data-content="$html">{$this->value}</a>
HTML
                ;
        }
    }
}
