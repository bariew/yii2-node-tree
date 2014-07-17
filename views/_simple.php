<?php
    $id = isset($id) ? "{$id}\\{$item}" : $item;
    $items = $children;
    $activeId = $this->context->behavior->getActiveNodeId($this->context->id);
    echo \yii\helpers\Html::beginTag('li', array(
        'id'            => $id,
        'data-jstree'   => json_encode(array(
            "opened"    => strpos($activeId, $id) === 0,
            "selected"  => $activeId == $id,
            "type"      => 'folder'
        ))
    ));
    echo \yii\helpers\Html::a($item, '#', ['data-id' => $id]);
;?>
    <ul>
        <?php foreach($items as $item => $children): ?>
            <?php if(!is_array($children)) { $children = []; } ;?>
            <?php echo $this->render($childView, compact('item', 'childView', 'children', 'id')); ?>
        <?php endforeach; ?>
    </ul>
</li>