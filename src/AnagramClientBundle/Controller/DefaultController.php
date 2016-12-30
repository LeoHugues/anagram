<?php

namespace AnagramClientBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction(Request $request)
    {
        $words = false;
        $form = $this->createFormBuilder()
            ->add('search', TextType::class, array(
                'attr'  => array(
                    'placeholder' => "Write a word here !",
                    'class'       => 'text-center'
                )
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'Search Anagrams',
                'attr'  => array(
                    'class'       => 'btn-valider'
                )
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $word = $form->getData()['search'];

            $client = new \SoapClient("http://localhost:8080/AnagrameWS/WebServiceDico?WSDL");
            $encoding = $client->soap_defencoding = 'utf-8';
            $words = $client->FindAnagrameList(array("Mot" => $word));
            $words = $this->getFilterdAnagramList($words->return);
        }


        return $this->render('AnagramClientBundle:Default:index.html.twig', array(
            'form'  => $form->createView(),
            'words' => $words
        ));
    }

    private function getFilterdAnagramList(array $anagramList)
    {
        $sortedList = array();
        foreach ($anagramList as $word) {
            $wordIsSorted = false;
            foreach ($sortedList as $key => $value) {
                if ($key == strlen($word)) {
                    $sortedList[$key][] = $word;
                    $wordIsSorted = true;
                }
            }
            if (!$wordIsSorted) {
                $sortedList[strlen($word)][] = $word;
            }
        }

        ksort($sortedList);

        return $sortedList;
    }
}
