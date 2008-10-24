{include file="header.tpl" title="Platform $platform Index"}
<div id="overview">
<div class="block">
<h2>Other Platforms</h2>
<ul>
{foreach from=$platforms item="item"}
{if $item ne $platform}
<li><a href="{$ROOT}/platform/{$item}">{$item}</a></li>
{/if}
{/foreach}
</ul>
</div>

<div class="block">
<h2>Compare to</h2>
<ul>
{foreach from=$platforms item="item"}
{if $item ne $platform}
<li><a href="{$ROOT}/compare/platform/{$item}/{$platform}">{$item}</a></li>
{/if}
{/foreach}
</ul>
</div>
</div>

<div class="body">
<h1>Platform {$platform} Index</h1>
<h2>Interfaces</h2>
<ul>
{foreach from=$interfaces item="item"}
<li><a href="{$ROOT}/platform/{$platform}/interface/{$item}">{$item}</a></li>
{/foreach}
</ul>
</div>
{include file="footer.tpl"}
