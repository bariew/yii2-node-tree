<?php
class TreeDeleteAction extends TreeAction
{
    public function run($id, $pid=false)
    {
        $model = $this->controller->getModel($id);
        if(!$model->isNewRecord && $model->delete()){
            Yii::app()->user->setFlash('success', 'Removed.');
        }
        $this->controller->redirect($pid
                ? array('read', 'id'=>$pid)
                : array('index')
        );
    }
}
