{include file="header.tpl" title="Interface search for $query"}
<div id="content">
<div class="body">
  <h1>Interface search for "{$query}"</h1>
  <ul>
    {foreach from=$interfaces item="item"}
      <li><a href="{$ROOT}/interface/{$item->name}">{$item}</a></li>
    {/foreach}
  </ul>
</div>
</div>
{include file="footer.tpl"}
