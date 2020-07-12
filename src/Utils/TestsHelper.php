<?php

namespace App\Utils;

use App\Enum\ParserType;
use App\Enum\StatusCode;

class TestsHelper
{
    public static $testAdminUser = [
        'username' => 'stud3nt',
        'password' => '1234567890'
    ];

    public static $sampleParserFiles = [
        [
            'parser' => ParserType::HentaiFoundry,
            'fileUrl' => 'https://pictures.hentai-foundry.com/0/0formant0/805018/0formant0-805018-The_Watchers_part_of_The_Trial.jpg',
            'identifier' => '805018',
            'description' => "TEST IMAGE - ".ParserType::Reddit,
            'name' => '0formant0-805018-The_Watchers_part_of_The_Trial',
            'thumbnail' => 'https://thumbs.hentai-foundry.com/thumb.php?pid=805018&size=350',
            'url' => 'http://www.hentai-foundry.com/pictures/user/0formant0/805018/The-Watchers-part-of-The-Trial',
            'parentNode' => [
                'id' => 713
            ]
        ],
        [
            'parser' => ParserType::Reddit,
            'fileUrl' => 'https://preview.redd.it/hgo298b4ux551.jpg?auto=webp&s=7e52902d0b0b5b7587688525f24b578334817745',
            'identifier' => '3zuCb_fuYhpn0y-2IpKyy5VQjC3FGRJKCdB47KiMmLQ',
            'name' => 'hgo298b4ux551',
            'thumbnail' => 'https://preview.redd.it/hgo298b4ux551.jpg?width=216&crop=smart&auto=webp&s=a24cbd62ab01d528370e4af13f3405af8e5dcaaf',
            'url' => 'https://preview.redd.it/hgo298b4ux551.jpg?auto=webp&s=7e52902d0b0b5b7587688525f24b578334817745',
            'parentNode' => [
                'id' => 889
            ]
        ],
        [
            'parser' => ParserType::Imagefap,
            'fileUrl' => 'https://cdn.imagefap.com/images/full/68/182/1820504028.jpg?end=1593768627&secure=02798946142165c2c3022',
            'identifier' => '1820504028',
            'name' => '1820504028',
            'thumbnail' => 'https://cdn.imagefap.com/images/thumb/68/182/1820504028.jpg?end=1593771771&secure=03cfbf8ff03e9563cd0be',
            'url' => 'https://www.imagefap.com/photo/1820504028/?pgid=&gid=8855629&page=0&idx=15',
            'parentNode' => [
                'id' => 321
            ]
        ]
    ];

    public static function generateNodeArray(): array
    {
        return [
            'name' => "REDDIT: BOARDS LIST",
            'label' => null,
            'identifier' => '',
            'parser' => "reddit",
            'level' => "boards_list",
            'description' => null,
            'personalDescription' => null,
            'url' => null,
            'rating' => 0,
            'personalRating' => 0,
            'imagesNo' => 0,
            'commentsNo' => 0,
            'thumbnails' => [],
            'localThumbnails' => [],
            'statuses' => [],
            'noImage' => false,
            'queued' => false,
            'blocked' => false,
            'favorited' => false,
            'finished' => false,
            'expirationTime' => 0,
            'lastViewedAt' => '---',
            'allowCategory' => false,
            'allowTags' => false,
            'category' => null,
            'tags' => [],
            'settings' => [
                'id' => null,
                'prefixType' => null,
                'prefix' => null,
                'sufixType' => null,
                'sufix' => null,
                'folderType' => null,
                'folder' => null,
                'maxWidth' => null,
                'maxHeight' => null,
                'maxSize' => null,
                'minLength' => null,
                'sizeUnit' => null,
            ]
        ];
    }

    public static function generatePaginationArray(): array
    {
        return [
            'active' => false,
            'currentPage' => 1,
            'currentLetter' => "A",
            'mode' => "numbers",
            'totalPages' => 1,
            'pageShift' => 0,
            'packageStep' => 100,
            'minPackage' => 1,
            'maxPackage' => 10,
            'currentPackage' => 1,
            'packageSize' => 100,
            'selectors' => null
        ];
    }

