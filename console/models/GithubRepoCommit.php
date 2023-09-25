<?php

namespace console\models;

class GithubRepoCommit extends \backend\modules\github\models\RepoCommit
{
    public static $customAttributes = [];

    public static function processCommits($commits)
    {
        foreach ($commits as $commit) {
            self::processCommit($commit['commit']);
        }
    }

    public static function processCommit($commit)
    {
        self::$customAttributes['oid'] = $commit['oid'];
        self::$customAttributes['author_id'] = 0;
        if (
            !empty($commit['author'])
            && !empty($commit['author']['user'])
            && !empty($commit['author']['user']['login'])
        ) {
            self::$customAttributes['author_id'] = GithubCollaborator::getCollaboratorIDByUsername($commit['author']['user']['login']);
        }
        self::$customAttributes['url'] = $commit['url'];
        self::$customAttributes['changed_files'] = $commit['changedFilesIfAvailable'];
        self::$customAttributes['additions'] = $commit['additions'];
        self::$customAttributes['deletions'] = $commit['deletions'];
        self::$customAttributes['message'] = $commit['message'];
        self::$customAttributes['created_at'] = self::formatDateTime($commit['committedDate']);
        self::$customAttributes['pushed_at'] = self::formatDateTime($commit['pushedDate']);

        try {
            $repoCommit = self::getByAttributes([
                'repo_id' => self::$customAttributes['repo_id'],
                'oid' => self::$customAttributes['oid']
            ], self::$customAttributes);
        } catch (\Exception $exc) {
            throw new \Exception('1. Error getting the repo commit. Error: ' . $exc->getMessage() . '. The commit received details were: ' . json_encode($commit));
        }

        if ($repoCommit === null) {
            throw new \Exception('2. Error getting the repo commit. The commit received details were: ' . json_encode($commit));
        }

        self::$customAttributes['repo_commit_id'] = $repoCommit->id;

        GithubPullRequestCommit::getByAttributes([
            'repo_commit_id' => $repoCommit->id
        ], self::$customAttributes);
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
}