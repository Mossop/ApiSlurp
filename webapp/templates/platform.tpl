{include file="header.tpl" title="Platform $platform Index"}
<div id="overview">
<div class="block">
  <h2>Other Platforms</h2>
  <ul>
    {foreach from=$platforms item="item"}
      {if $item->id ne $platform->id}
        <li><a href="{$ROOT}/platform/{$item->version}">{$item}</a></li>
      {/if}
    {/foreach}
  </ul>
</div>

<div class="block">
  <h2>Compare to</h2>
  <ul>
    {foreach from=$platforms item="item"}
      {if $item->id ne $platform->id}
        <li><a href="{$ROOT}/compare/platform/{$item->version}/{$platform->version}">{$item}</a></li>
      {/if}
    {/foreach}
  </ul>
  </div>
</div>

<div class="body">
  <h1>Platform {$platform} Index</h1>
  <h2>Interfaces</h2>
  <ul>
    {foreach from=$interfaces item="item"}
      <li><a href="{$ROOT}/platform/{$platform->version}/interface/{$item->name}">{$item}</a></li>
    {/foreach}
  </ul>
</div>
{include file="footer.tpl"}
