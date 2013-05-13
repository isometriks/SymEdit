<?php

namespace Isometriks\Bundle\SymEditBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Isometriks\Bundle\SymEditBundle\Annotation\PageController as Bind;
use Isometriks\Bundle\SymEditBundle\Entity\Post;
use Isometriks\Bundle\SitemapBundle\Annotation\Sitemap;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Bind(name="symedit-blog")
 */
class BlogController extends Controller
{
    /**
     * @Route("/", name="blog", defaults={"_format"="html"})
     * @Route("/feed.xml", name="blog_rss", defaults={"_format"="xml"})
     * @Sitemap()
     */
    public function indexAction(Request $request, $_format)
    {
        $em = $this->getDoctrine()->getManager();

        $modified = $em->createQuery('SELECT MAX(p.updatedAt) as modified FROM IsometriksSymEditBundle:Post p')
                       ->getSingleScalarResult();
        
        $modifiedDate = new \DateTime($modified); 
        $response = $this->createResponse($modifiedDate); 

        if ($response->isNotModified($request)) {
            return $response;
        }

        $template = sprintf('index.%s.twig', $_format); 
        
        return $this->render($this->getHostTemplate('Blog', $template), array(
            'Posts' => $this->getRecentPosts(), 
            'modified' => $modifiedDate, 
        ), $response);
    }
    
    private function getRecentPosts()
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT p FROM IsometriksSymEditBundle:Post p ORDER BY p.createdAt DESC');
        
        return $this->getPaginator($query);
    }

    public function recentAction()
    {
        // TODO Cache this for ESI

        return $this->render($this->getHostTemplate('Blog', 'list.html.twig'), array(
            'Posts' => $this->getRecentPosts(),
        ));
    }

    /**
     * @Route("/{slug}", name="blog_slug_view", requirements={"slug"="[a-z0-9_-]+"})
     * @Sitemap(params={"slug"="getSlug"}, entity="IsometriksSymEditBundle:Post")
     */
    public function slugViewAction($slug, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository('IsometriksSymEditBundle:Post')->findOneBySlug($slug);

        if (!$post) {
            throw $this->createNotFoundException(sprintf('Post with slug "%s" not found.', $slug));
        }

        $response = $this->createResponse($post->getUpdatedAt());

        if ($response->isNotModified($request)) {
            return $response;
        } 
        
        return $this->render($this->getHostTemplate('Blog', 'single.html.twig'), array(
            'Post' => $post,
            'SEO' => $post->getSeo(),
        ), $response);
    }

    /**
     * @Route("/preview/{slug}", name="blog_preview")
     */
    public function previewAction($slug)
    {
        $context = $this->get('security.context');

        if (!$context->isGranted('ROLE_ADMIN')) {
            throw $this->createNotFoundException('No preview available');
        }

        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('post_published');

        $post = $em->getRepository('IsometriksSymEditBundle:Post')->findOneBySlug($slug);

        if (!$post) {
            throw $this->createNotFoundException(sprintf('Post with slug "%s" not found.', $slug));
        }

        return $this->render($this->getHostTemplate('Blog', 'single.html.twig'), array(
            'Post' => $post,
            'SEO' => $post->getSeo(),
        ));
    }

    /**
     * @Route("/category/{slug}/{page}", defaults={"page"="1"}, requirements={"slug"=".*?", "page"="\d+"}, name="blog_category_view")
     * @Sitemap(params={"slug"="getSlug"}, entity="IsometriksSymEditBundle:Category")
     */
    public function categoryViewAction($slug, Request $request, $page = 1)
    {
        $em = $this->getDoctrine()->getManager();
        $category = $em->getRepository('IsometriksSymEditBundle:Category')->findOneBySlug($slug);

        if (!$category) {
            throw $this->createNotFoundException(sprintf('Category with slug "%s" not found.', $slug));
        }

        $query = $em->createQueryBuilder()
                ->select('p')
                ->from('IsometriksSymEditBundle:Post', 'p')
                ->join('p.categories', 'c')
                ->where(':catId MEMBER OF p.categories')
                ->orderBy('p.createdAt', 'DESC')
                ->setParameter('catId', $category->getId())
                ->getQuery();

        $posts = $this->getPaginator($query, $page);
        $latest = current($posts->getIterator());

        $modified = $latest === null ? null : $latest->getUpdatedAt(); 
        $response = $this->createResponse($modified); 

        if ($response->isNotModified($request)) {
            return $response;
        }

        return $this->render($this->getHostTemplate('Blog', 'category.html.twig'), array(
            'Category' => $category,
            'SEO' => $category->getSeo(),
            'Posts' => $posts,
            'Pages' => $this->getPages($posts, $page),
        ), $response);
    }

    /**
     * @Route("/user/{username}", name="blog_author_view")
     */
    public function authorViewAction($username)
    {
        $em = $this->getDoctrine()->getManager();

        $user_manager = $this->container->get('fos_user.user_manager');
        $user = $user_manager->findUserBy(array('username' => $username));

        $query = $em->createQueryBuilder()
                ->select('p')
                ->from('IsometriksSymEditBundle:Post', 'p')
                ->join('p.author', 'a')
                ->where('a.username = :username')
                ->setParameter('username', $username)
                ->getQuery();

        return $this->render($this->getHostTemplate('Blog', 'author.html.twig'), array(
            'Posts' => $query->getResult(),
            'Author' => $user,
        ));
    }

    private function getMaxPosts()
    {
        $settings = $this->getSettings();
        $max = 4;

        if ($settings->has('blog.max_posts')) {
            $max = $settings->get('blog.max_posts');
        }

        return $max;
    }

    /**
     * Returns Paginator from query
     * @param type $query
     * @param int $page
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    private function getPaginator($query, $page = 1)
    {
        $maxPosts = $this->getMaxPosts();
        $page = max(0, $page - 1);
        $start = $page * $maxPosts;

        $query->setFirstResult($start)
              ->setMaxResults($maxPosts);

        return new Paginator($query, true);
    }

    private function getPages(Paginator $paginator, $current = 1)
    {
        return array(
            'num' => ceil(count($paginator) / $this->getMaxPosts()),
            'cur' => $current,
        );
    }
}