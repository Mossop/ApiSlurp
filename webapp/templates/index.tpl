{include file="header.tpl" title="All Interfaces"}
<script type="text/javascript">
function platformSelect(version) {ldelim}
  if (!version)
    return;
  window.location.href = '{$ROOT}/platform/' + version;
{rdelim}

{literal}
function filterList() {
  gFilterTimeout = null;
  text = document.getElementById("filterbox").value.toLowerCase();
  var elements = document.getElementsByClassName("filteritem");
  for (var i = 0; i < elements.length; i++) {
    if (text == "")
      elements[i].style.display = null;
    else if (elements[i].textContent.toLowerCase().indexOf(text) >= 0)
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

function clearFilter() {
  document.getElementById("filterbox").value = "";
  filterList();
}
{/literal}
</script>

<div id="navbar">
<p class="navbox">
  <img src="{$ROOT}/silk/find.png" />
  Filter: <input id="filterbox" type="text" onkeypress="filterChange()"/>
  <a href="#" onclick="clearFilter(); return false"><img src="{$ROOT}/silk/cancel.png" /></a>
</p>

<p id="breadcrumbs">
  <img src="{$ROOT}/silk/bricks.png" /> <a href="{$ROOT}">Mozilla XPCOM</a> &raquo;
  <select onchange="platformSelect(this.value)">
    <option value="">--</option>
    {foreach from=$platforms item="item"}
      <option value="{$item->version}">{$item}</option>
    {/foreach}
  </select>
</p>
</div>

<div id="content">
<div class="body">
  <ul class="interfacelist">
    {foreach from=$interfaces item="item"}
      <li class="filteritem">
        <a href="{$ROOT}/interface/{$item->name}">{$item}</a>
        {if $item->oldest == $item->newest}
          <span class="variants">({$item->newest->platform->version})</span>
        {else}
          <span class="variants">({$item->oldest->platform->version} - {$item->newest->platform->version})</span>
        {/if}
      </li>
    {/foreach}
  </ul>
</div>
</div>
{include file="footer.tpl"}
