{include file="header.tpl" title="Comparing platform `$diff->left` to platform `$diff->right`"}
<div id="overview">
<div class="block">
  <p><a href="#removed">Removed Interfaces ({$diff->removed|@count})</a></p>
  <p><a href="#added">Added Interfaces ({$diff->added|@count})</a></p>
  <p><a href="#modified">Modified Interfaces ({$diff->modified|@count})</a></p>
  <p><a href="#matching">Unchanged Interfaces ({$diff->unchanged|@count})</a></p>
</div>
</div>

<div class="body">
  <h1>Comparing platform
      <a href="{$ROOT}/platform/{$diff->left->version}">{$diff->left}</a> to platform
      <a href="{$ROOT}/platform/{$diff->right->version}">{$diff->right}</a></h1>
  <h2><a name="removed">Removed Interfaces</a></h2>
  <ul class="interfacelist">
    {foreach from=$diff->removed item="item"}
      <li><a href="{$ROOT}/platform/{$diff->left->version}/interface/{$item->name}">{$item}</a></li>
    {/foreach}
  </ul>
  <h2><a name="added">Added Interfaces</a></h2>
  <ul class="interfacelist">
    {foreach from=$diff->added item="item"}
      <li><a href="{$ROOT}/platform/{$diff->right->version}/interface/{$item->name}">{$item}</a></li>
    {/foreach}
  </ul>
  <h2><a name="modified">Modified Interfaces</a></h2>
  <ul class="interfacelist">
    {foreach from=$diff->modified item="item"}
      <li><a href="{$ROOT}/compare/interface/{$item->name}/{$diff->left->version}/{$diff->right->version}">{$item}</a></li>
    {/foreach}
  </ul>
  <h2><a name="matching">Unchanged Interfaces</a></h2>
  <ul class="interfacelist">
    {foreach from=$diff->unchanged item="item"}
      <li><a href="{$ROOT}/platform/{$diff->right->version}/interface/{$item->name}">{$item}</a></li>
    {/foreach}
  </ul>
</div>
{include file="footer.tpl"}
