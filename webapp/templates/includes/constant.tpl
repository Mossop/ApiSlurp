<div class="constant signature {$class}">
  <a class="sourcelink" href="{$constant->sourceurl}" title="View source code"><img src="{$ROOT}/silk/script_link.png" /></a>
  <span class="keyword">const</span>
  <span class="type">
    {if $constant->typeisif}<a href="{$ROOT}/platform/{$constant->interface->platform->version}/interface/{$constant->type}">{/if}{$constant->type}{if $constant->typeisif}</a>{/if}
  </span>
  <span class="name">{$constant->name}</span> = <span class="value">{$constant->value}</span>
</div>