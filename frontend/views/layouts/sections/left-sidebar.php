<?php

use yii\base\InvalidConfigException;
use backend\widgets\adminLteWidgets\AdminLteNav;
use yii\bootstrap4\Html;

?>
<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?php echo Yii::$app->homeUrl; ?>" class="brand-link d-flex flex-column text-center">
        <img style="width: 150px; height: 40px;" src="/images/logo_ecf.png" class="icon m-auto" alt="ECF-ERP"
             title="Leviatan Design ERP">
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="images/lte-images/avatar4.png" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="text-white info">
                <?php echo Yii::t('app', 'Hello'); ?>, <?php echo Yii::$app->user->identity->username; ?>
            </div>
        </div>
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar nav-child-indent nav-compact flex-column"
                data-widget="treeview" role="menu" data-accordion="true">
                <?php
                $menuLinks[] = [
                    'label' => 'GENERAL',
                ];
                $menuLinks[] = [
                    'label' => 'PROIECTARE',
                ];
                $menuLinks[] = [
                    'label' => 'EXECUTIE',
                ];
                $menuLinks[] = [
                    'label' => 'CRM',
                ];
                $item = new AdminLteNav();
                $item->encodeLabels = false;
                $item->activateParents = true;
                $item->items = $menuLinks;
                $itemsHtml = '';
                foreach ($menuLinks as $key => $menuLink) {
                    if (empty($menuLink['url']) && empty($menuLink['items'])) {
                        $itemsHtml .= '<li class="nav-header">' . $menuLink['label'] . '</li>';
                        continue;
                    }
                    try {
                        $itemsHtml .= $item->renderItem($menuLink);
                    } catch (InvalidConfigException $e) {
                        Yii::error($e->getMessage());
                        continue;
                    }
                }
                ?>
                <?php echo $itemsHtml; ?>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>