<?php

/*
 * This file is part of the SymEdit package.
 *
 * (c) Craig Blanchette <craig.blanchette@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymEdit\Bundle\MailChimpBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;

class SubscribeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // If there is a list in the data use that
        if ($builder->getData() !== null) {
            $options = [];
        } else {
            $options = [
                'data' => $options['list'],
            ];
        }

        $builder
            ->add('email', 'email', [
                'constraints' => [
                    new Email(),
                ],
            ])
            ->add('list', 'hidden', $options)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'list' => null,
        ]);
    }

    public function getName()
    {
        return 'mailchimp_subscribe';
    }
}
