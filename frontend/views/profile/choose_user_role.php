<?php
/**
 * @var $roleName string
 */

$handler = <<< SCRIPT
function(event) {
alert(1);
}
SCRIPT;

?>

<div class="row">
    <div class="col-md-offset-1 col-md-10 col-lg-offset-2 col-lg-8">
        <div class="panel panel-default">
            <div class="panel-heading text-center"><h4>Изменить роль пользователя</h4></div>
            <div class="panel-body">
                <div class="form-group">
                    <label for="select_role" class="col-md-2 control-label">Выберите роль</label>
                    <div class="col-md-10">
                        <select id="select_role" class="form-control" onchange="profileController.handler">
                            <option data-value="user">обычный пользователь</option>
                            <option data-value="moderator">модератор</option>
                            <option data-value="admin">администратор</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>