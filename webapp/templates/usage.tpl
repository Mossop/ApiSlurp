{include file="header.tpl" title="Usage of $interface Interface"}
<script type="text/javascript">
function platformSelect(version) {ldelim}
  if (!version)
    return;
  window.location.href = '{$ROOT}/platform/' + version + '/interface/{$interface|escape:'url'}/usage';
{rdelim}
</script>

<div id="navbar">
<p id="breadcrumbs">
  <img src="{$ROOT}/silk/bricks.png" /> <a href="{$HOME}">Mozilla XPCOM</a> &raquo;
  <select onchange="platformSelect(this.value)">
    {foreach from=$interface->versions item="item"}
      <option value="{$item->platform->version|escape:'url'}"{if $item->platform->id eq $interface->platform->id} selected="selected"{/if}>{$item->platform|escape}</option>
    {/foreach}
  </select> &raquo;
  <a href="{$ROOT}/platform/{$interface->platform->version|escape:'url'}">Interfaces</a> &raquo;
  <a href="{$ROOT}/platform/{$interface->platform->version|escape:'url'}/interface/{$interface|escape:'url'}">{$interface|escape}</a> &raquo;
  Usage
</p>
</div>

<div id="content">
<div class="body">
  {if count($interface->subclasses) > 0}
    <h2>Subclasses</h2>
    <ul class="interfacelist">
      {foreach from=$interface->subclasses item="item"}
        <li><a href="{$ROOT}/platform/{$item->platform->version|escape:'url'}/interface/{$item->name|escape:'url'}">{$item|escape}</a></li>
      {/foreach}
    </ul>
  {/if}
  {if count($interface->attrUsage) > 0}
    <h2>Attributes</h2>
    {foreach from=$interface->attrUsage key="ti" item="list"}
      <h3>From <a href="{$ROOT}/platform/{$interface->platform->version|escape:'url'}/interface/{$ti|escape:'url'}">{$ti|escape}</a></h3>
      {foreach from=$list item="item"}
        <div class="member attribute">
          <pre class="comment">{$item->comment|escape}</pre>
          {include file="includes/attribute.tpl" attribute=$item}
        </div>
      {/foreach}
    {/foreach}
  {/if}
  {if count($interface->methodUsage) > 0}
    <h2>Returns</h2>
    {foreach from=$interface->methodUsage key="ti" item="list"}
      <h3>From <a href="{$ROOT}/platform/{$interface->platform->version|escape:'url'}/interface/{$ti|escape:'url'}">{$ti|escape}</a></h3>
      {foreach from=$list item="item"}
        <div class="member attribute">
          <pre class="comment">{$item->comment|escape}</pre>
          {include file="includes/method.tpl" method=$item}
        </div>
      {/foreach}
    {/foreach}
  {/if}
  {if count($interface->paramUsage) > 0}
    <h2>Parameters</h2>
    {foreach from=$interface->paramUsage key="ti" item="list"}
      <h3>From <a href="{$ROOT}/platform/{$interface->platform->version|escape:'url'}/interface/{$ti|escape:'url'}">{$ti|escape}</a></h3>
      {foreach from=$list item="item"}
        <div class="member attribute">
          <pre class="comment">{$item->comment|escape}</pre>
          {include file="includes/method.tpl" method=$item}
        </div>
      {/foreach}
    {/foreach}
  {/if}
  {if count($interface->subclasses) == 0}
    {if count($interface->attrUsage) == 0}
      {if count($interface->methodUsage) == 0}
        {if count($interface->paramUsage) == 0}
          <p>This interface is not used by any other interfaces.</p>
        {/if}
      {/if}
    {/if}
  {/if}
</div>
</div>
{include file="footer.tpl"}
