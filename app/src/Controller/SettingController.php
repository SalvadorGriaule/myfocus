<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\CityType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class SettingController extends AbstractController
{
    #[Route('/setting', name: 'app_setting')]
    #[IsGranted('ROLE_USER')]
    public function editCity(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();   // User connecté

        $form = $this->createForm(CityType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Ville enregistrée.');
            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('setting/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
