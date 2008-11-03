{include file="header.tpl" title="$interface Interface (from $interface->platform)"}
<div class="body">
  <h1>Usage of {$interface} Interface (from platform <a href="{$ROOT}/platform/{$interface->platform->version}">{$interface->platform}</a>)</h1>
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
{include file="footer.tpl"}
