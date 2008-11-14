{include file="header.tpl" title="$interface Interface"}
<script type="text/javascript">
function platformSelect(version) {ldelim}
  if (!version)
    return;
  window.location.href = '{$ROOT}/platform/' + version + '/interface/{$interface}';
{rdelim}

function diffSelect(version) {ldelim}
  if (!version)
    return;
  window.location.href = '{$ROOT}/compare/interface/{$interface}/{$interface->platform->version}/' + version;
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
{assign var="showdiff" value="false"}
{foreach from=$interface->versions item="item"}
  {if $item->hash ne $interface->hash}
    {assign var="showdiff" value="true"}
  {/if}
{/foreach}
{if $showdiff eq "true"}
  <p class="navbox">
    <img src="{$ROOT}/silk/arrow_divide.png" />
    Compare to:
    <select onchange="diffSelect(this.value)">
      <option value="" selected="selected">--</option>
      {foreach from=$interface->versions item="item"}
        {if $item->hash ne $interface->hash}
          <option value="{$item->platform->version}">{$item->platform}</option>
        {/if}
      {/foreach}
    </select>
  </p>
{/if}

<p class="navbox">
  <img src="{$ROOT}/silk/script_go.png" />
  <a href="{$interface->sourceurl}">View IDL</a>
</p>
<p class="navbox">
  <img src="{$ROOT}/silk/sitemap_color.png" />
  <a href="{$ROOT}/platform/{$interface->platform->version}/interface/{$interface->name}/usage">Interface Usage</a>
</p>
<p class="navbox">
  <img src="https://developer.mozilla.org/favicon.ico" />
  <a href="https://developer.mozilla.org/en/{$interface->name|capitalize:true}">More information</a>
</p>

<p id="breadcrumbs">
  <img src="{$ROOT}/silk/bricks.png" /> <a href="{$HOME}">Mozilla XPCOM</a> &raquo;
  <select onchange="platformSelect(this.value)">
    {foreach from=$interface->versions item="item"}
      <option value="{$item->platform->version}"{if $item->platform->id eq $interface->platform->id} selected="selected"{/if}>{$item->platform}</option>
    {/foreach}
  </select> &raquo;
  <a href="{$ROOT}/platform/{$interface->platform->version}">Interfaces</a> &raquo;
  {$interface}
</p>
</div>

<div id="content">
<div class="body">
  <div class="idl">
    <pre class="comment">{$interface->comment}</pre>
    {include file="includes/interface.tpl" interface=$interface}
  </div>
  {if count($interface->constants) > 0}
    <h2><a name="constants">Constants</a></h2>
    <div>
      {foreach from=$interface->constants item="item"}
        <div class="member constant">
          <pre class="comment">{$item->comment}</pre>
          {include file="includes/constant.tpl" constant=$item}
        </div>
      {/foreach}
    </div>
  {/if}
  {if count($interface->attributes) > 0}
    <h2><a name="attributes">Attributes</a></h2>
    <div>
      {foreach from=$interface->attributes item="item"}
        <div class="member attribute">
          <pre class="comment">{$item->comment}</pre>
          {include file="includes/attribute.tpl" attribute=$item}
        </div>
      {/foreach}
    </div>
  {/if}
  {if count($interface->methods) > 0}
    <h2><a name="methods">Methods</a></h2>
    <div>
      {foreach from=$interface->methods item="item"}
        <div class="member method">
          <pre class="comment">{$item->comment}</pre>
          {include file="includes/method.tpl" method=$item}
        </div>
      {/foreach}
    </div>
  {/if}
</div>
</div>
{include file="footer.tpl"}
