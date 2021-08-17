<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\User;
use App\Entity\Works;
use App\Form\ArticleType;
use App\Form\CategoryType;
use App\Form\WorksType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BlogController extends AbstractController
{
    //La Methode d'Admin

    public function admin()
    {
        $articles = $this->getDoctrine()->getRepository(Article::class)->findBy(
            [],
            ['lastUpdateDate' => 'DESC']
        );

        $works = $this->getDoctrine()->getRepository(Works::class)->findAll();
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

        return $this->render('admin/index.html.twig', [
            'articles' => $articles,
            'works' => $works,
            'users' => $users
        ]);
    }

    // La Methode de page D'acceuil
    public function index()
    {
        $articles = $this->getDoctrine()->getRepository(Article::class)->findBy(
            ['isPublished' => true],
            ['publicationDate' => 'desc']
        );

        return $this->render('blog/index.html.twig', ['articles' => $articles]);
    }

    //Ajouter un Article

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function add(Request $request)
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setLastUpdateDate(new \DateTime());
            if ($article->getPicture() !== null) {
                $file = $form->get('picture')->getData();

                $fileName =  uniqid(). '.' .$file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('images_directory'), // Le dossier dans le quel le fichier va etre charger
                        $fileName
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                $article->setPicture($fileName);
            }

            if ($article->getIsPublished()) {
                $article->setPublicationDate(new \DateTime());
            }

            $em = $this->getDoctrine()->getManager(); // On récupère l'entity manager
            $em->persist($article); // On confie notre entité à l'entity manager (on persist l'entité)
            $em->flush(); // On execute la requete

            return new Response("L'article a bien été enregistrer.");
        }

        return $this->render('blog/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    //Aficher un article
    public function show(Article $article)
    {
        return $this->render('blog/show.html.twig', [
            'article' => $article
        ]);
    }

    //modifier Un article
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(Article $article, Request $request)
    {
        $oldPicture = $article->getPicture();

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setLastUpdateDate(new \DateTime());

            if ($article->getIsPublished()) {
                $article->setPublicationDate(new \DateTime());
            }

            if ($article->getPicture() !== null && $article->getPicture() !== $oldPicture) {
                $file = $form->get('picture')->getData();
                $fileName = uniqid(). '.' .$file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('images_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                $article->setPicture($fileName);
            } else {
                $article->setPicture($oldPicture);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            return new Response('L\'article a bien été modifier.');
        }

        return $this->render('blog/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView()
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function remove($id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $article = $entityManager->getRepository(Article::class)->find($id);
        $entityManager->remove($article);
        $entityManager->flush();
        return new Response('<h1 style="color: green"> article Supprimer :' .$id. '</h1>');
    }

    //Ajouter une categorie
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function addCategorie(Request $req)
    {
        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();

        $categorie = new Category();
        $form = $this->createForm(CategoryType::class, $categorie);
        $form->handleRequest($req);
        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($categories as $item){
                if($item->getLabel() === $categorie->getLabel()){
                        return $this->render('blog/addcategory.html.twig', [
                            'message' => 'Categorie deja exister',
                            'categoform' => $form->createView()
                        ]);
                }
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($categorie);
            $entityManager->flush();
            return $this->render('blog/addcategory.html.twig', [
                'success' => 'Categorie bien enregistrer',
                'categoform' => $form->createView()
            ]);
        }
        return $this->render('blog/addcategory.html.twig', [
            'categoform' => $form->createView()
        ]);
    }

    //////////////////////////////// ###---WORKS---#### /////////////////////////////////////////

    // all work
    public function works()
    {
        $works = $this->getDoctrine()->getRepository(Works::class)->findAll();

        return $this->render('work/index.html.twig', ['works' => $works]);
    }

    // add a new work
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function addwork(Request $request)
    {
        $work = new Works();
        $form = $this->createForm(WorksType::class, $work);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

                $file = $form->get('picture')->getData();

                $fileName =  uniqid(). '.' .$file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('images_directory'), // Le dossier dans le quel le fichier va etre charger
                        $fileName
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                $work->setPicture($fileName);


            $em = $this->getDoctrine()->getManager(); // On récupère l'entity manager
            $em->persist($work); // On confie notre entité à l'entity manager (on persist l'entité)
            $em->flush(); // On execute la requete

            return new Response("Le work a bien été enregistrer.");
        }

        return $this->render('work/add.html.twig', [
            'formwork' => $form->createView()
        ]);
    }

    //Aficher un work
    public function showwork(Works $work)
    {
        return $this->render('work/show.html.twig', [
            'work' => $work
        ]);
    }

    //modifier work
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function editwork(Works $work, Request $request)
    {
        $oldWork = $work->getPicture();

        $form = $this->createForm(WorksType::class, $work);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {




                $file = $form->get('picture')->getData();
                $fileName = uniqid(). '.' .$file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('images_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                $work->setPicture($fileName);

                $work->setPicture($oldWork);


            $em = $this->getDoctrine()->getManager();
            $em->persist($work);
            $em->flush();

            return new Response('L\'article a bien été modifier.');
        }

        return $this->render('work/edit.html.twig', [
            'work' => $work,
            'formwork' => $form->createView()
        ]);
    }

    //supprimer un work
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function removework($id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $work = $entityManager->getRepository(Works::class)->find($id);
        $entityManager->remove($work);
        $entityManager->flush();
        return new Response('<h1 style="color: green"> work Supprimer :' .$id. '</h1>');
    }
}
