<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformAdminUi\Form\DataMapper;

use EzSystems\EzPlatformAdminUi\Form\Data\Version\VersionChoiceData;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class VersionChoiceMapper implements DataMapperInterface
{

    public function mapDataToForms($viewData, $forms)
    {
        if (null === $viewData || null === $viewData->getVersionInfo()) {
            return;
        }

        if (!$viewData instanceof VersionChoiceData) {
            throw new UnexpectedTypeException($viewData, VersionChoiceData::class);
        }

        $forms = iterator_to_array($forms);

        $forms['version']->setData($viewData->getVersionInfo()->versionNo . '/' . $viewData->getLanguageCode());

    }

    public function mapFormsToData($forms, &$viewData)
    {
    }
}
