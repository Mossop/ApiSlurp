{include file="header.tpl" title="$interface Interface (from $platform)"}
<div id="overview">
<div class="block">
  <h2>Appears in:</h2>
  <ul>
    {foreach from=$interface->versions item="item"}
      <li><a href="{$ROOT}/platform/{$item->platform->version}/interface/{$interface->name}">{$item->platform}</a></li>
    {/foreach}
  </ul>
</div>

<div class="block">
  <h2>Different in:</h2>
  <ul>
    {foreach from=$interface->versions item="item"}
      {if $item->hash ne $interface->hash}
        <li><a href="{$ROOT}/compare/interface/{$interface->name}/{$item->platform->version}/{$interface->platform->version}">{$item->platform}</a></li>
      {/if}
    {/foreach}
  </ul>
</div>

<div class="block">
  <ul>
    <li><a href="#constants">Constants ({$interface->constants|@count})</a></li>
    <li><a href="#attributes">Attributes ({$interface->attributes|@count})</a></li>
    <li><a href="#methods">Methods ({$interface->methods|@count})</a></li>
  </ul>
</div>
</div>

<div class="body">
  <h1>{$interface} Interface (from platform <a href="{$ROOT}/platform/{$platform->version}">{$platform}</a>) - <a href="{$interface->sourceurl}">Source</a></h1>
  <div class="code">
    <pre class="comment">{$interface->comment}</pre>
    {include file="includes/interface.tpl" interface=$interface}
  </div>
  <h2><a name="constants">Constants</a></h2>
  {foreach from=$interface->constants item="item"}
    <div class="code">
      <pre class="comment">{$item->comment}</pre>
      {include file="includes/constant.tpl" constant=$item}
    </div>
  {/foreach}
  <h2><a name="attributes">Attributes</a></h2>
  {foreach from=$interface->attributes item="item"}
    <div class="code">
      <pre class="comment">{$item->comment}</pre>
      {include file="includes/attribute.tpl" attribute=$item}
    </div>
  {/foreach}
  <h2><a name="methods">Methods</a></h2>
  {foreach from=$interface->methods item="item"}
    <div class="code">
      <pre class="comment">{$item->comment}</pre>
      {include file="includes/method.tpl" method=$item}
    </div>
  {/foreach}
</div>
{include file="footer.tpl"}
