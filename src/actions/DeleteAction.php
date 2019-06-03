<?

namespace studio451\yii2jsonrpc2api\actions;

use studio451\yii2jsonrpc2api\Exception;

class DeleteAction extends Action
{
    public function run($id)
    {
        $model = $this->findModel($id);

        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id, $model);
        }

        if ($model->delete() === false) {
            throw new Exception('Failed to delete the object for unknown reason.');
        }

        return true;
    }
}
