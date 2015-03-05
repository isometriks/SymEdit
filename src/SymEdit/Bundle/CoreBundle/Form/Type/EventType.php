<?php

/*
 * This file is part of the SymEdit package.
 *
 * (c) Craig Blanchette <craig.blanchette@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymEdit\Bundle\CoreBundle\Form\Type;

use SymEdit\Bundle\EventsBundle\Form\Type\EventType as BaseEventType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EventType extends BaseEventType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Left blank for tab builder
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'tabs_data' => array(
                'basic' => array(
                    'label' => 'symedit.form.event.tab.basic',
                    'icon' => 'info-sign',
                ),
                'dateTime' => array(
                    'label' => 'symedit.form.event.tab.time',
                    'icon' => 'calendar',
                ),
                'location' => array(
                    'label' => 'symedit.form.event.tab.location',
                    'icon' => 'map-marker',
                ),
            ),
        ));
    }

    public function getName()
    {
        return 'symedit_event';
    }
}
