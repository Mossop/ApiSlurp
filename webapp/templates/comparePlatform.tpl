{include file="header.tpl" title="Comparing `$diff->left` to `$diff->right`"}
<script type="text/javascript" src="{$ROOT}/scripts/filter.js"></script>
<script type="text/javascript">
function mainSelect(version) {ldelim}
  window.location.href = '{$ROOT}/compare/platform/' + version + '/{$diff->target->version|escape:'url'}';
{rdelim}

function targetSelect(version) {ldelim}
  window.location.href = '{$ROOT}/compare/platform/{$diff->main->version|escape:'url'}/' + version;
{rdelim}
</script>

<div id="navbar">
<p class="navbox">
  <img src="{$ROOT}/silk/find.png" />
  Filter: <input id="filterbox" type="text" onkeypress="filterChange()"/>
  <a href="#" onclick="clearFilter(); return false"><img src="{$ROOT}/silk/cancel.png" /></a>
</p>

<p id="breadcrumbs">
  <img src="{$ROOT}/silk/bricks.png" /> <a href="{$HOME}">Mozilla XPCOM</a> &raquo;
  <select onchange="mainSelect(this.value)">
    {foreach from=$platforms item="item"}
      {if $item->id ne $diff->target->id}
        <option value="{$item->version|escape:'url'}"{if $item->id eq $diff->main->id} selected="selected"{/if}>{$item|escape}</option>
      {/if}
    {/foreach}
  </select> &raquo;
  <a href="{$ROOT}/platform/{$diff->main->version|escape:'url'}">Interfaces</a> &raquo;
  compare to
  <select onchange="targetSelect(this.value)">
    {foreach from=$platforms item="item"}
      {if $item->id ne $diff->main->id}
        <option value="{$item->version|escape:'url'}"{if $item->id eq $diff->target->id} selected="selected"{/if}>{$item|escape}</option>
      {/if}
    {/foreach}
  </select>
</p>
</div>

<div id="content">
<div class="body">
  <div class="filtersection">
    <h2><a name="removed">Interfaces removed since {$diff->left|escape}</a></h2>
    <ul class="interfacelist">
      {foreach from=$diff->removed item="item"}
        <li class="filteritem"><a href="{$ROOT}/platform/{$diff->left->version|escape:'url'}/interface/{$item->name|escape:'url'}">{$item|escape}</a></li>
      {/foreach}
    </ul>
  </div>
  <div class="filtersection">
    <h2><a name="added">Interfaces added to {$diff->right}</a></h2>
    <ul class="interfacelist">
      {foreach from=$diff->added item="item"}
        <li class="filteritem"><a href="{$ROOT}/platform/{$diff->right->version|escape:'url'}/interface/{$item->name|escape:'url'}">{$item|escape}</a></li>
      {/foreach}
    </ul>
  </div>
  <div class="filtersection">
    <h2><a name="modified">Modified Interfaces</a></h2>
    <ul class="interfacelist">
      {foreach from=$diff->modified item="item"}
        <li class="filteritem"><a href="{$ROOT}/compare/interface/{$item->name|escape:'url'}/{$diff->left->version|escape:'url'}/{$diff->right->version|escape:'url'}">{$item|escape}</a></li>
      {/foreach}
    </ul>
  </div>
  <div class="filtersection">
    <h2><a name="matching">Unchanged Interfaces</a></h2>
    <ul class="interfacelist">
      {foreach from=$diff->unchanged item="item"}
        <li class="filteritem"><a href="{$ROOT}/platform/{$diff->right->version|escape:'url'}/interface/{$item->name|escape:'url'}">{$item|escape}</a></li>
      {/foreach}
    </ul>
  </div>
</div>
</div>
{include file="footer.tpl"}
