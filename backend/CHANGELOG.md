### v0.0.0.1

- implementare import istoric articole din folosite in vechile proiecte

### v0.0.0.2

- implementare vizualizare lista facturilor care sunt in sistem (US#25744)

### v0.0.0.3

- implementare importul de date pentru echipamente beneficiari (US#26835)

### v0.0.0.4

- fixare problema afisare activitati document pentru checklist (Ticket #27160)

### v0.0.0.5

- implementare import istoric articole procurement (US#26257)

### v0.0.0.6

- implementare export liste de cantitati (US#26253)

### v0.0.0.7

- implementare export liste de cantitati (US#26253)

### v0.0.0.8

- prevenire afisare date incorect cand se schimba manual url-ul (Ticket #27186)

### v0.0.0.9

- implementare afisare previzualizare factura pentru print (US#26796 )

### v0.0.1

- v0.0.0.1
- v0.0.0.2
- v0.0.0.4
- v0.0.0.6

### v0.0.2

- v0.0.0.3
- v0.0.0.5
- v0.0.0.7
- v0.0.0.8
- v0.0.0.9

### v0.0.2.1

- fixare bug valoare prea mare cantitati (Ticket #27078)
- update pop stergere/activare categorii/subcategorii articole (Ticket #27099)
- fixare bug update categorii/subcategorii echipamente (Ticket #27180)

### v0.0.2.2

- fixare bug equipment/article price history (git #747)
- adaugare `use PhpOffice\PhpSpreadsheet\Writer\Xlsx` in QuantityListController (git #750)
- optimizare incarcare pagina documente checklist (git #752)

### v0.0.2.3

- implementare export fise tehnice echipamente in fiser .docx (US #26254)
- fixare bug assign activity to typology (#27832)
- fixare bug adaugare echipament din modala (#27846)
- fixare bug incarcare documente cand avem un filtru setat (#773)

### v0.0.2.4

- afisare proiecte/obiecte/specialitati/faze indiferent daca acestea sunt marcate a sterse (#27849)
- fixare bug adaugare activitati unei tipologii (#27899)
- update MsGraph client se secret
- modificare fisere de configurare, mutare parametri necesari in productie si adaugati pana acum in fisere locale de
  configurare

### v0.0.2.5

- adaugare metoda de calculare preturi min/max/avg pentru articole si echipamente (#26812/#27598)
- modificare animatie preloader

### v0.0.2.6

- implementare pagina index centralizator preturi (#27594)

### v0.0.3

- v0.0.2.1
- v0.0.2.2
- v0.0.2.3
- v0.0.2.4
- v0.0.2.5
- v0.0.2.6
- afisare floating bar in centralizator articole(#27597)
- afisare valoare de referinta in centralizatoare(#27595)

### v0.0.3.1

- chainDelete levelRoom(#28153)

### v0.0.3.2

- setare valori de referinta centralizator(#27701)
- chainDelete level(#28154)
- marcare articole in centralizator in functie de tipul istoricului de pret(#27703)

### v0.0.3.3

- chainActivate level(#28155)
- blocare modificari pret articol centralizator(#27704)
- preluare zile lucratoare din DB(#28188)
- blocare modificari toate preturile articolelor din centralizator(#27705)
- verificare zile lucratoare in DB inainte de salvare(#28232)

### v0.0.4

- dezactivare butoane Activeaza/Sterge la click pentru a preveni click-uri multiple
- afisare overlay aplicatie atunci cand se trimite cereri catre server folosind butoanele Activeaza/Sterge care a la
  onClick bind-uita functia `deleteActivateRecord`
- imbunatatiri apicatie zile lucratoare
- implementare activare(chain) nivel sters(#28156)
- implementare stergere(chain) obiect(#28157)
- alte imbuntatiri si bugfix-uri merge-uite deja in DEV
- alte optimizari si bugfix-uri(#826)
- inlaturare denumiri lungi in titlu pe anumite pagini(#28233)

### v0.0.4.1

- dezactivare butoane Activeaza/Sterge la click pentru a preveni click-uri multiple
- afisare overlay aplicatie atunci cand se trimite cereri catre server folosind butoanele Activeaza/Sterge care a la
  onClick bind-uita functia `deleteActivateRecord`
- imbunatatiri apicatie zile lucratoare
- implementare activare(chain) nivel sters(#28156)
- implementare stergere(chain) obiect(#28157)
- alte imbuntatiri si bugfix-uri merge-uite deja in DEV
- alte optimizari si bugfix-uri(#826)
- inlaturare denumiri lungi in titlu pe anumite pagini(#28233)

### v0.0.4.2

- implementare activare obiecte sterse(#853)
- implementare zile de lucru angajati
- implementare partiala devize(#860 #870)
- fixare bug-uri ebva/p&l(#861)

### v0.0.4.3

- adaugare migrare pentru tabela 'finance.client'(#876)
- afisare obiecte sterse(#28160, #872)
- imbunatatiri calendar zile de lucru (#28977, #28976, #28951, #880, #879, #878)

### v0.0.5

- adaugare migrare pentru tabela 'client'
- implementare partiala pagina devize(#875, #877)
- implementare chain delete proiect(US#28178 #883)
- implementare partiala pagina angajati(#885)
- bugfix EBVA (#888)

### v0.0.5.1

- implementare chain delete proiect(US#28179 #892)
- adaugare migrare pentru tabela finance.cost(US#28703 #893)

### v0.0.5.2

- adaugare noi functionalitati in implementarea pagini devize
- optimizari pagina devize

### v0.0.5.3

- modificare meniu stanga (US#28781 #911)
- implementare activare in lant proiecte sterse(US#28180 #902)
- bugfix-uri (TK#29145 #906)
- adaugare migrari noi folosite de domeniul financiar(US#28703 #907)

### v0.0.5.4

- generarea automata a articolelor din centralizator folosind trenzactii(TK#28584 #916)
- activare/stergere in lant tipologie(#919)
- imbunatatiri buton back si reload din pagini de editare(TK#29214 #920)
- regenerare modele dupa adaugare de coloane noi(#921)
- activare/stergere in lant activitati
- activare/stergere in lant specialitati

### v0.0.6

- implementare centralizator montaj(#929)
- imbunatatire functii setNames(#931)
- imbunatatire codului folosit la chainDelete(#932)
- implementare sterger/activare puncte de lucru(#933)
- implementare sterger/activare functie angajat(#935)
- implementare sterger/activare birouri(#936)
- implementare sterger/activare departamente(#937)
- implementare sterger/activare etape(#942)
- implementare sterger/activare checklist(#943)
- implementare centralizator echipamente(#946)
- optimizare cod pentru prevenire sql injections(#938)

### v0.0.6.1

- implementare afisare status pentru formulare F*(#939)
    - restictionare acces la formulare F* in functie de status
- implementare sterger/activare categorii articole(#952)

### v0.0.6.2

- fixare bug equipment_category, speciality_id nu este required(#973)
- adaugare migrare qa system table(#974)
- adaugre in meniu `qa/system/index`(#974)
- modificare foreign key pentru tabela `supplier_payment.supplier_id`(#974)
- adaugare traduceri lipsa(#975)
- implementare activare angajati(#976)
- implementare stergere toatala echipamente si dotari atunci cand stergem un centralizator(#978)
- bugfix: afisare proiect/obiect/specialitate in partea de sus a unei lista de cantitati(#979)
- bugfix: generare automata cod articol/echipament atunci cand se foloseste modala de adaugare(#980)
- implementare select2 pentru filtrele din paginile Zile lucratoare/Concedii/Invoiri(#981)
- implementare hint quantity list(#982)
- implementare afisare category/subcategorii echipamente/articole sterse in filtre liste de cantitati(#983)
- implementare chain delete/activate categorii echipamente(#984)

### v0.6.2

- fixare bug equipment_category, speciality_id nu este required(#973)
- adaugare migrare qa system table(#974)
- adaugre in meniu `qa/system/index`(#974)
- modificare foreign key pentru tabela `supplier_payment.supplier_id`(#974)
- adaugare traduceri lipsa(#975)
- implementare activare angajati(#976)
- implementare stergere toatala echipamente si dotari atunci cand stergem un centralizator(#978)
- bugfix: afisare proiect/obiect/specialitate in partea de sus a unei lista de cantitati(#979)
- bugfix: generare automata cod articol/echipament atunci cand se foloseste modala de adaugare(#980)
- implementare select2 pentru filtrele din paginile Zile lucratoare/Concedii/Invoiri(#981)
- implementare hint quantity list(#982)
- implementare afisare category/subcategorii echipamente/articole sterse in filtre liste de cantitati(#983)
- imlementare chain delete/activate categorii echipamente(#984)

### v0.6.4

- Implemented index page that displays the quantity list changes(#988)

### v0.6.5

- Extend unique equipment category name validation(#986)
- allow working day calendar multiple days' selection using Shift(#991)
- implemented centralizer index RBAC(#992)
    - added new permission `centralizerManager`
    - added new role `CentralizerAdmin`

### v0.6.6

- Afisare in titlu calcul zile concediu (#993)
- Optimizare coloane added by and updated by(#998)
- Centralizer estimate f4 features (#987)
- Afisare detalii modificari liste de cantitati(#994)
- Optimizare cod(#997)
- Remediere bug adaugare angajati by(#999)
- Adaugare sistem in meniu stinga + traduceri(#1000)
- Remediere bug buton activate/save(#1001)
- Modificare hint quantity update(#1002)
- Update traduceri(#1003)
- Remediere eroare php in activitati(#1004)
- Implementare o versiune de lucru pentru API

### v0.7.0

- am realizat bug-ul filtrul company pentru employee by @Mihnea15
- buton de salvat calendar default pe tot anul by @DianaBasoc
- Feature improvements app employee by @NDM1
- Article forms different names by @calin1214
- regenerare model Parent by @calin1214
- am instalat data-range by @calin1214
- edit percentage in Ebva sales by @Anca212000
- Quantity list changes bugs rezolve by @calin1214
- Mini improvments by @NDM1
- Unique name equipment category by @Mihnea15
- Button activate save bug by @NDM1
- Bug fix quantity add new equipment or feature by @NDM1
- Bug filters employee by @grimaliuc
- Edit last date import transaction by @Anca212000
- modificari in functiile de viewDeletedEntities by @calin1214
- Adugat header actiuni by @NDM1
- Afisare ehipamente active by @NDM1
- Bugs multiples by @grimaliuc
- Filter subcategory article by @NDM1
- am rezolvat cu update la angajati angajat preselectat by @calin1214
- export centralizer article by @grimaliuc
- Add record to quantity list changes by @calin1214
- chain delete activate equipment subcategory by @Mihnea15
- Bug text equipment by @NDM1
- Bug function back and reset by @NDM1
- chain delete activate equipment by @Mihnea15
- Edit transaction index by @Anca212000
- Edit index in balance by @Anca212000
- Create base rbac roles and permissions by @davidutzus
- Permission work location by @NDM1
- Chain delete activate article by @grimaliuc
- Edit index in balance by @Anca212000
- Order by code article quantity equipment quantity by @calin1214
- Bug account index file by @Anca212000
- Bug error price calc by @grimaliuc
- chain delete activate measure unit by @Mihnea15
- Unique code measure unit by @Mihnea15
- chain delete activate package by @Mihnea15
- chain delete activate brand by @Mihnea15
- Adaugare migrari domain , entity , subdomain si entity_domain by @grimaliuc
- domain entity subdomain models by @grimaliuc
- Adde new rbac migrations by @davidutzus
- adaugare unique text-equipment by @grimaliuc
- Bug financial reports by @Anca212000
- Ebva hot fix by @Anca212000
- validare ore invoire by @DianaBasoc
- alterare tabela employee by @DianaBasoc
- salvare istoric program de lucru by @DianaBasoc
- P&l hot fix by @Anca212000
- create migration fuel by @Mihnea15
- Fix chain delete activate equipment by @Mihnea15
- unique name equipment by @Mihnea15
- unique name package by @Mihnea15
- View deleted unit measure by @Mihnea15
- View deleted equipments by @Mihnea15
- View deleted brand by @Mihnea15
- Unique name brand by @Mihnea15
- Done bugs employee by @grimaliuc
- Change name variable url button activate by @NDM1
- am rezolvat cu filtrarea la nivel de modificat by @calin1214
- Create forms f3 f4 by @NDM1
- Am rezolvat bug-ul by @Mihnea15
- create purchasing by @Mihnea15
- Bug delete article by @Mihnea15
- flux article subcategory by @Mihnea15
- am optimizat codul pentru formularele quantity lists by @calin1214
- Edit ebva sales and costs by @Anca212000

### v0.7.1

- added schedule time tab in employee app by @DianaBasoc

### v0.7.2

- move acquisition table from crm to finance by @calin1214

### v0.8.1

- fix unique fuel name by @Mihnea15

### v0.8.2

- financiar improvements by @Anca212000
- start implementing F3/F4 forms

### v0.9.0

- start implementing Redmine integration
    - Feature tabela redmine office spent time by @DianaBasoc

### v0.9.1

- F3/F4 form improvements by @NDM1
- upload articles form improvements by @davidutzus

### v0.9.2

- added index and view files for accounting journal by @Anca212000
- improved cost center by @Anca212000
- alter cost center table by @Anca212000

### v0.9.3

- added new speciality by @NDM1
- F3/F4 improvements by @NDM1

### v0.10.0

- rezolvare bug filtrare recorduri modificate by @calin1214
- adaugare migrare si model ecf hr eval data arrived left by @DianaBasoc
- left side bar redmine office spent time by @DianaBasoc
- adaugare migrare si model ecf hr eval source by @DianaBasoc
- modify data f3 f4 forms by @NDM1
- Add new speciality by @NDM1
- Edit accounting journal by @Anca212000
- Disabled fields in update quantity list by @NDM1
- Notification first release by @calin1214

### v0.10.1

- adaugare migrare si model ecf hr eval analytics at the office by @DianaBasoc
- date importate de la api ul din redmine by @DianaBasoc
- calcul valori eval analytics by @DianaBasoc
- edit file name accounting journal by @Anca212000

### v0.10.2

- Bugfix: employee user data save by @calin1214
- am adaugat valoarea default a parametrilor by @calin1214
- Merge API repo back by @davidutzus
- Work with qa domain by @calin1214
- am modificat codul specialitatii din ICS in CS by @calin1214
- Form F2 by @NDM1 in https://github.com/mobutu/ecf-erp/pull/1109
- Feature: refactorizare cod by @DianaBasoc
- Feature: migrare si model ecf hr eval personal daily scrum by @DianaBasoc
- am facut traducerile by @calin1214
- Feature: date importate de la api redmine 2 by @DianaBasoc
- Feature: init accounting journal table by @Anca212000
- Edit file name balance import by @Anca212000
- Edit file name accounting journal import by @Anca212000
- Bugfix: lista de cantiati by @calin1214
- Edit validate import balance by @Anca212000
- Bugfix: se stergeau toate centralizatoarele inclusiv formu… by @calin1214
- Api move user spa implementation to user by @davidutzus
- am facut modificarile necesare pentru calcularea automata a cantitatii de baza by @calin1214
- Migrations for ecf pmp domain by @calin1214
- Edit last imported date pnl by @Anca212000
- Edit last imported date ebva by @Anca212000
- added filter for added column and edit year,month,company filter by @Anca212000

### v0.10.3

- device_id migrare si model by @LascarDaniel
- Car api by @grimaliuc
- Quantity list status completed by @NDM1
- Permission estimate by @NDM1
- Estimate F2 Table Auction Price by @NDM1
- Centralizer bug deleted article, equipment by @NDM1
- Estimate F1 by @NDM1
- Input equipment, article quantity by @NDM1
- Pmp modifications by @calin1214
- Add migration for car detail api by @grimaliuc
- Feature cars add page by @grimaliuc
- Quantity list modifications by @calin1214
- Feature adaugare categorie generala conturi contabile by @aiuresi
- Estimate category investment by @NDM1
- Estimate dg first chapter by @NDM1
- Bugs quantity list  by @NDM1
- Feature create spp field by @Anca212000
- Car detail api by @grimaliuc
- realizare modificari sugerate - administrare masini by @grimaliuc
- Feature import categorii generale by @aiuresi
- Feature categorii specifice by @aiuresi
- Pmp work by @calin1214
- Feature eliminare tabela ecf hr redmine office spent time by @DianaBasoc
- Feature asociere utilizatori Redmine cu utilizatori ERP by @DianaBasoc
- Feature generare model si crud pentru tabela eval employee by @DianaBasoc
- Feature modificare functie find user by by @DianaBasoc
- Pmp env by @calin1214
- Feature modificari structura tabele by @DianaBasoc
- Modificari pmp env by @calin1214
- Bug f3 f4 forms cd by @NDM1
- Feature variabile de cautare by @DianaBasoc
- Feature import rj validate messages by @Anca212000
- Car zone api history by @grimaliuc
- Modify api car zone history by @grimaliuc
- lucru pentru Andrei si Cornel by @calin1214
- Feature import nomenclator by @aiuresi
- adaugare trim() by @aiuresi
- modificari-api by @grimaliuc
- Adaugare traducere by @grimaliuc
- Feature import nomenclator update by @aiuresi
- modify-status by @grimaliuc
- api-final-msg by @grimaliuc
- finish message by @grimaliuc
- Estimate dg all chapters by @NDM1
- Feature on duplicate key update by @DianaBasoc
- Export antemasuratoare by @calin1214
- Bug form f3 equipment by @NDM1
- Small bugs rezolving by @calin1214
- Feature categorii specifice validari by @aiuresi
- Modificari pmp by @calin1214
- Migrari by @calin1214
- Feature dinamizare api by @DianaBasoc
- modificari api by @grimaliuc
- adaugare traduceri la importul nomenclatorului by @aiuresi
- Feature categorii generale update by @aiuresi

### v0.10.4

- Rbac migrations folder by @calin1214
- Create new cruds by @calin1214
- Feature categorii specifice update by @aiuresi
- Feature categorii specifice update fg by @aiuresi
- Feature conturi contabile modificari by @aiuresi
- Feature categorii generale order by by @aiuresi
- Improvements functions api by @grimaliuc
- adaugare functie pentru modificarea numarului de ordine by @aiuresi
- adaugare buton fa-minus disabled by @aiuresi
- P&L - actualizare order by by @aiuresi
- Feature conturi contabile actualizari by @aiuresi
- Feature categorii gs actualizari by @aiuresi
- Add new same article quantity list by @calin1214
- Improve car unlock action by @grimaliuc
- Bug cost center import by @Anca212000
- Bug validate import rj by @Anca212000
- edit date and alert messages
- bug-fix by @calin1214
- am pus nr crt by @calin1214
- Feature categorii generale import alerte by @aiuresi
- adaugare traducele buton delete categorie specifica by @aiuresi
- Feature categorii gs view by @aiuresi
- Feature categorii generale by @aiuresi
- Feature tab nou by @DianaBasoc
- Feature annual spp by @Anca212000
- Estimate dg data by @NDM1
- BUG calcule vizuale  by @NDM1
- done improvements by @grimaliuc
- Feature details view by @DianaBasoc
- Feature cookie by @DianaBasoc
- rezolvare bug by @calin1214
- Feature categorii specifice order by by @aiuresi
- Feature import nomenclator - reordonarea categoriilor by @aiuresi
- Feature actualizare meniu financiar by @aiuresi
- Creare tabela ecf hr eval pomodoro by @DianaBasoc
- Feature generare model si crud pentru tabela ecf hr eval pomodoro by @DianaBasoc
- Ascundere butoane la edit by @DianaBasoc
- Am modificat badge-danger cu barde-warning by @Mihnea15
- align photo
- Token based auth by @davidutzus
- Feature monthly spp report by @Anca212000
- Article quantity add new article by @calin1214
- translate buttons cancel/ok from pop-up imports by @Anca212000
- Bug add column
- add type 7 -> android-car-app for type of device by @grimaliuc
- Feature categorii specifice -  limit order by by @aiuresi
- afiasre mesaj de eroare la importul nomenclatorului by @aiuresi
- Price history by @calin1214
- selectarea a tot ce este
- Checklist checkbox repeat quiz by @calin1214
- Bug column added balance index by @Anca212000
- resolve bug
- Feature - P&L / ordonarea companiilor dupa nume by @aiuresi
- Import categorii generale - Valdiarea header by @aiuresi
- Adaugare asterix pentru order_by si prefix by @aiuresi
- edit Monthly Spp name's tab by @Anca212000
- Feature update order by delete by @aiuresi
- Edit scrollbar spp table by @Anca212000
- Feature activare categorie generala by @aiuresi
- Feature permisiuni financiar rbac by @aiuresi
- Feature filtrare categorie specifica dupa companie by @aiuresi
- Modify api available cars to request token by @Mihnea15
- Modify user auth to request UUID by @Mihnea15
- Creat PDF pe un template nou  by @LascarDaniel
- Feature filtrare cat gen by @aiuresi
- Estimate dg data save by @NDM1
- Quantity list message status error by @NDM1
- Update Formule Valoare BAZA, NCS si PTDE by @NDM1
- update-pv-pdf by @LascarDaniel
- BUG General Estimate by @NDM1
- adaugare migrare - alter table order_by by @aiuresi
- Bug input error by @NDM1
- Feature categorii specifice delete order by by @aiuresi
- Feature cat gen fix bug by @aiuresi
- Feature cat gen import bug by @aiuresi
- Modificare optiuni checklist by @calin1214
- Math operation article quantity by @calin1214
- rezolvare filtru modified by @calin1214
- Modificari pmp by @calin1214
- feature clasa SendSharePointMailHelper by @LascarDaniel
- improvments-pv-pdf by @LascarDaniel
- rezolvarea by @calin1214
- Add crud interface for settings table by @davidutzus
- Export f3 f4 by @NDM1
- feature-api-validate-journeys by @LascarDaniel
- Bug Export by @NDM1
- Bug export nr crt by @NDM1

### v0.10.5

- Feature adaugare filtre categorii specifice by @aiuresi
- Feature categorii generale sort by @aiuresi
- Feature - ordonarea alfabetica a companiilor by @aiuresi
- Feature ordonarea categoriilor specifice by @aiuresi
- Feature cat gen fix index bug by @aiuresi
- feature-api-journeys by @LascarDaniel
- bug-no-images-pdf  by @LascarDaniel
- Feature pnl resolve bug by @aiuresi
- improvments-pv-pdf-sign-position by @LascarDaniel
- pv-pdf-traducere by @LascarDaniel
- Feature conturi contabile permisiuni by @aiuresi
- Feature categorii specifice permisiuni by @aiuresi
- adaugare permisiuni import balanta by @aiuresi
- adaugare permisiune import by @aiuresi
- Improvements auto by @grimaliuc
- permisiune buton delete
- adaugare car_id pentru post auth by @grimaliuc
- Feature activarea unei categorii specifice by @aiuresi
- Actualziare functie pentru filtrarea prefixului by @aiuresi

### v0.10.6

- Feature - fix bug renumerotare order_by dupa activare categorie by @aiuresi
- Feature categorii generale specifice adaugare buton resetare filtre by @aiuresi
- Feature categorii specifice ordonare dupa companie by @aiuresi
- edit text Extinde/Restrange tabel from button spp by @Anca212000
- Bug filter companies import balance by @Anca212000
- adaugare migrare rbac auto by @grimaliuc
- improvments-pv-pdf-observations by @LascarDaniel
- Feature categorii specifice actualizare traducere by @aiuresi
- Clone checklist by @calin1214

### v0.10.7

- improvments-pv-pdf-sign-company by @LascarDaniel
- Feature categorii specifice imbunatatiri by @aiuresi
- Export Centralizator
- Feature view spp values by @Anca212000
- Feature filter asc companies by @Anca212000
- Bug by @NDM1

### v0.11.0

- Bug order asc companies ebva by @Anca212000
- feature-expired-documents by @LascarDaniel
- Feature adm tipuri locatii by @grimaliuc
- Feature locations by @grimaliuc
- Auiz analitics by @calin1214
- Quiz modifications by @calin1214
- feature-api-preview-pv by @LascarDaniel
- improvment-send-share-point-mail by @LascarDaniel
- Improvements location type location by @grimaliuc
- Send qty list changes notification to email  by @davidutzus
- validate header excel
- Feature categorii generale specifice modificari by @aiuresi
- validate header
- Feature actualizare afisare pnl by @aiuresi
- Adaugare traducere pentru centre de cost by @aiuresi
- Pmp modifications by @calin1214
- Update car model to match latest table changes by @davidutzus
- Bug f3f4 input by @NDM1
- Bug export centralizer by @NDM1
- Bug centralizer values (CAM, Equipment Percent, etc) by @NDM1
- Bug general estimate by @NDM1
- Feature modificare import nomenclator fg by @aiuresi
- Feature import nomenclator fg eliminare duplicate by @aiuresi
- Feature last imported date spp by @Anca212000
- Export f1 by @NDM1
- Preview pdf by @Mihnea15
- improvments-notifications-documents by @LascarDaniel
- Feature algoritm analytics pomodoros by @DianaBasoc

### v0.11.1

- Modificari pmp by @calin1214
- am finisat device by @calin1214

### v0.11.2

- Migration for journeys by @grimaliuc
- Am modificat font-size-ul ca nu era bun pentru telefon by @Mihnea15
- Improve auto document expiration notification by @davidutzus
- Feature api journey by @grimaliuc
- Am modificat titlu pentru pagina cu imagini din pdf by @Mihnea15
- Feature api journey by @grimaliuc
- Modificari pmp by @calin1214
- Am modificat api-ul
- Feature add button invoice by @Anca212000
- Bug F2 by @NDM1
- permissions by @Anca212000
- BUG back button by @NDM1
- Feature creare tabela ecf hr eval kpi working schedule by @DianaBasoc
- Feature generare model si crud pentru tabela ecf hr eval kpi working schedule by @DianaBasoc
- Feature api journey by @grimaliuc
- Feature import journey by @grimaliuc
- Feature -  Adauga / modifica IBAN din sectiunea „Companii” by @aiuresi
- edit message import account by @Anca212000
- Feature filtrare limitare date introduse by @aiuresi
- Export general estimate by @NDM1
- change-antet-pdf by @LascarDaniel

### v0.12.0

- change-pdf-auto-img-title by @LascarDaniel
- Send prev zone images to app by @davidutzus
- Import location continue by @grimaliuc
- adaugare parametru getHpList by @grimaliuc
- Feature validare iban by @aiuresi
- Centralizers filters by @NDM1
- Document filter by name by @calin1214
- Bug quantityListCloser by @NDM1
- Add by cod quantity list by @NDM1
- baza nu poate fi modificata desi apara ca modificata

### v0.12.1

- Feature code optimization layout change by @calin1214
- rezolvari cerute de marius by @calin1214
- Improvements location by @grimaliuc
- change-title-fuel-page by @LascarDaniel
- change-fuel-create-button-name by @LascarDaniel
- back_button_functionality by @calin1214
- Add loading export estimate by @NDM1
- F3 f4 forms filters by @NDM1
- Improvements journey import by @grimaliuc
- Bug Equipment by @NDM1
- feature-fuel-filter-by-user by @LascarDaniel
- Api articles equipments by @calin1214
- Import default user on user migrate by @calin1214
- feature-migration-car-accessory by @LascarDaniel
- feature-car-accessory-crud by @LascarDaniel
- feature-api-projects-list by @LascarDaniel
- Feature functionalitati imbunatatite SPP by @aiuresi
- Feature rapoarte financiare - Probleme rezolvate by @aiuresi
- Feature functionalitati imbunatatite P&L by @aiuresi
- Feature modificare meniu financiar by @aiuresi
- Finalizare modificari / Financiar / categorii generale si specifice by @aiuresi
- Adaugare traduceri
- Modificare latime coloane by @DianaBasoc
- Export quantity list by @NDM1

### v0.12.2

- Diacritice export by @NDM1
- Improvements auto pag by @grimaliuc
- Am facut ca preview-ul sa primeasca ce modificam noi
- improvements toogle by @grimaliuc
- Feature administrare zile lucratoare by @DianaBasoc
- Revit family source by @NDM1
- Revit project by @NDM1
- Import family revit by @NDM1

### v0.12.3

- Bug required vin auto by @grimaliuc
- Bug-fix-preview-pv by @Mihnea15
- Bug expiration date auto by @grimaliuc
- Bug filter date picker by @grimaliuc
- feature-see-deleted-fuel by @LascarDaniel
- bug-car-accessory-create-page by @LascarDaniel
- improve-car-messages by @LascarDaniel
- Am adaugat o clasa custom by @Mihnea15
- Add buttons by @NDM1
- Column Status PMP DEVICE by @NDM1
- improve-fuel-id-filter by @LascarDaniel
- Feature - Stergere "tip companie" by @aiuresi
- Feature - Functionalitati imbunatatite CRM - Companii by @aiuresi
- Feature - Modificare view IBAN by @aiuresi
- improve-accessory-permission by @LascarDaniel
- change-device-active-column by @LascarDaniel
- Am scos un height care imi facea imaginea
- send-prev-img-new-details by @LascarDaniel
- Bug import locatii
- Bug nu lua datele din baza de date by @Mihnea15
- Feature add date picker
- Feature modify details api add accessory by @grimaliuc
- Bug delete nexus car by @grimaliuc
- Bug nu ia date din baza de date by @Mihnea15
- Feature create migration model car accessory by @grimaliuc
- Feature administrare concedii by @DianaBasoc
- Feature administrare invoiri by @DianaBasoc
- Feature imbunatatiri crm companii validare cui by @aiuresi
- Order articles equipments by code exports by @NDM1
- Bug general estimate  by @NDM1
- Estimate closer role by @NDM1

### v0.12.3

- Bug Centralizer by @NDM1
- am facut functionarea operatiilor matematice +, -, *, / by @calin1214
- feature-filter-journey by @LascarDaniel
- Feature add tab car accessory by @grimaliuc
- Feature - Actualizari P&L / SPP by @aiuresi
- Qty list notification by @calin1214
- modificare denumire coloana quantity->default-qty by @grimaliuc
- Feature - APLICATII/RAPOARTE FINANCIARE/SPP by @aiuresi
- Save price from centralizer
- api by @calin1214
- Bug la modificare companie ramane angajatul celeilalte companii (la prima intrare pe pragina de concedii și invoiri) by @DianaBasoc
- wip by @calin1214
- Bug permission by @DianaBasoc

### v0.12.5

- API versioning implementation by @davidutzus
- Feature modificare buton full by @DianaBasoc
- improve-filter-journey by @LascarDaniel
- Feature API GET Car accessories by @LascarDaniel
- Import car list from nexus by @grimaliuc
- Bug - Select car by multiple users by @LascarDaniel
- Feature adaugare db ecf location by @aiuresi
- Feature update journey scope and status by @LascarDaniel
- Feature stergere camp administrator din crm companii by @aiuresi
- Toggle index by @DianaBasoc
- Pagination page size by @DianaBasoc
- api - accessory by @grimaliuc
- Add column type for journey migration by @LascarDaniel
- Improve update journey scope status by @LascarDaniel
- Improvements auto doc cards by @grimaliuc
- Bug Api search just by user by @LascarDaniel
- Bug Api cars/details block car by @LascarDaniel
- Bug general estimate by @NDM1
- Device alghoritms by @calin1214
- improvements-cards-auto-documents by @grimaliuc
- Bug modify car administration page by @grimaliuc
- Feature crm companii adaugare tva by @aiuresi
- Feature ascoierea unui departament cu o companie by @DianaBasoc
- Bug api cars/details by @LascarDaniel
- Feature preview pdf for fr7 app by @LascarDaniel
- Feature modificare layout angajati by @DianaBasoc
- Implement API http bearer auth by @davidutzus
- Translate page car detail tab by @LascarDaniel

### v0.12.6

- wip by @calin1214
- Improve style pv pdf by @LascarDaniel
- Integrate with v1 api by @LascarDaniel
- Feature pastrare iban nevalid by @aiuresi
- wip by @calin1214

### v0.13.0

- Send notification for expired car documents by @davidutzus
- Send separate notification for documents that don't have exp date set by @davidutzus
- Improve preview pdf by @LascarDaniel
- Bug preview-pv by @Mihnea15
- Add new referral options price by @NDM1
- preselected current month
- Feature crm companii modificare tva cui by @aiuresi
- done by @calin1214
- Feature asocierea unui birou cu o companie by @DianaBasoc
- Feature asocierea unui punct de lucru cu o companie by @DianaBasoc
- Bug fixing api by @LascarDaniel
- Preview-pv by @Mihnea15
- Revit Family Name by @NDM1
- Feature add invoice page by @Anca212000
- Feature administration journey page by @grimaliuc
- Preview by @Mihnea15
- Fr7 preview by @Mihnea15

### v0.13.1

- improvements preview by @Mihnea15
- Fixing bugs on car api by @LascarDaniel
- Improvements adm journeys by @grimaliuc
- Accessories by @Mihnea15
- Match pv-pdf with preview-pdf by @LascarDaniel
- Preview-pv accessories by @Mihnea15
- Adaugare judet
- Export bug by @NDM1
- Bug quantity list status auction by @NDM1
- Centralizers improvements by @NDM1
- Exports improvements by @NDM1

### v0.14.0

- Preview-pv by @Mihnea15
- Improvements journey adm by @grimaliuc
- Quantity list changes by @NDM1
- edit buttons
- Feature add invoice image upload by @Anca212000
- data ultimului import pt SPP by @Anca212000
- Modificare coloana nexus_car_id -> gps_car_id by @grimaliuc
- Export by @NDM1
- Feature - Imbunatatiri import conturi contabile by @aiuresi
- F3 f4 improvements by @NDM1
- Bug fix modify add contract number by @grimaliuc
- Feature text on footer by @Mihnea15
- adaugare adresa de email la cc pentru import masini din nexus by @grimaliuc
- Feature input plate number create modify by @grimaliuc
- Feature blocked account by @Anca212000
- Bug Centralizer by @NDM1
- Centralizers by @NDM1
- Export improvements by @NDM1
- Feature - Ordonare categorii generale / specifice by @aiuresi
- Feature - Actualizarea conturilor contabile la import by @aiuresi
- Add custom class to cards
- Centralizer by @NDM1
- Estimate f3 f4 by @NDM1
- modificare migrare rbac auto by @grimaliuc
- Aici by @calin1214
- Bug update office department name and index search filters by @DianaBasoc
- estimate f3 f4 by @NDM1
- Feature modificare filtre search + reset filters by @DianaBasoc
- Feature update accessories by @Mihnea15
- Bug fix administration auto rbac by @grimaliuc
- Bug update accessories by @Mihnea15
- Update cruds families by @NDM1
- Activate equipment to architecture speciality by @NDM1
- Bug email pdf by @Mihnea15
- Feature edit account columns by @Anca212000
- Feature - (IMB) Import centre de cost by @aiuresi
- Feature asocierea unei functii cu o companie by @DianaBasoc
- Feature - (IMB) Ordonare categorii generale / specifice by @aiuresi
- Feature delete account by @Anca212000
- Feature account general edits by @Anca212000
- Feature import locations with custom coordonates by @grimaliuc
- Bug accesory observation pdf by @LascarDaniel
- Bug font size text pdf by @LascarDaniel
- Improvements import journey add columns by @grimaliuc
- Feature angajati adaugare campuri noi by @DianaBasoc
- Feature hr left side menu by @DianaBasoc
- Bugs by @NDM1
- Improvements by @NDM1

### v0.15.0

- Feature account subcategory specific edits by @Anca212000
- Feature view account by @Anca212000
- Feature cost center delete by @Anca212000
- block cost center by @Anca212000
- Feature cost center create page by @Anca212000
- Feature cost center update page by @Anca212000
- Bug Centralizer by @NDM1
- Bug preview pdf observations by @LascarDaniel
- Bug by @NDM1
- Feature number rows per page cost center by @Anca212000
- Feature cost center index by @Anca212000
- Improvements price history by @NDM1
- Bug accessory observation pdf preview by @LascarDaniel
- Feature import locations with custom coordonates by @grimaliuc
- Feature number rows by @Anca212000
- Feature accounting journal index by @Anca212000
- Feature accounting journal view page by @Anca212000
- Bug email pdf by @Mihnea15
- Feature balance number rows by @Anca212000
- Feature balance view by @Anca212000
- edit index page for balance by @Anca212000
- (IMB) Import centre de cost - Update
- Feature import journeys list from nexus by @grimaliuc
- Feature modificari campuri angajat by @DianaBasoc
- Filters equipment index by @NDM1
- export by @NDM1
- Bug observation preview pdf by @LascarDaniel
- Feature import locations with custom coordonates by @grimaliuc
- Increase memory limit by @NDM1
- Order by ptde value by @NDM1
- Feature - (IMB) Import registru jurnal by @aiuresi
- Feature - (IMB) Import balante by @aiuresi
- Feature acquisitions index filter rows by @Anca212000
- Feature acquisitions index by @Anca212000
- Feature acquisitions view by @Anca212000
- Feature view countries by @Anca212000
- Bug block account by @Anca212000
- F3 by @NDM1
- show icons on enable/disable block by @Anca212000
- Feature - Administrare tranzactii by @aiuresi
- Feature indentification user
- Improvements import locations by @grimaliuc
- Feature - (IMB) Import balante by @aiuresi
- Cost index by @NDM1
- Feature countries nb rows on page by @Anca212000
- Feature nb rows index states by @Anca212000
- Feature view states by @Anca212000
- Feature cities nb rows by @Anca212000
- Feature view cities by @Anca212000
- Feature view index invoice by @Anca212000
- Centralizer improvements by @NDM1
- F1 header by @NDM1
- Category, Subcategory by @NDM1
- Feature modify filter reg number by @grimaliuc
- Duplicate estimate by @NDM1
- Export antemasuratoare, centralizatoare by @NDM1
- Create migration for documents history by @Mihnea15
- Feature modify name app by @grimaliuc
- Bug pv pdf by @LascarDaniel
- Modify inactive cars title by @Mihnea15
- Create model and controller by @Mihnea15
- crm company index by @Anca212000
- Qty list view mode forms by @calin1214
- Update Quanity List by @NDM1
- Centralizer index by @NDM1
- Feature edit add company by @Anca212000
- Feature - (IMB) Administrare categorii generale by @aiuresi
- Feature admin account category specific by @Anca212000
- Feature admin cost center by @Anca212000
- Migrare by @NDM1
- Centralizer permission by @NDM1
- Bug - Financiar / Categorii specifice by @aiuresi
- Bug - CRM / Companii by @aiuresi
- Bug-permisiuni-brand-model by @grimaliuc
- Feature car documents custom class by @LascarDaniel
- ascending order name by @Alexandru1099
- adaugare caractere alfanumerice by @Alexandru1099
- add select2
- Adaugare traduceri modal by @grimaliuc
- change position toogle by @Alexandru1099
- Feature add brand from cars adm by @grimaliuc
- Modify filter by @Alexandru1099
- Feature add fuel tab by @Mihnea15
- Feature save documents history by @Mihnea15
- Feature layout modal documents_history by @Mihnea15
- modificare structura card-uri
- Feature actualizare toggle by @aiuresi
- Pagination fuel page by @Alexandru1099
- Feature add brand model from car create by @grimaliuc
- Improve  auto car accesory tab by @LascarDaniel
- Centralizer fitting by @NDM1
- Feature admin accounting journal by @Anca212000
- reozlved bug label icon-active by @Alexandru1099
- ascending order to name by @Alexandru1099
- Restricting input by @Alexandru1099
- bug-auto-car-accesory-tab by @LascarDaniel
- Restricting name by @Alexandru1099
- Add select2 by @Alexandru1099
- Feature admin accounts by @Anca212000
- Upload files for documents by @Mihnea15
- Feature admin balances by @Anca212000
- Feature show car iamge by @LascarDaniel
- add toogle with functionality
- Feature auto remove holder user car by @LascarDaniel
- Feature add  view-history-icon by @Mihnea15
- Financiar / Import balanta by @aiuresi
- Feature add op casco from cars create by @grimaliuc
- Feature - CRM / Companii (Roluri și permisiuni) by @aiuresi
- Feature admin achizitii by @Anca212000
- width columns by @Anca212000
- Feature general states by @Anca212000
- (IMB) Generale Locații - modificari pagina index by @aiuresi
- (IMB) Administrare conturi contabile - Actualizare filtre by @aiuresi
- (IMB) Generale Locații - Orașe / Selectare numar randuri pe pagina si paginatie by @aiuresi
- Import revit by @NDM1
- Bug export by @NDM1
- Bug estimate by @NDM1
- Bug centralizer/f3f4 by @NDM1
- Improve document expiration notification by @LascarDaniel
- Export antemasuratoare by @NDM1
- (IMB) Generale - Facturi / Coloane dinamice by @aiuresi
- Export  by @NDM1
- Feature add gps tab by @Mihnea15
- Feature auto add filter status by @LascarDaniel
- Brand input name by @Alexandru1099
- ascending order to name by @Alexandru1099
- Feature add holder tab by @Mihnea15
- Feature crm brand model items per page by @LascarDaniel
- Improve crm brand model view by @LascarDaniel
- Feature add dynamic history
- Improve crm brandModel form by @LascarDaniel
- Feature caps lock CI by @DianaBasoc
- add pages-list by @Alexandru1099
- Improve crm brandModel column size by @LascarDaniel
- Improve crm brandModel pagination by @LascarDaniel
- add pages-list
- Improve crm brandModel filters by @LascarDaniel
- add pages-list
- Api journey send projects by @LascarDaniel
- Feature financiar categorii generale reordonare by @aiuresi
- (IMB)Registru Jurnal / Revizuire latime coloane by @aiuresi
- (IMB) Balante / pagina de index by @aiuresi
- (IMB) Generale Locații / Revizuire latime coloane by @aiuresi
- add pagination by @Alexandru1099
- Restriction name by @Alexandru1099
- Pagination brand by @Alexandru1099
- Change auto btn modal add by @LascarDaniel
- Feature imp upload files by @Mihnea15
- Feature remove details tab by @Mihnea15
- (IMB) Administrare categorii specifice / Ordonare fara refresh by @aiuresi
- (NEW) Generale - Facturi by @aiuresi
- (IMB) Administrare centre de cost / Actualizare latime coloane
- Recalculate general estimate by @NDM1
- Centralizer formula by @NDM1
- Eliminare tab "Concedii/Învoiri" by @DianaBasoc
- Feature create permision fuel by @Mihnea15
- (IMB) Administrare tranzactii by @aiuresi
- (IMB) Balante / Stergere coloana "Numar" by @aiuresi
- resolved bug migration-fuel-rbac by @Alexandru1099
- Add auto logo images brand  by @LascarDaniel
- Improve auto translations by @LascarDaniel
- order columns by @Alexandru1099
- Feature imp upload files by @Mihnea15
- Change filter status by @Alexandru1099
- Change crm brandModel setAddedBy/setUpdatedBy by @LascarDaniel
- Input-id by @Alexandru1099
- Feature create permission document history by @Mihnea15
- Înregistrări pe pagină by @Alexandru1099
- order ascending order to name by @Alexandru1099
- Feature create permission car holder detail by @Mihnea15
- Input id location type by @Alexandru1099
- Filter2 add input by @Alexandru1099
- Feature afisare numar angajati activi inactivi by @DianaBasoc
- Bug filtru nume angajat by @DianaBasoc
- change UpdatedBy => updateBy and same with addedBy by @Alexandru1099
- Add toogle by @Alexandru1099
- Bug draw calendar last line by @DianaBasoc
- Feature invoiri by @DianaBasoc
- Feature dimensiuni coloane hr by @DianaBasoc
- add sort for added by @Alexandru1099
- Bug modal document history by @Mihnea15
- Bug auto car tabs by @LascarDaniel
- Input id location by @Alexandru1099
- Export and Centralizers by @NDM1
- Bug auto filter status by @LascarDaniel
- Feature tab history cars by @grimaliuc
- Modify modal by @Mihnea15
- Add input company by @Alexandru1099
- Centralizer by @NDM1
- Feature center updated by @DianaBasoc
- Recalculate General Estimate by @NDM1
- Remove btn import location by @Alexandru1099
- Bug modal by @Mihnea15
- Feature add car operator rca by @grimaliuc
- pmp by @NDM1
- Bug work location pagination by @DianaBasoc
- pmp by @NDM1
- Redenumire device type
- Feature filtrarea după utilizatorul care a adaugat by @DianaBasoc
- Prices by @NDM1
- Feature filtrarea dupa utilizatorul care a modificat (din branch-ul de pe #35159) by @DianaBasoc
- CRM / Companii by @aiuresi
- Bug estimate by @NDM1
- (IMB) Administrare conturi contabile / Imbunătățiri aduse la floating bar by @aiuresi
- Feature edit add invoice by @Anca212000
- Financiar / Facturi / Migrare by @aiuresi
- Financiar / Facturi by @aiuresi
- Articles notifications by @calin1214
- Feature add employee by @DianaBasoc
- Bug company model by @Anca212000
- Feature add file extension restriction by @Mihnea15
- add number of records by @Alexandru1099
- Feature view number of records
- Financiar / Facturi by @aiuresi
- Modify fuel permission by @Mihnea15
- Feature api view journeys remove hotspot name by @LascarDaniel
- Feature location activate view page by @LascarDaniel
- Feature view number of records
- Bug journey filter by @LascarDaniel
- Improvements holder car by @grimaliuc
- Multiple delete journeys by @Alexandru1099
- Feature add company
- Butoane de la "Adaugă angajat" by @DianaBasoc
- Financiar / Conturi contabile by @aiuresi
- update-version-1.0.5 by @Alexandru1099
- Feature edit view invoice by @Anca212000
- Bug reset modify buttons by @Anca212000
- Feature buttons acquisition by @Anca212000
- Feature canvas upload by @Anca212000
- Feature accept only alphanumeric by @Mihnea15
- Improvements index locations by @grimaliuc
- Feature delete journey by @grimaliuc
- tabela noua employee auto fleet by @DianaBasoc
- tab nou flote auto (din branch feature-tabela-nou-employee-auto-fleet) by @DianaBasoc
- descriere tab nou flote auto (din branch feature-tab-nou-flote-auto) by @DianaBasoc
- Bug export by @NDM1
- Export by @NDM1
- add new  versione
- Code refactoring for journey and location by @LascarDaniel
- Uptate app auto version 1.0.6 by @LascarDaniel
- Improvements journeys by @Alexandru1099
- (IMB) Administrare conturi contabile / Imbunatatire floating bar by @aiuresi
- Feature change action location column by @grimaliuc
- Fixed bug checkbox by @Alexandru1099
- Bug finance account floating bar by @aiuresi
- Feature accept only alphanumeric by @Mihnea15
- modificare api pentru preluare companii by @grimaliuc
- Feature edit form buttons acq by @Anca212000
- Feature update invoice by @Anca212000
- Report general estimate by @NDM1
- Feature salvare actualizare date
- Report general estimate by @NDM1
- wip by @DianaBasoc
- Report by @NDM1
- Feature edit plus buttons add invoice by @Anca212000
- Feature disable buttons cost center by @Anca212000
- Improvements api journey by @grimaliuc
- Feature disable butt category gen spec by @Anca212000
- Feature crm disable buttons by @Anca212000
- Feature edit form buttons account by @Anca212000
- Improvements number of visits by @grimaliuc
- Improvements details api journeys by @grimaliuc
- (NEW) Ștergerea unei facturi by @aiuresi
- Bugs by @NDM1
- (NEW+IMB) Centre de cost / Adăugare coloana "Select" si floating bar by @aiuresi
- (NEW+IMB) Achizitii / Adaugare coloana "Select" si floating bar by @aiuresi
- (NEW+IMB) CRM/Companii / Adăugare floating bar by @aiuresi
- Filtrarea dupa identificator by @DianaBasoc
- Feature modificare denumire tabela by @DianaBasoc
- Equipment by @NDM1
- Feature location map view by @LascarDaniel
- update cod pentru validare calatorie din _form by @grimaliuc
- bug-integrity-violation-user-id by @grimaliuc
- adaugare oninput -> diacritice name & address by @grimaliuc
- bug-update-scope-journey by @grimaliuc
- Bug api journey send deleted journey by @LascarDaniel
- Add column short name by @grimaliuc
- bug-availble-cars by @grimaliuc
- resolved bug disabled select2 project and type by @Alexandru1099
- improvements-available-cars by @grimaliuc
- feature-api-short-name-company by @grimaliuc
- update version
- Feature button import crm by @Anca212000
- add version
- Modificare latime butoane adauga / import by @aiuresi
- Improvements delete multiple journeys by @grimaliuc
- (IMB) Facturi / Actualizare status factura by @aiuresi
- add btn delete when journeys is invalidate by @Alexandru1099
- improvements-delete-unused-filter-code-api-available-cars by @grimaliuc
- Feature tab alte platforme by @DianaBasoc
- Activi/inactivi - apar inversati by @DianaBasoc
- Bugs report by @NDM1
- Export by @NDM1
- Mousehover pe butonul on/of by @DianaBasoc
- Modificarea detaliilor unei facturi / Previzualizare imagine factura by @aiuresi
- Feature imagine asociata utilizator by @DianaBasoc
- Imagine asociată utilizator by @DianaBasoc
- Feature imagine gen by @DianaBasoc
- finance / invoice/ upload by @aiuresi
- Feature view invoice image by @Anca212000