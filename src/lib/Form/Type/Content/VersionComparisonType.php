<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformAdminUi\Form\Type\Content;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use EzSystems\EzPlatformAdminUi\Form\DataTransformer\VersionInfoComparisonTransformer;
use EzSystems\EzPlatformAdminUi\Form\DataTransformer\VersionInfoTransformer;
use EzSystems\EzPlatformAdminUi\Form\Type\ChoiceList\Loader\VersionInfoChoiceLoader;
use EzSystems\EzPlatformAdminUi\Form\Type\Version\VersionChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VersionComparisonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'version_a',
                VersionChoiceType::class,
                [
                    'label' => false,
                    'content_info' => $options['content_info'],
                ]
            )
            ->add(
                'version_b',
                VersionChoiceType::class,
                [
                    'label' => false,
                    'content_info' => $options['content_info'],
                ]
            )
            ->add(
                'compare',
                ButtonType::class
            )
            ->add(
                'side_by_side',
                ButtonType::class
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(
                'content_info'
            )
            ->setAllowedTypes(
                'content_info', ContentInfo::class
            );
    }
}
