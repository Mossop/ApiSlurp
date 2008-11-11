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

{literal}
function filterList() {
  gFilterTimeout = null;
  text = document.getElementById("filterbox").value.toLowerCase();
  var elements = document.getElementsByClassName("filteritem");
  for (var i = 0; i < elements.length; i++) {
    if (elements[i].textContent.toLowerCase().indexOf(text) >= 0)
      elements[i].style.display = null;
    else
      elements[i].style.display = "none";
  }
}

var gFilterTimeout = null;
function filterChange() {
  if (gFilterTimeout)
    window.clearTimeout(gFilterTimeout);

  gFilterTimeout = window.setTimeout(filterList, 500);
}
{/literal}
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

<p class="navbox">
  Filter: <input id="filterbox" type="text" onkeypress="filterChange()"/>
</p>

<p id="breadcrumbs">
  <img src="{$ROOT}/silk/bricks.png" /> <a href="{$ROOT}">Mozilla XPCOM</a> &raquo;
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
  {foreach from=$modules key="module" item="interfaces"}
    <h2>{$module}</h2>
    <ul class="interfacelist">
      {foreach from=$interfaces item="item"}
        <li class="filteritem"><a href="{$ROOT}/platform/{$platform->version}/interface/{$item->name}">{$item}</a></li>
      {/foreach}
    </ul>
  {/foreach}
</div>
</div>
{include file="footer.tpl"}
