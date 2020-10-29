<?php
namespace App\Controller;

use App\Entity\TinyUrl;
use App\Form\TinyUrlType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Home extends AbstractController
{
    /**
     * @Route("/", name="Homepage")
     */
    public function home(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $tinyUrl = new TinyUrl();
        $form = $this->createForm(TinyUrlType::class, $tinyUrl, [
            'action' => $this->generateUrl('Homepage'),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $string = $this->genUniqueString();

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
     * @Route("/view/{short}", name="view")
     */
    public function view(string $short): Response
    {

        $tinyUrl = $this->getDoctrine()
            ->getRepository(TinyUrl::class)
            ->findOneBy(['short' => $short]);

        return $this->render('view.html.twig', [
            'url' => $tinyUrl,
        ]);
    }

    /**
     * Get the number of possible unique values between two lengths with a given number of possible values per a position
     * position matters therefore az != za
     * @param int $charCount possible values for any given postion, 36 = a-z0-9
     * @param int $minLength minimal length of string
     * @param int $maxLength maximum length of string
     * @return int
     */
    private function countUniqueValues(int $charCount = 36, int $minLength = 5, int $maxLength = 9): int
    {
        if($maxLength < $minLength) return -1;// -1 because 0 index
        return pow($charCount, $maxLength) + $this->countUniqueValues($charCount, $minLength, $maxLength-1);
    }

    /**
     * gets the offset needed so that 0 is a string of length $length
     * @param int $charCount number of possible characters
     * @param int $length minimum length
     * @return int
     */
    private function getMinOffset(int $charCount = 36, int $length = 5): int
    {
        if($length == 0) return -1;// -1 for 0 index
        return pow($charCount, $length-1) + $this->getMinOffset($charCount, $length-1);
    }

    /**
     * recursively convert int to string
     * @param int $i the integrer to convert to a string
     * @param int $offset a offset to add to the int. Used to create minimum length strings.
     * @return string
     */
    private function intToString(int $i, string $characters, int $charCount, int $offset = 0): string {
        $i += $offset;
        return ($i >= $charCount ? $this->intToString((floor($i / $charCount) >> 0) - 1, $characters, $charCount, 0) : '') .  $characters[$i % $charCount >> 0];
    }

    /**
     * @param int $min min string length
     * @param int $max max string length
     * @param string $chracters possible characters
     * @return string
     * @throws \Exception
     */
    private function genUniqueString($min = 5, $max = 9, $chracters = 'abcdefghijklmnopqrstuvwxyz0123456789'): string
    {
        $charCount = strlen($chracters);
        $max = $this->countUniqueValues($charCount, $min, $max);
        $offset = $this->getMinOffset($charCount, $min);
        $number = random_int(0, $max);
        $string = $this->intToString($number, $chracters, $charCount, $offset);

        $tinyUrl = $this->getDoctrine()
            ->getRepository(TinyUrl::class)
            ->findOneBy(['short' => $string]);

        return $tinyUrl ? $this->genUniqueString($min, $max, $chracters) : $string;
    }
}