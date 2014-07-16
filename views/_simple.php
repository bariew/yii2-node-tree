<?php
    $items = $children;
    echo \yii\helpers\Html::beginTag('li', array(
        'id'            => \bariew\nodeTree\ARTreeMenuWidget::$uniqueKey++,
        'data-jstree'   => json_encode(array(
            "opened"    => false,
            "selected"  => false,
            "type"      => 'folder'
        ))
    ));
    echo \yii\helpers\Html::a(" {$item}", '#');
;?>
        <ul>
            <?php foreach($items as $item => $children): ?>
                <?php if(!is_array($children)) { $children = []; } ;?>
                <?php echo $this->render($childView, compact('item', 'childView', 'children')); ?>
            <?php endforeach; ?>
        </ul>	    
</li>