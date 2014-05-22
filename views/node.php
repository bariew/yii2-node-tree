<div>
    <form class="form-search" onsubmit="return false;">
        <input id="jstreeSearch"
               class="input-medium search-query"
               placeholder="search"
               onchange = "$('.tree').jstree(true).search(this.value);"/>
    </form>
</div>
<div class='tree' id='jstree'>
    <ul>
        <?php $viewName = '_' . preg_replace('/.*\/(\w+)\.php$/', '$1', __FILE__); ?>
        <?php foreach($items as $item): ?>
            <?php echo $this->render($viewName, compact('item', 'viewName', 'behavior')); ?>
        <?php endforeach; ?>
    </ul>
</div>