<?php

/*
 * This file is part of the SymEdit package.
 *
 * (c) Craig Blanchette <craig.blanchette@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymEdit\Bundle\ResourceBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController as BaseResourceController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ResourceController extends BaseResourceController
{
    /**
     * {@inheritDoc}
     *
     * Here for AOP
     */
    public function showAction(Request $request)
    {
        return parent::showAction($request);
    }

    public function findOr404(Request $request, array $criteria = array())
    {
        if ($request->attributes->has('_symedit_entity')) {
            return $request->attributes->get('_symedit_entity');
        }

        return parent::findOr404($request, $criteria);
    }

    /**
     * Bulk reorder, for drag and drops
     */
    public function reorderAction(Request $request)
    {
        $resource = $this->findOr404($request);
        $position = $this->config->getSortablePosition();
        $accessor = PropertyAccess::createPropertyAccessor();

        if (($index = $request->request->get('index', null)) !== null) {
            $accessor->setValue($resource, $position, $index);

            // Don't setup flashes
            $this->getManager()->flush();
        }

        $view = $this->view()
            ->setFormat('json')
            ->setTemplateVar('status')
            ->setData(array(
                'status' => true,
            ));

        return $this->handleView($view);
    }

    /**
     * @return ObjectManager
     */
    protected function getManager()
    {
        return $this->get($this->getConfiguration()->getServiceName('manager'));
    }
}
