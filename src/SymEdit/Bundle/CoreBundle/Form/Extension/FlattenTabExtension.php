<?php

/*
 * This file is part of the SymEdit package.
 *
 * (c) Craig Blanchette <craig.blanchette@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymEdit\Bundle\CoreBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * This extension will see if tabs_data is defined and if it is, will
 * attempt to build tabs based on these settings. If build_tabs is enabled
 * then it will create the tabs, if not it will bypass. This assumes that
 * every tab that is made uses inherit_data so it basically only functions
 * on one object at a time but broken into pieces. 
 */
class FlattenTabExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Creatd with createNamed('', ...) so likely an API request, might
        // need to make this a little better in the future
        $buildTabs = $builder->getName() === '' ? false : $options['build_tabs'];
        $tabsData = $options['tabs_data'];
        $formType = $builder->getType()->getInnerType();

        $tabDefaults = array(
            'inherit_data' => true,
        );

        foreach ($tabsData as $name => $data) {
            if ($buildTabs) {
                $parent = $builder->create($name, 'tab', array_merge($tabDefaults, $data));
                $builder->add($parent);
            } else {
                $parent = $builder;
            }

            $method = sprintf('build%sForm', ucfirst($name));

            if (!method_exists($formType, $method)) {
                throw new \InvalidArgumentException(
                    sprintf('Method "%s" does not exist in "%s"', $method, get_class($formType))
                );
            }

            // call buildTabNameForm
            call_user_func(array($formType, $method), $parent, $options);
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'build_tabs' => true,
            'tabs_data' => array(),
        ));
    }

    public function getExtendedType()
    {
        return 'form';
    }
}
