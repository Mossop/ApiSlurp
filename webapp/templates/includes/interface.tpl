<div class="interface signature {$class}">
  <div class="attributes">[{if $interface->scriptable}<span class="keyword">scriptable</span>, {/if}{if $interface->noscript}<span class="keyword">noscript</span>, {/if}{if $interface->function}<span class="keyword">function</span>, {/if}<span class="keyword">uuid</span>(<span class="value">{$interface->iid}</span>)]</div>
  <div class="header"><span class="keyword">interface</span> <span class="type">{$interface->name}</span> : <span class="type"><a href="{$ROOT}/platform/{$interface->platform->version}/interface/{$interface->base}">{$interface->base}</a></span></div>
</div>