{include file="header.tpl" title="Comparing `$diff->right` between platform `$diff->left->platform` and platform `$diff->right->platform`"}
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
        <h2>{$diff->right} Interface</h2>
        <div class="controls"><input type="checkbox" id="hidesame" name="hidesame" onchange="toggleVisible()"/><label for="hidesame">Hide unchanged items</label></div>
      </td>
    </tr>
    <tr>
      <td class="before"><h2><a href="{$ROOT}/platform/{$diff->left->platform->version}/interface/{$diff->left->name}">{$diff->left->platform}</a> - 
                             <a href="{$diff->left->sourceurl}">Source</a></h2></td>
      <td class="after"><h2><a href="{$ROOT}/platform/{$diff->right->platform->version}/interface/{$diff->right->name}">{$diff->right->platform}</a> - 
                             <a href="{$diff->right->sourceurl}">Source</a></h2></td>
    </tr>
    <tr class="commentrow">
      <td class="before">
        <pre class="comment">{$diff->left->comment}</pre>
      </td>
      <td class="after">
        <pre class="comment">{$diff->right->comment}</pre>
      </td>
    </tr>
    <tr class="signaturerow">
      <td class="before">
        {include file="includes/interface.tpl" interface=$diff->left}
      </td>
      <td class="after">
        {include file="includes/interface.tpl" interface=$diff->right}
      </td>
    </tr>
    <tr>
      <td colspan="2"><h2><a name="constants">Constants</a></h2></td>
    </tr>
    {foreach from=$diff->constants item="item"}
      <tr class="commentrow {$item->state}">
        <td class="before">
          {if $item->state ne "added"}
            <pre class="comment">{$item->left->comment}</pre>
          {/if}
        </td>
        <td class="after">
          {if $item->state ne "removed"}
            <pre class="comment">{$item->right->comment}</pre>
          {/if}
        </td>
      </tr>
      <tr class="signaturerow {$item->state}">
        <td class="before">
          {if $item->state ne "added"}
            {include file="includes/constant.tpl" constant=$item->left}
          {/if}
        </td>
        <td class="after">
          {if $item->state ne "removed"}
            {include file="includes/constant.tpl" constant=$item->right}
          {/if}
        </td>
      </tr>
    {/foreach}

    <tr>
      <td colspan="2"><h2><a name="attributes">Attributes</a></h2></td>
    </tr>
    {foreach from=$diff->attributes item="item"}
      <tr class="commentrow {$item->state}">
        <td class="before">
          {if $item->state ne "added"}
            <pre class="comment">{$item->left->comment}</pre>
          {/if}
        </td>
        <td class="after">
          {if $item->state ne "removed"}
            <pre class="comment">{$item->right->comment}</pre>
          {/if}
        </td>
      </tr>
      <tr class="signaturerow {$item->state}">
        <td class="before">
          {if $item->state ne "added"}
            {include file="includes/attribute.tpl" attribute=$item->left}
          {/if}
        </td>
        <td class="after">
          {if $item->state ne "removed"}
            {include file="includes/attribute.tpl" attribute=$item->right}
          {/if}
        </td>
      </tr>
    {/foreach}

    <tr>
      <td colspan="2"><h2><a name="methods">Methods</a></h2></td>
    </tr>
    {foreach from=$diff->methods item="item"}
      <tr class="commentrow {$item->state}">
        <td class="before">
          {if $item->state ne "added"}
            <pre class="comment">{$item->left->comment}</pre>
          {/if}
        </td>
        <td class="after">
          {if $item->state ne "removed"}
            <pre class="comment">{$item->right->comment}</pre>
          {/if}
        </td>
      </tr>
      <tr class="signaturerow {$item->state}">
        <td class="before">
          {if $item->state ne "added"}
            {include file="includes/method.tpl" method=$item->left}
          {/if}
        </td>
        <td class="after">
          {if $item->state ne "removed"}
            {include file="includes/method.tpl" method=$item->right}
          {/if}
        </td>
      </tr>
    {/foreach}
  </table>
</div>
{include file="footer.tpl"}
