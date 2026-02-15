<?php

declare(strict_types=1);

namespace Netgen\Bundle\SiteBundle\DataCollector;

use Exception;
use SebastianFeldmann\Git\Repository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

final class GitDataCollector extends DataCollector
{
    public function collect(Request $request, Response $response, ?Exception $exception = null)
    {
        try {
            $repository = new Repository();

            $branch = $repository->getInfoOperator()->getCurrentBranch();
            $lastCommitHash = $repository->getInfoOperator()->getCurrentCommitHash();
        } catch (Exception $e) {
            // Gracefully handle non-git deployments
            // This can occur in production environments where code is deployed
            // without .git directory (e.g., from tarball or direct copy)
            $branch = 'N/A (not a git repository)';
            $lastCommitHash = 'N/A (not a git repository)';
        }

        $this->data = [
            'git_branch' => $branch,
            'last_commit_hash' => $lastCommitHash,
        ];
    }

    public function getGitBranch(): string
    {
        return $this->data['git_branch'];
    }

    public function getLastCommitHash(): string
    {
        return $this->data['last_commit_hash'];
    }

    public function getName()
    {
        return 'ngsite.data_collector.git';
    }
}
