{include file="header.tpl" title="API Index"}
<div class="body">
<h1>API Index</h1>
<h2>Platforms</h2>
<ul>
{foreach from=$platforms item="item"}
<li><a href="{$ROOT}/platform/{$item}">{$item}</a></li>
{/foreach}
</ul>
<h2>Interfaces</h2>
<ul>
{foreach from=$interfaces item="item"}
<li><a href="{$ROOT}/interface/{$item}">{$item}</a></li>
{/foreach}
</ul>
</div>
{include file="footer.tpl"}
