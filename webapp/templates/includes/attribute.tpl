<div class="attribute signature {$class}">
  <a class="sourcelink" href="{$attribute->sourceurl}" title="View source code"><img src="{$ROOT}/silk/script_link.png" /></a>
  {include file="includes/idlattributes.tpl" attributes=$attribute->attributes}
  <span class="keyword">{if $attribute->readonly}readonly {/if}attribute</span>
  <span class="type">
    {if $attribute->typeisif}<a href="{$ROOT}/platform/{$attribute->interface->platform->version|escape:'url'}/interface/{$attribute->type|escape:'url'}">{/if}{$attribute->type|escape}{if $attribute->typeisif}</a>{/if}
  </span>
  <span class="name">{$attribute->name|escape}</span>
</div>