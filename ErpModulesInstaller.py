

import importlib
import json
import logging
from   logging.handlers import RotatingFileHandler
import os
import re
import subprocess
from  sys import stdout

# get configuration
erp_config = {}
try:
    from  erp_config import erp_config
except ModuleNotFoundError as err:
    print(err)
    pass

erp_config.update({"project_path": erp_config.get("project_path",  os.path.dirname(os.path.abspath(__file__)))})
erp_config.update({"log_file": erp_config.get("log_file", str(erp_config.get("project_path")) + "/console/runtime/logs/erp_installer.log")})
erp_config.update({"log_level": erp_config.get("log_level", "INFO")})
print(erp_config)

log_formatter = logging.Formatter('%(asctime)s.%(msecs)03d | %(levelname)s | %(funcName)s(%(lineno)d) | %(message)s', "%z %Z %Y-%m-%d %I:%M:%S")

# rotate logs at 250MB
my_handler = RotatingFileHandler(erp_config.get("log_file"), mode='a', maxBytes=250*1024*1024,
                                 backupCount=2, encoding=None, delay=False)

my_handler.setFormatter(log_formatter)
app_log = logging.getLogger()
app_log.setLevel(erp_config.get("log_level"))

app_log.addHandler(my_handler)

i = 0
for i in range(5):
    try:
        importlib.import_module('git')
    except ModuleNotFoundError as err:
        app_log.warning('ImportError: {}'.format(err))
        try:
            proc = subprocess.run("pip install GitPython", shell=True, universal_newlines = True, stdout = subprocess.PIPE, stderr=subprocess.PIPE)
            app_log.info(proc.stdout)
            if proc.stderr != "":
                app_log.error(proc.stderr)
        except Exception as err:
            app_log.error( "Error: " + str(err) )
if i == 5:
    app_log.error("Fail to install GitPython")

from git import Repo
from git import cmd
from git import Remote
from git import exc

# we are looking in .gitignore for not excluded modules from /backend/modules
def get_core_modules():
    core_modules = []
    with open(".gitignore","r") as file:
        for line in file:
            if re.search('!/backend/modules/', line):
                module = line.strip("!\/\n").split('/')
                core_modules.append(module[ len(module)-1 ])
    core_modules.append('api')
    return core_modules

def get_modules():
    error_code = 0
    modules_set = []
    ok_modules = []
    ok_core_modules = []
    composer_json = None
    
    try:
        composer = open('erp_modules.json')
        composer_json = json.load(composer)
        if 'require' not in composer_json.keys():
            app_log.warning("There is no \'require\' node in erp_modules.json'")
            quit(0)
        if 'repositories' not in composer_json.keys():
            app_log.warning('There is no \'repositories\' node in erp_modules.json')
            quit(0)

        required_modules = composer_json['require']
        for module in required_modules:
            modules_set.append(module)
            if module not in composer_json['repositories'].keys():
                app_log.warning('The module {} doesn\'t exist in erp_modules.json'.format(module))
                error_code = 1
                continue
            if 'url' not in composer_json['repositories'][module].keys():
                app_log.warning('Url for module {} is not set '.format(module))
                error_code = 1
                continue
            if 'name' not in composer_json['repositories'][module].keys():
                app_log.warning('Name for module {} is not set '.format(module))
                error_code = 1
                continue
            ok_modules.append(module)


    except IOError as e:
        # print("Couldn't open or write to file (%s)." % e) # python 2
        app_log.warning("Couldn't open the file: {}".format(e))
        error_code = 2
    except ValueError as e:  # includes simplejson.decoder.JSONDecodeError 
        app_log.warning("Decoding JSON has failed:  {}".format(e))
        error_code = 3
        
    if error_code == 2:
        quit(0)
    if error_code == 3:
        quit(1)
    if len(required_modules) == 0:
        app_log.info("No module required to be installed")
        quit(0)
    if  len(ok_modules) == 0:
        app_log.warning("No requested module has correct settings")
        quit(1)
    return composer_json, modules_set, ok_modules


def install_modules():
    try:
        composer_json, required_modules, ok_modules = get_modules()
        app_log.info("Core modules: {}".format(get_core_modules()))
        app_log.info("Modules required to be installed: {}".format(required_modules))
        app_log.info("Modules configured right: {}".format(ok_modules))

        run_core_migrations()
        for module in ok_modules:
            module_url = composer_json['repositories'][module]["url"]
            module_name = composer_json['repositories'][module]["name"]
            branch = composer_json['require'][module]
            install_module(module_url, module_name, branch)
    except Exception as err:
        app_log.error("Error on installing the module: {}".format(str(err)))

