<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformAdminUi\Form\Data;

use EzSystems\EzPlatformAdminUi\Form\Data\Version\VersionChoiceData;

class VersionComparisonData
{
    /** @var \EzSystems\EzPlatformAdminUi\Form\Data\Version\VersionChoiceData */
    private $versionA;

    /** @var \EzSystems\EzPlatformAdminUi\Form\Data\Version\VersionChoiceData */
    private $versionB;

    public function __construct(
        VersionChoiceData $versionA,
        VersionChoiceData $versionB
    ) {
        $this->versionA = $versionA;
        $this->versionB = $versionB;
    }

    public function getVersionA(): VersionChoiceData
    {
        return $this->versionA;
    }

    public function getVersionB(): VersionChoiceData
    {
        return $this->versionB;
    }
}
