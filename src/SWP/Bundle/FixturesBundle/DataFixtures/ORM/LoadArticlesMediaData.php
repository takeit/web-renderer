<?php

/*
 * This file is part of the Superdesk Web Publisher Fixtures Bundle.
 *
 * Copyright 2015 Sourcefabric z.u. and contributors.
 *
 * For the full copyright and license information, please see the
 * AUTHORS and LICENSE files distributed with this source code.
 *
 * @copyright 2015 Sourcefabric z.ú
 * @license http://www.superdesk.org/license
 */

namespace SWP\Bundle\FixturesBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use SWP\Bundle\ContentBundle\Doctrine\ORM\Article;
use SWP\Bundle\ContentBundle\Doctrine\ORM\ArticleMedia;
use SWP\Bundle\ContentBundle\Doctrine\ORM\ImageRendition;
use SWP\Bundle\ContentBundle\Model\ArticleInterface;
use SWP\Bundle\FixturesBundle\AbstractFixture;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadArticlesMediaData extends AbstractFixture implements FixtureInterface, OrderedFixtureInterface
{
    private $manager;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $env = $this->getEnvironment();
        $this->loadArticles($env, $manager);

        $manager->flush();
    }

    /**
     * Sets articles manually (not via Alice) for test env due to fatal error:
     * Method PHPCRProxies\__CG__\Doctrine\ODM\PHPCR\Document\Generic::__toString() must not throw an exception.
     */
    public function loadArticles($env, $manager)
    {
        $articles = [
            'test' => [
                [
                    'title' => 'Test news article',
                    'content' => 'Test news article content',
                    'locale' => 'en',
                ],
            ],
        ];

        $renditions = [
            'original' => [
                'width' => '4000',
                'height' => '2667',
                'media' => '12345678987654321a',
            ],
            '16-9' => [
                'width' => '1079',
                'height' => '720',
                'media' => '12345678987654321b',
            ],
            '4-3' => [
                'width' => '800',
                'height' => '533',
                'media' => '12345678987654321c',
            ],
        ];

        $mediaManager = $this->container->get('swp_content_bundle.manager.media');
        $fakeImage = __DIR__.'/../../Resources/assets/test_cc_image.jpg';

        if (isset($articles[$env])) {
            foreach ($articles[$env] as $articleData) {
                $article = new Article();
                $article->setTitle($articleData['title']);
                $article->setBody($articleData['content']);
                $article->setLocale($articleData['locale']);
                $article->setPublishedAt(new \DateTime());
                $article->setPublishable(true);
                $article->setStatus(ArticleInterface::STATUS_PUBLISHED);
                $manager->persist($article);

                // create Media
                $articleMedia = new ArticleMedia();
                $articleMedia->setArticle($article);
                $articleMedia->setBody('article media body');
                $articleMedia->setByLine('By Best Editor');
                $articleMedia->setLocated('Porto');
                $articleMedia->setDescription('Media description');
                $articleMedia->setUsageTerms('Some super open terms');
                $articleMedia->setMimetype('image/jpeg');
                $manager->persist($articleMedia);

                /* @var $rendition Rendition */
                foreach ($renditions as $key => $rendition) {
                    $uploadedFile = new UploadedFile($fakeImage, $rendition['media'], 'image/jpeg', filesize($fakeImage), null, true);
                    $image = $mediaManager->handleUploadedFile($uploadedFile, $rendition['media']);

                    if ($key === 'original') {
                        $articleMedia->setImage($image);
                    }

                    $imageRendition = new ImageRendition();
                    $imageRendition->setImage($image);
                    $imageRendition->setHeight($rendition['height']);
                    $imageRendition->setWidth($rendition['width']);
                    $imageRendition->setName($key);
                    $imageRendition->setMedia($articleMedia);
                    $articleMedia->addRendition($imageRendition);
                    $manager->persist($imageRendition);
                }
            }

            $manager->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 999;
    }
}