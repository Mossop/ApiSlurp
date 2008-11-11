{include file="header.tpl" title="Comparing <a href=\"$ROOT/platform/`$diff->left->version`\">`$diff->left`</a> to <a href=\"$ROOT/platform/`$diff->right->version`\">`$diff->right`</a>"}
<script type="text/javascript">
function leftSelect(version) {ldelim}
  window.location.href = '{$ROOT}/compare/platform/' + version + '/{$diff->right->version}';
{rdelim}

function rightSelect(version) {ldelim}
  window.location.href = '{$ROOT}/compare/platform/{$diff->left->version}/' + version;
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
  <select onchange="leftSelect(this.value)">
    {foreach from=$platforms item="item"}
      {if $item->id ne $diff->right->id}
        <option value="{$item->version}"{if $item->id eq $diff->left->id} selected="selected"{/if}>{$item}</option>
      {/if}
    {/foreach}
  </select> &raquo;
  <a href="{$ROOT}/platform/{$diff->left->version}">Interfaces</a> &raquo;
  compare to
  <select onchange="rightSelect(this.value)">
    {foreach from=$platforms item="item"}
      {if $item->id ne $diff->left->id}
        <option value="{$item->version}"{if $item->id eq $diff->right->id} selected="selected"{/if}>{$item}</option>
      {/if}
    {/foreach}
  </select>
</p>
</div>

<div id="content">
<div class="body">
  <h2><a name="removed">Interfaces removed since {$diff->left}</a></h2>
  <ul class="interfacelist">
    {foreach from=$diff->removed item="item"}
      <li class="filteritem"><a href="{$ROOT}/platform/{$diff->left->version}/interface/{$item->name}">{$item}</a></li>
    {/foreach}
  </ul>
  <h2><a name="added">Interfaces added to {$diff->right}</a></h2>
  <ul class="interfacelist">
    {foreach from=$diff->added item="item"}
      <li class="filteritem"><a href="{$ROOT}/platform/{$diff->right->version}/interface/{$item->name}">{$item}</a></li>
    {/foreach}
  </ul>
  <h2><a name="modified">Modified Interfaces</a></h2>
  <ul class="interfacelist">
    {foreach from=$diff->modified item="item"}
      <li class="filteritem"><a href="{$ROOT}/compare/interface/{$item->name}/{$diff->left->version}/{$diff->right->version}">{$item}</a></li>
    {/foreach}
  </ul>
  <h2><a name="matching">Unchanged Interfaces</a></h2>
  <ul class="interfacelist">
    {foreach from=$diff->unchanged item="item"}
      <li class="filteritem"><a href="{$ROOT}/platform/{$diff->right->version}/interface/{$item->name}">{$item}</a></li>
    {/foreach}
  </ul>
</div>
</div>
{include file="footer.tpl"}
