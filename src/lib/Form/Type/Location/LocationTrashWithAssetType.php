<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformAdminUi\Form\Type\Location;

use EzSystems\EzPlatformAdminUi\Form\Data\Location\LocationTrashWithAssetData;
use EzSystems\EzPlatformAdminUi\Form\Type\Content\LocationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationTrashWithAssetType extends AbstractType
{
    const RADIO_SELECT_TRASH_WITH_ASSETS = 'trash_with_assets';
    const RADIO_SELECT_DEFAULT_TRASH = 'trash_default';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'location',
                LocationType::class,
                ['label' => false]
            )
            ->add(
                'trashAssets',
                ChoiceType::class,
                [
                    'expanded' => true,
                    'multiple' => false,
                    'choices' => $options['trashAssetsOptions'],
                ]
            )
            ->add(
                'trash',
                SubmitType::class,
                ['label' => /** @Desc("Send to Trash") */ 'location_trash_form.trash']
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => LocationTrashWithAssetData::class,
            'translation_domain' => 'forms',
            'trashAssetsOptions' => [],
        ]);
    }
}
