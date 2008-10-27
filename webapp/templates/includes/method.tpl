<div class="method signature {$class}">
  <a class="sourcelink" href="{$method->sourceurl}" title="View source code"><img src="{$ROOT}/silk/script_link.png" /></a>
  {if $method->attributes}[<span class="keyword">{$method->attributes}</span>]{/if}
  <span class="type">
    {if $method->typeisif}<a href="{$ROOT}/platform/{$method->interface->platform->name}/interface/{$method->type}">{/if}{$method->type}{if $method->typeisif}</a>{/if}</span>
  <span class="name">{$method->name}</span>(<span class="paramlist">{foreach from=$method->params item="param" name="paramlist"}<span class="param">{if $param->attributes}[<span class="keyword">{$param->attributes}</span>] {/if}<span class="type">{if $param->typeisif}<a href="{$ROOT}/platform/{$param->method->interface->platform->name}/interface/{$param->type}">{/if}{$param->type}{if $param->typeisif}</a>{/if}</span> <span class="name">{$param->name}</span></span>{if not $smarty.foreach.paramlist.last}, {/if}{/foreach}</span>)
</div>