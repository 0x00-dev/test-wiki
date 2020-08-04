<?php

namespace App\Controller;

use App\Entity\Page;
use App\Interfaces\TransliteratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    /**
     * @Route("/page/create", name="page_create")
     */
    public function create()
    {
        return $this->render('page/create.html.twig', ['parent_address' => '']);
    }

    /**
     * @Route("/page/add", name="page_add")
     * @param Request $request
     * @param TransliteratorInterface $transliterator
     *
     * @return RedirectResponse
     */
    public function add(Request $request, TransliteratorInterface $transliterator)
    {
        $page = new Page();
        $page->setAddress(empty($request->get('address')) ? $transliterator->transliterate($request->get('title')) : $request->get('address'))
            ->setText($request->get('text'))
            ->setTitle($request->get('title'))
            ->setParentPage($request->get('parent_page', null));
        $page->markLinks($this->getDoctrine());
        $em = $this->getDoctrine()
            ->getManager();
        $em->persist($page);
        $em->flush();
        if (!empty($page->getParentPage())) {
            $this->addFlash('success', "Дочерняя страница {$page->getParentPage()} -> {$page->getAddress()}  добавлена");
        } else {
            $this->addFlash('success', "Страница {$page->getAddress()} добавлена");
        }

        return $this->redirect("/{$page->getAddress()}/edit");
    }

    /**
     * @Route("/{address}/edit", name="page_edit", requirements={"address" = "[a-zA-Z0-9_]+"})
     * @param $address
     *
     * @return RedirectResponse|Response
     */
    public function edit($address)
    {
        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Page::class);
        /** @var Page $page */
        $page = $repository->findOneBy(['address' => $address]);
        if (null === $page) {
            return $this->redirectToRoute('error_404', [], 404);
        }

        $text = $page->getText();
        $text = preg_replace("~<b>([a-zA-Zа-яА-Я0-9\s\w]+)</b>~", "**$1**", $text);
        $text = preg_replace("~<i>([a-zA-Zа-яА-Я0-9\s\w]+)</i>~", "//$1//", $text);
        $text = preg_replace("~<u>([a-zA-Zа-яА-Я0-9\s\w]+)</u>~", "__$1__", $text);

        $page->setWikiText($text);

        return $this->render('/page/edit.html.twig', ['page' => $page]);
    }

    /**
     * @Route("/{address}", name="page_view", requirements={"address" = "[a-zA-Z0-9_]+"})
     * @param $address
     */
    public function view($address)
    {
        $page = $this->getDoctrine()
            ->getRepository(Page::class)
            ->findOneBy(['address' => $address]);
        if (null === $page) {
            return $this->redirectToRoute('error_404', [], 404);
        }
        $childs = $this->getDoctrine()
            ->getRepository(Page::class)
            ->findBy(['parent_page' => $address]);

        return $this->render('page/view.html.twig', ['page' => $page, 'childs' => $childs]);
    }

    /**
     * @Route("/{address}/delete", name="page_delete", requirements={"address" = "[a-zA-Z0-9_]+"})
     * @param $address
     *
     * @return RedirectResponse
     */
    public function delete($address)
    {
        $page = $this->getDoctrine()
            ->getRepository(Page::class)
            ->findOneBy(['address' => $address]);
        if (null === $page) {
            return $this->redirectToRoute('error_404', [], 404);
        }
        $em = $this->getDoctrine()
            ->getManager();
        $em->remove($page);
        $em->flush();

        $this->addFlash('success', "Страница $address удалена");

        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/{address}/add", name="page_add_child", requirements={"address" = "[a-zA-Z0-9_]+"})
     * @param $address
     *
     * @return RedirectResponse|Response
     */
    public function add_child($address)
    {
        $page = $this->getDoctrine()
            ->getRepository(Page::class)
            ->findOneBy(['address' => $address]);
        if (null === $page) {
            return $this->redirectToRoute('error_404', [], 404);
        }

        return $this->render('page/create.html.twig', ['parent_address' => $address]);
    }

    /**
     * @Route("page/save", name="page_save")
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function save(Request $request)
    {
        $page = $this->getDoctrine()
            ->getRepository(Page::class)
            ->find($request->get('id'));
        if (null === $page) {
            return $this->redirectToRoute('error_404', [], 404);
        }
        $page->setText($request->get('text'))
            ->setTitle($request->get('title'));
        $em = $this->getDoctrine()
            ->getManager();
        $em->flush();
        $this->addFlash('success', "Страница {$page->getAddress()} сохранена");

        return $this->redirect("/{$page->getAddress()}/edit");
    }
}
