<?php

namespace App\Utils;

class TestsHelper
{
    public static $testAdminUser = [
        'username' => 'stud3nt',
        'password' => '1234567890'
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

    public static function generateParserRequestArray()
    {
        return [
            'actionName' => null,
            'apiToken' => "oNX6aajXAl404gbuC5DMhawmJ8v877Jb",
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
}