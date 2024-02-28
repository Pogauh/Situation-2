<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Mailer\MailerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\ORM\EntityManagerInterface;

use App\Form\ModifAccountType;
use App\Form\AddProduitType;
use App\Form\ContactType;

use App\Entity\User;
use App\Entity\Produit;
use App\Entity\Contact;
//use App\Entity\Avis;
use App\Entity\Article;
use App\Entity\Ajouter;
use App\Entity\Panier;




class BaseController extends AbstractController
{
    #[Route('/base', name: 'app_base')]
    public function index(): Response
    {
        return $this->render('base/index.html.twig', [
            'controller_name' => 'BaseController',
        ]);
    }

    #[Route('/cgv', name: 'cgv')]
    public function cgv(): Response
    {
        return $this->render('base/cgv.html.twig', [
        ]);
    }

    #[Route('/mentionlegale', name: 'mentionlegale')]
    public function mentionlegale(): Response
    {
        return $this->render('base/mentionlegale.html.twig', [
        ]);
    }

    #[Route('/account', name: 'account')]
    public function account(): Response
    {
        return $this->render('base/compte.html.twig', [
        ]);
    }

    #[Route('/produit', name: 'produit')]
    public function produit(): Response
    {
        return $this->render('produit/produit.html.twig', [
        ]);
    }

    #[Route('/offre', name: 'offre')]
    public function offre(): Response
    {
        return $this->render('produit/offre.html.twig', [
        ]);
    }

    #[Route('/programme', name: 'programme')]
    public function programme(): Response
    {
        return $this->render('produit/programme.html.twig', [
        ]);
    }

    #[Route('modifAccount', name: 'modifAccount')]
    public function modifAccount(Request $request, EntityManagerInterface $entityManagerInterface): Response
    {
    $user = $this->getUser();

    $form = $this->createForm(ModifAccountType::class, $user);

    if ($request->isMethod('POST')) {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManagerInterface->flush();

            $this->addFlash('notice', 'Modifications effectuées');
            return $this->redirectToRoute('account');
        } else {
            $this->addFlash('notice', 'Modifications non effectuées');
        }
    }

