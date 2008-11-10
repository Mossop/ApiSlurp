{include file="header.tpl" title="API Index"}
<script type="text/javascript">
function platformSelect(version) {ldelim}
  if (!version)
    return;
  window.location.href = '{$ROOT}/platform/' + version;
  {rdelim}
</script>

<div id="navbar">
<p id="breadcrumbs">
  <a href="{$ROOT}">Mozilla XPCOM</a> &raquo;
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
  <h1>All Interfaces</h1>
  <ul class="interfacelist">
    {foreach from=$interfaces item="item"}
      <li>
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
