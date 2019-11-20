<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformAdminUi\UI\Service;

use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use EzSystems\EzPlatformAdminUi\Exception\InvalidArgumentException;
use Symfony\Contracts\Translation\TranslatorInterface;

final class VersionStatus
{
    /** @var \Symfony\Contracts\Translation\TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function translateStatus(int $status): string
    {
        switch ($status) {
            case VersionInfo::STATUS_DRAFT:
                /** @Desc("Draft") */
                return $this->translator->trans('version_info.status.draft', [], 'version');
            case VersionInfo::STATUS_PUBLISHED:
                /** @Desc("Published") */
                return $this->translator->trans('version_info.status.published', [], 'version');
            case VersionInfo::STATUS_ARCHIVED:
                /** @Desc("Archived") */
                return $this->translator->trans('version_info.status.archived', [], 'version');
            default:
                throw new InvalidArgumentException('$status', sprintf('Unrecognized versionInfo status: "%d"', $status));
        }
    }

}
