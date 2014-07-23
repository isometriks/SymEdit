<?php

/*
 * This file is part of the SymEdit package.
 *
 * (c) Craig Blanchette <craig.blanchette@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymEdit\Bundle\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MenuController extends Controller
{
    public function renderAction(Request $request)
    {
        $lastUpdated = $this->getPageRepository()->getLastUpdated();

        $response = new Response();
        $response
            ->setLastModified($lastUpdated)
            ->setPublic();

        if ($response->isNotModified($request)) {
            return $response;
        }

        return $this->render('@SymEdit/Menu/render.html.twig', array(), $response);
    }

    protected function getPageRepository()
    {
        return $this->get('symedit.repository.page');
    }
}
