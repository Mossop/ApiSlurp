{include file="header.tpl" title="$platform Interfaces"}
<script type="text/javascript" src="{$ROOT}/scripts/filter.js"></script>
<script type="text/javascript">
function platformSelect(version) {ldelim}
  if (!version)
    return;
  window.location.href = '{$ROOT}/platform/' + version;
{rdelim}

function diffSelect(version) {ldelim}
  if (!version)
    return;
  window.location.href = '{$ROOT}/compare/platform/{$platform->version|escape:'url'}/' + version;
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
  <img src="{$ROOT}/silk/arrow_divide.png" />
  Compare to:
  <select onchange="diffSelect(this.value)">
    <option value="" selected="selected">--</option>
    {foreach from=$platforms item="item"}
      {if $item->id ne $platform->id}
        <option value="{$item->version|escape:'url'}">{$item|escape}</option>
      {/if}
    {/foreach}
  </select>
</p>

<p class="navbox">
  <img src="{$ROOT}/silk/find.png" />
  Filter: <input id="filterbox" type="text" onkeypress="filterChange()"/>
  <a href="#" onclick="clearFilter(); return false"><img src="{$ROOT}/silk/cancel.png" /></a>
</p>

<p id="breadcrumbs">
  <img src="{$ROOT}/silk/bricks.png" /> <a href="{$HOME}">Mozilla XPCOM</a> &raquo;
  <select onchange="platformSelect(this.value)">
    {foreach from=$platforms item="item"}
      <option value="{$item->version|escape:'url'}"{if $item->id eq $platform->id} selected="selected"{/if}>{$item|escape}</option>
    {/foreach}
  </select> &raquo;
  Interfaces
</p>
</div>

<div id="content">
<div class="body">
  {foreach from=$modules key="module" item="interfaces"}
    <div class="filtersection">
      <h2>{$module|escape}</h2>
      <ul class="interfacelist">
        {foreach from=$interfaces item="item"}
          <li class="filteritem"><a href="{$ROOT}/platform/{$platform->version|escape:'url'}/interface/{$item->name|escape:'url'}">{$item|escape}</a></li>
        {/foreach}
      </ul>
    </div>
  {/foreach}
</div>
</div>
{include file="footer.tpl"}
