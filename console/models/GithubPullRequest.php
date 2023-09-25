<?php

namespace console\models;

use DateTime;

class GithubPullRequest extends \backend\modules\github\models\PullRequest
{
    public $forceUpdate = false;
    public static $customAttributes = [];
    public static function setCustomAttributes($repo, $pr)
    {
        $hash = self::generateHash(json_encode($pr));

        $author = '';
        if (
            !empty($pr['author'])
            && !empty($pr['author']['login'])
        ) {
            $author = $pr['author']['login'];
        }
        $authorID = GithubCollaborator::getCollaboratorIDByUsername($author);

        $mergedByID = 0;
        if (
            !empty($pr['mergedBy'])
            && !empty($pr['mergedBy']['login'])
        ) {
            $mergedByID = GithubCollaborator::getCollaboratorIDByUsername($pr['mergedBy']['login']);
        }

        self::$customAttributes = [
            'repo_id' => $repo['id'],
            'hash' => $hash,
            'pr_id' => $pr['id'],
            'pr_number' => $pr['number'],
            'pr_url' => $pr['url'],
            'pr_title' => $pr['title'],
            'pr_author' => $author,
            'pr_author_id' => $authorID,
            'created_at' => self::formatDateTime($pr['createdAt']),
            'updated_at' => self::formatDateTime($pr['updatedAt']),
            'locked' => $pr['locked'] ? 1 : 0,
            'closed' => $pr['closed'] ? 1 : 0,
            'closed_at' => self::formatDateTime($pr['closedAt']),
            'merged' => $pr['merged'] ? 1 : 0,
            'merged_at' => self::formatDateTime($pr['mergedAt']),
            'merged_by' => $mergedByID,
            'additions' => $pr['additions'],
            'deletions' => $pr['deletions'],
            'changed_files' => $pr['changedFiles'],
            'commits' => $pr['commits']['totalCount'],
            'state' => $pr['state']
        ];
    }

    public static function formatDateTime($graphQLDateTime)
    {
        // aceasta functie va converti valoarea datetime trimisa de GitHub GraphQL 2023-03-14T07:39:04Z
        // intr-un format ca acesta: 2023-03-14 07:39:04

        $newDateTime = str_replace('Z', '', $graphQLDateTime);

        $pieces = explode('T', $newDateTime);
        if (count($pieces) != 2) {
            return $newDateTime;
        }

        $newDateTime2 = implode(' ', $pieces);
        if (strtotime($newDateTime2)) {
            return $newDateTime2;
        }

        return $newDateTime;
    }

    public static function generateHash($text)
    {
        return hash('sha256', $text);
    }

    public function process($repo, $node)
    {
        if (empty(self::$customAttributes)) {
            self::setCustomAttributes([
                'id' => $repo['id']
            ], $node);
        }
        GithubPullRequest::$customAttributes['pull_request_id'] = $this->id;

        if (
            empty($this->pull_request_history_id)
            || $this->forceUpdate
        ) {
            $prHistory = GithubPullRequestHistory::createByAttributes(GithubPullRequest::$customAttributes);
            $this->updateByAttributes([
                'pull_request_history_id' => $prHistory->id
            ]);

            $this->savePrFiles($repo, $node, $prHistory->id);
            $this->savePrCommits($repo, $node, $prHistory->id);

            return true;
        }

        if ($this->hash == GithubPullRequest::$customAttributes['hash']) {
            return true;
        }

        $prHistory = GithubPullRequestHistory::createByAttributes(GithubPullRequest::$customAttributes);
        $this->updateByAttributes([
            'pull_request_history_id' => $prHistory->id
        ]);

        $this->savePrFiles($repo, $node, $prHistory->id);
        $this->savePrCommits($repo, $node, $prHistory->id);

        return true;
    }

    public function savePrFiles($repo, $node, $prHistoryID)
    {
        if (
            empty($node['files'])
            || empty($node['files']['nodes'])
        ) {
            return false;
        }
        $this->log("Repo {$repo['name']} - Pull request {$this->pr_number} - Files: " . count($node['files']['nodes']));

        GithubRepoFile::$customAttributes = [
            'repo_id' => $repo['id'],
            'pull_request_id' => $this->id,
            'pull_request_history_id' => $prHistoryID
        ];
        GithubRepoFile::processFiles($node['files']['nodes']);
    }

    public function savePrCommits($repo, $node, $prHistoryID)
    {
        if (
            empty($node['commits'])
            || empty($node['commits']['nodes'])
        ) {
            return false;
        }
        $this->log("Repo {$repo['name']} - Pull request {$this->pr_number} - Commits: " . count($node['commits']['nodes']));

        GithubRepoCommit::$customAttributes = [
            'repo_id' => $repo['id'],
            'pull_request_id' => $this->id,
            'pull_request_history_id' => $prHistoryID
        ];
        GithubRepoCommit::processCommits($node['commits']['nodes']);
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