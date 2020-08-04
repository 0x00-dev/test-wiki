<?php

namespace App\Controller;

use App\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        $pages = $this->getDoctrine()
            ->getRepository(Page::class)
            ->findAll();
        return $this->render('home/index.html.twig', ['pages' => $pages]);
    }
}
