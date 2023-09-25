echo "======================================================================"
echo "-------------------- $(date) --------------------"
echo "======================================================================"

#get PATH of executed file
MY_PATH="$(dirname -- "${BASH_SOURCE[0]}")"

# import INSTANCE_NAME, DBHOST, PATH
if [ ! -f $MY_PATH/instance-local.conf ]
then
    echo "Fisierul $MY_PATH/instance-local.conf nu exista"
    echo "Variable de inserat INSTANCE_NAME, DBHOST, MYSQL_USER, MYSQL_PASSWORD, SQL_PATH"
    exit 1;
fi
source $MY_PATH/instance-local.conf


if  [ "$INSTANCE_NAME" ==  "" ]; then
    echo "Adauga variabila INSTANCE_NAME in $(pwd)/instance-name.conf "
    exit 1
fi

path_erp_modules="/var/www/ecf-erp/backend/modules"
# path_erp_modules="backend/modules"
echo "Modulele gasite:"
ls $path_erp_modules | grep -v 2del

# grep -v 2del:  eliminare din ecuatie modulele care contin 2del
ls $path_erp_modules | grep -v 2del | while read -r modul ; do
    echo "Cautare daca este folderul crontabjobs in modulul $modul"
    if [ -d "$path_erp_modules/$modul/crontabjobs" ];
    then
        echo "Are crontaburi"
        ls $path_erp_modules/$modul/crontabjobs | while read -r modul_cronjob ; do

            # verifica daca
            cron_instanta=$(echo $modul_cronjob | grep $INSTANCE_NAME | wc -l)
            if [ $cron_instanta -eq 0 ]; then
                echo "Nu se copiaza, $modul_cronjob nu e pentru instanta selectata $INSTANCE_NAME"
                continue
            fi
            # verifica sa nu fie cronjob rulat cu root
            ls $path_erp_modules/$modul/crontabjobs/$modul_cronjob
            aparitii_root=$(less  $path_erp_modules/$modul/crontabjobs/$modul_cronjob | grep root | wc -l)
            echo "aparitii_root: $aparitii_root"
            if [ $aparitii_root -ne 0 ]; then
                echo "Nu se copiaza, contine root"
                continue
            fi
            # verifica daca cronjob-ul a fost modificat
            cmp --silent $path_erp_modules/$modul/crontabjobs/$modul_cronjob /etc/cron.d/$modul_cronjob
            crontabul_a_fost_schimbat=$?
            if [ "$crontabul_a_fost_schimbat" ==  "0" ]; then
                echo "Nu se copiaza, continutul e identic"
                continue
            fi
            # modificare separatori de linie in cazul in care e de pe windows
            dos2unix $path_erp_modules/$modul/crontabjobs/$modul_cronjob
            # totul este ok, se copiaza
            echo "Copiere $path_erp_modules/$modul/crontabjobs/$modul_cronjob in /etc/cron.d/"
            cp $path_erp_modules/$modul/crontabjobs/$modul_cronjob /etc/cron.d/$modul_cronjob
        done
    else
        echo "Nu are crontaburi"
    fi
done
