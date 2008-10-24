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
      <span class="keyword">const</span> <span class="type">{$item.type}</span> <span class="name">{$item.name}</span> = <span class="value">{$item.value}</span>
    </div>
  {/foreach}
  <h2><a name="attributes">Attributes</a></h2>
  {foreach from=$attributes item="item"}
    <div class="code">
      <pre class="comment">{$item.comment}</pre>
      <span class="keyword">{$item.readonly} attribute</span> <span class="type">{$item.type}</span> <span class="name">{$item.name}</span>
    </div>
  {/foreach}
  <h2><a name="methods">Methods</a></h2>
  {foreach from=$methods item="item"}
    <div class="code">
      <pre class="comment">{$item.comment}</pre>
      <span class="type">{$item.type}</span> <span class="name">{$item.name}</span>({$item.paramcount}{foreach from=$item.params item="param" name="paramlist"}<span class="type">{$param.type}</span> <span class="name">{$param.name}</span>{if not $smarty.foreach.paramlist.last}, {/if}{/foreach})
    </div>
  {/foreach}
</div>
{include file="footer.tpl"}
