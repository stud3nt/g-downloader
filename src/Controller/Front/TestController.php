<?php

namespace App\Controller\Front;

use App\Entity\Parser\Node;
use App\Enum\ParserType;
use App\Factory\RedisFactory;
use App\Manager\Object\FileManager;
use App\Model\ParsedFile;
use App\Model\ParsedNode;
use App\Model\ParserRequest;
use App\Converter\EntityConverter;
use App\Service\DownloadService;
use App\Service\FileCache;
use App\Service\ParserService;
use App\Utils\StringHelper;
use Doctrine\Common\Util\Debug;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Stopwatch\Stopwatch;

class TestController extends \App\Controller\Api\Base\Controller
{
    /**
     * @Route("/tester/parser_test/{parser}/{test}", name="app_parser_test_function")
     * @throws \ReflectionException
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function parserTest(Request $request, ParserService $parserService)
    {
        $test = $request->get('test');
        $parserName = $request->get('parser');
        $stopwatch = new Stopwatch();
        $stopwatch->start($test);

        $parserRequest = new ParserRequest();
        $parserRequest->setCurrentNode(
            (new ParsedNode())
        );

        $parsedFile = new ParsedFile();
        $parser = $parserService->loadParser($parserName, $this->getUser());

        switch ($parserName) {
            case ParserType::Boards4chan:
                switch ($test) {
                    case 'load_boards_list':
                        $parser->getBoardsListData($parserRequest);
                        break;

                    case 'load_galleries':
                        $parserRequest->getCurrentNode()->setUrl('https://boards.4chan.org/hc/catalog');
                        $parser->getBoardData($parserRequest);
                        break;


                    case 'load_gallery':
                        $parserRequest->currentNode->url = 'http://boards.4chan.org/s/thread/19147511';
                        $parser->getGalleryData($parserRequest);
                        break;

                }
                break;

            case ParserType::Reddit:
                switch ($test) {
                    case 'load_subreddits':
                        $parser->getBoardsListData($parserRequest);
                        break;

                    case 'load_subreddit':
                        $parserRequest->currentNode->url = '/r/Boobies';
                        $parser->getBoardData($parserRequest);
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
                switch ($test) {
                    case 'load_users': // load users
                        $parserRequest->sorting = ['page' => 0];
                        $parser->loadOwnersList($parserRequest);
                        break;

                    case 'load_users_boards': // load user boards
                        $parserRequest->name = 'Jasondayfap83';
                        $parserRequest->identifier = 1481674;
                        $parser->getBoardData($parserRequest);
                        break;

                    case 'load_board_galleries': // load board galleries
                        $parserRequest->currentNode->url = 'https://www.imagefap.com/showfavorites.php?userid=1613432&folderid=3023725';
                        $parser->getBoardData($parserRequest);
                        break;

                    case 'load_gallery':
                        $parserRequest->currentNode->imagesNo = 227;
                        $parserRequest->currentNode->url = 'https://www.imagefap.com/pictures/4863376/Black-Babe---White-Cock-002';
                        $parser->getGalleryData($parserRequest);
                        break;

                    case 'load_file_data':
                        $parserRequest->currentNode->url = 'https://www.imagefap.com/photo/2045072831/?pgid=&gid=3121356&page=0&idx=27';
                        $parser->getFileData($parsedFile);
                        break;
                }
                break;

            case ParserType::HentaiFoundry:
                switch($test) {
                    case 'load_board_data':
                        $parser->getBoardData($parserRequest);
                        break;

                    case 'load_gallery':
                        $parserRequest->currentNode->url = 'pictures/user/a-rimbaud';
                        $parser->getGalleryData($parserRequest);
                        break;

                    case 'load_file_data':
                        $parserRequest->currentNode->url = 'pictures/user/sabudenego/733826/Zelda-Zelda-BotW';
                        $parser->getFileData($parsedFile);
                        break;
                }
                break;
        }

        if ($parsedFile->url) {
            var_dump($parsedFile);
        } else {
            var_dump($parserRequest);
        }

        $event = $stopwatch->stop($test);

        die("SCRIPT EXECUTION TIME: ".($event->getDuration()/1000)."s");
    }

    /**
     * @Route("/tester/app/download_files", name="app_test_api_download_files", methods={"GET"})
     * @throws \ReflectionException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function downloadTest(Request $request, DownloadService $downloadService, FileManager $fileManager)
    {
        $filesForDownload = $fileManager->getQueuedFiles(6);

        if ($filesForDownload) {
            $downloadedFiles = $downloadService->downloadQueuedParserFiles($filesForDownload);
            $fileManager->updateDownloadedFiles($downloadedFiles);

            var_dump(['success' => count($downloadedFiles)]);
        }

        return new \Symfony\Component\HttpFoundation\Response('TEST_DONE.');
    }

    /**
     * @Route("/tester/app/download_file/{fileId}", name="app_test_api_download_files", methods={"GET"})
     * @throws \ReflectionException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function downloadSingleFileTest(Request $request, DownloadService $downloadService, FileManager $fileManager)
    {
        $file = $fileManager->get($request->get('fileId'));
        $file = $downloadService->downloadFileByEntity($file, $this->getUser());

        Debug::dump($file);

        return new \Symfony\Component\HttpFoundation\Response('TEST_DONE.');
    }

    /**
     * @Route("/tester/app/{test}", name="app_funct_test_function", methods={"GET"})
     * @throws \ReflectionException
     */
    public function functionallityTest(Request $request)
    {
        $test = $request->get('test');

        switch ($test) {
            case 'entity_converter':
                $board = new Node();
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
        $user = $this->getUser();
        $cache = new FileCache($user);

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

            case 'redis_test':
                $randomString1 = StringHelper::randomStr(28);
                $randomString2 = StringHelper::randomStr(30);

                var_dump($randomString1);
                echo "<br/>";
                var_dump($randomString2);
                echo "<br/>";

                $redis = (new RedisFactory())->initializeConnection();
                $redis->set('test_key_1', $randomString1); echo "<br/>";
                sleep(2);
                var_dump($redis->get('test_key_1')); echo "<br/>";
                $redis->expire('test_key_1', 1);
                usleep(200);
                var_dump("AFTER 200MS: ");
                var_dump($redis->get('test_key_1')); echo "<br/>";
                sleep(1);
                var_dump($redis->get('test_key_1')); echo "<br/>";


                echo "<br/>";

                break;
        }

        return new \Symfony\Component\HttpFoundation\Response('TEST_DONE');
    }
}