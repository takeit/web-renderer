<?php

namespace SWP\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Migrations\IrreversibleMigrationException;
use Doctrine\DBAL\Schema\Schema;
use SWP\Bundle\CoreBundle\Model\Article;
use SWP\Bundle\CoreBundle\Model\ArticleInterface;
use SWP\Bundle\CoreBundle\Model\ArticleStatistics;
use SWP\Bundle\MultiTenancyBundle\MultiTenancyEvents;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add article statistics to articles.
 */
class Version20180118194100 extends AbstractMigration implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');
        $this->container->get('event_dispatcher')->dispatch(MultiTenancyEvents::TENANTABLE_DISABLE);

        $articles = $entityManager
            ->createQuery('SELECT a FROM SWP\Bundle\ContentBundle\Model\Article a')
            ->getArrayResult();



        /* @var ArticleInterface $article */
        foreach ($articles as $article) {
            if (isset($article['articleStatistics']) && null !== $article['articleStatistics']) {
                continue;
            }

            $articleStatistics = new ArticleStatistics();
            $articleStatistics->setArticle($entityManager->getReference(Article::class, $article['id']));
            $articleStatistics->setTenantCode($article['tenantCode']);
            $this->container->get('doctrine')->getManager()->persist($articleStatistics);
        }
        $this->container->get('doctrine')->getManager()->flush();
    }

    /**
     * @param Schema $schema
     *
     * @throws IrreversibleMigrationException
     * @throws \Doctrine\DBAL\Migrations\AbortMigrationException
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        // No way to rollback this
        throw new IrreversibleMigrationException();
    }
}
