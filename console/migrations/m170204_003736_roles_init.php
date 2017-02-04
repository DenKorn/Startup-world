<?php

use yii\db\Migration;

class m170204_003736_roles_init extends Migration
{
    public function up()
    {
        $rbac = \Yii::$app->authManager;

        $guest = $rbac->createRole('guest');
        $guest->description = 'Any unauthorized user';
        $rbac->add($guest);

        $user = $rbac->createRole('user');
        $user->description = 'Common authorized forum user';
        $rbac->add($user);

        $moderator = $rbac->createRole('moderator');
        $moderator->description = 'Role with ability to block normal users and to edit messaging of all users';
        $rbac->add($moderator);

        $admin = $rbac->createRole('admin');
        $admin->description = 'Главный батька всея форума (можно просто Бог)';
        $rbac->add($admin);

        $rbac->addChild($admin,$moderator);
        $rbac->addChild($moderator,$user);
        $rbac->addChild($user,$guest);

        $rbac->assign($admin,1); // присвоим к самому первому и единственному на данный момент пользователю
    }

    public function down()
    {
        $rbac = \Yii::$app->authManager;
        $rbac->removeAll();

        return true;
    }

}
