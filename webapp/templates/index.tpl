{include file="header.tpl" title="API Index"}
<div class="body">
  <h1>API Index</h1>
  <h2>All Platforms</h2>
  <ul>
    {foreach from=$platforms item="item"}
      <li><a href="{$ROOT}/platform/{$item->version}">{$item}</a></li>
    {/foreach}
  </ul>
  <h2>All Interfaces</h2>
  <ul>
    {foreach from=$interfaces item="item"}
      <li>
        <a href="{$ROOT}/interface/{$item->name}">{$item}</a>
        {if $item->oldest == $item->newest}
          <span class="variants">({$item->newest->platform})</span>
        {else}
          <span class="variants">({$item->oldest->platform} - {$item->newest->platform})</span>
        {/if}
      </li>
    {/foreach}
  </ul>
</div>
{include file="footer.tpl"}
