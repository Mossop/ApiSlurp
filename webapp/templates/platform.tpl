{include file="header.tpl" title="Platform $platform Index"}
<script type="text/javascript">
function platformSelect(version) {ldelim}
  if (!version)
    return;
  window.location.href = '{$ROOT}/platform/' + version;
{rdelim}

function diffSelect(version) {ldelim}
  if (!version)
    return;
  window.location.href = '{$ROOT}/compare/platform/{$platform->version}/' + version;
{rdelim}
</script>

<div id="navbar">
<p class="navbox">
  Compare to:
  <select onchange="diffSelect(this.value)">
    <option value="" selected="selected">--</option>
    {foreach from=$platforms item="item"}
      {if $item->id ne $platform->id}
        <option value="{$item->version}">{$item}</option>
      {/if}
    {/foreach}
  </select>
</p>

<p id="breadcrumbs">
  <a href="{$ROOT}">Mozilla XPCOM</a> &raquo;
  <select onchange="platformSelect(this.value)">
    {foreach from=$platforms item="item"}
      <option value="{$item->version}"{if $item->id eq $platform->id} selected="selected"{/if}>{$item}</option>
    {/foreach}
  </select> &raquo;
  Interfaces
</p>
</div>

<div id="content">
<div class="body">
  <h1>Platform {$platform} Interfaces</h1>
  <ul class="interfacelist">
    {foreach from=$interfaces item="item"}
      <li><a href="{$ROOT}/platform/{$platform->version}/interface/{$item->name}">{$item}</a></li>
    {/foreach}
  </ul>
</div>
</div>
{include file="footer.tpl"}
