<?php

namespace console\models;

class GithubRepoFile extends \backend\modules\github\models\RepoFile
{
    public static $customAttributes = [];

    public static function processFiles($files)
    {
        foreach ($files as $file) {
            self::processFile($file);
        }
    }

    public static function processFile($file)
    {
        $pathHash = self::generateHash($file['path']);

        try {
            $repoFile = self::getByAttributes([
                'repo_id' => self::$customAttributes['repo_id'],
                'path_hash' => $pathHash
            ], [
                    'repo_id' => self::$customAttributes['repo_id'],
                    'path' => $file['path'],
                    'path_hash' => $pathHash
                ]);
        } catch (\Exception $exc) {
            throw new \Exception('1. Error getting the repo file. Error: ' . $exc->getMessage() . '. The file received details were: ' . json_encode($file));
        }

        if ($repoFile === null) {
            throw new \Exception('2. Error getting the repo file. The file received details were: ' . json_encode($file));
        }

        self::$customAttributes['change_type'] = $file['changeType'];
        self::$customAttributes['additions'] = $file['additions'];
        self::$customAttributes['deletions'] = $file['deletions'];

        self::$customAttributes['repo_file_id'] = $repoFile->id;

        GithubPullRequestFile::getByAttributes([
            'repo_file_id' => $repoFile->id
        ], self::$customAttributes);
    }

    public static function generateHash($text)
    {
        return hash('sha256', $text);
    }
}