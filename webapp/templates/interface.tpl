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
<h2>Constants</h2>
<ul>
{foreach from=$constants item="item"}
<li><a href="#{$item.name}">{$item.name}</a></li>
{/foreach}
</ul>
</div>

<div class="block">
<h2>Attributes</h2>
<ul>
{foreach from=$attributes item="item"}
<li><a href="#{$item.name}">{$item.name}</a></li>
{/foreach}
</ul>
</div>

<div class="block">
<h2>Methods</h2>
<ul>
{foreach from=$methods item="item"}
<li><a href="#{$item.name}">{$item.name}</a></li>
{/foreach}
</ul>
</div>
</div>

<div class="body">
<h1>{$interface} Interface (from {$platform})</h1>
<h2>Constants</h2>
<ul class="code">
{foreach from=$constants item="item"}
<li><pre>  {$item.comment}
<a name="{$item.name}">const {$item.type} {$item.name} = {$item.value}</a></pre></li>
{/foreach}
</ul>
<h2>Attributes</h2>
<ul class="code">
{foreach from=$attributes item="item"}
<li><pre>  {$item.comment}
<a name="{$item.name}">{$item.readonly} attribute {$item.type} {$item.name}</a></pre></li>
{/foreach}
</ul>
<h2>Methods</h2>
<ul class="code">
{foreach from=$methods item="item"}
<li><pre>  {$item.comment}
<a name="{$item.name}">{$item.type} {$item.name} ()</a></pre></li>
{/foreach}
</ul>
</div>
{include file="footer.tpl"}
