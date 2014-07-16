<?php if(!isset($this->context->options['plugins']) || in_array('search', $this->context->options['plugins'])): ?>
    <div>
        <form class="form-search" onsubmit="return false;">
            <input id="<?php echo $this->context->id ;?>Search"
                   class="input-medium search-query"
                   placeholder="search"
                   onchange = "$('#<?php echo $this->context->id ;?>').jstree(true).search(this.value);"/>
        </form>
    </div>
<?php endif ;?>
<div class='tree' id='<?php echo $this->context->id ;?>'>
    <ul>
        <?php foreach($items as $item => $children): ?>
            <?php echo $this->render($childView, compact('item', 'childView', 'children')); ?>
        <?php endforeach; ?>
    </ul>
</div>