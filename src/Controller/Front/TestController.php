<?php

namespace App\Controller\Front;

use App\Controller\Front\Base\Controller;
use App\Enum\ParserType;
use App\Enum\SettingsGroups;
use App\Manager\SettingsManager;
use App\Model\ParsedFile;
use App\Model\ParserRequestModel;
use App\Parser\Boards4chanParser;
use App\Converter\EntityConverter;
use App\Parser\HentaiFoundryParser;
use App\Parser\ImagefapParser;
use App\Parser\RedditParser;
use App\Service\FileCache;
use App\Service\ImgurApi;
use App\Utils\CacheHelper;
use Doctrine\Common\Util\Debug;
use http\Client\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Stopwatch\Stopwatch;

class TestController extends Controller
{
    /**
     * @Route("/tester/parser_test/{parser}/{test}", name="app_parser_test_function")
     * @Method({"GET"})
     * @throws \ReflectionException
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function parserTest(Request $request)
    {
        $test = $request->get('test');
        $parser = $request->get('parser');
        $stopwatch = new Stopwatch();
        $stopwatch->start($test);

        $parserRequestModel = new ParserRequestModel();
        $parserRequestModel->currentNode = new \stdClass();

        $parsedFile = new ParsedFile();

        switch ($parser) {
            case ParserType::Boards4chan:
                $parser = $this->get(Boards4chanParser::class);

                switch ($test) {
                    case 'load_boards_list':
                        $parser->getBoardsListData($parserRequestModel);
                        break;

                    case 'load_galleries':
                        $parserRequestModel->currentNode->url = 'https://boards.4chan.org/s/catalog';
                        $parser->getBoardData($parserRequestModel);
                        break;


                    case 'load_gallery':
                        $parserRequestModel->currentNode->url = 'http://boards.4chan.org/s/thread/19147511';
                        $parser->getGalleryData($parserRequestModel);
                        break;

                }
                break;

            case ParserType::Reddit:
                $parser = $this->get(RedditParser::class);

                switch ($test) {
                    case 'load_subreddits':
                        $parser->getBoardsListData($parserRequestModel);
                        break;

                    case 'load_subreddit':
                        $parserRequestModel->currentNode->url = '/r/Boobies';
                        $parser->getBoardData($parserRequestModel);
                        break;

                    case 'load_gfycat_preview_data':
                        $parsedFile->url = 'https://gfycat.com/linearimpeccablehogget';
                        $parser->getFilePreview($parsedFile);
                        break;

                    case 'load_gfycat_file_data':
                        $parsedFile->url = 'https://gfycat.com/linearimpeccablehogget';
                        $parser->getFilePreview($parsedFile);
                        break;
                }
                break;

            case ParserType::Imagefap:
                $parser = $this->get(ImagefapParser::class);

                switch ($test) {
                    case 'load_users': // load users
                        $parserRequestModel->sorting = ['page' => 0];
                        $parser->loadOwnersList($parserRequestModel);
                        break;

                    case 'load_users_boards': // load user boards
                        $parserRequestModel->name = 'Jasondayfap83';
                        $parserRequestModel->identifier = 1481674;
                        $parser->getBoardData($parserRequestModel);
                        break;

                    case 'load_board_galleries': // load board galleries
                        $parserRequestModel->currentNode->url = 'https://www.imagefap.com/showfavorites.php?userid=1613432&folderid=3023725';
                        $parser->getBoardData($parserRequestModel);
                        break;

                    case 'load_gallery':
                        $parserRequestModel->currentNode->imagesNo = 227;
                        $parserRequestModel->currentNode->url = 'https://www.imagefap.com/pictures/4863376/Black-Babe---White-Cock-002';
                        $parser->getGalleryData($parserRequestModel);
                        break;

                    case 'load_file_data':
                        $parserRequestModel->currentNode->url = 'https://www.imagefap.com/photo/2045072831/?pgid=&gid=3121356&page=0&idx=27';
                        $parser->getFileData($parserRequestModel);
                        break;
                }
                break;

            case ParserType::HentaiFoundry:
                /** @var HentaiFoundryParser $parser */
                $parser = $this->get(HentaiFoundryParser::class);

                switch($test) {
                    case 'load_board_data':
                        $parser->getBoardData($parserRequestModel);
                        break;

                    case 'load_gallery':
                        $parserRequestModel->currentNode->url = 'pictures/user/a-rimbaud';
                        $parser->getGalleryData($parserRequestModel);
                        break;

                    case 'load_file_data':
                        $parserRequestModel->currentNode->url = 'pictures/user/sabudenego/733826/Zelda-Zelda-BotW';
                        $parser->getFileData($parserRequestModel);
                        break;
                }
                break;
        }

        if ($parsedFile->url) {
            var_dump($parsedFile);
        } else {
            var_dump($parserRequestModel);
        }

        $event = $stopwatch->stop($test);

        die("SCRIPT EXECUTION TIME: ".($event->getDuration()/1000)."s");
    }

    /**
     * @Route("/tester/app/{test}", name="app_funct_test_function")
     * @Method({"GET"})
     * @throws \ReflectionException
     */
    public function functionallityTest(Request $request)
    {
        $test = $request->get('test');

        switch ($test) {
            case 'entity_converter':
                $board = new Board();
                $data = [
                    'name' => 'test',
                    'description' => 'test2',
                    'url' => 'test3',
                    'createdAt' => '2018-02-02 14:32:33'
                ];

                $board = $this->get(EntityConverter::class)->setData($data, $board);
                $boardArray = $this->get(EntityConverter::class)->convert($board);

                Debug::dump($board);
                break;
        }

        return new \Symfony\Component\HttpFoundation\Response('TEST_DONE.');
    }

    /**
     * @Route("/tester/cache/{test}", name="app_tester_cache", methods={"GET"})
     */
    public function cacheTest(Request $request)
    {
        $test = $request->get('test');
        $cache = new FileCache();

        switch ($test) {
            case 'cache_read_write':
                $cache->savePageLoaderDescription(32);
                var_dump($cache->read('page_loader_status'));
                break;

            case 'cache_write_timing':
                $cache->save('test-value', '32', 5);

                sleep(3);

                var_dump($cache->read('test-value'));

                sleep(4);

                var_dump($cache->read('test-value'));

                break;
        }



        return new \Symfony\Component\HttpFoundation\Response('TEST_DONE');
    }
}