    return $this->render('base/modifAccount.html.twig', [
        'form' => $form->createView(),
    ]);
    }

    #[Route('/contact', name: 'contact')]
    public function contact(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManagerInterface): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);

        if($request->isMethod('POST')){
            $form->handleRequest($request);
            if ($form->isSubmitted()&&$form->isValid()){   
                $email = (new TemplatedEmail())
                ->from($contact->getEmail())
                ->to('ultrabaga@hotmail.com')
                ->subject($contact->getSujet())
                ->htmlTemplate('emails/email.html.twig')
                ->context([
                    'nom'=> $contact->getNom(),
                    'sujet'=> $contact->getSujet(),
                    'message'=> $contact->getMessage()
                ]);

                $contact->setDateEnvoi(new \Datetime());
                $entityManagerInterface->persist($contact);
                $entityManagerInterface->flush();
              
                $mailer->send($email);
                $this->addFlash('notice','Message envoyé');
                //return $this->redirectToRoute('contact');
                
            }
        }


        return $this->render('contact/contact.html.twig', [
            'form' => $form->createView()
        ]);
   
    }

    //-----------------------------------------------------------------------------------------------------------

    #[Route('/ajouterFavoris', name: 'ajouterFavoris')]
    public function ajouterFavoris(Request $request, EntityManagerInterface $entityManagerInterface): Response
    {
    $id = $request->get('id');
    $produit = $entityManagerInterface->getRepository(Produit::class)->find($id);


    $action = $request->get('action');
    $this->addFlash('notice', 'Produit ajouter au favoris');

    //ajouter un favoris
    if ($action == 'ajouterFavoris'){
        $this->getUser()->addFavori($produit);
        $entityManagerInterface->persist($this->getUser());
        $entityManagerInterface->flush();

    } 
    

    return $this->redirectToRoute('produit');
    }


    #[Route('/supprimerFavoris', name: 'supprimerFavoris')]
    public function SupprimerFavoris(Request $request, EntityManagerInterface $entityManagerInterface): Response
    {
    $id = $request->get('id');
    $produit = $entityManagerInterface->getRepository(Produit::class)->find($id);

    $action = $request->get('action');
    $this->addFlash('notice','Produit supprimer des favoris');

    // Supprimer un favoris
    if ($action == 'supprimerFavoris'){
        $this->getUser()->removeFavori($produit);
        $entityManagerInterface->persist($this->getUser());
        $entityManagerInterface->flush();
    }
        return $this->redirectToRoute('produit');

    }

    #[Route('/favoris', name: 'favoris')]
    public function favoris(Request $request, EntityManagerInterface $entityManagerInterface): Response
    {
        $action = $request->get('action');
        $produitRepository = $entityManagerInterface->getRepository(Produit::class);

        
        $produits = $produitRepository->findAll(true);
        

        return $this->render('base/favoris.html.twig', [
            'produits' => $produits

        ]);
    }

    #[Route('/panier', name: 'panier')]
    public function panier(Request $request, EntityManagerInterface $entityManagerInterface): Response
    {
        $produitRepository = $entityManagerInterface->getRepository(Produit::class);
        $produit = $produitRepository->findall();

        return $this->render('base/panier.html.twig', [
            'produit' => $produit
        ]);
    }
    #[Route('/gestion-panier', name: 'gestionPanier')]
    public function gestionPanier(Request $request, EntityManagerInterface $entityManagerInterface): Response
    {
        //supprimer du panier
        $action = $request->get('action');
        $id = $request->get('id');
        if ($action == 'removePanier'){
            $panierRepository = $entityManagerInterface->getRepository(Panier::class);
            $panier = $panierRepository->find($id);
            $ajouterRepository = $entityManagerInterface->getRepository(Ajouter::class);
            $ajouter = $ajouterRepository->find($id);
            $entityManagerInterface->remove($ajouter);
            $entityManagerInterface->flush();
            $this->addFlash('notice', 'Produit supprimer du panier');

        }

        // Supprimer toutes les lignes enfants de la table `ajouter` qui font référence à la ligne parente dans la table `panier`
        if ($action == 'removeAll'){
            $panierRepository = $entityManagerInterface->getRepository(Panier::class);
            $panier = $panierRepository->find($id);
            $ajouterRepository = $entityManagerInterface->getRepository(Ajouter::class);
            $ajouter = $ajouterRepository->find($id);
            $ajouterRepository->createQueryBuilder('a')
            ->delete()
            ->where('a.panier = :panier')
            ->setParameter('panier', $panier)
            ->getQuery()
            ->execute();
        }

            // Pour gerer les quantité 
            if ($action == 'plus'){
                $ajouter = $entityManagerInterface->getRepository(Ajouter::class)->find($id);
                $ajouter->setQuantite($ajouter->getQuantite()+1);
                $entityManagerInterface->persist($ajouter);
                $entityManagerInterface->flush();
            }
            if ($action == 'moins'){
                $ajouter = $entityManagerInterface->getRepository(Ajouter::class)->find($id);
                $qte = $ajouter->getQuantite();
                if($qte>1){
                    $ajouter->setQuantite($qte-1);
                }
                $entityManagerInterface->persist($ajouter);
                $entityManagerInterface->flush();
            }
            

        //ajouter au panier
        $id = $request->get('id');
        if ($action == 'addPanier'){
            if($this->getUser()->getPanier()==null){
                $panier= New Panier();
                $this->setPanier($panier);
            }
            $produit = $entityManagerInterface->getRepository(Produit::class)->find($id);
            $ajouter = new Ajouter();
            // A modifié
            $ajouter->setQuantite(1);
            if ($produit!==null){
                $ajouter -> setProduit($produit);
                $ajouter->setIdPanier($this->getUser()->getPanier());
                $entityManagerInterface->persist($ajouter);
                $this->getUser()->getPanier()->addAjouter($ajouter);
                $entityManagerInterface->persist($this->getUser());
                $entityManagerInterface->persist($ajouter);
                $entityManagerInterface->flush();
                $this->addFlash('notice', 'Produit ajouter au panier');
                
            }
        }

        return $this->redirectToRoute('panier');
        
            
    }

    #[Route('/gestion', name: 'gestion')]
    public function gestion(): Response
    {
        return $this->render('base/gestion.html.twig', [
        ]);
    }

#[Route('/addProduit', name: 'addProduit')]
    public function addProduit(Request $request, SluggerInterface $slugger, EntityManagerInterface $entityManagerInterface): Response
    {
        $produit = new Produit();

        $form = $this->createForm(AddProduitType::class, $produit);

        if($request->isMethod('POST')){
            $form->handleRequest($request);
            if ($form->isSubmitted()&&$form->isValid()){


                //C'est pour enregistré les image dans le produit
                
                $img = $form->get('image')->getData();
                if($img){

                    $nomImage= pathinfo($img->getClientOriginalName(), PATHINFO_FILENAME);
                    $nomImage= $slugger->slug($nomImage);
                    $nomImage = $nomImage.'-'.uniqid().'.'.$img->guessExtension();
                    $produit->setImage($nomImage);
                       try{                 
                           $img->move($this->getParameter('image_directory'), $nomImage);
                           $this->addFlash('notice', 'Fichier envoyé');
                       }
                       catch(FileException $e){
                           $this->addFlash('notice', 'Erreur d\'envoi');
                       } 
                }


                $entityManagerInterface->persist($produit);
                $entityManagerInterface->flush();
                

            }

        }

        return $this->render('base/addProduit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
