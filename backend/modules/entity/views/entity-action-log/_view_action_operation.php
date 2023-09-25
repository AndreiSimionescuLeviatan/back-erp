<?php

use yii\helpers\Html;

/* @var $operations */
?>

<div class="entity-operation-details">
    <?php foreach ($operations as $item) { ?>
        <div class="row">
            <div class="col-12 entity-operation-col">
                <div class="entity-operation entity-operation-domain"> <?php echo $item['domain_description']; ?></div>
                <div class="entity-operation entity-operation-entity"> <?php echo $item['entity_description']; ?></div>
                <div class="entity-operation entity-operation-entity-old-id"> <?php echo $item['affected_id']; ?></div>
                <div class="entity-operation entity-operation-description"> <?php echo $item['description']; ?></div>
                <div class="entity-operation entity-operation-old-value">
                    <?php
                    if (strlen($item['old_value']) > 50) {
                        echo Html::textarea('old_value', $item['old_value'], [
                            'style' => 'width: 100%;',
                            'rows' => 5,
                            'disabled' => true
                        ]);
                    } else {
                        echo $item['old_value'];
                    }
                    ?>
                </div>
                <div class="entity-operation entity-operation-new-value">
                    <?php
                    if (strlen($item['new_value']) > 50) {
                        echo Html::textarea('new_value', $item['new_value'], [
                            'style' => 'width: 100%;',
                            'rows' => 5,
                            'disabled' => true
                        ]);
                    } else {
                        echo $item['new_value'];
                    }
                    ?>
                </div>
                <div class="entity-operation entity-operation-old-code"> <?php echo $item['old_value_code']; ?></div>
                <div class="entity-operation entity-operation-new-code"> <?php echo $item['new_value_code']; ?></div>
                <div class="entity-operation entity-operation-added"> <?php echo $item['added']; ?></div>
            </div>
        </div>
    <?php } ?>
</div>
