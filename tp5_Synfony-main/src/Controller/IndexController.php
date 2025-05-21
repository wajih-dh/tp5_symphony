<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    // Afficher la liste des articles
    #[Route('/', name: 'article_list')]
    public function home(ManagerRegistry $doctrine): Response
    {
        // Fetch all articles from the Article repository
        $articles = $doctrine->getRepository(Article::class)->findAll();

        return $this->render('articles/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    // Créer un article via un formulaire
    #[Route('/article/new', name: 'article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ManagerRegistry $doctrine): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->persist($article);
            $entityManager->flush();

            $this->addFlash('success', 'Article ajouté avec succès');
            return $this->redirectToRoute('article_list');
        }

        return $this->render('articles/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Sauvegarder un article manuellement (pour tests)
    #[Route('/article/save', name: 'article_save')]
    public function save(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $article = new Article();
        $article->setNom('Article 1');
        $article->setPrix('1000.00');
        $entityManager->persist($article);
        $entityManager->flush();

        return new Response('Article enregistré avec id ' . $article->getId());
    }

    // Afficher un article spécifique
    #[Route('/article/{id}', name: 'article_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(ManagerRegistry $doctrine, int $id): Response
    {
        $article = $doctrine->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé');
        }

        return $this->render('articles/show.html.twig', [
            'article' => $article,
        ]);
    }

    // Modifier un article
    #[Route('/article/edit/{id}', name: 'article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ManagerRegistry $doctrine, int $id): Response
    {
        $article = $doctrine->getRepository(Article::class)->find($id);
        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé');
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->flush();

            $this->addFlash('success', 'Article modifié avec succès');
            return $this->redirectToRoute('article_list');
        }

        return $this->render('articles/edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

    // Supprimer un article
    #[Route('/article/delete/{id}', name: 'article_delete', methods: ['POST'])]
    public function delete(Request $request, ManagerRegistry $doctrine, int $id): Response
    {
        $article = $doctrine->getRepository(Article::class)->find($id);
        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé');
        }

        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            $entityManager = $doctrine->getManager();
            $entityManager->remove($article);
            $entityManager->flush();

            $this->addFlash('success', 'Article supprimé avec succès');
        } else {
            throw $this->createAccessDeniedException('Token CSRF invalide');
        }

        return $this->redirectToRoute('article_list');
    }
}