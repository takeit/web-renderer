<?php

declare(strict_types=1);

/*
 * This file is part of the Superdesk Web Publisher Core Bundle.
 *
 * Copyright 2019 Sourcefabric z.u. and contributors.
 *
 * For the full copyright and license information, please see the
 * AUTHORS and LICENSE files distributed with this source code.
 *
 * @copyright 2019 Sourcefabric z.ú
 * @license http://www.superdesk.org/license
 */

namespace SWP\Bundle\CoreBundle\Service;

use SWP\Bundle\ContentBundle\Model\ArticleSeoMetadataInterface;
use SWP\Bundle\CoreBundle\Model\ArticleInterface;

interface SeoImageUploaderInterface
{
    public function handleUpload(ArticleInterface $article, ArticleSeoMetadataInterface $seoMetadata): void;
}