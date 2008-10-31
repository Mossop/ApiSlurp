<div class="method signature {$class}">
  <a class="sourcelink" href="{$method->sourceurl}" title="View source code"><img src="{$ROOT}/silk/script_link.png" /></a>
  {include file="includes/idlattributes.tpl" attributes=$method->attributes}
  <span class="type">
    {if $method->typeisif}<a href="{$ROOT}/platform/{$method->interface->platform->version}/interface/{$method->type}">{/if}{$method->type}{if $method->typeisif}</a>{/if}</span>
  <span class="name">{$method->name}</span>(<span class="paramlist">{foreach from=$method->params item="param" name="paramlist"}<span class="param">{include file="includes/idlattributes.tpl" attributes=$param->attributes}<span class="keyword">{$param->direction}</span> <span class="type">{if $param->typeisif}<a href="{$ROOT}/platform/{$param->method->interface->platform->version}/interface/{$param->type}">{/if}{$param->type}{if $param->typeisif}</a>{/if}</span> <span class="name">{$param->name}</span></span>{if not $smarty.foreach.paramlist.last}, {/if}{/foreach}</span>)
</div>