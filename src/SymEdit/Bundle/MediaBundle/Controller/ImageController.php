<?php

/*
 * This file is part of the SymEdit package.
 *
 * (c) Craig Blanchette <craig.blanchette@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymEdit\Bundle\MediaBundle\Controller;

use SymEdit\Bundle\ResourceBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Image controller.
 */
class ImageController extends ResourceController
{
    public function jsonAction()
    {
        $images = $this->getRepository()->findAll();
        $out = array();

        foreach ($images as $image) {
            $out[] = array(
                'thumb' => $this->getThumbnail($image->getPath()),
                'image' => $image->getWebPath(),
            );
        }

        return new JsonResponse($out);
    }

    protected function getThumbnail($path, $size = 'symedit_64x64')
    {
        $this->container->get('liip_imagine.controller')->filterAction(
            $this->getRequest(),
            $path,
            $size
        );

        $cacheManager = $this->container->get('liip_imagine.cache.manager');

        return $cacheManager->getBrowserPath($path, $size);
    }

    public function quickUploadAction(Request $request)
    {
        $file = $request->files->get('file');
        $image = $this->getRepository()->createNew();
        $image->setFile($file);

        // Validate the new image
        $errors = $this->get('validator')->validate($image, array('file_only'));

        if (count($errors) > 0) {
            return new JsonResponse(array(
                'error' => 'Invalid image: ' . $errors[0]->getMessage(),
            ));
        }

        try {
            $this->getManager()->persist($image);
            $this->getManager()->flush($image);

            return new JsonResponse(array(
                'filelink' => $image->getWebPath(),
            ));

        } catch (\Exception $ex) {
            return new JsonResponse(array(
                'error' => 'Error uploading, try renaming your image file.',
            ));
        }
    }
}
