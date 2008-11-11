{include file="header.tpl" title="Comparing `$diff` between `$diff->left->platform` and `$diff->right->platform`"}
<script type="text/javascript">
function mainSelect(version) {ldelim}
  window.location.href = '{$ROOT}/compare/interface/{$diff}/' + version + '/{$diff->target->platform->version}';
{rdelim}

function targetSelect(version) {ldelim}
  window.location.href = '{$ROOT}/compare/interface/{$diff}/{$diff->main->platform->version}/' + version;
{rdelim}

function toggleVisible() {ldelim}
  var checkbox = document.getElementById("hidesame");
  document.getElementById("diff").className = checkbox.checked ? "diff hidesame" : "diff";
{rdelim}
</script>

<div id="navbar">
<p id="breadcrumbs">
  <img src="{$ROOT}/silk/bricks.png" /> <a href="{$HOME}">Mozilla XPCOM</a> &raquo;
  <select onchange="mainSelect(this.value)">
    {foreach from=$diff->versions item="item"}
      {if $item->platform->id ne $diff->target->platform->id}
        <option value="{$item->platform->version}"{if $item->platform->id eq $diff->main->platform->id} selected="selected"{/if}>{$item->platform}</option>
      {/if}
    {/foreach}
  </select> &raquo;
  <a href="{$ROOT}/platform/{$diff->main->platform->version}">Interfaces</a> &raquo;
  <a href="{$ROOT}/platform/{$diff->main->platform->version}/interface/{$diff}">{$diff}</a> &raquo;
  compare to
  <select onchange="targetSelect(this.value)">
    {foreach from=$diff->versions item="item"}
      {if $item->platform->id ne $diff->main->platform->id}
        <option value="{$item->platform->version}"{if $item->platform->id eq $diff->target->platform->id} selected="selected"{/if}>{$item->platform}</option>
      {/if}
    {/foreach}
  </select>
</p>
</div>

<div id="content">
<div class="body">
  <table id="diff" class="diff">
    <tr>
      <td class="before"><h2><a href="{$ROOT}/platform/{$diff->left->platform->version}/interface/{$diff->left->name}">{$diff} in {$diff->left->platform}</a></h2></td>
      <td class="after"><h2><a href="{$ROOT}/platform/{$diff->right->platform->version}/interface/{$diff->right->name}">{$diff} in {$diff->right->platform}</a></h2></td>
    </tr>
    <tr>
      <td colspan="2">
        <div class="controls"><input type="checkbox" id="hidesame" name="hidesame" onchange="toggleVisible()"/> <label for="hidesame">Hide unchanged items</label></div>
      </td>
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
      <td class="before idl">
        {include file="includes/interface.tpl" interface=$diff->left}
      </td>
      <td class="after idl">
        {include file="includes/interface.tpl" interface=$diff->right}
      </td>
    </tr>

    {if count($diff->constants) > 0}
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
          <td class="before member constant">
            {if $item->state ne "added"}
              {include file="includes/constant.tpl" constant=$item->left}
            {/if}
          </td>
          <td class="after member constant">
            {if $item->state ne "removed"}
              {include file="includes/constant.tpl" constant=$item->right}
            {/if}
          </td>
        </tr>
      {/foreach}
    {/if}

    {if count($diff->attributes) > 0}
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
          <td class="before member attribute">
            {if $item->state ne "added"}
              {include file="includes/attribute.tpl" attribute=$item->left}
            {/if}
          </td>
          <td class="after member attribute">
            {if $item->state ne "removed"}
              {include file="includes/attribute.tpl" attribute=$item->right}
            {/if}
          </td>
        </tr>
      {/foreach}
    {/if}

    {if count($diff->methods) > 0}
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
          <td class="before member method">
            {if $item->state ne "added"}
              {include file="includes/method.tpl" method=$item->left}
            {/if}
          </td>
          <td class="after member method">
            {if $item->state ne "removed"}
              {include file="includes/method.tpl" method=$item->right}
            {/if}
          </td>
        </tr>
      {/foreach}
    {/if}
  </table>
</div>
{include file="footer.tpl"}
