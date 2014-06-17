<?php
    $children   = $item['children'];
    $item       = $item['model'];
    $attributes = $behavior->nodeAttributes($item);
    $id = str_replace("node-", "", $attributes['id']);
    $active     = (($id == @$_GET[$behavior->id]));
    echo \yii\helpers\Html::beginTag('li', array(
        'id'            => $attributes['id'],
        'data-jstree'   => json_encode(array(
            "opened"    => $active,
            "selected"  => $active,
            "type"      => $attributes['type']
        ))
    ));
    echo \yii\helpers\Html::a(" ".$attributes['text'], $attributes['a_attr']['href'], $attributes['a_attr']);
;?>
    <?php if(!empty($children)): ?>
        <ul>
            <?php foreach($children as $item): ?>
                    <?php echo $this->render($viewName, compact('item', 'viewName', 'behavior')); ?>
            <?php endforeach; ?>
        </ul>	    
    <?php endif; ?>
</li>