<?php

namespace App\Controller\Front;

use App\Controller\Front\Base\Controller;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class WebsocketController extends Controller
{
    /**
     * @Route("/websocket/server/parser", name="app_websocket_server", options={"expose"=true})
     * @throws \Exception
     */
    public function server(KernelInterface $kernel)
    {
        set_time_limit(0);

        $application = new Application($kernel);
        $application->setAutoExit(false);

        echo $this->renderView('websocket/panel_top.html.twig');

        $input = new ArrayInput([
            'command' => 'app:websocket:server'
        ]);

        $output = new BufferedOutput();
        $application->run($input, $output);

        $content = $output->fetch();

        echo $content;

        return $this->render('websocket/panel_bottom.html.twig');
    }
}