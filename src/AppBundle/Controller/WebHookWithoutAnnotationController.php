<?php
namespace AppBundle\Controller;

use Github\Client;
use Github\HttpClient\CachedHttpClient;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WebHookWithoutAnnotationController extends Controller
{
    /**
     * @Route("/", name="webhook")
     */
    public function webHookAction(Request $request)
    {
        if (!$this->validateSignature($request, 'my_secret')) {
            return new JsonResponse(['status' => 'Invalid signature'], 401);
        }

        $event = $request->headers->get('X-GitHub-Event');

        if ($event !== 'pull_request') {
            return new Response();
        }

        $payload = @json_decode($request->getContent(), true);

        if ($payload['action'] !== 'opened') {
            return new Response();
        }

        $author = $payload['pull_request']['user']['login'];
        $prId = $payload['pull_request']['number'];

        $client = new Client(
            new CachedHttpClient(
                array(
                    'cache_dir' => $this->container->getParameter('kernel.cache_dir') . '/github-api-cache'
                )
            )
        );
        $client->authenticate('THE_GITHUB_TOKEN', null, Client::AUTH_HTTP_TOKEN);

        $client->issue()->comments()->create(
            $payload['repository']['owner']['login'],
            $payload['repository']['name'],
            $prId,
            ['body' => sprintf('Hi %s! Thanks for your participation :heart:!', $author)]
        );

        return new Response('OK');
    }

    /**
     * @param Request $request
     * @param string $secret
     *
     * @return bool
     */
    private function validateSignature(Request $request, $secret)
    {
        $signature = $request->headers->get('X-Hub-Signature');
        $payload   = $request->getContent();

        if (empty($signature)) {
            return false;
        }

        $explodeResult = explode('=', $signature, 2);

        if (2 !== count($explodeResult)) {
            return false;
        }

        list($algorithm, $hash) = $explodeResult;

        if (empty($algorithm) || empty($hash)) {
            return false;
        }

        $payloadHash = @hash_hmac($algorithm, $payload, $secret);

        return $hash === $payloadHash;
    }
}
