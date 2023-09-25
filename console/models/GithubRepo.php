<?php

namespace console\models;

use DateTime;
class GithubRepo extends \backend\modules\github\models\Repo
{
    public function savePRs($setting)
    {
        if ($setting['limit'] <= 0) {
            return true;
        }

        $after = '';
        if (!empty($setting['endCursor'])) {
            $after = 'after: "' . $setting['endCursor'] . '"';
        }

        $first = min($setting['pageSize'], $setting['limit']);

        $query = <<<GRAPHQL
        {
            search(query: "repo:mobutu/ecf-erp-core is:pr is:open created:2023-04-20T00:00:00..2023-04-20T23:59:59") {
              
              
            }
          }
          
          query {
            search(
              first: 10,
              query: "repo:mobutu/ecf-erp-core is:pr updated:2023-04-20T00:00:00..2023-04-20T23:59:59 sort:updated-desc",
              type: ISSUE
            ) {
              issueCount
              pageInfo {
                  hasNextPage
                  startCursor
                  endCursor
              }
              nodes {
                ... on PullRequest {
                  repository {
                      name
                      owner {
                          login
                      }
                  }
                  id
                  number
                  url
                  title
                  author {
                      login
                  }
                  labels (
                      first: 100
                  ) {
                      totalCount
                      pageInfo {
                          hasNextPage
                          startCursor
                          endCursor
                      }
                      nodes {
                          color
                          name
                      }
                  }
                  createdAt
                  updatedAt
          
                  state
          
                  locked
          
                  closed 
                  closedAt
          
                  merged
                  mergedAt 
                  mergedBy {
                      login
                  }
          
                  additions
                  deletions
          
                  changedFiles
          
                  files (
                      first: 100
                  ) {
                      totalCount
                      pageInfo {
                          hasNextPage
                          startCursor
                          endCursor
                      }
                      nodes {
                          path
                          changeType 
                          additions
                          deletions
                      }
                  }
          
                  commits (
                      first: 100
                  ) {
                      totalCount
                      pageInfo {
                          hasNextPage
                          startCursor
                          endCursor
                      }
                      nodes {
                          commit {
                              url 
                              oid
                              changedFilesIfAvailable
                              additions
                              deletions 
                              message 
                              committedDate 
                              pushedDate 
                              author {
                                  user {
                                      login
                                  }
                              }
                          }
                      }
                  }
          
                  reviewRequests (
                      first: 100
                  ) {
                      totalCount
                      pageInfo {
                          hasNextPage
                          startCursor
                          endCursor
                      }
                      nodes {
                          asCodeOwner 
                          requestedReviewer {
                              ... on User {
                                  login
                              }
                          }
                      }
                  }
          
                  reviewDecision
          
                  reviews (
                      first: 100
                  ) {
                      totalCount
                      pageInfo {
                          hasNextPage
                          startCursor
                          endCursor
                      }
                      nodes {
                          author {
                              login
                          }
                          comments (
                              first: 100
                          ) {
                              totalCount
                              pageInfo {
                                  hasNextPage
                                  startCursor
                                  endCursor
                              }
                              nodes {
                                  author {
                                      login
                                  }
                                  body 
                                  path 
                                  position 
                                  resourcePath 
                                  url 
                                  createdAt 
                                  publishedAt 
                                  updatedAt 
                                  state 
                              }
                          }
                      }
                  }
                }
              }
            }
        }

        query {
            repository(name: "{$this->name}", owner: "{$this->owner}") {
                pullRequests (
                    first: {$first}
                    {$after}
                    orderBy: {field: UPDATED_AT, direction: DESC}
                    updatedSince: 2023-04-20 00:00:00.000
                ) {
                    totalCount
                    pageInfo {
                        hasNextPage
                        startCursor
                        endCursor
                    }
                    nodes {
                        repository {
                            name
                            owner {
                                login
                            }
                        }
                        id
                        number
                        url
                        title
                        author {
                            login
                        }
                        labels (
                            first: 100
                        ) {
                            totalCount
                            pageInfo {
                                hasNextPage
                                startCursor
                                endCursor
                            }
                            nodes {
                                color
                                name
                            }
                        }
                        createdAt
                        updatedAt
            
                        state
            
                        locked
            
                        closed 
                        closedAt
            
                        merged
                        mergedAt 
                        mergedBy {
                            login
                        }
            
                        additions
                        deletions
            
                        changedFiles
            
                        files (
                            first: 100
                        ) {
                            totalCount
                            pageInfo {
                                hasNextPage
                                startCursor
                                endCursor
                            }
                            nodes {
                                path
                                changeType 
                                additions
                                deletions
                            }
                        }
            
                        commits (
                            first: 100
                        ) {
                            totalCount
                            pageInfo {
                                hasNextPage
                                startCursor
                                endCursor
                            }
                            nodes {
                                commit {
                                    url 
                                    oid
                                    changedFilesIfAvailable
                                    additions
                                    deletions 
                                    message 
                                    committedDate 
                                    pushedDate 
                                    author {
                                        user {
                                            login
                                        }
                                    }
                                }
                            }
                        }
            
                        reviewRequests (
                            first: 100
                        ) {
                            totalCount
                            pageInfo {
                                hasNextPage
                                startCursor
                                endCursor
                            }
                            nodes {
                                asCodeOwner 
                                requestedReviewer {
                                    ... on User {
                                        login
                                    }
                                }
                            }
                        }

                        reviewDecision

                        reviews (
                            first: 100
                        ) {
                            totalCount
                            pageInfo {
                                hasNextPage
                                startCursor
                                endCursor
                            }
                            nodes {
                                author {
                                    login
                                }
                                comments (
                                    first: 100
                                ) {
                                    totalCount
                                    pageInfo {
                                        hasNextPage
                                        startCursor
                                        endCursor
                                    }
                                    nodes {
                                        author {
                                            login
                                        }
                                        body 
                                        path 
                                        position 
                                        resourcePath 
                                        url 
                                        createdAt 
                                        publishedAt 
                                        updatedAt 
                                        state 
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
GRAPHQL;

        $result = GithubGraphQL::getGraphQL($query);
        if (empty($result['data'])) {
            throw new \Exception("Repo {$this->name} - 1. There were problems getting the data from GitHub. the result received was: " . json_encode($result));
        }
        if (empty($result['data']['repository'])) {
            throw new \Exception("Repo {$this->name} - 2. There were problems getting the data from GitHub. the result received was: " . json_encode($result));
        }

        $total = $result['data']['repository']['pullRequests']['totalCount'];
        $nodes = $result['data']['repository']['pullRequests']['nodes'];

        $currentTotal = count($nodes);
        if ($currentTotal == 0) {
            return true;
        }

        foreach ($nodes as $node) {
            if (empty($node['number'])) {
                throw new \Exception("Repo {$this->name} - No name for the PR found. The PR details received from GitHub were: " . json_encode($node));
            }
            GithubPullRequest::setCustomAttributes([
                'id' => $this->id
            ], $node);

            try {
                $pr = GithubPullRequest::getByAttributes([
                    'repo_id' => GithubPullRequest::$customAttributes['repo_id'],
                    'pr_number' => GithubPullRequest::$customAttributes['pr_number']
                ], GithubPullRequest::$customAttributes);
            } catch (\Exception $exc) {
                throw new \Exception("Repo {$this->name} - 1. Error getting the PR. Error: " . $exc->getMessage() . '. The extracted attributes were: ' . json_encode(GithubPullRequest::$customAttributes));
            }

            if ($pr === null) {
                throw new \Exception("Repo {$this->name} - 2. Error getting the PR. The extracted attributes were: " . json_encode(GithubPullRequest::$customAttributes));
            }

            $this->log("Repo {$this->name} - Pull request " . GithubPullRequest::$customAttributes['pr_number']);

            try {
                $pr->process([
                    'id' => $this->id,
                    'name' => $this->name
                ], $node);
            } catch (\Exception $exc) {
                throw new \Exception("Repo {$this->name} - Error processing the PR Node. Error: " . $exc->getMessage() . '. The PR details received from GitHub were: ' . json_encode($node));
            }
        }

        if (
            $result['data']['repository']['pullRequests']['pageInfo']['hasNextPage']
            && $setting['limit'] - $currentTotal > 0
        ) {
            $this->savePRs([
                'endCursor' => $result['data']['repository']['pullRequests']['pageInfo']['endCursor'],
                'limit' => $setting['limit'] - $currentTotal,
                'pageSize' => $setting['pageSize']
            ]);
        }

        return true;
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