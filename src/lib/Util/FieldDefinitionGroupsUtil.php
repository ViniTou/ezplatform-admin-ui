<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformAdminUi\Util;

use eZ\Publish\API\Repository\Values\Content\VersionDiff\FieldValueDiff;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\VersionDiff;
use eZ\Publish\Core\Helper\FieldsGroups\FieldsGroupsList;

class FieldDefinitionGroupsUtil
{
    /** @var \eZ\Publish\Core\Helper\FieldsGroups\FieldsGroupsList */
    private $fieldsGroupsListHelper;

    /**
     * @param \eZ\Publish\Core\Helper\FieldsGroups\FieldsGroupsList $fieldsGroupsListHelper
     */
    public function __construct(FieldsGroupsList $fieldsGroupsListHelper)
    {
        $this->fieldsGroupsListHelper = $fieldsGroupsListHelper;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[] $fieldDefinitions
     *
     * @return array
     */
    public function groupFieldDefinitions(array $fieldDefinitions): array
    {
        $fieldDefinitionsByGroup = [];
        foreach ($this->fieldsGroupsListHelper->getGroups() as $groupId => $groupName) {
            $fieldDefinitionsByGroup[$groupId] = [
                'name' => $groupName,
                'fieldDefinitions' => [],
            ];
        }

        foreach ($fieldDefinitions as $fieldDefinition) {
            $groupId = $fieldDefinition->fieldGroup;
            if (!$groupId) {
                $groupId = $this->fieldsGroupsListHelper->getDefaultGroup();
            }

            $fieldDefinitionsByGroup[$groupId]['fieldDefinitions'][] = $fieldDefinition;
            $fieldDefinitionsByGroup[$groupId]['name'] = $fieldDefinitionsByGroup[$groupId]['name'] ?? $groupId;
        }

        return $fieldDefinitionsByGroup;
    }

    public function groupFieldDefinitionsDiff(VersionDiff $versionDiff): array
    {
        $fieldDefinitionsByGroup = [];
        foreach ($this->fieldsGroupsListHelper->getGroups() as $groupId => $groupName) {
            $fieldDefinitionsByGroup[$groupId] = [
                'name' => $groupName,
                'fieldValueDiff' => [],
            ];
        }

        /** @var \eZ\Publish\API\Repository\Values\Content\VersionDiff\FieldValueDiff $fieldDiff */
        foreach ($versionDiff as $fieldDiff) {
            $groupId = $fieldDiff->getFieldDefinition()->fieldGroup;
            if (!$groupId) {
                $groupId = $this->fieldsGroupsListHelper->getDefaultGroup();
            }

            $fieldDefinitionsByGroup[$groupId]['fieldValueDiff'][] = $fieldDiff;
            $fieldDefinitionsByGroup[$groupId]['name'] = $fieldDefinitionsByGroup[$groupId]['name'] ?? $groupId;
        }

        return $fieldDefinitionsByGroup;
    }
}
