{include file="header.tpl" title="Comparing platform `$platform1.name` to platform `$platform2.name`"}
<div id="overview">
<div class="block">
  <p><a href="#removed">Removed Interfaces ({$removed_interfaces|@count})</a></p>
  <p><a href="#added">Added Interfaces ({$added_interfaces|@count})</a></p>
  <p><a href="#modified">Modified Interfaces ({$modified_interfaces|@count})</a></p>
  <p><a href="#matching">Unchanged Interfaces ({$matching_interfaces|@count})</a></p>
</div>
</div>

<div class="body">
  <h1>Comparing platform
      <a href="{$ROOT}/platform/{$platform1.name}">{$platform1.name}</a> to platform
      <a href="{$ROOT}/platform/{$platform2.name}">{$platform2.name}</a></h1>
  <h2><a name="removed">Removed Interfaces ({$removed_interfaces|@count})</a></h2>
  <ul>
    {foreach from=$removed_interfaces item="item"}
      <li><a href="{$ROOT}/platform/{$platform1.name}/interface/{$item}">{$item}</a></li>
    {/foreach}
  </ul>
  <h2><a name="added">Added Interfaces ({$added_interfaces|@count})</a></h2>
  <ul>
    {foreach from=$added_interfaces item="item"}
      <li><a href="{$ROOT}/platform/{$platform2.name}/interface/{$item}">{$item}</a></li>
    {/foreach}
  </ul>
  <h2><a name="modified">Modified Interfaces ({$modified_interfaces|@count})</a></h2>
  <ul>
    {foreach from=$modified_interfaces item="item"}
      <li><a href="{$ROOT}/compare/interface/{$item}/{$platform1.name}/{$platform2.name}">{$item}</a></li>
    {/foreach}
  </ul>
  <h2><a name="matching">Unchanged Interfaces ({$matching_interfaces|@count})</a></h2>
  <ul>
    {foreach from=$matching_interfaces item="item"}
      <li><a href="{$ROOT}/platform/{$platform2}/interface/{$item}">{$item}</a></li>
    {/foreach}
  </ul>
</div>
{include file="footer.tpl"}
