<?php
/**
 * @var $targetUserRoles array
 */
?>

<div class="row">
    <div class="col-md-offset-1 col-md-10 col-lg-offset-2 col-lg-8">
        <div class="panel panel-default">
            <div class="panel-heading text-center"><h4>Изменить роль пользователя</h4></div>
            <div class="panel-body">
                <p>Если вы хотите возвести этого обычного смертного пользователя в ранг админов или модераторов, или, наоборот, понизить
                    неугодного модератора, выберите для него новую роль.</p>
                <div class="form-group">
                    <label for="select_role" class="col-md-2 control-label">Выберите роль</label>
                    <div class="col-md-10">
                        <select id="select_role" class="form-control">
                            <option data-value="user" <?= array_key_exists('user',$targetUserRoles) ? "selected":"" ?> >обычный пользователь</option>
                            <option data-value="moderator" <?= array_key_exists('moderator',$targetUserRoles) ? "selected":"" ?> >модератор</option>
                            <option data-value="admin" <?= array_key_exists('admin',$targetUserRoles) ? "selected":"" ?> >администратор</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>