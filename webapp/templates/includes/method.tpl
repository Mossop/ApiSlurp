<div class="method signature {$class}"><span class="type">{$method->type}</span> <span class="name">{$method->name}</span>({foreach from=$method->params item="param" name="paramlist"}<span class="type">{$param->type}</span> <span class="name">{$param->name}</span>{if not $smarty.foreach.paramlist.last}, {/if}{/foreach})</div>