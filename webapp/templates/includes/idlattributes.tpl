{foreach name="attrlist" from=$attributes item="attr"}{*
  *}{if $smarty.foreach.attrlist.first}[{/if}{*
    *}{if $attr->value}{*
      *}<span class="keyword">{$attr->name}</span>(<span class="name">{$attr->value}</span>){*
    *}{else}{*
      *}<span class="keyword">{$attr->name}</span>{*
    *}{/if}{*
  *}{if $smarty.foreach.attrlist.last}]{else}, {/if}{*
*}{/foreach}