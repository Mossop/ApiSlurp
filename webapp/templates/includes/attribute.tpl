<div class="attribute signature {$class}">
  <a class="sourcelink" href="{$attribute->sourceurl}" title="View source code"><img src="{$ROOT}/silk/script_link.png" /></a>
  {include file="includes/idlattributes.tpl" attributes=$attribute->attributes}
  <span class="keyword">{if $attribute->readonly}readonly {/if}attribute</span>
  <span class="type">
    {if $attribute->typeisif}<a href="{$ROOT}/platform/{$attribute->interface->platform->version}/interface/{$attribute->type}">{/if}{$attribute->type}{if $attribute->typeisif}</a>{/if}
  </span>
  <span class="name">{$attribute->name}</span>
</div>