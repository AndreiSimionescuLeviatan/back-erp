<?php

namespace console\controllers;

use DateTime;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use console\models\GithubRepo;

class GithubGetPullRequestsController extends Controller
{
    public function actionIndex($repoName = '', $repoOwner = '')
    {
        ini_set("memory_limit", "4096M");
        set_time_limit(0);

        $repos = [];

        if (!empty($repoName) && !empty($repoOwner)) {
            $repo = GithubRepo::findOneByAttributes([
                'name' => $repoName,
                'owner' => $repoOwner
            ]);
            if ($repo !== null) {
                $repos[] = $repo;
            }
        }

        if (empty($repos)) {
            $repos = GithubRepo::findAllByAttributes(['deleted' => 0]);
            if (empty($repos)) {
                $this->log('No repos to get the PRs for');
                return ExitCode::OK;
            }
            $this->log('The repositories list was built from a local file');
        }

        foreach ($repos as $repo) {
            $this->log("Repo {$repo['name']} - getting pull requests from GitHub ...");

            $setting = [
                'limit' => 1000000,
                'pageSize' => 10
            ];

            try {
                $repo->savePRs($setting);
            } catch (\Exception $exc) {
                $this->log($exc->getMessage());
                continue;
            }

            $this->log("Repo {$repo['name']} - finished saving locally pull requests");
        }

        $this->log('Finished saving locally the repos pull requests');

        return ExitCode::OK;
    }

    private function log($message)
    {
        echo $this->currentDateTime() . " - {$message}" . "\n";
    }

    private function currentDateTime()
    {
        $now = DateTime::createFromFormat('U.u', microtime(true));
        return $now->format("Y-m-d H-i-s.u");
    }
}