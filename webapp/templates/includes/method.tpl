<div class="method signature {$class}">
  <a class="sourcelink" href="{$method->sourceurl}" title="View source code"><img src="{$ROOT}/silk/script_link.png" /></a>
  {include file="includes/idlattributes.tpl" attributes=$method->attributes}
  <span class="type">
    {if $method->typeisif}<a href="{$ROOT}/platform/{$method->interface->platform->version|escape:'url'}/interface/{$method->type|escape:'url'}">{/if}{$method->type|escape}{if $method->typeisif}</a>{/if}</span>
  <span class="name">{$method->name|escape}</span>(<span class="paramlist">{foreach from=$method->params item="param" name="paramlist"}<span class="param">{include file="includes/idlattributes.tpl" attributes=$param->attributes}<span class="keyword">{$param->direction}</span> <span class="type">{if $param->typeisif}<a href="{$ROOT}/platform/{$param->method->interface->platform->version|escape:'url'}/interface/{$param->type|escape:'url'}">{/if}{$param->type|escape}{if $param->typeisif}</a>{/if}</span> <span class="name">{$param->name|escape}</span></span>{if not $smarty.foreach.paramlist.last}, {/if}{/foreach}</span>)
</div>