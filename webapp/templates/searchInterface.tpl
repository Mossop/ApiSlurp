{include file="header.tpl" title="Interface search for $query"}
<div class="body">
  <h1>Search for {$query}</h1>
  <h2>Interfaces</h2>
  <ul>
    {foreach from=$interfaces item="item"}
      <li><a href="{$ROOT}/interface/{$item->name}">{$item}</a></li>
    {/foreach}
  </ul>
</div>
{include file="footer.tpl"}