def run_module_migrations(module_name):
    if module_name == "":
        migrate_command = "migrate"
    else:
        migrate_command = "migrate-{}".format(module_name)
    try:
        app_log.info("Running migration for {}".format(module_name))
        proc = subprocess.run("php yii {} --interactive=0 ".format(migrate_command), shell=True, universal_newlines = True,  capture_output=True)
        if proc.returncode != 0:
            if re.search("Unknown command:", proc.stderr):
                app_log.warn(proc.stderr)
            else:
                app_log.error("Error code: {}.".format(proc.returncode))
                if proc.stderr != "":
                    app_log.error(proc.stderr)
        if proc.stdout != "":
            app_log.info(proc.stdout)
        proc = subprocess.run("php yii {}-rbac --interactive=0 ".format(migrate_command), shell=True, universal_newlines = True, stdout = subprocess.PIPE, stderr=subprocess.PIPE)
        if proc.returncode != 0:
            if re.search("Unknown command:", proc.stderr):
                app_log.warn(proc.stderr)
            else:
                app_log.error("Error code: {}.".format(proc.returncode))
                if proc.stderr != "":
                    app_log.error(proc.stderr)
        if proc.stdout != "":
            app_log.info(proc.stdout)
    except Exception as err:
        app_log.error( "Error: {}".format(err) )


def install_module(module_url, module_name, branch):
    try:
        app_log.info("Installing module: {}, {}, {}".format(module_name, module_url, branch))
        if not module_url:
            app_log.warning('The module url cannot be set. {}'.format(module))
        if not module_name:
            app_log.warning ('The module name cannot be set {}'.format(module))

        module_path = "{}/backend/modules/{}".format(erp_config.get("project_path"),module_name)
        if ( not os.path.exists(module_path) ):
            app_log.warning("cloneaza modulul: " + module_name)
            Repo.clone_from(module_url, module_path).git.checkout(branch)
            run_module_migrations(module_name)
        else:
            app_log.warning("The module {} is installed. Updateing it".format(module_name))
            update_module(module_url, module_name, branch)
    except exc.GitError as gitError:
        app_log.error("Error cloning repo. ".format(gitError))
    except Exception as err:
        app_log.error("Error on installing the module: {}".format(err))

def update_modules():
    composer_json, required_modules, ok_modules = get_modules()
    core_modules = get_core_modules()
    app_log.info("Core modules: {}".format(get_core_modules()))
    app_log.info("Modules required to be installed: {}".format(required_modules))
    app_log.info("Modules configured right: {}".format(ok_modules))

    if required_modules == 0:
        return 0
    try:
        for module in ok_modules:
            module_url = composer_json['repositories'][module]["url"]
            module_name = composer_json['repositories'][module]["name"]
            branch = composer_json['require'][module]
            update_module(module_url, module_name, branch)
    except Exception as err:
        app_log.error("Something went wrong on updating the module.  {}".format(err))


def update_module(module_url, module_name, branch):
    try:
        app_log.info("modulul: " + module_name)
        module_path = "backend/modules/{}".format(module_name)
        if ( not os.path.exists(module_path) ):
            app_log.info("The module: {} is not installed".format(module_name))
            install_module(module_url, module_name, branch)
        else:
            repo = Repo(module_path)
            repo.git.fetch()
            repo.git.checkout(branch)
            if repo.is_dirty():
                app_log.error('There are changes in the repo {}. It will not be updated.'.format(module_name))
            else:
                app_log.info("Updating module")
                origin = Remote(repo, 'origin')
                origin.pull(rebase=True)
            run_module_migrations(module_name)
    except exc.GitError as gitError:
        app_log.error("{}".format(gitError))
    except Exception as err:
        app_log.error("Something went wrong on updating the module. {}".format(err))

def update_core(core_path=erp_config.get("project_path"), branch="main"):
    try:
        if ( not os.path.exists(core_path) ):
            app_log.error("Error: Path to core not found.")
        else:
            repo = Repo(core_path)
            repo.git.fetch()
            repo.git.checkout(branch)
            if repo.is_dirty():
                app_log.error('There are changes in the repo. It will not be updated.')
            else:
                app_log.info("Updating core")
                origin = Remote(repo, 'origin')
                origin.pull(rebase=True)
            
            proc = subprocess.run("/usr/local/bin/composer install --working-dir={}".format(core_path), shell=True, universal_newlines = True,  capture_output=True)
            if proc.returncode != 0:
                app_log.error("Error code: {}.".format(proc.returncode))
            if proc.stdout != "":
                app_log.info(proc.stdout)
            if proc.stderr != "":
                app_log.info(proc.stderr)

            run_core_migrations()
    except exc.GitError as gitError:
        app_log.error("{}".format(gitError))
    except Exception as err:
        app_log.error("Something went wrong on updating the module. {}".format(err))

def run_core_migrations():
    try:
        core_modules = get_core_modules()
        app_log.info("Core modules: {}".format(core_modules))
        run_module_migrations("")
        for core_module in core_modules:
            run_module_migrations(core_module)
    except Exception as err:
        app_log.error("Error installing the module: {}".format(err))

def update_erp(core_path=erp_config.get("project_path"), branch="main"):
    update_core(core_path, branch)
    update_modules()
