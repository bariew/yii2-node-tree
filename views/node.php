<div>
    <form class="form-search" onsubmit="return false;">
        <input id="jstreeSearch"
               class="input-medium search-query"
               placeholder="search"
               onchange = "$('.tree').jstree(true).search(this.value);"/>
    </form>
</div>
<div class='tree' id='<?php echo $this->context->id ;?>'>
    <ul>
        <?php foreach($items as $item): ?>
            <?php echo $this->render($childView, compact('item', 'childView', 'behavior')); ?>
        <?php endforeach; ?>
    </ul>
</div>