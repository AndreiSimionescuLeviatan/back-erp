<?php
return [
    'version' => 'v0.15.0',
    'adminEmail' => 'admin@example.com',
    'defaultEmployeePsw' => 'EcfErpPass' . date("Y"),
    'productHistoryVersionFilesUploadDir' => 'upload/products-releases',
    'user.passwordResetTokenExpire' => 3600,
    'user.passwordMinLength' => 8,
    'bsVersion' => '4.x', // this will set globally `bsVersion` to Bootstrap 4.x for all Krajee Extensions
    'pagination' => [
        '1' => 1, '5' => 5, '10' => 10, '20' => 20, '30' => 30, '40' => 40, '50' => 50,
        '60' => 60, '70' => 70, '80' => 80, '90' => 90, '100' => 100,
        '150' => 150, '200' => 200, '300' => 300, '400' => 400, '500' => 500, '700' => 700, '1000' => 1000
    ],
    'columnWidthAction' => '100px',
    'columnWidthId' => '90px',
    'columnWidthAddedUpdated' => '150px'
];