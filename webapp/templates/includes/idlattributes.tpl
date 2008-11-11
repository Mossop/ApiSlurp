{foreach name="attrlist" from=$attributes item="attr"}{*
  *}{if $smarty.foreach.attrlist.first}[{/if}{include file="includes/attributes/`$attr->name`.tpl" value=$attr->value}{if $smarty.foreach.attrlist.last}] {else}, {/if}{*
*}{/foreach}