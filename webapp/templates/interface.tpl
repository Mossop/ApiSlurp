{include file="header.tpl" title="$interface Interface (from $platform)"}
<div id="overview">
<div class="block">
<h2>Appears in</h2>
<ul>
{foreach from=$platforms item="item"}
  <li><a href="{$ROOT}/platform/{$item}">{$item}</a></li>
{/foreach}
</ul>
</div>

<div class="block">
<h2>Compare to</h2>
<ul>
{foreach from=$platforms item="item"}
  {if $item ne $platform}
    <li><a href="{$ROOT}/compare/interface/{$interface}/{$item}/{$platform}">{$item}</a></li>
  {/if}
{/foreach}
</ul>
</div>

<div class="block">
<ul>
  <li><a href="#constants">Constants ({$constants|@count})</a></li>
  <li><a href="#attributes">Attributes ({$attributes|@count})</a></li>
  <li><a href="#methods">Methods ({$methods|@count})</a></li>
</ul>
</div>
</div>

<div class="body">
  <h1>{$interface} Interface (from {$platform})</h1>
  <h2><a name="constants">Constants</a></h2>
  {foreach from=$constants item="item"}
    <div class="code">
      <pre class="comment">{$item.comment}</pre>
      {include file="includes/constant.tpl" constant=$item}
    </div>
  {/foreach}
  <h2><a name="attributes">Attributes</a></h2>
  {foreach from=$attributes item="item"}
    <div class="code">
      <pre class="comment">{$item.comment}</pre>
      {include file="includes/attribute.tpl" attribute=$item}
    </div>
  {/foreach}
  <h2><a name="methods">Methods</a></h2>
  {foreach from=$methods item="item"}
    <div class="code">
      <pre class="comment">{$item.comment}</pre>
      {include file="includes/method.tpl" method=$item}
    </div>
  {/foreach}
</div>
{include file="footer.tpl"}
