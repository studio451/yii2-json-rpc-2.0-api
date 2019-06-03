<?

namespace studio451\yii2jsonrpc2api\actions;

use Yii;
use studio451\yii2jsonrpc2api\Exception;

class DeleteAllAction extends Action
{
    public function run()
    {
        $modelClass = $this->modelClass;
        
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id, $model);
        }
        
        if ($modelClass::deleteAll() === false) {
            throw new Exception('Failed to delete the objects for unknown reason.');
        }
        Yii::$app->db->createCommand('ALTER TABLE `'.$modelClass::tableName().'` auto_increment = 1')->execute();
        
        return true;
    }
}
