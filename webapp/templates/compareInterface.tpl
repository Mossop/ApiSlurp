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
<h1>Comparing {$interface} between platform
    <a href="{$ROOT}/platform/{$platform1}">{$platform1}</a> and platform
    <a href="{$ROOT}/platform/{$platform2}">{$platform2}</a></h1>
<h2>Constants</h2>
<ul class="code">
{foreach from=$constants item="item"}
{if $item.state eq "added"}
<li class="added"><a name="{$item.name}">{$item.new.text}</a></li>
{elseif $item.state eq "removed"}
<li class="removed"><a name="{$item.name}">{$item.old.text}</a></li>
{elseif $item.state eq "modified"}
<li class="modified"><a name="{$item.name}">{$item.old.text}</a><br />
                     <a name="{$item.name}">{$item.new.text}</a></li>
{else}
<li class="matching"><a name="{$item.name}">{$item.old.text}</a></li>
{/if}
{/foreach}
</ul>
<h2>Attributes</h2>
<ul class="code">
{foreach from=$attributes item="item"}
{if $item.state eq "added"}
<li class="added"><a name="{$item.name}">{$item.new.text}</a></li>
{elseif $item.state eq "removed"}
<li class="removed"><a name="{$item.name}">{$item.old.text}</a></li>
{elseif $item.state eq "modified"}
<li class="modified"><a name="{$item.name}">{$item.old.text}</a><br />
                     {$item.new.text}</a></li>
{else}
<li class="matching"><a name="{$item.name}">{$item.old.text}</a></li>
{/if}
{/foreach}
</ul>
<h2>Methods</h2>
<ul class="code">
{foreach from=$methods item="item"}
{if $item.state eq "added"}
<li class="added"><a name="{$item.name}">{$item.new.text}</a></li>
{elseif $item.state eq "removed"}
<li class="removed"><a name="{$item.name}">{$item.old.text}</a></li>
{elseif $item.state eq "modified"}
<li class="modified"><a name="{$item.name}">{$item.old.text}</a><br />
                     <a name="{$item.name}">{$item.new.text}</a></li>
{else}
<li class="matching"><a name="{$item.name}">{$item.old.text}</a></li>
{/if}
{/foreach}
</ul>
</div>
{include file="footer.tpl"}
