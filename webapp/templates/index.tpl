{include file="header.tpl" title="All Interfaces"}
<script type="text/javascript" src="{$ROOT}/scripts/filter.js"></script>
<script type="text/javascript">
function platformSelect(version) {ldelim}
  if (!version)
    return;
  window.location.href = '{$ROOT}/platform/' + version;
{rdelim}

{*{literal}
$(document).ready(function() {
  $("h2").click(function() {
    $(this).next().animate({ opacity: "toggle" }, 250);
  });
});
{/literal}*}
</script>

<div id="navbar">
<p class="navbox">
  <img src="{$ROOT}/silk/find.png" />
  Filter: <input id="filterbox" type="text" onkeypress="filterChange()"/>
  <a href="#" onclick="clearFilter(); return false"><img src="{$ROOT}/silk/cancel.png" /></a>
</p>

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
  {foreach from=$modules key="module" item="list"}
    <div class="filtersection">
      <h2>{$module|escape}</h2>
      <ul class="interfacelist">
        {foreach from=$list item="item"}
          <li class="filteritem">
            <a href="{$ROOT}/interface/{$item->name|escape:'url'}">{$item|escape}</a>
            {if $item->oldest == $item->newest}
              <span class="variants">({$item->newest->platform->version|escape})</span>
            {else}
              <span class="variants">({$item->oldest->platform->version|escape} - {$item->newest->platform->version|escape})</span>
            {/if}
          </li>
        {/foreach}
      </ul>
    </div>
  {/foreach}
</div>
</div>
{include file="footer.tpl"}
