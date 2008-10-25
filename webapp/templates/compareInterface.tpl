{include file="header.tpl" title="Comparing `$interface.name` between platform `$platform1.name` and platform `$platform2.name`"}
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
<script type="text/javascript">
function toggleVisible() {ldelim}
  var checkbox = document.getElementById("hidesame");
  document.getElementById("diff").className = checkbox.checked ? "diff hidesame" : "diff";
{rdelim}
</script>

<div class="body">
  <table id="diff" class="diff">
    <tr>
      <td colspan="2">
        <h2>{$interface.name} Interface</h2>
        <div class="controls"><input type="checkbox" id="hidesame" name="hidesame" onchange="toggleVisible()"/><label for="hidesame">Hide unchanged items</label></div>
      </td>
    </tr>
    <tr>
      <td class="before"><h2><a href="{$ROOT}/platform/{$platform1.name}/interface/{$interface.name}">{$platform1.name}</a> - 
                             <a href="{$platform1.url}{$interface.old.path}">Source</a></h2></td>
      <td class="after"><h2><a href="{$ROOT}/platform/{$platform2.name}/interface/{$interface.name}">{$platform2.name}</a> - 
                             <a href="{$platform2.url}{$interface.new.path}">Source</a></h2></td>
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
