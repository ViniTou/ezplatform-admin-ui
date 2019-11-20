<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformAdminUi\Form\Data\Version;

use eZ\Publish\API\Repository\Values\Content\VersionInfo;

class VersionChoiceData
{
    /** @var \eZ\Publish\API\Repository\Values\Content\VersionInfo|null */
    protected $versionInfo;

    /** @var string|null */
    private $languageCode;

    public function __construct(
        ?VersionInfo $versionInfo = null,
        ?string $languageCode = null
    ) {
        $this->versionInfo = $versionInfo;
        $this->languageCode = $languageCode;
    }

    public function getVersionInfo(): ?VersionInfo
    {
        return $this->versionInfo;
    }

    public function getLanguageCode(): ?string
    {
        return $this->languageCode;
    }
}
