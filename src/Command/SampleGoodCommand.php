<?php

namespace App\Command;

use App\Entity\Test;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\WhereInWalker;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'sample:good')]
class SampleGoodCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $qb = $this->entityManager->getRepository(Test::class)
            ->createQueryBuilder('t')
            ->andWhere('t.id = :xxx')
            ->setParameter('xxx', 1);

        $v = '%gs%';
        $qb
            ->andWhere($qb->expr()->like('t.name', $qb->expr()->literal($v)));

        $query = $qb->getQuery();
        $query->execute();

        $whereInQuery = clone $query;

        $whereInQuery->setParameters(clone $query->getParameters());
        $whereInQuery->setCacheable(false);
        $whereInQuery->setHint(Query::HINT_CUSTOM_TREE_WALKERS, [WhereInWalker::class]);
        $whereInQuery->setHint(WhereInWalker::HINT_PAGINATOR_HAS_IDS, true);
        $whereInQuery->setFirstResult(0)->setMaxResults(null);
        $whereInQuery->setCacheable(false);

        $databaseIds = [1];
        $whereInQuery->setParameter(WhereInWalker::PAGINATOR_ID_ALIAS, $databaseIds);

        $res = $whereInQuery->execute([], AbstractQuery::HYDRATE_ARRAY);

        return Command::SUCCESS;
    }
}

