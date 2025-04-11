<?php

namespace Dcat\Admin\Grid\Displayers;

use Dcat\Admin\Admin;
use Illuminate\Contracts\Support\Renderable;

/**
 * Class Copyable.
 *
 * @see https://codepen.io/shaikmaqsood/pen/XmydxJ
 */
class Copyable extends AbstractDisplayer
{
    protected function addScript()
    {
        $script = <<<'JS'
$('.grid-column-copyable').off('click').on('click', function (e) {
    let content = $(this).data('content');

    if (navigator.clipboard) {
        navigator.clipboard.writeText(content);
    } else {
        const textArea = document.createElement('textarea');
        textArea.value = content;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
        } catch (err) {
        }
        document.body.removeChild(textArea);
    }

    $(this).tooltip('show');

    setTimeout(() => {
        $(this).tooltip('hide');
    }, 1000)
});
JS;
        Admin::script($script);
    }

    public function display($callback = null)
    {
        $this->addScript();

        $content = null;
        if ($callback instanceof \Closure) {
            $content = $callback->call($this->row);
            if ($content instanceof Renderable) {
                $content = $content->render();
            }
        }

//        $this->value = Helper::htmlEntityEncode($this->value);
        $content = $content ?: $this->value;

        $html = <<<HTML
{$this->value}
&nbsp;
<a href="javascript:void(0);" class="grid-column-copyable text-muted" data-content="{$content}" title="{$this->trans('copied')}" data-placement="bottom" data-trigger="click">
    <i class="fa fa-copy"></i>
</a>
HTML;

        return $this->value === '' || $this->value === null ? $this->value : $html;
    }
}
