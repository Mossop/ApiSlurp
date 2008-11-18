<div class="constant signature {$class}">
  <a class="sourcelink" href="{$constant->sourceurl}" title="View source code"><img src="{$ROOT}/silk/script_link.png" /></a>
  <span class="keyword">const</span>
  <span class="type">
    {if $constant->typeisif}<a href="{$ROOT}/platform/{$constant->interface->platform->version|escape:'url'}/interface/{$constant->type|escape:'url'}">{/if}{$constant->type|escape}{if $constant->typeisif}</a>{/if}
  </span>
  <span class="name">{$constant->name|escape}</span> = <span class="value">{$constant->value|escape}</span>
</div>