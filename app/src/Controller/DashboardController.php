<?php

namespace App\Controller;

use App\Entity\Goal;
use App\Form\GoalType;
use App\Repository\GoalRepository;
use App\Service\FinanceService;
use App\Service\NewsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        NewsService $newsService,
        FinanceService $financeService
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Gestion Goal Form
        $goal = new Goal();
        $form = $this->createForm(GoalType::class, $goal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $goal->setUser($user);
            $entityManager->persist($goal);
            $entityManager->flush();

            return $this->redirectToRoute('app_dashboard');
        }

        // Récupère Data
        $weather = null; // Placeholder pour weather API
        $news = $newsService->getNews($user->getNewsKeywords() ?? 'technology');
        $finance = [
            'rate' => $financeService->getExchangeRate(),
            'bitcoin' => $financeService->getBitcoinPrice(),
        ];

        return $this->render('dashboard/index.html.twig', [
            'user' => $user,
            'weather' => $weather,
            'news' => $news,
            'finance' => $finance,
            'goalForm' => $form->createView(),
            'goals' => $user->getGoals(),
        ]);
    }

    #[Route('/dashboard/goal/{id}/toggle', name: 'app_goal_toggle')]
    public function toggleGoal(Goal $goal, EntityManagerInterface $entityManager): Response
    {
        if ($goal->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $goal->setDone(!$goal->isDone());
        $entityManager->flush();

        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/dashboard/goal/{id}/delete', name: 'app_goal_delete')]
    public function deleteGoal(Goal $goal, EntityManagerInterface $entityManager): Response
    {
        if ($goal->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $entityManager->remove($goal);
        $entityManager->flush();

        return $this->redirectToRoute('app_dashboard');
    }
}
