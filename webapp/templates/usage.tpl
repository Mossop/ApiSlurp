{include file="header.tpl" title="$interface Interface (from $interface->platform)"}
<script type="text/javascript">
function platformSelect(version) {ldelim}
  if (!version)
    return;
  window.location.href = '{$ROOT}/platform/' + version + '/interface/{$interface}/usage';
{rdelim}
</script>

<div id="navbar">
<p id="breadcrumbs">
  <img src="{$ROOT}/silk/bricks.png" /> <a href="{$ROOT}">Mozilla XPCOM</a> &raquo;
  <select onchange="platformSelect(this.value)">
    {foreach from=$interface->versions item="item"}
      <option value="{$item->platform->version}"{if $item->platform->id eq $interface->platform->id} selected="selected"{/if}>{$item->platform}</option>
    {/foreach}
  </select> &raquo;
  <a href="{$ROOT}/platform/{$interface->platform->version}">Interfaces</a> &raquo;
  <a href="{$ROOT}/platform/{$interface->platform->version}/interface/{$interface}">{$interface}</a> &raquo;
  Usage
</p>
</div>

<div id="content">
<div class="body">
  <h1>Usage of {$interface} Interface</h1>
  <h2>Subclasses</h2>
  <ul>
    {foreach from=$interface->subclasses item="item"}
      <li><a href="{$ROOT}/platform/{$item->platform->version}/interface/{$item->name}">{$item}</a></li>
    {/foreach}
  </ul>
  <h2>Attributes</h2>
  {foreach from=$interface->attrUsage item="item"}
    <div class="member attribute">
      <pre class="comment">{$item->comment}</pre>
      {include file="includes/attribute.tpl" attribute=$item}
    </div>
  {/foreach}
  <h2>Returns</h2>
  {foreach from=$interface->methodUsage item="item"}
    <div class="member attribute">
      <pre class="comment">{$item->comment}</pre>
      {include file="includes/method.tpl" method=$item}
    </div>
  {/foreach}
  <h2>Parameters</h2>
  {foreach from=$interface->paramUsage item="item"}
    <div class="member attribute">
      <pre class="comment">{$item->comment}</pre>
      {include file="includes/method.tpl" method=$item}
    </div>
  {/foreach}
</div>
</div>
{include file="footer.tpl"}
