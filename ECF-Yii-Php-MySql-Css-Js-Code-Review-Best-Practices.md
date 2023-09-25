1. Model
    - Generare Model
        - vom genera ModelNameParent.php in Gii
        - toate modificarile vor fi facute in ModelName.php, care va extinde ModelNameParent.php
        - de fiecare data cand vom aduaga coloane noi sau vom face alte modificarile la model, reguli noi de validare, etc, vom suprascrie modelul parinte ModelNameParent
    - Lucrul cu bazele de date
        - pentru fiecare request in bazele de date se va scrie codul nativ care a dus la folosirea functiei corespunzatoare din YII
        - INSERT
            ```sql 
            # ex cod sql:
            ex cod sql: INSERT INTO `ecf_adm`.`user` (`email`, `last_name`) VALUES ('cornel@leviatan.ro', 'Cornel');
            ```
            ```php 
            # ex cod Yii:
            $model = new User(); 
            $model->email = 'Cornel@leviatan.ro'; 
            $model->last_name = 'Cornel'; 
            $model->save();
            ```
        - SELECT
            ```sql 
            # ex cod sql:
            ex cod sql: SELECT `id`, `email` FROM `ecf_adm`.`user` WHERE `last_name` = 'Cornel' LIMIT 100, 10;
            ```
            ```php 
            # ex cod Yii:
            $rows = (new \yii\db\Query())->select(['id', 'email'])->from(User::tableName())->where("`last_name` = ':last_name'", [':last_name' => 'Cornel'])->limit(10)->offset(100)->all();
            ```
        - UPDATE
            ```sql 
            # ex cod sql: 
            UPDATE `ecf_adm`.`user` SET (`email` = 'cornel@leviatan.ro', `last_name` = 'Cornel') WHERE `id` = 1;
            ```
            ```php 
            # ex cod Yii:
            $model = User::find(1)->one(); 
            $model->email = 'Cornel@leviatan.ro'; 
            $model->last_name = 'Cornel'; 
            $model->save();
            ```
        - DELETE 
            ```sql 
            # ex cod sql:
            DELETE FROM `ecf_adm`.`user` WHERE `id` = 1;
            ```
            ```php 
            # ex cod Yii:
            $model = User::find(1)->one(); 
            $model->delete();
            ```
        - Mai multe exemple si documentatia oficiala YII:
            - https://www.yiiframework.com/doc/guide/2.0/en/db-query-builder
        - Mai multe exemple si alte informatii MySql:
            - https://www.mysqltutorial.org/mysql-basics/
            - https://www.techonthenet.com/mysql/index.php
        
2. View
    - HTML
        - NU vom folosi helpere de Yii!
        - EXCEPTII
            * cand generam grid-ul si formulare
            * in cazul in care vrem sa prevenim atacuri XSS
                - un asemena caz de preveniere este atunci cand primim date din forumlare complectate de urilizatori
            - avem de scris o bucata de cod de genul:
            ```php
            # folosim helper Yii
            return Html::a($ac->icons['eye-open'], ['view', 'id' => $model->id], [
                'class' => 'btn btn-xs btn-info',
                'style' => 'width:24px;',
                'data-toggle' => 'tooltip',
                'title' => Yii::t('app', 'View more details')
            ]);
            # nu folosim helper YII vom scrie codul html aferent
            echo Html::a(Yii::t('app', 'Import Articles'), ['import'], ['class' => 'btn btn-sm btn-info']);
            ```
        - Vom refolosi cat mai multe componente din tema care sta la baza aplicatiei 
    - CSS
        - ar trebui sa evitam folosirea cifrelor in denumirea claselor si al id-urilor, ex: '`section-1`' 
        - id-urile trebuie sa fie unice in pagina
        - clasele care sunt compuse din mai multe cuvinte si se folosesc doar in scopul bind-uri in JS si le vom concatena folosind '`_`' NU '`-`'
    - JS
    - Date de afisat
        - le vom genera tot timpul in controller/action si le vom trimite ca parametru catre view
