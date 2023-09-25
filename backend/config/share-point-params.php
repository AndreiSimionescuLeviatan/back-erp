<?php
const TENANT_ID = '5669e44c-6a4d-415f-bad4-de9d7605e699';
const CLIENT_ID = 'c3876d1d-7fff-4e05-b15a-1e04d22e1072';
const CLIENT_SECRET = 'Nu48Q~oY_GwHGQRkXRsQAEMNA1PYW6uA3VxcEaHN';
const CLIENT_SECRET_ID = 'd0d50ce5-16dd-4357-bf86-1186bcbf8716';
const SHARE_POINT_USERNAME = 'automate@leviatan.ro';
const SHARE_POINT_PASS = '2022!@#Atm#@!';
const LEVIATAN_DOMAIN = 'sites/leviatandesign.sharepoint.com:';
const LEVIATAN_SITE = 'sites/FINANCIAR:';
// Lists IDs
const INVOICE_LIST_ID = 'lists/0396c1af-638b-4cbf-8523-3b23cc45c761';
const INVOICE_BODY_RAW_LIST_ID = 'lists/516f2400-f73b-4c91-93b3-aa04b0ce566a';
const INVOICE_HEADER_RAW_LIST_ID = 'lists/60b651b5-df19-44e0-a489-90c11718508c';
// Selected fields
const INVOICE_FIELDS = 'items?expand=fields(select=ID,Status,Layout_Id,Number_Of_Records,Vendor_Name,Document_Id,Added,Added_By,Updated,Updated_By)';
const INVOICE_BODY_RAW_FIELDS = 'items?expand=fields(select=ID,Invoice_Id,Description,Info_Description,Unit_Measure,Quantaty,Unit_Price,Value_Record,TVA_Value_Record,Account,OrderRecord,Added,Added_By,Updated,Updated_By,Task_Id)';
const INVOICE_HEADER_RAW_FIELDS = 'items?expand=fields(select=ID,Invoice_Id,Vendor_Name,Vendor_CUI,Vendor_Reg_Number,Vendor_Adress,Vendor_IBAN,Customer_Name,Customer_CUI,Customer_Reg_Number,Customer_Adress,Customer_IBAN,Number_Invoice,Date_Invoice,Project_Id,Value_Invoice,TVA_Value_Invoice,Total_Value_Invoice,Added,Added_By,Updated,Updated_By,Task_Id)';
