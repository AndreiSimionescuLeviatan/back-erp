<?php
/* @var $user */
/* @var $lastMonthName */
/* @var $plateNumber */
?>
<div>Bună <?php echo $user->last_name . ' ' . $user->first_name; ?>
    <br>Acest email conține foile de parcurs pe luna <?php echo Yii::t('cmd-auto', $lastMonthName); ?> pentru mașina - <?php echo $plateNumber; ?>.
    <br>Toate cele bune,
    <br>Econfaire ID!
</div>
