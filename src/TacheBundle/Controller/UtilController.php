<?php

namespace TacheBundle\Controller;

use AppBundle\Entity\CategorieFiscaleSociale;
use AppBundle\Entity\RegimeFiscal;
use AppBundle\Entity\TypeActivite;
use Doctrine\DBAL\Exception\ServerException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class UtilController extends Controller
{
    public function categorieAction()
    {
        $categories = $this->getDoctrine()
            ->getRepository('AppBundle:CategorieFiscaleSociale')
            ->getAllOrderByType();

        $rows = array();
        /** @var CategorieFiscaleSociale $categorie */
        foreach ($categories as $categorie) {
            $rows[] = array(
                'id' => $categorie->getId(),
                'cell' => array(
                    $categorie->getType(),
                    $categorie->getNom(),
                    '<i class="fa fa-save icon-action js-save-button js-save-categorie" title="Enregistrer"></i><i class="fa fa-trash icon-action js-delete-categorie" title="Supprimer"></i>',
                )
            );
        }
        $liste = array(
            'rows' => $rows,
        );
        return new JsonResponse($liste);
    }

    public function categorieEditAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $id = $request->request->get('id', '');
            $categorie_type = $request->request->get('categorie-type');
            $categorie_nom = $request->request->get('categorie-nom');
            $categorie_type_libelle = "Fiscale";
            if ($categorie_type != 1) {
                $categorie_type_libelle = "Sociale";
            }
            $em = $this->getDoctrine()
                ->getManager();
            if ($id != '') {
                try {
                    //Modification
                    if ($id != 'new_row') {
                        $categorie = $this->getDoctrine()
                            ->getRepository('AppBundle:CategorieFiscaleSociale')
                            ->find($id);
                        if ($categorie) {
                            $categorie->setNom($categorie_nom);
                            $categorie->setType($categorie_type);
                            $em->flush();
                            $data = array(
                                'erreur' => false,
                            );
                            return new JsonResponse(json_encode($data));
                        }
                    } else {
                        //Ajout nouvelle catégorie
                        $categorie = new CategorieFiscaleSociale();
                        $categorie
                            ->setNom($categorie_nom)
                            ->setType($categorie_type);
                        $em->persist($categorie);
                        $em->flush();

                        $data = array(
                            'erreur' => false,
                        );
                        return new JsonResponse(json_encode($data));
                    }
                } catch (\Exception $ex) {
                        if (strpos($ex->getMessage(), "nom_type_UNIQUE")) {
                            return new Response("La catégorie '$categorie_nom' existe déjà dans '$categorie_type_libelle'.", 500);
                        }
                    }

            }
            throw new NotFoundHttpException("Catégorie introuvable.");
        } else {
            throw new AccessDeniedHttpException("Accès refusé");
        }
    }

    public function categorieRemoveAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $id = $request->request->get('id', '');
            if ($id != '' && $id != 'new_row') {
                $categorie = $this->getDoctrine()
                    ->getRepository('AppBundle:CategorieFiscaleSociale')
                    ->find($id);
                if ($categorie) {
                    $em = $this->getDoctrine()
                        ->getManager();
                    $em->remove($categorie);
                    $em->flush();
                } else {
                    throw new NotFoundHttpException("Catégorie introuvable.");
                }
            }
        } else {
            throw new AccessDeniedHttpException("Accès refusé");
        }

        $data = array(
            'erreur' => false,
        );
        return new JsonResponse(json_encode($data));
    }

    public function activiteAction()
    {
        $activites = $this->getDoctrine()
            ->getRepository('AppBundle:TypeActivite')
            ->getAll();

        $rows = array();
        /** @var TypeActivite $activite */
        foreach ($activites as $activite) {
            $rows[] = array(
                'id' => $activite->getId(),
                'cell' => array(
                    $activite->getLibelle(),
                    '<i class="fa fa-save icon-action js-save-button js-save-activite" title="Enregistrer"></i><i class="fa fa-trash icon-action js-delete-activite" title="Supprimer"></i>',
                )
            );
        }
        $liste = array(
            'rows' => $rows,
        );
        return new JsonResponse($liste);
    }

    public function activiteEditAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $id = $request->request->get("id", "");
            $libelle = $request->request->get("activite-libelle");
            $em = $this->getDoctrine()
                ->getManager();
            if ($id != "") {
                try {
                    if ($id != "new_row") {
                        $activite = $this->getDoctrine()
                            ->getRepository('AppBundle:TypeActivite')
                            ->find($id);
                        if ($activite) {
                            $activite->setLibelle($libelle);
                        } else {
                            throw new NotFoundHttpException("Type d'activité introuvable.");
                        }
                    } else {
                        $activite = new TypeActivite();
                        $activite->setLibelle($libelle);
                        $em->persist($activite);
                    }
                    $em->flush();
                    $data = array(
                        'erreur' => false,
                    );
                    return new JsonResponse(json_encode($data));
                } catch (\Exception $ex) {
                    if (strpos($ex->getMessage(), "libelle_UNIQUE")) {
                        return new Response("L'activité '$libelle' existe déjà", 500);
                    }
                }
            }
            throw new NotFoundHttpException("Type d'activité introuvable.");
        } else {
            throw new AccessDeniedHttpException("Accès refusé.");
        }
    }

    public function activiteRemoveAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $id = $request->request->get("id", "");
            $em = $this->getDoctrine()
                ->getManager();
            if ($id != "") {
                if ($id != "new_row") {
                    $activite = $this->getDoctrine()
                        ->getRepository('AppBundle:TypeActivite')
                        ->find($id);
                    if ($activite) {
                        $em->remove($activite);
                    } else {
                        throw new NotFoundHttpException("Type d'activité introuvable.");
                    }
                }
                $em->flush();
                $data = array(
                    'erreur' => false,
                );
                return new JsonResponse(json_encode($data));
            }
            throw new NotFoundHttpException("Type d'activité introuvable.");
        } else {
            throw new AccessDeniedHttpException("Accès refusé.");
        }
    }

    public function regimeFiscalAction()
    {
        $regimes = $this->getDoctrine()
            ->getRepository('AppBundle:RegimeFiscal')
            ->getAll();

        $rows = array();
        /** @var RegimeFiscal $regime */
        foreach ($regimes as $regime) {
            $rows[] = array(
                'id' => $regime->getId(),
                'cell' => array(
                    $regime->getLibelle(),
                    $regime->getStatus(),
                    '<i class="fa fa-save icon-action js-save-button js-save-regime" title="Enregistrer"></i><i class="fa fa-trash icon-action js-delete-regime" title="Supprimer"></i>',
                )
            );
        }
        $liste = array(
            'rows' => $rows,
        );
        return new JsonResponse($liste);
    }

    public function regimeFiscalEditAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $id = $request->request->get("id", "");
            $libelle = $request->request->get("regime-libelle");
            $status = $request->request->get("regime-status") == 1 ? true : false;
            $em = $this->getDoctrine()
                ->getManager();
            if ($id != "") {
                try {
                    if ($id != "new_row") {
                        $regime = $this->getDoctrine()
                            ->getRepository('AppBundle:RegimeFiscal')
                            ->find($id);
                        if ($regime) {
                            $regime->setLibelle($libelle);
                            $regime->setStatus($status);
                        } else {
                            throw new NotFoundHttpException("Regime Fiscal introuvable.");
                        }
                    } else {
                        $regime = new RegimeFiscal();
                        $regime->setLibelle($libelle);
                        $regime->setStatus($status);
                        $em->persist($regime);
                    }
                    $em->flush();
                    $data = array(
                        'erreur' => false,
                    );
                    return new JsonResponse(json_encode($data));
                } catch (\Exception $ex) {
                    if (strpos($ex->getMessage(), "libelle_UNIQUE")) {
                        return new Response("Le Regime Fiscal '$libelle' existe déjà", 500);
                    } else {
                        return new Response($ex->getMessage(), 500);
                    }
                }
            }
            throw new NotFoundHttpException("Type d'activité introuvable.");
        } else {
            throw new AccessDeniedHttpException("Accès refusé.");
        }
    }

    public function regimeFiscalRemoveAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $id = $request->request->get("id", "");
            $em = $this->getDoctrine()
                ->getManager();
            if ($id != "") {
                if ($id != "new_row") {
                    $regime = $this->getDoctrine()
                        ->getRepository('AppBundle:RegimeFiscal')
                        ->find($id);
                    if ($regime) {
                        $em->remove($regime);
                    } else {
                        throw new NotFoundHttpException("Regime Fiscal introuvable.");
                    }
                }
                $em->flush();
                $data = array(
                    'erreur' => false,
                );
                return new JsonResponse(json_encode($data));
            }
            throw new NotFoundHttpException("Regime Fiscal introuvable.");
        } else {
            throw new AccessDeniedHttpException("Accès refusé.");
        }
    }

    public function tacheLegaleColNamesAction()
    {
        $regimes_fiscaux = $this->getDoctrine()
            ->getRepository('AppBundle:RegimeFiscal')
            ->getAllForTacheLegale();
        $rf = array();
        /** @var  RegimeFiscal $regime_fiscal */
        foreach ($regimes_fiscaux as $regime_fiscal)
        {
            $rf[] = $regime_fiscal->getLibelle();
        }
        $col1 = ['Domaine', 'Date', 'Tâche', 'Type_Entreprise_Value', 'Type Entreprise', 'Cat_Sociale_Value',
            'Cat. Sociale', 'Cat_Fiscale_Value', 'Cat. Fiscale', 'Regime_Fiscal_Value'];
        $col2 = ['Clôture', 'Date Déclencheur',
            'Déclaration à Faire', 'Nom cerfa', 'Télépaiement à faire', 'Description', 'Jalon', 'Légale',
            'Tâches Préc.', 'Tâches Suiv.', '<span class="fa fa-bookmark-o" style="display:inline-block"/> Action'];
        $col_names = array_merge($col1, $rf, $col2);

        $rf_col_data = array();

        $i = 1;
        /** @var  RegimeFiscal $regime_fiscal2 */
        foreach ($regimes_fiscaux as $regime_fiscal2)
        {
            $rf_col_data[] = ['name' => 'l-regime-fiscal-'.$i, 'index' => 'l-regime-fiscal-'.$i, 'width' => 90, 'fixed' => true,
                'formatter' => 'checkbox', 'align' => 'center', 'classes' => 'js-regime-fiscal'];
            $i++;
        }

        $col_data1 = [
            ['name' => 'l-domaine', 'index' => 'l-domaine', 'hidden' => true, 'classes' => 'js-l-domaine'],
            ['name' => 'l-date-tache', 'index' => 'l-date-tache', 'width' => 80, 'fixed' => true, 'align' => 'center', 'classes' => 'js-l-date-tache'],
            ['name' => 'l-tache', 'index' => 'l-tache', 'width' => 250, 'classes' => 'js-l-tache'],
            ['name' => 'l-type-activite-value', 'index' => 'l-type-activite-value', 'hidden' => true, 'classes' => 'js-l-type-activite-value'],
            ['name' => 'l-type-activite', 'index' => 'l-type-activite', 'classes' => 'js-l-type-activite'],
            ['name' => 'l-cat-sociale-value', 'index' => 'l-cat-sociale-value', 'hidden' => true, 'classes' => 'js-l-cat-sociale-value'],
            ['name' => 'l-cat-sociale', 'index' => 'l-cat-sociale', 'classes' => 'js-l-cat-sociale'],
            ['name' => 'l-cat-fiscale-value', 'index' => 'l-cat-fiscale-value', 'hidden' => true, 'classes' => 'js-l-cat-fiscale-value'],
            ['name' => 'l-cat-fiscale', 'index' => 'l-cat-fiscale', 'classes' => 'js-l-cat-fiscale'],
            ['name' => 'l-regime-fiscale-value', 'index' => 'l-regime-fiscale-value', 'hidden' => true, 'classes' => 'js-l-regime-fiscale-value'],
        ];

        $col_data2 = [
            ['name' => 'l-cloture', 'index' => 'l-cloture', 'width' => 100, 'fixed' => true, 'align' => 'center', 'classes' => 'js-l-cloture'],
            ['name' => 'l-date-declencheur', 'index' => 'l-date-declencheur', 'classes' => 'js-l-date-declencheur'],
            ['name' => 'l-declaration', 'index' => 'l-declaration', 'classes' => 'js-l-declaration'],
            ['name' => 'l-cerfa', 'index' => 'l-cerfa', 'classes' => 'js-l-cerfa'],
            ['name' => 'l-telepaiement', 'index' => 'l-telepaiement', 'formatter' => 'checkbox', 'align' => 'center', 'classes' => 'js-l-telepaiement'],
            ['name' => 'l-description', 'index' => 'l-description', 'width' => 300, 'classes' => 'js-l-description'],
            ['name' => 'l-jalon', 'index' => 'l-jalon', 'formatter' => 'checkbox', 'align' => "center", 'classes' => 'js-l-jalon'],
            ['name' => 'l-legale', 'index' => 'l-legale', 'formatter' => 'checkbox', 'align' => "center", 'classes' => 'js-l-legale'],
            ['name' => 'l-precedent', 'index' => 'l-precedent', 'editable' => false, 'width' => 150, 'align' => 'center', 'classes' => 'js-l-precedent pointer'],
            ['name' => 'suivant', 'index' => 'suivant', 'editable' => false, 'width' => 150, 'align' => 'center', 'classes' => 'js-l-suivant pointer'],
            ['name' => 'l-action', 'index' => 'l-action', 'width' => 80, 'fixed' => true, 'align' => 'center', 'sortable' => false, 'classes' => 'js-l-action']
        ];

        $col_models = array_merge($col_data1, $rf_col_data, $col_data2);

        $data = [
            'col_names' => $col_names,
            'col_models' => $col_models,
        ];
        return new JsonResponse(json_encode($data));
    }
}
