{include file="header.tpl" title="API Index"}
<div class="body">
  <h1>API Index</h1>
  <h2>All Platforms</h2>
  <ul>
    {foreach from=$platforms item="item"}
      <li><a href="{$ROOT}/platform/{$item->name}">{$item}</a></li>
    {/foreach}
  </ul>
  <h2>All Interfaces</h2>
  <ul>
    {foreach from=$interfaces item="item"}
      <li>
        <a href="{$ROOT}/interface/{$item->name}">{$item}</a> (
        {foreach from=$item->versions item="version"}
          <a href="{$ROOT}/platform/{$version->platform->name}/interface/{$item->name}">{$version->platform}</a>
        {/foreach})
      </li>
    {/foreach}
  </ul>
</div>
{include file="footer.tpl"}
