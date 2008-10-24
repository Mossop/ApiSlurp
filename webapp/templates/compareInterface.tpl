{include file="header.tpl" title="Comparing $interface between platform $platform1 and platform $platform2"}
<!--<div id="overview">
<div class="block">
<h2>Appears in</h2>
<ul>
{foreach from=$platforms item="item"}
  <li><a href="{$ROOT}/platform/{$item}/interface/{$interface.name}">{$item}</a></li>
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
</div>-->

<div class="body">
  <table class="diff">
    <tr>
      <td colspan="2"><h2>{$interface.name} Interface</h2></td>
    </tr>
    <tr>
      <td class="before"><h2><a href="{$ROOT}/platform/{$platform1}/interface/{$interface.name}">{$platform1}</a></h2></td>
      <td class="after"><h2><a href="{$ROOT}/platform/{$platform2}/interface/{$interface.name}">{$platform2}</a></h2></td>
    </tr>
    <tr class="commentrow">
      <td class="before">
        <pre class="comment">{$interface.old.comment}</pre>
      </td>
      <td class="after">
        <pre class="comment">{$interface.new.comment}</pre>
      </td>
    </tr>
    <tr class="signaturerow">
      <td class="before">
        {include file="includes/interface.tpl" interface=$interface.old}
      </td>
      <td class="after">
        {include file="includes/interface.tpl" interface=$interface.new}
      </td>
    </tr>
    <tr>
      <td colspan="2"><h2><a name="constants">Constants</a></h2></td>
    </tr>
    {foreach from=$constants item="item"}
      <tr class="commentrow {$item.state}">
        <td class="before">
          {if $item.state ne "added"}
            <pre class="comment">{$item.old.comment}</pre>
          {/if}
        </td>
        <td class="after">
          {if $item.state ne "removed"}
            <pre class="comment">{$item.new.comment}</pre>
          {/if}
        </td>
      </tr>
      <tr class="signaturerow {$item.state}">
        <td class="before">
          {if $item.state ne "added"}
            {include file="includes/constant.tpl" constant=$item.old}
          {/if}
        </td>
        <td class="after">
          {if $item.state ne "removed"}
            {include file="includes/constant.tpl" constant=$item.new}
          {/if}
        </td>
      </tr>
    {/foreach}

    <tr>
      <td colspan="2"><h2><a name="attributes">Attributes</a></h2></td>
    </tr>
    {foreach from=$attributes item="item"}
      <tr class="commentrow {$item.state}">
        <td class="before">
          {if $item.state ne "added"}
            <pre class="comment">{$item.old.comment}</pre>
          {/if}
        </td>
        <td class="after">
          {if $item.state ne "removed"}
            <pre class="comment">{$item.new.comment}</pre>
          {/if}
        </td>
      </tr>
      <tr class="signaturerow {$item.state}">
        <td class="before">
          {if $item.state ne "added"}
            {include file="includes/attribute.tpl" attribute=$item.old}
          {/if}
        </td>
        <td class="after">
          {if $item.state ne "removed"}
            {include file="includes/attribute.tpl" attribute=$item.new}
          {/if}
        </td>
      </tr>
    {/foreach}

    <tr>
      <td colspan="2"><h2><a name="methods">Methods</a></h2></td>
    </tr>
    {foreach from=$methods item="item"}
      <tr class="commentrow {$item.state}">
        <td class="before">
          {if $item.state ne "added"}
            <pre class="comment">{$item.old.comment}</pre>
          {/if}
        </td>
        <td class="after">
          {if $item.state ne "removed"}
            <pre class="comment">{$item.new.comment}</pre>
          {/if}
        </td>
      </tr>
      <tr class="signaturerow {$item.state}">
        <td class="before">
          {if $item.state ne "added"}
            {include file="includes/method.tpl" method=$item.old}
          {/if}
        </td>
        <td class="after">
          {if $item.state ne "removed"}
            {include file="includes/method.tpl" method=$item.new}
          {/if}
        </td>
      </tr>
    {/foreach}
  </table>
</div>
{include file="footer.tpl"}
