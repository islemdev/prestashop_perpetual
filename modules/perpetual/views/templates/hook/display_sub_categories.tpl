<div class="sub-categories">
{foreach $subCategories as $subCategory}
    <div style="width:fit-content">
    <a class="sub-category" href="{$subCategory.url}">
        <img src="{$subCategory.image.bySize.small_default.url}" width="{$subCategory.image.bySize.small_default.width}" height="{$subCategory.image.bySize.small_default.height}" />
        <span class="sub-category-title">{$subCategory.name} ({$subCategory.nb_products})</span>
    </a>
    </div>
{/foreach}


</div>