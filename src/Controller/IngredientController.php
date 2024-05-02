<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


class IngredientController extends AbstractController
{
    #[Route('/ingredient_home', name: 'home.index')]
    public function home(): Response
    {
        return $this->render('pages/home/home.html.twig');
    }
    #[Route('/ingredient', name: 'ingredient.index',methods: ['GET'])]
    public function index(IngredientRepository $repository,PaginatorInterface $paginator,Request $request): Response
    {
        $ingredients = $paginator->paginate(
            $repository->findAll(), /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('pages/ingredient/index.html.twig', [
            'ingredients'=>$ingredients
        ]);
    }
        //Creation et traitement de formulaire d'ajout
    #[Route('/ingredient/nouveau',name: 'ingredient.new',methods: ['GET','POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $manager):Response {
        $ingredient=new Ingredient();
        $form=$this->createForm(IngredientType::class,$ingredient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
           $ingredient=$form->getdata();

           $manager->persist($ingredient);
           $manager->flush();

            $this->addFlash(
                'success',
                'Votre ingredient a été crée avec succès!'
            );
           return $this->redirectToRoute('ingredient.index');
        }

        return $this->render('pages/ingredient/new.html.twig',
            [
            'form'=>$form->createView()
            ]);
    }

    #[Route('/ingredient/edition/{id}', name: 'ingredient.edit', methods: ['GET', 'POST'])]
    public function edit($id,
                         Request $request,
                         EntityManagerInterface $manager,
                         IngredientRepository $repository
    ): Response
    {
        $ingredient = $repository->find($id);

        if (!$ingredient) {
            throw $this->createNotFoundException('Ingredient not found');
        }

        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre ingrédient a été modifié avec succès!'
            );
            return $this->redirectToRoute('ingredient.index');
        }

        return $this->render('pages/ingredient/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/ingredient/suppression/{id}', name: 'ingredient.delete', methods: ['GET'])]
    public function delete(Request $request, EntityManagerInterface $manager, IngredientRepository $repository, $id): Response
    {
        $ingredient = $repository->find($id);

        if (!$ingredient) {
            $this->addFlash(
                'error',
                "L'ingrédient n'a pas été trouvé"
            );
        } else {
            $manager->remove($ingredient);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre ingrédient a été supprimé avec succès!'
            );
        }

        return $this->redirectToRoute('ingredient.index');
    }


}
