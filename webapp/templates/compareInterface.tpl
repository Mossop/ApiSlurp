{include file="header.tpl" title="Comparing $interface between platform $platform1 and platform $platform2"}
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
<ul>
  <li><a href="#constants">Constants ({$constants|@count})</a></li>
  <li><a href="#attributes">Attributes ({$attributes|@count})</a></li>
  <li><a href="#methods">Methods ({$methods|@count})</a></li>
</ul>
</div>
</div>

<div class="body">
  <h1>Comparing {$interface} between platform
      <a href="{$ROOT}/platform/{$platform1}">{$platform1}</a> and platform
      <a href="{$ROOT}/platform/{$platform2}">{$platform2}</a></h1>

  <h2><a name="constants">Constants</a></h2>
  {foreach from=$constants item="item"}
    <div class="code {$item.state}">
      {if $item.state eq "removed"}
        <pre class="comment">{$item.old.comment}</pre>
        {include file="includes/constant.tpl" constant=$item.old}
      {else}
        <pre class="comment">{$item.new.comment}</pre>
        {if $item.state eq "modified"}
          {include file="includes/constant.tpl" constant=$item.old}
        {/if}
        {include file="includes/constant.tpl" constant=$item.new}
      {/if}
    </div>
  {/foreach}

  <h2><a name="attributes">Attributes</a></h2>
  {foreach from=$attributes item="item"}
    <div class="code {$item.state}">
      {if $item.state eq "removed"}
        <pre class="comment">{$item.old.comment}</pre>
        {include file="includes/attribute.tpl" attribute=$item.old}
      {else}
        <pre class="comment">{$item.new.comment}</pre>
        {if $item.state eq "modified"}
          {include file="includes/attribute.tpl" attribute=$item.old}
        {/if}
        {include file="includes/attribute.tpl" attribute=$item.new}
      {/if}
    </div>
  {/foreach}

  <h2><a name="methods">Methods</a></h2>
  {foreach from=$attributes item="item"}
    <div class="code {$item.state}">
      {if $item.state eq "removed"}
        <pre class="comment">{$item.old.comment}</pre>
        {include file="includes/method.tpl" method=$item.old}
      {else}
        <pre class="comment">{$item.new.comment}</pre>
        {if $item.state eq "modified"}
          {include file="includes/method.tpl" method=$item.old}
        {/if}
        {include file="includes/method.tpl" method=$item.new}
      {/if}
    </div>
  {/foreach}
</div>
{include file="footer.tpl"}
