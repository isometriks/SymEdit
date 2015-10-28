<?php

/*
 * This file is part of the SymEdit package.
 *
 * (c) Craig Blanchette <craig.blanchette@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymEdit\Bundle\CoreBundle\Doctrine\ORM;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use SymEdit\Bundle\CoreBundle\Repository\ParameterRepositoryInterface;

class ParameterRepository extends EntityRepository implements ParameterRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getLastUpdated()
    {
        $result = $this->createQueryBuilder('o')
            ->select('o.updatedAt')
            ->orderBy('o.updatedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult()
        ;

        return current($result);
    }
}