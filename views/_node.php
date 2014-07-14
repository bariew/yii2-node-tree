<?php
    $pid        = isset($parent) ? $parent[$behavior->id] : '';
    $attributes = $behavior->nodeAttributes($item, $pid);
    $children   = $attributes['children'];
    $item       = $attributes['model'];
    $active     = @$attributes['selected'] 
        || ($item[$behavior->id] == @$_GET[$behavior->id] && $pid == @$_GET['pid'])
        || ($item[$behavior->id] == @$_GET[$behavior->id] && !@$_GET['pid'])
        || !$pid && !@$_GET['pid'] && !@$_GET[$behavior->id]
    ;
    
    echo \yii\helpers\Html::beginTag('li', array(
        'id'            => $attributes['id'],
        'data-jstree'   => json_encode(array(
            "opened"    => $active,
            "selected"  => $active,
            "type"      => $attributes['type']
        ))
    ));
    
    echo \yii\helpers\Html::a(
        " ".$attributes['text'], 
        $attributes['a_attr']['href'], 
        $attributes['a_attr']);
;?>
    <?php $parent     = $item;
        if(!empty($children)): ?>
        <ul>
            <?php foreach($children as $item): ?>
                    <?php echo $this->render($childView, compact('item', 'childView', 'behavior', 'parent')); ?>
            <?php endforeach; ?>
        </ul>	    
    <?php endif; ?>
</li>