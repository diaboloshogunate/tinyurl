<?php
namespace App\Controller;

use App\Entity\TinyUrl;
use App\Form\TinyUrlType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class Home extends AbstractController
{
    /**
     * @Route("/", name="Homepage")
     */
    public function home(Request $request, \App\Service\TinyURL $urlGenerator): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $tinyUrl = new TinyUrl();
        $form = $this->createForm(TinyUrlType::class, $tinyUrl, [
            'action' => $this->generateUrl('Homepage'),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $string = $urlGenerator->genUniqueString();

            $tinyUrl = $form->getData();
            $tinyUrl->setShort($string);

            $entityManager->persist($tinyUrl);
            $entityManager->flush();

            return $this->redirectToRoute('view', ['short' => $string]);
        }

        return $this->render('home.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/view/{short}", name="view", requirements={"short"="[a-z0-9]+"})
     */
    public function view(string $short): Response
    {

        $tinyUrl = $this->getDoctrine()
            ->getRepository(TinyUrl::class)
            ->findOneBy(['short' => $short]);

        if(!$tinyUrl) throw new NotFoundHttpException();

        return $this->render('view.html.twig', [
            'url' => $tinyUrl,
        ]);
    }

    /**
     * @Route("/{short}", name="reroute", requirements={"short"="[a-z0-9]+"})
     */
    public function reroute(string $short): Response
    {
        $tinyUrl = $this->getDoctrine()
            ->getRepository(TinyUrl::class)
            ->findOneBy(['short' => $short]);

        if(!$tinyUrl) throw new NotFoundHttpException();

        return $this->redirect($tinyUrl->getFull());
    }
}