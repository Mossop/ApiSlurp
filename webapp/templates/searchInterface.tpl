{include file="header.tpl" title="Interface search for \"$query\""}
<script type="text/javascript">
function platformSelect(version) {ldelim}
  if (!version)
    return;
  window.location.href = '{$ROOT}/platform/' + version;
  {rdelim}
</script>

<div id="navbar">
<p id="breadcrumbs">
  <img src="{$ROOT}/silk/bricks.png" /> <a href="{$HOME}">Mozilla XPCOM</a> &raquo;
  <select onchange="platformSelect(this.value)">
    <option value="">--</option>
    {foreach from=$platforms item="item"}
      <option value="{$item->version|escape:'url'}">{$item|escape}</option>
    {/foreach}
  </select>
</p>
</div>

<div id="content">
<div class="body">
  {if count($interfaces) > 0}
    <ul>
      {foreach from=$interfaces item="item"}
        <li><a href="{$ROOT}/interface/{$item->name|escape:'url'}">{$item|escape}</a></li>
      {/foreach}
    </ul>
  {else}
    <p>No interfaces match the search "{$query}".</p>
  {/if}
</div>
</div>
{include file="footer.tpl"}
