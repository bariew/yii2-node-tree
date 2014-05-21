<?php
$children   = $item['children'];
$item       = $item['model'];
$attributes = $this->behavior->nodeAttributes($item);
$active     = (($item['id'] == @$_GET['id']) || (($item['id'] == 1) && !@$_GET['id']));
echo CHtml::openTag('li', array(
    'id'            => $attributes['id'],
    'data-jstree'   => json_encode(array(
        "opened"    => $active,
        "selected"  => $active,
        "type"      => $attributes['type']
    ))
));
echo CHtml::link($item['title'], "", $attributes['a_attr']);
;?>
<?php if(!empty($children)): ?>
    <ul>
        <?php  foreach($children as $item): ?>
            <?php $this->render($viewName, compact('item', 'viewName')); ?>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
</li>