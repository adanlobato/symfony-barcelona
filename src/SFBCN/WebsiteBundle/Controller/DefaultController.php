<?php

namespace SFBCN\WebsiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DefaultController extends Controller
{
    /**
     * Renders Home page
     *
     * @return array
     * @Route("/", name="home")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $nextEvent = $em->getRepository('SFBCNWebsiteBundle:Event')->getNextEvent();

        return array(
            'current' => 'home',
            'nextEvent' => $nextEvent,
        );
    }

    /**
     * Renders About page
     *
     * @return array
     * @Route("/acerca-de", name="about")
     * @Template()
     */
    public function aboutAction()
    {
        $members = array(
          array(
            'name' => 'Ricard Clau',
            'email' => 'ricard.clau[at]gmail[dot]com',
            'foto' => 'https://secure.gravatar.com/avatar/2d5aaa5bfc55afb812af6693826e382b?s=140&d=https://a248.e.akamai.net/assets.github.com%2Fimages%2Fgravatars%2Fgravatar-140.png',
            'github' => 'ricardclau',
            'twitter' => 'ricardclau',
          ),
          array(
            'name' => 'Marcos Quesada',
          ),
          array(
            'name' => 'Berny Cantos',
          ),
          array(
            'name' => 'Adán Lobato',
            'email' => 'adan.lobato[at]gmail[dot]com',
            'foto' => 'http://1.gravatar.com/avatar/4295f5a4b169152d287fc4009d1afb19?size=140',
            'github' => 'adanlobato',
            'twitter' => 'adanlobato',
          ),
        );

        usort($members, function($a, $b) { return ($a['name'] > $b['name']) ? 1 : -1; });

        return array(
            'current' => 'about',
            'members' => $members,
        );
    }

    /**
     * Renders Contact page
     *
     * @return array
     * @Route("/contacto", name="contact")
     * @Method("get")
     * @Template()
     */
    public function contactAction()
    {
        return array(
            'current' => 'contact',
        );
    }

    /**
     * Sends the email through AJAX
     *
     * @return array
     * @Route("/contacto", name="contact_post", defaults={"_format"="json"})
     * @Method("post")
     * @Template()
     */
    public function sendMailAction()
    {
        $contactData = array(
           'nombre' => $this->getRequest()->get('nombre'),
           'email' => $this->getRequest()->get('email'),
           'mensaje' => $this->getRequest()->get('mensaje'),
        );

        $this->validateContactData($contactData);
        $mailTo = $this->container->getParameter('contactmail');

        $message =\Swift_Message::newInstance()
                    ->setSubject('Mensaje recibido desde la web Symfony-Barcelona')
                    ->setFrom(array($contactData['email'] => $contactData['nombre']))
                    ->setTo($mailTo)
                    ->setBody($contactData['mensaje']);

        $this->container->get('mailer')->send($message);

        return new Response(json_encode(array('message' => 'Mail enviado correctamente. En breve contactaremos contigo')));
    }

    /**
     * Validates contact data and throws exception if anything goes wrong
     *
     * @param array $contactData
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    private function validateContactData(array $contactData)
    {
        $collectionConstraint = new Collection(array(
            'nombre' => array(
                new NotBlank()
             ),
            'email' => array(
                new NotBlank(),
                new Email(),
            ),
            'mensaje' => array(
                new NotBlank()
            ),
        ));

        $errors = $this->container->get('validator')->validateValue($contactData, $collectionConstraint);
        if (count($errors) !== 0) {
            throw new HttpException(400, $errors[0]->getPropertyPath() . ':' . $this->container->get('translator')->trans($errors[0]->getMessage(), array(), 'validators'));
        }
    }
}
