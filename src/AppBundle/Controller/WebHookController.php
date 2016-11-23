<?php
namespace AppBundle\Controller;

use Github\Client;
use Github\HttpClient\CachedHttpClient;
use Swop\GitHubWebHook\Event\GitHubEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Swop\Bundle\GitHubWebHookBundle\Annotation\GitHubWebHook;

class WebHookController extends Controller
{
    /**
     * @Route("/", name="webhook")
     *
     * @GitHubWebHook(eventType="pull_request", secret="my_secret")
     */
    public function webHookAction(GitHubEvent $gitHubEvent)
    {
        $payload = $gitHubEvent->getPayload();

        if ($payload['action'] !== 'opened') {
            return '';
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

        return 'ok';
    }
}