    public static function generateCategoriesArray(array $categories = []): array
    {
        $categoriesArray = [];

        if ($categories) {
            $x = random_int(1, 1000);

            foreach ($categories as $category) {
                $categoriesArray[] = [
                    'id' => $x,
                    'name' => $category,
                    'label' => null,
                    'symbol' => StringHelper::basicCharactersOnly($category),
                    'active' => true
                ];

                $x++;
            }
        }

        return $categoriesArray;
    }

    public static function generateTagsArray(array $tags = []): array
    {
        $tagsArray = [];

        if ($tags) {
            $x = random_int(1, 1000);

            foreach ($tags as $tag) {
                $tagsArray[] = [
                    'id' => $x,
                    'name' => mb_strtoupper($tag)
                ];

                $x++;
            }
        }

        return $tagsArray;
    }

    public static function generateParserRequestArray(): array
    {
        return [
            'actionName' => null,
            'apiToken' => StringHelper::randomStr(32),
            'currentNode' => self::generateNodeArray(),
            'parsedNodes' => [],
            'nextNode' => null,
            'previousNode' => null,
            'breadcrumbNodes' => [],
            'files' => [],
            'fileData' => null,
            'pagination' => self::generatePaginationArray(),
            'tokens' => [
                'before' => null,
                'after' => null
            ],
            'ignoreCache' => false,
            'cachedData' => false,
            'sorting' => [
                'submig' => null,
                'page' => 0
            ],
            'status' => [
                'code' => 202,
                'progress' => 100,
                'description' => null
            ],
            'requestIdentifier' => "reddit_boards_list_oNX6aajXAl404gbuC5DMhawmJ8v877Jb",
            'categories' => self::generateCategoriesArray([
                'BBW', 'Big Tits', 'Blowjob', 'Chubby', 'Futanari', 'Gangbang', 'Hairy'
            ]),
            'tags' => self::generateTagsArray([
                'BAD DRAGON', 'BBW', 'BIG TITS', 'HAIRY', 'CHUBBY', 'CURVY', 'DILDO', 'FAT', 'FIT', 'GANGBANG'
            ])
        ];
    }

    public static function generateStatusArray(): array
    {
        return [
            "code" => StatusCode::NoEffect,
            "progress" => 0,
            "description" => null
        ];
    }

    public static function generateFileArray(): array
    {
        return [
            "description" => "TEST IMAGE - ".ParserType::Reddit,
            "dimensionRatio" => 0.75,
            "domain" => null,
            "downloadedAt" => null,
            "extension" => "jpg",
            "fileUrl" => "",
            "height" => 3264,
            "htmlPreview" => null,
            "icon" => "reddit",
            "identifier" => "3zuCb_fuYhpn0y-2IpKyy5VQjC3FGRJKCdB47KiMmLQ",
            "length" => 0,
            "localThumbnail" => null,
            "localUrl" => null,
            "mimeType" => "image/jpeg",
            "miniPreview" => false,
            "name" => "hgo298b4ux551",
            "parentNode" => [],
            "parser" => ParserType::Reddit,
            "previewUrl" => null,
            "rating" => 48,
            "size" => 405005,
            "status" => self::generateStatusArray(),
            "statuses" => ["waiting", "queued"],
            "textSize" => "0 B",
            "thumbnail" => "https://preview.redd.it/hgo298b4ux551.jpg?width=216&crop=smart&auto=webp&s=a24cbd62ab01d528370e4af13f3405af8e5dcaaf",
            "title" => "TEST IMAGE - ".ParserType::Reddit,
            "type" => "image",
            "uploadedAt" => "2 days ago",
            "url" => "https://preview.redd.it/hgo298b4ux551.jpg?auto=webp&s=7e52902d0b0b5b7587688525f24b578334817745",
            "width" => 2448
        ];
    }
}