<?php if(!isset($this->context->options['plugins']) || in_array('search', $this->context->options['plugins'])): ?>
    <form class="form-search" onsubmit="return false;">
        <input id="<?php echo $this->context->id ;?>Search"
               class="input-medium search-query"
               placeholder="search"
               onchange = "$('#<?php echo $this->context->id ;?>').jstree(true).search(this.value);"/>
    </form>
<?php endif ;?>
<div class='tree' id='<?php echo $this->context->id ;?>'>
    <ul>
        <?php foreach($items as $i => $item): ?>
            <?php
            $next = @$items[$i+1];
            $isParent = $next && $next['depth'] > $item['depth'];
            $lastDepth = $next ? ($item['depth'] - $next['depth']) : $item['depth'];
            $attributes = $item['nodeAttributes'];
            echo \yii\helpers\Html::beginTag('li', array(
                'id'            => $attributes['id'],
                'data-jstree'   => json_encode(array(
                    "opened"    => $attributes['active'],
                    "selected"  => $attributes['active'],
                    "type"      => $items ? 'folder' : 'file'
                ))
            ));
            echo \yii\helpers\Html::a($attributes['text'], $attributes['a_attr']['href'], $attributes['a_attr']);
            echo ($isParent) ? "<ul>" : "</li>";
            while ($lastDepth > 0) {
                $lastDepth--;
                echo "</ul></li>";
            } ?>
        <?php endforeach; ?>
    </ul>
</div>